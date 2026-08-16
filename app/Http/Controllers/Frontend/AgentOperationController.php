<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\AgentStatus;
use App\Exceptions\NotifyErrorException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Agent\AgentCashInRequest;
use App\Http\Requests\Agent\AgentCashOutOtpRequest;
use App\Http\Requests\Agent\AgentCashOutRequest;
use App\Http\Requests\Agent\CustomerQrCashOutRequest;
use App\Models\Agent;
use App\Models\AgentOperation;
use App\Services\AgentOperationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Throwable;

class AgentOperationController extends Controller
{
    public function __construct(private readonly AgentOperationService $agentOperations) {}

    public function cashIn(AgentCashInRequest $request): RedirectResponse
    {
        return $this->process(
            callback: fn () => $this->agentOperations->cashIn($request->user(), $request->validated()),
            successMessage: __('Cash-in completed successfully.')
        );
    }

    public function cashOut(AgentCashOutRequest $request): RedirectResponse
    {
        return $this->process(
            callback: fn () => $this->agentOperations->cashOut($request->user(), $request->validated()),
            successMessage: __('Cash-out completed successfully.')
        );
    }

    public function sendCashOutOtp(AgentCashOutOtpRequest $request): RedirectResponse
    {
        return $this->process(
            callback: fn () => $this->agentOperations->sendCashOutOtp($request->user(), $request->validated()),
            successMessage: __('Customer OTP sent. Enter the code to complete assisted cash-out.'),
            input: $request->except('customer_otp')
        );
    }

    public function showQrCashOut(string $token): View
    {
        $agent = $this->qrAgent($token);

        $currencyIds = $agent->supportedCurrencyIds();
        $wallets     = request()->user()
            ->activeWallets()
            ->whereIn('currency_id', $currencyIds)
            ->values();

        return view('frontend.user.agent.qr_cash_out', compact('agent', 'wallets'));
    }

    public function storeQrCashOut(CustomerQrCashOutRequest $request, string $token): RedirectResponse
    {
        $agent = $this->qrAgent($token);

        return $this->process(
            callback: fn () => $this->agentOperations->customerQrCashOut($request->user(), $agent, $request->validated()),
            successMessage: __('Cash-out request confirmed. Show the reference to the agent and collect cash.')
        );
    }

    public function markCashPaid(AgentOperation $operation): RedirectResponse
    {
        return $this->process(
            callback: fn () => $this->agentOperations->markQrCashOutPaid(request()->user(), $operation),
            successMessage: __('Cash handover marked as paid.')
        );
    }

    private function process(callable $callback, string $successMessage, ?array $input = null): RedirectResponse
    {
        try {
            $callback();
            notifyEvs('success', $successMessage);
        } catch (NotifyErrorException $e) {
            notifyEvs('error', $e->getMessage());
        } catch (Throwable $e) {
            report($e);
            notifyEvs('error', __('Agent operation failed. Please try again.'));
        }

        $response = back();

        return $input === null ? $response : $response->withInput($input);
    }

    private function qrAgent(string $token): Agent
    {
        return Agent::query()
            ->where('qr_token', $token)
            ->where('qr_enabled', true)
            ->where('status', AgentStatus::APPROVED)
            ->with(['user', 'currency', 'supportedCurrencies'])
            ->firstOrFail();
    }
}
