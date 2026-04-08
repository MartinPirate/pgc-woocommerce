<?php

final class WC_PaymentGatewayCloud_CallbackHandler
{
    public static function process(WC_Order $order, $callbackResult)
    {
        if ($callbackResult->getResult() == \PaymentGatewayCloud\Client\Callback\Result::RESULT_OK) {
            switch ($callbackResult->getTransactionType()) {
                case \PaymentGatewayCloud\Client\Callback\Result::TYPE_DEBIT:
                case \PaymentGatewayCloud\Client\Callback\Result::TYPE_CAPTURE:
                    $order->payment_complete();
                    break;
                case \PaymentGatewayCloud\Client\Callback\Result::TYPE_VOID:
                    $order->update_status('cancelled', __('Void', 'woocommerce'));
                    break;
                case \PaymentGatewayCloud\Client\Callback\Result::TYPE_PREAUTHORIZE:
                    $order->update_status('on-hold', __('Awaiting capture/void', 'woocommerce'));
                    break;
            }
        } elseif ($callbackResult->getResult() == \PaymentGatewayCloud\Client\Callback\Result::RESULT_ERROR) {
            switch ($callbackResult->getTransactionType()) {
                case \PaymentGatewayCloud\Client\Callback\Result::TYPE_DEBIT:
                case \PaymentGatewayCloud\Client\Callback\Result::TYPE_CAPTURE:
                case \PaymentGatewayCloud\Client\Callback\Result::TYPE_VOID:
                    $order->update_status('failed', __('Error', 'woocommerce'));
                    break;
            }
        }
    }
}
