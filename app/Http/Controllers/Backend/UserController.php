<?php

namespace App\Http\Controllers\Backend;

use App\Enums\AgentStatus;
use App\Enums\TrxStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Events\TransactionUpdated;
use App\Models\Agent;
use App\Models\Currency;
use App\Models\Merchant;
use App\Models\User;
use App\Models\UserFeature;
use App\Services\AgentService;
use App\Traits\FileManageTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends BaseController
{
    use FileManageTrait;

    public static function permissions(): array
    {
        return [
            'index|activeUser|suspendedUser|unverifiedUser' => 'user-list',
            'store'                                         => 'user-create',
            'destroy'                                       => 'user-delete',
            'transactionStats'                              => 'user-list',
            'convertToMerchant'                             => 'user-manage',
            'convertToAgent'                                => 'user-manage',
        ];
    }

    /**
     * Display a listing of the users with filters.
     */
    public function index(Request $request)
    {

        $title = __('Users List');
        $users = User::query()->filter($request)->latest()->paginate(10)->withQueryString();

        return view('backend.user.index', compact('users', 'title'));
    }

    /**
     * Show active users.
     */
    public function activeUser(Request $request)
    {
        $title = __('Active Users');
        $users = User::query()->filter($request)->where('status', UserStatus::ACTIVE)->latest()->paginate(10)->withQueryString();

        $statusFilter = false;

        return view('backend.user.index', compact('users', 'title', 'statusFilter'));
    }

    public function suspendedUser(Request $request)
    {
        $title = __('Suspended Users');

        $users = User::query()->filter($request)->where('status', UserStatus::INACTIVE)->latest()->paginate(10)->withQueryString();

        return view('backend.user.index', compact('users', 'title'));
    }

    public function unverifiedUser(Request $request)
    {
        $title = __('Unverified Users');
        $users = User::query()->whereNull('email_verified_at')->filter($request)->latest()->paginate(10)->withQueryString();

        return view('backend.user.index', compact('users', 'title'));
    }

    public function kycUnverifiedUser(Request $request)
    {
        $title = __('KYC Unverified Users');
        $users = User::query()->kycUnverified()->filter($request)->latest()->paginate(10)->withQueryString();

        return view('backend.user.index', compact('users', 'title'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Step 1: Validate incoming request
        $validated = $request->validate([
            'avatar'     => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'username'   => 'required|string|max:255|unique:users,username',
            'country'    => 'required|string|size:2',
            'phone'      => 'required|string',
            'email'      => 'required|email|unique:users,email',
            'password'   => 'required|same:confirm_password',
            'status'     => 'nullable|boolean',
            'role'       => 'required|string',
        ]);

        // Step 2: Process phone number
        $countryCode = strtoupper((string) $validated['country']);
        $countryData = getCountryByCode($countryCode);
        if (! $countryData) {
            return back()
                ->withErrors(['country' => __('The selected country is invalid.')])
                ->withInput();
        }
        $formattedPhone = formatPhone((string) ($countryData['dial_code'] ?? ''), $validated['phone']);

        // Step 3: Handle avatar upload if provided
        $avatarPath = $request->hasFile('avatar')
            ? $this->uploadImage($request->file('avatar'))
            : null;

        // Step 4: Create user with transformed data
        $user = User::create([
            'avatar'     => $avatarPath,
            'first_name' => $validated['first_name'],
            'last_name'  => $validated['last_name'],
            'username'   => $validated['username'],
            'country'    => $countryCode,
            'phone'      => $formattedPhone,
            'email'      => $validated['email'],
            'password'   => Hash::make($validated['password']),
            'role'       => $validated['role'],
            'status'     => $request->boolean('status') ? UserStatus::ACTIVE : UserStatus::INACTIVE,
        ]);

        // Step 5: Sync features and fire event
        UserFeature::syncWithConfigForUser($user->id);
        event(new TransactionUpdated($user));

        // Step 6: Notify and redirect
        notifyEvs('success', __('User created successfully'));

        return redirect()->route('admin.user.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $filesToDelete = [];
        $userSnapshot  = null;

        try {
            DB::transaction(function () use ($id, &$filesToDelete, &$userSnapshot) {
                $user         = User::findOrFail($id);
                $userSnapshot = [
                    'name'      => $user->name,
                    'username'  => $user->username,
                    'email'     => $user->email,
                    'user_type' => $user->user_type ?? 'user',
                ];

                // Collect uploaded-file paths from related rows BEFORE we delete
                // anything. DB-level cascades don't fire Eloquent events, so
                // file paths inside cascaded rows would otherwise be lost
                // and the files would orphan on disk.
                $filesToDelete = $this->collectUserFilePaths($user);

                // ── Manual cleanup for tables WITHOUT cascade FKs ──────────

                // Polymorphic: Laravel notifications
                DB::table('notifications')
                    ->where('notifiable_type', User::class)
                    ->where('notifiable_id', $id)
                    ->delete();

                // Polymorphic: per-user notification channel preferences
                DB::table('notification_preferences')
                    ->where('notifiable_type', User::class)
                    ->where('notifiable_id', $id)
                    ->delete();

                // Polymorphic: Sanctum API tokens — leaving these behind is a
                // security hole, since a leaked token would still authenticate
                // after the account is gone.
                DB::table('personal_access_tokens')
                    ->where('tokenable_type', User::class)
                    ->where('tokenable_id', $id)
                    ->delete();

                // Auth sessions for the deleted user
                DB::table('sessions')->where('user_id', $id)->delete();

                // login_activities (nullable user_id, no FK constraint)
                DB::table('login_activities')->where('user_id', $id)->delete();

                // Support tickets + messages (FK is NO ACTION, not cascade)
                $ticketIds = DB::table('tickets')->where('user_id', $id)->pluck('id');
                if ($ticketIds->isNotEmpty()) {
                    DB::table('messages')->whereIn('ticket_id', $ticketIds)->delete();
                    DB::table('tickets')->where('user_id', $id)->delete();
                }

                // Wallets (FK is NO ACTION, not cascade)
                DB::table('wallets')->where('user_id', $id)->delete();

                // Trigger DB cascades for all other related tables.
                $user->delete();
            });

            // ── Delete files AFTER the transaction commits ─────────────────
            // Doing this outside the transaction means a storage failure
            // can't roll back a successful row deletion (the files are
            // already orphaned anyway at that point).
            $fileResult = $this->deleteCollectedFiles($filesToDelete);

            Log::info('User deleted successfully', [
                'deleted_user_id'  => $id,
                'deleted_by_admin' => auth()->id(),
                'deleted_at'       => now(),
                'user_data'        => $userSnapshot,
                'files_attempted'  => $fileResult['attempted'],
                'files_deleted'    => $fileResult['deleted'],
            ]);

            notifyEvs('success', __('User deleted successfully'));

            return redirect()->route('admin.user.index');
        } catch (\Exception $e) {
            Log::error('Failed to delete user', [
                'user_id'  => $id,
                'admin_id' => auth()->id(),
                'error'    => $e->getMessage(),
                'trace'    => $e->getTraceAsString(),
            ]);

            notifyEvs('error', __('Failed to delete user. Please try again.'));

            return redirect()->back();
        }
    }

    /**
     * Collect every storage-path string attached to the user across the
     * tables that cascade-delete with the user row.
     *
     * @return list<string>
     */
    private function collectUserFilePaths(User $user): array
    {
        $paths = [];

        // 1. Avatar on the user row itself
        if (! empty($user->avatar)) {
            $paths[] = $user->avatar;
        }

        // 2. KYC submissions — submission_data is a JSON map of label → value
        DB::table('kyc_submissions')
            ->where('user_id', $user->id)
            ->pluck('submission_data')
            ->each(function ($json) use (&$paths) {
                $paths = array_merge($paths, $this->extractStoragePaths($json));
            });

        // 3. Agent logo
        DB::table('agents')
            ->where('user_id', $user->id)
            ->pluck('logo')
            ->filter()
            ->each(function ($logo) use (&$paths) {
                $paths[] = $logo;
            });

        // 4. Merchant business logo
        DB::table('merchants')
            ->where('user_id', $user->id)
            ->pluck('business_logo')
            ->filter()
            ->each(function ($logo) use (&$paths) {
                $paths[] = $logo;
            });

        // 5. Cardholder KYC documents (JSON map)
        DB::table('cardholders')
            ->where('user_id', $user->id)
            ->pluck('kyc_documents')
            ->each(function ($json) use (&$paths) {
                $paths = array_merge($paths, $this->extractStoragePaths($json));
            });

        // 6. Business documents (JSON map)
        DB::table('businesses')
            ->where('user_id', $user->id)
            ->pluck('documents')
            ->each(function ($json) use (&$paths) {
                $paths = array_merge($paths, $this->extractStoragePaths($json));
            });

        // 7. Ticket + message attachments
        $ticketIds = DB::table('tickets')->where('user_id', $user->id)->pluck('id');
        if ($ticketIds->isNotEmpty()) {
            DB::table('tickets')
                ->whereIn('id', $ticketIds)
                ->pluck('attachment')
                ->filter()
                ->each(function ($path) use (&$paths) {
                    $paths[] = $path;
                });

            DB::table('messages')
                ->whereIn('ticket_id', $ticketIds)
                ->pluck('attachment')
                ->filter()
                ->each(function ($path) use (&$paths) {
                    $paths[] = $path;
                });
        }

        // De-dupe + drop blanks.
        return array_values(array_filter(array_unique($paths), fn (string $p): bool => $this->isUserUploadPath($p)));
    }

    /**
     * Run the FileManageTrait deleter over each path and return counts.
     *
     * @param  list<string>                     $paths
     * @return array{attempted:int,deleted:int}
     */
    private function deleteCollectedFiles(array $paths): array
    {
        $attempted = count($paths);
        $deleted   = 0;

        foreach ($paths as $path) {
            try {
                $this->delete($path);
                $deleted++;
            } catch (\Throwable $e) {
                Log::warning('Failed to delete user file', [
                    'path'  => $path,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return ['attempted' => $attempted, 'deleted' => $deleted];
    }

    /**
     * Pull every string value that looks like an upload path out of a JSON
     * blob (decoded recursively).
     *
     * @return list<string>
     */
    private function extractStoragePaths(mixed $json): array
    {
        if (blank($json)) {
            return [];
        }

        $decoded = is_array($json) ? $json : json_decode((string) $json, true);
        if (! is_array($decoded)) {
            return [];
        }

        $paths = [];
        array_walk_recursive($decoded, function ($value) use (&$paths) {
            if (is_string($value) && $this->isUserUploadPath($value)) {
                $paths[] = $value;
            }
        });

        return $paths;
    }

    /**
     * Only delete files that match the FileManageTrait upload convention
     * (`images/YYYY/MM/DD/...` or `files/YYYY/MM/DD/...`). Demo / seeded
     * assets under `general/`, `demo/`, etc. are shared across users and
     * must be left alone.
     */
    private function isUserUploadPath(string $path): bool
    {
        if (blank($path)) {
            return false;
        }

        $normalised = ltrim($path, '/');
        if (str_starts_with($normalised, 'storage/')) {
            $normalised = substr($normalised, 8);
        }

        return str_starts_with($normalised, 'images/')
            || str_starts_with($normalised, 'files/');
    }

    /**
     * Get transaction statistics for a user (used in delete confirmation modal)
     */
    public function transactionStats(string $id)
    {
        try {
            $user = User::findOrFail($id);

            // Get all transactions for this user
            $allTransactions = $user->transactions();

            // Get successful transactions only
            $successfulTransactions = $user->transactions()->where('status', TrxStatus::COMPLETED);

            // Calculate statistics
            $totalTransactions = $allTransactions->count();
            $totalAmount       = $allTransactions->sum('amount');
            $successfulCount   = $successfulTransactions->count();
            $successfulAmount  = $successfulTransactions->sum('amount');

            // Get default currency for formatting
            $currencySymbol = siteCurrency('symbol');

            $decimals = (int) (setting('site_decimal', 2));

            return response()->json([
                'success' => true,
                'data'    => [
                    'total_transactions'      => number_format($totalTransactions),
                    'total_amount'            => $currencySymbol.number_format($totalAmount, $decimals),
                    'successful_transactions' => number_format($successfulCount),
                    'successful_amount'       => $currencySymbol.number_format($successfulAmount, $decimals),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to fetch transaction statistics'),
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Convert a regular user to merchant.
     */
    public function convertToMerchant($id)
    {
        try {
            DB::beginTransaction();

            $user = User::findOrFail($id);

            // Check if user is already a merchant
            if ($user->isMerchant()) {
                notifyEvs('error', __('User is already a merchant'));

                return redirect()->back();
            }

            // Check if user already has a merchant record
            if (Merchant::where('user_id', $user->id)->exists()) {
                notifyEvs('error', __('User already has a merchant account'));

                return redirect()->back();
            }

            // Update user role to merchant
            $user->update(['role' => UserRole::MERCHANT]);

            DB::commit();

            // Log the conversion
            Log::info('User converted to merchant', [
                'user_id'            => $user->id,
                'converted_by_admin' => auth()->id(),
                'converted_at'       => now(),
            ]);

            notifyEvs('success', __('User successfully converted to merchant'));

            return redirect()->back();

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to convert user to merchant', [
                'user_id'  => $id,
                'admin_id' => auth()->id(),
                'error'    => $e->getMessage(),
                'trace'    => $e->getTraceAsString(),
            ]);

            notifyEvs('error', __('Failed to convert user to merchant. Please try again.'));

            return redirect()->back();
        }
    }

    /**
     * Convert a regular user to agent.
     */
    public function convertToAgent($id)
    {
        try {
            DB::beginTransaction();

            $user = User::findOrFail($id);

            if ($user->isAgent()) {
                notifyEvs('error', __('User is already an agent'));

                return redirect()->back();
            }

            if (Agent::where('user_id', $user->id)->exists()) {
                notifyEvs('error', __('User already has an agent account'));

                return redirect()->back();
            }

            $user->update(['role' => UserRole::AGENT]);
            app(AgentService::class)->createDefaultForUser($user, AgentStatus::APPROVED->value);

            DB::commit();

            Log::info('User converted to agent', [
                'user_id'            => $user->id,
                'converted_by_admin' => auth()->id(),
                'converted_at'       => now(),
            ]);

            notifyEvs('success', __('User successfully converted to agent'));

            return redirect()->back();

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to convert user to agent', [
                'user_id'  => $id,
                'admin_id' => auth()->id(),
                'error'    => $e->getMessage(),
                'trace'    => $e->getTraceAsString(),
            ]);

            notifyEvs('error', __('Failed to convert user to agent. Please try again.'));

            return redirect()->back();
        }
    }
}
