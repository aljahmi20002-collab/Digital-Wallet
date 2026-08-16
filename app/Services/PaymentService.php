<?php

namespace App\Services;

use App\Constants\FixPctType;
use App\Data\TransactionData;
use App\Enums\AmountFlow;
use App\Enums\MethodType;
use App\Enums\TrxStatus;
use App\Enums\TrxType;
use App\Exceptions\NotifyErrorException;
use App\Models\DepositMethod;
use App\Models\Transaction as TransactionModel;
use App\Models\Wallet as WalletModel;
use App\Models\WithdrawMethod;
use App\Services\Handlers\WithdrawHandler;
use App\Services\Payment\PaymentGatewayFactory;
use App\Support\WithdrawFieldNormalizer;
use App\Traits\FileManageTrait;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Log;
use Throwable;
use Transaction;
use Wallet;

class PaymentService
{
    use FileManageTrait;

    protected PaymentGatewayFactory $paymentFactory;

    public function __construct(PaymentGatewayFactory $paymentFactory)
    {
        $this->paymentFactory = $paymentFactory;
    }

    /**
     * Handle deposit via payment method.
     *
     * @throws Throwable
     */
    public function depositWithPaymentMethod($paymentMethodId, $amount, $walletId): array
    {
        DB::beginTransaction();

        try {
            $wallet = WalletModel::with('currency')
                ->where('id', $walletId)
                ->where('user_id', auth()->id())
                ->firstOrFail();
            $depositMethod = DepositMethod::findOrFail($paymentMethodId);

            if ($amount <= 0) {
                throw new NotifyErrorException(__('Amount must be greater than zero.'));
            }

            if ($depositMethod->min_deposit > $amount || $depositMethod->max_deposit < $amount) {
                throw new NotifyErrorException(__('Amount must be between :min and :max.', ['min' => $depositMethod->min_deposit, 'max' => $depositMethod->max_deposit]));
            }

            if ($depositMethod->currency !== $wallet->currency->code) {
                throw new NotifyErrorException(__('The selected payment method is not available for this wallet.'));
            }

            $details = $this->calculateTransactionDetails($amount, $depositMethod, (float) $wallet->currency->exchange_rate);

            if ($depositMethod->type === MethodType::MANUAL) {

                $credentials = collect($depositMethod->fields)->map(function ($field) {

                    $credentials = request('credentials');

                    if (isset($credentials[$field['name']]) && is_file($credentials[$field['name']])) {
                        // Handle file upload
                        $field['value'] = self::uploadImage($credentials[$field['name']]);
                    } else {
                        // Handle non-file inputs
                        $field['value'] = $credentials[$field['name']] ?? null;
                    }

                    return $field;
                });

                $details['trxData'] = $credentials->toArray();
            }

            $data = $this->createTransactionData($details, $depositMethod, $wallet, TrxType::DEPOSIT);

            $transaction    = Transaction::create($data);
            $paymentGateway = $this->paymentFactory->getGateway($depositMethod->paymentGateway->code ?? $depositMethod->type->value);

            $redirectUrl = $paymentGateway->deposit($details['payableAmount'], $depositMethod->currency, $transaction->trx_id);

            DB::commit();

            return [$transaction, $redirectUrl];

        } catch (Exception $e) {

            DB::rollBack();
            Log::error('Deposit failed', ['error' => $e->getMessage()]);
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Handle withdrawal process.
     *
     * @throws Throwable
     */
    public function withdrawMoney($withdrawAccount, $wallet, $amount): TransactionModel
    {
        DB::beginTransaction();

        try {
            $withdrawAccount->loadMissing('withdrawMethod.paymentGateway');
            $withdrawMethod = $withdrawAccount->withdrawMethod;
            $wallet         = WalletModel::where('id', $wallet->id)
                ->where('user_id', auth()->id())
                ->lockForUpdate()
                ->firstOrFail();

            if ($amount <= 0) {
                throw new NotifyErrorException(__('Amount must be greater than zero.'));
            }

            if ((int) $withdrawAccount->user_id !== (int) auth()->id()) {
                throw new NotifyErrorException(__('Invalid withdrawal account.'));
            }

            $details = $this->calculateTransactionDetails($amount, $withdrawMethod);

            $details['trxData'] = $withdrawAccount->credentials;

            if ($wallet->balance < $details['payableAmount']) {
                throw new NotifyErrorException(__('Insufficient wallet balance.'));
            }

            // Subtract money from wallet
            Wallet::subtractMoney($wallet, $details['payableAmount']);

            $data        = $this->createTransactionData($details, $withdrawMethod, $wallet, TrxType::WITHDRAW);
            $transaction = Transaction::create($data);

            DB::commit();

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Withdrawal failed', ['error' => $e->getMessage()]);

            if ($e instanceof NotifyErrorException) {
                throw $e;
            }

            throw new NotifyErrorException(__('Withdrawal processing failed. Please try again.'));
        }

        if ($withdrawMethod->type === MethodType::AUTOMATIC) {
            try {
                $this->processAutomaticWithdrawal($transaction, $withdrawMethod, $details);
            } catch (Throwable $e) {
                Log::error('Automatic withdrawal gateway failed', [
                    'transaction_id' => $transaction->trx_id,
                    'gateway'        => $withdrawMethod->paymentGateway?->code,
                    'error'          => $e->getMessage(),
                ]);

                $this->refundFailedAutomaticWithdrawal($transaction, $e->getMessage());

                throw new NotifyErrorException(__('Withdrawal processing failed. The deducted amount has been refunded.'));
            }
        }

        // Notify the admin/user
        if ($transaction->processing_type === MethodType::MANUAL) {
            app(WithdrawHandler::class)->handleSubmitted($transaction);
        }

        return $transaction;
    }

    /**
     * @throws Throwable
     */
    public function paymentWithPaymentMethod($paymentMethodCode, $transaction)
    {

        $depositMethod = DepositMethod::getByCode($paymentMethodCode);
        $amount        = $transaction->amount;

        if ($amount <= 0) {
            throw new Exception(__('Amount must be greater than zero.'));
        }
        $paymentGateway = $this->paymentFactory->getGateway($depositMethod->paymentGateway->code ?? $depositMethod->type);

        return $paymentGateway->deposit($transaction->payable_amount, $depositMethod->currency, $transaction->trx_id);
    }

    public function generateToken($trxId, $minutesValid = 30): string
    {
        // Prepare a payload with transaction ID and expiration
        $payload = [
            'trx_id' => $trxId,
            'exp'    => Carbon::now()->addMinutes($minutesValid)->timestamp,
        ];
        // Convert to JSON
        $jsonPayload = json_encode($payload);

        // Base64-encode
        $base64Payload = base64_encode($jsonPayload);

        // Create an HMAC signature using your app key or another secret key
        $secretKey = config('app.key'); // or a separate key from .env
        $signature = hash_hmac('sha256', $base64Payload, $secretKey);

        // Return final token as "base64Payload.signature"
        return $base64Payload.'.'.$signature;
    }

    /**
     * @throws NotifyErrorException
     */
    public function verifyTokenAndGetData($token)
    {
        // 1. Split the token into payload + signature
        [$base64Payload, $signature] = explode('.', $token);

        // 2. Verify the signature
        $secretKey         = config('app.key');
        $expectedSignature = hash_hmac('sha256', $base64Payload, $secretKey);

        if (! hash_equals($expectedSignature, $signature)) {
            throw new NotifyErrorException(__('Invalid or tampered token.'));
        }

        // 3. Decode the payload
        $payloadJson = base64_decode($base64Payload, true);
        $payload     = json_decode($payloadJson, true);

        // 4. Check expiration
        if (isset($payload['exp']) && $payload['exp'] < now()->timestamp) {
            throw new NotifyErrorException(__('Token has expired.'));
        }

        return $payload;

    }

    /**
     * Calculate transaction charges and amounts.
     */
    protected function calculateTransactionDetails($amount, $method, ?float $conversionRate = null)
    {
        $charge = $this->calculateCharge($amount, $method->charge, $method->charge_type);
        $conversionRate ??= $method->conversion_rate;

        $netAmount     = $amount * $conversionRate;
        $payableCharge = $charge * $conversionRate;
        $payableAmount = $netAmount + $payableCharge;

        return compact('amount', 'charge', 'netAmount', 'payableCharge', 'payableAmount');
    }

    /**
     * Helper to calculate charge based on type.
     */
    protected function calculateCharge($amount, $charge, $chargeType)
    {
        return $chargeType === FixPctType::PERCENT ? $amount * $charge / 100 : $charge;
    }

    /**
     * Create transaction data object.
     */
    protected function createTransactionData($details, $method, $wallet, $trxType)
    {
        return new TransactionData(
            user_id: auth()->id(),
            trx_type: $trxType,
            amount: $details['amount'],
            amount_flow: $trxType === TrxType::DEPOSIT ? AmountFlow::PLUS : AmountFlow::MINUS,
            fee: $details['charge'],
            provider: $method->name,
            processing_type: $method->type,
            net_amount: $details['netAmount'],
            payable_amount: $details['payableAmount'],
            payable_currency: $method->currency,
            wallet_reference: $wallet->uuid,
            trx_data: $details['trxData'] ?? null,
            description: __(':type via :method', ['type' => $trxType->value, 'method' => $method->name]),
            status: TrxStatus::PENDING
        );
    }

    private function processAutomaticWithdrawal(TransactionModel $transaction, WithdrawMethod $withdrawMethod, array $details): void
    {
        $gatewayCode = (string) $withdrawMethod->paymentGateway?->code;

        if ($gatewayCode === '') {
            throw new NotifyErrorException(__('Automatic withdrawal gateway is not configured.'));
        }

        $paymentGateway = $this->paymentFactory->getGateway($gatewayCode);

        if (! method_exists($paymentGateway, 'withdraw')) {
            throw new NotifyErrorException(__('This gateway does not support automatic withdrawals.'));
        }

        $credentialValues   = WithdrawFieldNormalizer::values($details['trxData'] ?? []);
        $withdrawCredential = count($credentialValues) === 1 ? reset($credentialValues) : $credentialValues;
        $response           = $paymentGateway->withdraw($details['netAmount'], $withdrawMethod->currency, $transaction->trx_id, $withdrawCredential);

        if (is_array($response)) {
            $status = $this->gatewayWithdrawalStatus($response);

            try {
                $transaction = $this->recordAutomaticWithdrawalResponse($transaction, $gatewayCode, $response);
                $this->applyAutomaticWithdrawalStatus($transaction, $gatewayCode, $response);
            } catch (Throwable $e) {
                if ($this->isFailedGatewayWithdrawalStatus($status)) {
                    throw $e;
                }

                Log::error('Automatic withdrawal response handling failed after gateway accepted request', [
                    'transaction_id' => $transaction->trx_id,
                    'gateway'        => $gatewayCode,
                    'status'         => $status,
                    'error'          => $e->getMessage(),
                ]);
            }
        }
    }

    private function recordAutomaticWithdrawalResponse(TransactionModel $transaction, string $gatewayCode, array $response): TransactionModel
    {
        $reference = data_get($response, 'reference')
            ?? data_get($response, 'transfer.reference')
            ?? data_get($response, 'data.reference');

        $trxData                           = $transaction->fresh()->trx_data ?? [];
        $trxData[$gatewayCode.'_withdraw'] = $response;

        $updates = ['trx_data' => $trxData];

        if (is_string($reference) && $reference !== '') {
            $updates['trx_reference'] = $reference;
        }

        $status = $this->gatewayWithdrawalStatus($response);
        if ($status === 'otp') {
            $updates['remarks'] = __('Gateway transfer requires OTP finalization.');
        }

        $transaction->update($updates);

        return $transaction->refresh();
    }

    private function applyAutomaticWithdrawalStatus(TransactionModel $transaction, string $gatewayCode, array $response): void
    {
        $status = $this->gatewayWithdrawalStatus($response);

        if ($status === null || $transaction->status !== TrxStatus::PENDING) {
            return;
        }

        if (in_array($status, ['success', 'successful', 'completed', 'complete', 'paid'], true)) {
            Transaction::completeTransaction(
                $transaction->trx_id,
                __(':gateway withdrawal completed.', ['gateway' => ucfirst($gatewayCode)])
            );

            return;
        }

        if ($this->isFailedGatewayWithdrawalStatus($status)) {
            Transaction::cancelTransaction(
                $transaction->trx_id,
                __(':gateway withdrawal failed with status: :status', ['gateway' => ucfirst($gatewayCode), 'status' => $status]),
                true
            );
        }
    }

    private function gatewayWithdrawalStatus(array $response): ?string
    {
        $status = data_get($response, 'status')
            ?? data_get($response, 'transfer.status')
            ?? data_get($response, 'data.status');

        if (! is_scalar($status)) {
            return null;
        }

        $status = strtolower(trim((string) $status));

        return $status !== '' ? $status : null;
    }

    private function refundFailedAutomaticWithdrawal(TransactionModel $transaction, string $reason): void
    {
        try {
            if ($transaction->fresh()?->status === TrxStatus::PENDING) {
                Transaction::cancelTransaction($transaction->trx_id, $reason, true);
            }
        } catch (Throwable $refundException) {
            Log::critical('Automatic withdrawal refund failed', [
                'transaction_id' => $transaction->trx_id,
                'error'          => $refundException->getMessage(),
            ]);
        }
    }

    private function isFailedGatewayWithdrawalStatus(?string $status): bool
    {
        return in_array($status, ['failed', 'fail', 'reversed', 'rejected', 'blocked', 'abandoned', 'canceled', 'cancelled'], true);
    }
}
