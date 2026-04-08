<?php

final class WC_PaymentGatewayCloud_TransactionFactory
{
    public static function make(
        WC_PaymentGatewayCloud_CreditCard $gateway,
        WC_Order $order,
        PaymentGatewayCloud\Client\Data\Customer $customer,
        array $extraData
    ) {
        $transactionRequest = (string) $gateway->get_option('transactionRequest');

        switch ($transactionRequest) {
            case 'preauthorize':
                $transaction = new \PaymentGatewayCloud\Client\Transaction\Preauthorize();
                break;
            case 'debit':
            default:
                $transaction = new \PaymentGatewayCloud\Client\Transaction\Debit();
                break;
        }

        $gatewayOrderTransactionId = $gateway->createOrderTransactionId((string) $order->get_id());
        $order->add_meta_data('orderTxId', $gatewayOrderTransactionId, true);
        $order->save_meta_data();

        $transaction->setTransactionId($gatewayOrderTransactionId)
            ->setAmount((float) $order->get_total())
            ->setCurrency($order->get_currency())
            ->setCustomer($customer)
            ->setExtraData($extraData)
            ->setCallbackUrl($gateway->getGatewayCallbackUrl())
            ->setCancelUrl(wc_get_checkout_url())
            ->setSuccessUrl($gateway->getPaymentSuccessUrl($order))
            ->setErrorUrl($gateway->getPaymentErrorUrl($order));

        return $transaction;
    }

    public static function execute(
        WC_PaymentGatewayCloud_CreditCard $gateway,
        PaymentGatewayCloud\Client\Client $client,
        $transaction
    ) {
        switch ((string) $gateway->get_option('transactionRequest')) {
            case 'preauthorize':
                return $client->preauthorize($transaction);
            case 'debit':
            default:
                return $client->debit($transaction);
        }
    }
}
