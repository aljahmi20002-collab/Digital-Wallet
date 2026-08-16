<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\WithdrawAccount;
use App\Models\WithdrawMethod;
use App\Support\WithdrawFieldNormalizer;
use App\Traits\FileManageTrait;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WithdrawAccountController extends Controller
{
    use FileManageTrait;

    public function index()
    {
        if (request()->ajax()) {
            $walletId = request('wallet_id');

            // Validate and fetch the wallet with only necessary fields
            $withdrawWallet = Wallet::select('id', 'user_id', 'currency_id')
                ->where('id', $walletId)
                ->where('user_id', auth()->id())
                ->with([
                    'currency:id,code', // Load only necessary fields for the related model
                ])
                ->firstOrFail();

            // Fetch withdraw accounts with optimized query
            $withdrawAccounts = WithdrawAccount::select('id', 'user_id', 'name')
                ->with([
                    'withdrawMethod:id,currency', // Load only necessary fields
                ])
                ->where('user_id', auth()->id())
                ->whereHas('withdrawMethod', function ($query) use ($withdrawWallet) {
                    $query->where('currency', $withdrawWallet->currency->code); // Filter by currency
                })
                ->get();

            return response()->json($withdrawAccounts);
        }

        // Fetch withdraw accounts for the view
        $withdrawAccounts = WithdrawAccount::with('withdrawMethod')->where('user_id', auth()->id())->get();

        return view('frontend.user.withdraw.account.index', compact('withdrawAccounts'));
    }

    public function store(Request $request)
    {
        $baseValidated = $request->validate([
            'method_id'    => ['required', Rule::exists('withdraw_methods', 'id')->where('status', true)],
            'account_name' => 'required|string|max:255',
        ]);

        $withdrawMethod = WithdrawMethod::active()->findOrFail($baseValidated['method_id']);

        $request->validate(WithdrawFieldNormalizer::rules($withdrawMethod->fields));

        WithdrawAccount::create([
            'user_id'            => auth()->user()->id,
            'withdraw_method_id' => $baseValidated['method_id'],
            'name'               => $baseValidated['account_name'],
            'credentials'        => $this->buildCredentials($withdrawMethod->fields, $request),
        ]);

        notifyEvs('success', __('Withdraw Account Added Successfully'));

        return redirect()->route('user.withdraw.account.index');
    }

    public function create()
    {
        $withdrawMethods = WithdrawMethod::active()->orderBy('name')->get();

        return view('frontend.user.withdraw.account.create', compact('withdrawMethods'));
    }

    public function edit($id)
    {
        $withdrawAccount = WithdrawAccount::where('user_id', auth()->id())->findOrFail($id);

        return view('frontend.user.withdraw.account.edit', compact('withdrawAccount'));
    }

    public function update(Request $request, $id)
    {
        $withdrawAccount = WithdrawAccount::where('user_id', auth()->id())->findOrFail($id);

        $validated = $request->validate([
            'account_name' => 'required|string|max:255',
        ]);

        $request->validate(WithdrawFieldNormalizer::rules($withdrawAccount->credentials, preserveExistingFiles: true));

        // Update the withdrawal account details.
        $withdrawAccount->update([
            'name'        => $validated['account_name'],
            'credentials' => $this->buildCredentials($withdrawAccount->credentials, $request, true),
        ]);

        notifyEvs('success', __('Withdraw Account Updated Successfully'));

        return redirect()->route('user.withdraw.account.index');
    }

    public function accountInfo($id)
    {
        $withdrawAccount = WithdrawAccount::with(['withdrawMethod'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $data = [
            'min_limit'       => $withdrawAccount->withdrawMethod->min_withdraw,
            'max_limit'       => $withdrawAccount->withdrawMethod->max_withdraw,
            'charge'          => $withdrawAccount->withdrawMethod->charge,
            'charge_type'     => $withdrawAccount->withdrawMethod->charge_type,
            'currency'        => $withdrawAccount->withdrawMethod->currency,
            'processing_time' => $withdrawAccount->withdrawMethod->processing_time,
            'conversion_rate' => $withdrawAccount->withdrawMethod->conversion_rate,
        ];

        return response()->json($data);
    }

    private function buildCredentials(mixed $fieldSchema, Request $request, bool $preserveExistingFiles = false): array
    {
        $inputCredentials = $request->input('credentials', []);
        $fileCredentials  = $request->file('credentials', []);

        return collect(WithdrawFieldNormalizer::normalize($fieldSchema))
            ->map(function (array $field) use ($inputCredentials, $fileCredentials, $preserveExistingFiles) {
                $name = (string) $field['name'];

                if (($field['type'] ?? null) === 'file' && isset($fileCredentials[$name])) {
                    $field['value'] = self::uploadImage(
                        $fileCredentials[$name],
                        $preserveExistingFiles ? ($field['value'] ?? null) : null
                    );
                } elseif (array_key_exists($name, $inputCredentials)) {
                    $field['value'] = $inputCredentials[$name];
                } elseif (! array_key_exists('value', $field)) {
                    $field['value'] = null;
                }

                return $field;
            })
            ->toArray();
    }
}
