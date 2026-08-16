<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend\P2P;

use App\Enums\P2P\OfferStatus;
use App\Enums\P2P\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\P2P\StorePaymentAccountRequest;
use App\Http\Requests\P2P\UpdatePaymentAccountRequest;
use App\Models\P2P\Offer;
use App\Models\P2P\Order;
use App\Models\P2P\PaymentAccount;
use App\Models\P2P\PaymentMethod;
use App\Support\P2PPaymentMethodManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PaymentAccountController extends Controller
{
    public function index(): View
    {
        $userCountryCode = P2PPaymentMethodManager::resolveCountryCode(auth()->user());
        $accounts        = PaymentAccount::query()
            ->with('paymentMethod')
            ->where('user_id', auth()->id())
            ->latest('id')
            ->get();
        $accounts = P2PPaymentMethodManager::sortPaymentAccounts($accounts, $userCountryCode);

        $savedMethodIds = $accounts
            ->pluck('payment_method_id')
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->values()
            ->all();

        $methods = P2PPaymentMethodManager::sortMethods(
            PaymentMethod::query()
                ->where('status', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            $userCountryCode,
            $savedMethodIds
        );

        return view('frontend.user.p2p.payment_accounts.index', compact('accounts', 'methods', 'userCountryCode'));
    }

    public function store(StorePaymentAccountRequest $request): RedirectResponse
    {
        [$method, $accountData] = $this->validatedAccountPayload($request);

        $account = new PaymentAccount;
        $account->fill($accountData);
        $account->user_id = (int) auth()->id();
        $account->setRelation('paymentMethod', $method);
        $account->syncDerivedAttributes($method);
        $account->save();

        notifyEvs('success', __('P2P payment account added successfully.'));

        return redirect()->route('user.p2p.payment-accounts.index');
    }

    public function update(UpdatePaymentAccountRequest $request, PaymentAccount $paymentAccount): RedirectResponse
    {
        $this->authorize('update', $paymentAccount);

        [$method, $accountData] = $this->validatedAccountPayload($request, $paymentAccount);

        $paymentAccount->fill($accountData);
        $paymentAccount->setRelation('paymentMethod', $method);
        $paymentAccount->syncDerivedAttributes($method);
        $paymentAccount->save();

        notifyEvs('success', __('P2P payment account updated successfully.'));

        return back();
    }

    public function destroy(PaymentAccount $paymentAccount): RedirectResponse
    {
        $this->authorize('delete', $paymentAccount);

        $hasOpenOrders = Order::query()
            ->where(function ($query) use ($paymentAccount) {
                $query->where('payer_payment_account_id', $paymentAccount->id)
                    ->orWhere('receiver_payment_account_id', $paymentAccount->id);
            })
            ->whereIn('status', [
                OrderStatus::PENDING->value,
                OrderStatus::PAID->value,
                OrderStatus::DISPUTED->value,
            ])
            ->exists();

        if ($hasOpenOrders) {
            notifyEvs('error', __('This payment account is being used in active trades and cannot be removed right now.'));

            return back();
        }

        $usedBySellAds = Offer::query()
            ->where('user_id', auth()->id())
            ->where('side', 'SELL')
            ->whereIn('status', [OfferStatus::ACTIVE->value, OfferStatus::PAUSED->value])
            ->whereHas('paymentMethods', function ($query) use ($paymentAccount) {
                $query->where('p2p_payment_methods.id', $paymentAccount->payment_method_id);
            })
            ->exists();

        if ($usedBySellAds) {
            notifyEvs('error', __('This payment account is linked to one or more active sell ads. Remove that method from those ads first.'));

            return back();
        }

        $paymentAccount->delete();

        notifyEvs('success', __('P2P payment account removed successfully.'));

        return back();
    }

    private function validatedAccountPayload(Request $request, ?PaymentAccount $paymentAccount = null): array
    {
        $baseValidated = $request->validate([
            'payment_method_id' => [
                'required',
                'integer',
                Rule::exists('p2p_payment_methods', 'id'),
                Rule::unique('p2p_payment_accounts', 'payment_method_id')
                    ->ignore($paymentAccount?->id)
                    ->where(fn ($query) => $query->where('user_id', auth()->id())->whereNull('deleted_at')),
            ],
            'label' => ['nullable', 'string', 'max:191'],
        ]);

        $method = PaymentMethod::query()->findOrFail((int) $baseValidated['payment_method_id']);

        if (! (bool) $method->status) {
            throw ValidationException::withMessages([
                'payment_method_id' => __('The selected payment method is not available right now.'),
            ]);
        }

        $rules = [];
        foreach ($method->normalizedFields() as $field) {
            $key = (string) ($field['key'] ?? '');
            if ($key === '') {
                continue;
            }

            $fieldRules   = [];
            $fieldRules[] = ! empty($field['required']) ? 'required' : 'nullable';

            $type = (string) ($field['type'] ?? 'text');
            if ($type === 'number') {
                $fieldRules[] = 'numeric';
            } elseif ($type === 'file') {
                $fieldRules[] = 'file';
                $fieldRules[] = 'mimes:jpg,jpeg,png,webp,pdf';
                $fieldRules[] = 'max:5120';
            } else {
                $fieldRules[] = 'string';
                $fieldRules[] = 'max:500';
            }

            if ($type === 'select') {
                $options = collect((array) ($field['options'] ?? []))
                    ->map(fn ($option) => trim((string) $option))
                    ->filter(fn (string $option) => $option !== '')
                    ->values()
                    ->all();

                if ($options !== []) {
                    $fieldRules[] = Rule::in($options);
                }
            }

            $rules['field_values.'.$key] = $fieldRules;
        }

        $dynamicValidated = $rules !== []
            ? Validator::make($request->all(), $rules)->validate()
            : [];

        $fieldValues = [];
        foreach ($method->normalizedFields() as $field) {
            $key = (string) ($field['key'] ?? '');
            if ($key === '') {
                continue;
            }

            $type = (string) ($field['type'] ?? 'text');
            if ($type === 'file') {
                if ($request->hasFile('field_values.'.$key)) {
                    $uploadedFile = $request->file('field_values.'.$key);
                    if ($uploadedFile) {
                        $fieldValues[$key] = (string) $uploadedFile->store('p2p/payment-accounts', 'public');
                    }
                }

                continue;
            }

            $value = data_get($dynamicValidated, 'field_values.'.$key, $request->input('field_values.'.$key));
            if ($value === null) {
                continue;
            }

            $value = is_scalar($value) ? trim((string) $value) : '';
            if ($value === '') {
                continue;
            }

            $fieldValues[$key] = $value;
        }

        $account                    = $paymentAccount ?? new PaymentAccount;
        $account->payment_method_id = (int) $method->id;
        $account->label             = trim((string) ($baseValidated['label'] ?? '')) ?: null;
        $account->field_values      = $fieldValues !== [] ? $fieldValues : null;
        $account->setRelation('paymentMethod', $method);
        $account->syncDerivedAttributes($method);

        return [$method, [
            'payment_method_id' => (int) $method->id,
            'label'             => $account->label,
            'field_values'      => $account->field_values,
            'display_name'      => $account->display_name,
            'display_value'     => $account->display_value,
            'account_name'      => $account->account_name,
            'account_number'    => $account->account_number,
            'instructions'      => $account->instructions,
        ]];
    }
}
