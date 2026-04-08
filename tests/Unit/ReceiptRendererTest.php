<?php

namespace PgcWooCommerce\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class ReceiptRendererTest extends TestCase
{
    public function testItFallsBackToClassicTemplateWhenTemplateIsUnknown(): void
    {
        $gateway = new \WC_PaymentGatewayCloud_CreditCard([
            'receiptTemplate' => 'not-real',
        ]);

        $this->assertSame('classic', \WC_PaymentGatewayCloud_ReceiptRenderer::getTemplate($gateway));
    }

    public function testItRendersEmailMarkupWithBrandingAndSupportEmail(): void
    {
        $gateway = new \WC_PaymentGatewayCloud_CreditCard([
            'receiptTemplate' => 'summary',
            'receiptBrandName' => 'Martin Pirate Pay',
            'receiptSupportEmail' => 'support@example.test',
            'receiptAccentColor' => '#11cc88',
        ]);
        $order = new \WC_Order(99, 249.5, 'EUR');
        $order->update_meta_data('gatewayReferenceId', 'uuid-123');
        $order->update_meta_data('orderTxId', 'tx-99');

        $html = \WC_PaymentGatewayCloud_ReceiptRenderer::renderEmail($order, $gateway);

        $this->assertStringContainsString('Martin Pirate Pay', $html);
        $this->assertStringContainsString('support@example.test', $html);
        $this->assertStringContainsString('#99', $html);
        $this->assertStringContainsString('249.50 EUR', $html);
        $this->assertStringContainsString('tx-99', $html);
    }
}
