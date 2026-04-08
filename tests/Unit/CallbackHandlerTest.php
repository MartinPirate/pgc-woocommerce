<?php

namespace PgcWooCommerce\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class CallbackHandlerTest extends TestCase
{
    public function testItMarksDebitCallbacksAsPaid(): void
    {
        $order = new \WC_Order(51);
        $callbackResult = new class {
            public function getResult()
            {
                return \PaymentGatewayCloud\Client\Callback\Result::RESULT_OK;
            }

            public function getTransactionType()
            {
                return \PaymentGatewayCloud\Client\Callback\Result::TYPE_DEBIT;
            }
        };

        \WC_PaymentGatewayCloud_CallbackHandler::process($order, $callbackResult);

        $this->assertTrue($order->wasPaymentCompleted());
        $this->assertSame([], $order->getStatusUpdates());
    }

    public function testItMovesErroredCallbacksToFailed(): void
    {
        $order = new \WC_Order(52);
        $callbackResult = new class {
            public function getResult()
            {
                return \PaymentGatewayCloud\Client\Callback\Result::RESULT_ERROR;
            }

            public function getTransactionType()
            {
                return \PaymentGatewayCloud\Client\Callback\Result::TYPE_CAPTURE;
            }
        };

        \WC_PaymentGatewayCloud_CallbackHandler::process($order, $callbackResult);

        $this->assertFalse($order->wasPaymentCompleted());
        $this->assertSame([['failed', 'Error']], $order->getStatusUpdates());
    }
}
