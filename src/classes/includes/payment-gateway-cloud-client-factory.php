<?php

final class WC_PaymentGatewayCloud_ClientFactory
{
    public static function make(WC_Payment_Gateway $gateway)
    {
        WC_PaymentGatewayCloud_Provider::autoloadClient();

        PaymentGatewayCloud\Client\Client::setApiUrl((string) $gateway->get_option('apiHost'));

        return new PaymentGatewayCloud\Client\Client(
            (string) $gateway->get_option('apiUser'),
            htmlspecialchars_decode((string) $gateway->get_option('apiPassword')),
            (string) $gateway->get_option('apiKey'),
            (string) $gateway->get_option('sharedSecret')
        );
    }
}
