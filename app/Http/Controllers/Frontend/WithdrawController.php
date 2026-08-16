<?php

namespace App\Http\Controllers\Frontend;

use App\Exceptions\NotifyErrorException;
use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\WithdrawAccount;
use App\Models\WithdrawMethod;
use App\Models\WithdrawSchedule;
use Exception;
use Illuminate\Http\Request;
use Payment;

class WithdrawController extends Controller
{
    public function create()
    {
        $isWithdrawEnabledToday = WithdrawSchedule::isWithdrawEnabledToday();

        $withdrawOffDays = WithdrawSchedule::where('status', false)->pluck('day')->toArray();

        $wallets = auth()->user()->wallets()->where('balance', '>', 0)->get();

        return view('frontend.user.withdraw.create', compact('wallets', 'isWithdrawEnabledToday', 'withdrawOffDays'));
    }

    /**
     * @throws Exception
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:withdraw_accounts,id',
            'wallet_id'  => 'required|exists:wallets,id',
            'amount'     => 'required|numeric|min:1',
        ]);

        if (! WithdrawSchedule::isWithdrawEnabledToday()) {
            throw new NotifyErrorException('Withdrawals are not enabled for today.');
        }

        $account = WithdrawAccount::with(['withdrawMethod'])
            ->where('user_id', auth()->id())
            ->findOrFail($validated['account_id']);
        $wallet = Wallet::where('user_id', auth()->id())->findOrFail($validated['wallet_id']);

        $amount = $validated['amount'];

        if ($account->withdrawMethod->currency !== $wallet->currency->code) {
            throw new NotifyErrorException('Withdrawal account currency does not match the selected wallet.');
        }

        if (! $account->withdrawMethod->isWithinLimits($amount)) {
            throw new NotifyErrorException('Amount is not within the allowed limits.');
        }

        Payment::withdrawMoney($account, $wallet, $amount);

        notifyEvs('success', __('Withdrawal Requested and will be processed shortly.'));

        return redirect()->route('user.transaction.index');

    }

    public function credentialsFields($method_id)
    {

        $method = WithdrawMethod::active()->findOrFail($method_id);

        $view = view('frontend.user.withdraw.partials.credentials_fields', compact('method'));

        return response()->json(['html' => $view->render(), 'method_name' => $method->name]);

    }
}
