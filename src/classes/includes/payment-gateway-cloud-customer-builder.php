<?php

final class WC_PaymentGatewayCloud_CustomerBuilder
{
    public static function fromOrder(WC_Order $order)
    {
        $customer = new PaymentGatewayCloud\Client\Data\Customer();
        $customer
            ->setBillingAddress1($order->get_billing_address_1())
            ->setBillingAddress2($order->get_billing_address_2())
            ->setBillingCity($order->get_billing_city())
            ->setBillingCountry($order->get_billing_country())
            ->setBillingPhone($order->get_billing_phone())
            ->setBillingPostcode($order->get_billing_postcode())
            ->setBillingState($order->get_billing_state())
            ->setCompany($order->get_billing_company())
            ->setEmail($order->get_billing_email())
            ->setFirstName($order->get_billing_first_name())
            ->setIpAddress(WC_Geolocation::get_ip_address())
            ->setLastName($order->get_billing_last_name());

        if ($order->get_shipping_country()) {
            $customer
                ->setShippingAddress1($order->get_shipping_address_1())
                ->setShippingAddress2($order->get_shipping_address_2())
                ->setShippingCity($order->get_shipping_city())
                ->setShippingCompany($order->get_shipping_company())
                ->setShippingCountry($order->get_shipping_country())
                ->setShippingFirstName($order->get_shipping_first_name())
                ->setShippingLastName($order->get_shipping_last_name())
                ->setShippingPostcode($order->get_shipping_postcode())
                ->setShippingState($order->get_shipping_state());
        }

        return $customer;
    }
}
