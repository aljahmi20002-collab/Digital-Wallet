<?php

namespace App\Services\Payment;

use App\Services\Payment\Airtel\AirtelPaymentGateway;
use App\Services\Payment\Binance\BinancePaymentGateway;
use App\Services\Payment\Bitnob\BitnobPaymentGateway;
use App\Services\Payment\Bitpayserver\BitpayserverPaymentGateway;
use App\Services\Payment\Blockchain\BlockchainPaymentGateway;
use App\Services\Payment\Blockio\BlockioPaymentGateway;
use App\Services\Payment\Cashmaal\CashmaalPaymentGateway;
use App\Services\Payment\Coinbase\CoinbasePaymentGateway;
use App\Services\Payment\Coingate\CoingatePaymentGateway;
use App\Services\Payment\Coinpayments\CoinpaymentsPaymentGateway;
use App\Services\Payment\Cryptomus\CryptomusPaymentGateway;
use App\Services\Payment\Flutterwave\FlutterwavePaymentGateway;
use App\Services\Payment\Instamojo\InstamojoPaymentGateway;
use App\Services\Payment\Mollie\MolliePaymentGateway;
use App\Services\Payment\Moneroo\MonerooPaymentGateway;
use App\Services\Payment\MTN\MTNPaymentGateway;
use App\Services\Payment\Nowpayments\NowpaymentsPaymentGateway;
use App\Services\Payment\Paymob\PaymobPaymentGateway;
use App\Services\Payment\Paypal\PaypalPaymentGateway;
use App\Services\Payment\Paystack\PaystackPaymentGateway;
use App\Services\Payment\Razorpay\RazorpayPaymentGateway;
use App\Services\Payment\Stripe\StripePaymentGateway;
use App\Services\Payment\StroWallet\StroWalletPaymentGateway;
use App\Services\Payment\Twocheckout\TwocheckoutPaymentGateway;
use App\Services\Payment\Voguepay\VoguepayPaymentGateway;
use Exception;
use Illuminate\Support\Facades\App;

class PaymentGatewayFactory
{
    /**
     * Create an instance of a payment gateway.
     *
     *
     * @throws Exception
     */
    public function getGateway(string $gatewayCode)
    {
        return match ($gatewayCode) {
            // Existing gateways
            'paypal'       => App::make(PaypalPaymentGateway::class),
            'stripe'       => App::make(StripePaymentGateway::class),
            'mollie'       => App::make(MolliePaymentGateway::class),
            'coinbase'     => App::make(CoinbasePaymentGateway::class),
            'paystack'     => App::make(PaystackPaymentGateway::class),
            'paymob'       => App::make(PaymobPaymentGateway::class),
            'flutterwave'  => App::make(FlutterwavePaymentGateway::class),
            'cryptomus'    => App::make(CryptomusPaymentGateway::class),
            'manual'       => App::make(ManualPaymentSystem::class),
            'moneroo'      => App::make(MonerooPaymentGateway::class),
            'strowallet'   => App::make(StroWalletPaymentGateway::class),
            'voguepay'     => App::make(VoguepayPaymentGateway::class),
            'twocheckout'  => App::make(TwocheckoutPaymentGateway::class),
            'razorpay'     => App::make(RazorpayPaymentGateway::class),
            'nowpayments'  => App::make(NowpaymentsPaymentGateway::class),
            'mtn'          => App::make(MTNPaymentGateway::class),
            'instamojo'    => App::make(InstamojoPaymentGateway::class),
            'coinpayments' => App::make(CoinpaymentsPaymentGateway::class),
            'coingate'     => App::make(CoingatePaymentGateway::class),
            'cashmaal'     => App::make(CashmaalPaymentGateway::class),
            'bitpayserver' => App::make(BitpayserverPaymentGateway::class),
            'blockio'      => App::make(BlockioPaymentGateway::class),
            'blockchain'   => App::make(BlockchainPaymentGateway::class),
            'airtel'       => App::make(AirtelPaymentGateway::class),
            'binance'      => App::make(BinancePaymentGateway::class),
            'bitnob'       => App::make(BitnobPaymentGateway::class),

            default => throw new Exception(sprintf('Unsupported payment gateway: %s', $gatewayCode)),
        };
    }
}
