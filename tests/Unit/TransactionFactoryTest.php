<?php

namespace PgcWooCommerce\Tests\Unit;

use PaymentGatewayCloud\Client\Client;
use PaymentGatewayCloud\Client\Data\Customer;
use PaymentGatewayCloud\Client\Transaction\Debit;
use PaymentGatewayCloud\Client\Transaction\Preauthorize;
use PHPUnit\Framework\TestCase;

final class TransactionFactoryTest extends TestCase
{
    public function testItBuildsADebitTransactionAndStoresTheOrderMeta(): void
    {
        $gateway = new \WC_PaymentGatewayCloud_CreditCard([
            'transactionRequest' => 'debit',
        ]);
        $order = new \WC_Order(84, 149.99, 'EUR');
        $customer = new Customer();

        $transaction = \WC_PaymentGatewayCloud_TransactionFactory::make(
            $gateway,
            $order,
            $customer,
            ['threeDSecure' => 'MANDATORY']
        );

        $this->assertInstanceOf(Debit::class, $transaction);
        $this->assertSame('tx-84', $transaction->getTransactionId());
        $this->assertSame(149.99, $transaction->getAmount());
        $this->assertSame('EUR', $transaction->getCurrency());
        $this->assertSame($customer, $transaction->getCustomer());
        $this->assertSame(['threeDSecure' => 'MANDATORY'], $transaction->getExtraData());
        $this->assertSame('https://example.test/callback', $transaction->getCallbackUrl());
        $this->assertSame('https://example.test/checkout', $transaction->getCancelUrl());
        $this->assertSame('https://example.test/success?order=84', $transaction->getSuccessUrl());
        $this->assertSame('https://example.test/error?order=84', $transaction->getErrorUrl());
        $this->assertSame('tx-84', $order->get_meta('orderTxId'));
    }

    public function testItUsesTheMatchingClientMethodForPreauthorizeTransactions(): void
    {
        $gateway = new \WC_PaymentGatewayCloud_CreditCard([
            'transactionRequest' => 'preauthorize',
        ]);
        $client = new class ('user', 'pass', 'key', 'secret') extends Client {
            public array $calls = [];

            public function debit(\PaymentGatewayCloud\Client\Transaction\Debit $transaction)
            {
                $this->calls[] = ['debit', $transaction];

                return 'debit-result';
            }

            public function preauthorize(\PaymentGatewayCloud\Client\Transaction\Preauthorize $transaction)
            {
                $this->calls[] = ['preauthorize', $transaction];

                return 'preauth-result';
            }
        };

        $transaction = new Preauthorize();

        $result = \WC_PaymentGatewayCloud_TransactionFactory::execute($gateway, $client, $transaction);

        $this->assertSame('preauth-result', $result);
        $this->assertCount(1, $client->calls);
        $this->assertSame('preauthorize', $client->calls[0][0]);
        $this->assertSame($transaction, $client->calls[0][1]);
    }
}
