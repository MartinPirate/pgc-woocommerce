<?php

namespace PgcWooCommerce\Tests\Unit;

use PaymentGatewayCloud\Client\Data\Customer;
use PHPUnit\Framework\TestCase;

final class CustomerBuilderTest extends TestCase
{
    public function testItBuildsACustomerFromOrderData(): void
    {
        $order = new \WC_Order(42);

        $customer = \WC_PaymentGatewayCloud_CustomerBuilder::fromOrder($order);

        $this->assertInstanceOf(Customer::class, $customer);
        $this->assertSame('Amina', $customer->getFirstName());
        $this->assertSame('Otieno', $customer->getLastName());
        $this->assertSame('buyer@example.test', $customer->getEmail());
        $this->assertSame('123 Billing St', $customer->getBillingAddress1());
        $this->assertSame('KE', $customer->getBillingCountry());
        $this->assertSame('456 Shipping Rd', $customer->getShippingAddress1());
        $this->assertSame('Mombasa', $customer->getShippingCity());
        $this->assertSame('127.0.0.1', $customer->getIpAddress());
    }
}
