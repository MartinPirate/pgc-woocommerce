<?php

require dirname(__DIR__) . '/vendor/autoload.php';

if (!function_exists('__')) {
    function __($text, $domain = null)
    {
        return $text;
    }
}

if (!function_exists('apply_filters')) {
    function apply_filters($hookName, $value)
    {
        return $value;
    }
}

if (!function_exists('wc_get_checkout_url')) {
    function wc_get_checkout_url()
    {
        return 'https://example.test/checkout';
    }
}

if (!function_exists('add_query_arg')) {
    function add_query_arg($key, $value = null, $url = '')
    {
        if (is_array($key)) {
            $query = http_build_query($key);
            $separator = strpos($value, '?') === false ? '?' : '&';

            return $value . $separator . $query;
        }

        $separator = strpos($url, '?') === false ? '?' : '&';

        return $url . $separator . rawurlencode((string) $key) . '=' . rawurlencode((string) $value);
    }
}

if (!class_exists('WC_Payment_Gateway')) {
    class WC_Payment_Gateway
    {
        protected array $options = [];

        public function get_option($key, $emptyValue = null)
        {
            return $this->options[$key] ?? $emptyValue;
        }
    }
}

if (!class_exists('WC_Order')) {
    class WC_Order
    {
        protected array $meta = [];
        protected array $statusUpdates = [];
        protected bool $paymentCompleted = false;

        public function __construct(protected int $id = 1, protected float $total = 100.5, protected string $currency = 'USD')
        {
        }

        public function get_id()
        {
            return $this->id;
        }

        public function get_total()
        {
            return $this->total;
        }

        public function get_currency()
        {
            return $this->currency;
        }

        public function add_meta_data($key, $value, $unique = false)
        {
            $this->meta[$key] = $value;
        }

        public function save_meta_data()
        {
        }

        public function get_meta($key)
        {
            return $this->meta[$key] ?? null;
        }

        public function payment_complete()
        {
            $this->paymentCompleted = true;
        }

        public function update_status($status, $note = '')
        {
            $this->statusUpdates[] = [$status, $note];
        }

        public function wasPaymentCompleted()
        {
            return $this->paymentCompleted;
        }

        public function getStatusUpdates()
        {
            return $this->statusUpdates;
        }

        public function get_billing_address_1()
        {
            return '123 Billing St';
        }

        public function get_billing_address_2()
        {
            return 'Suite 4';
        }

        public function get_billing_city()
        {
            return 'Nairobi';
        }

        public function get_billing_country()
        {
            return 'KE';
        }

        public function get_billing_phone()
        {
            return '+254700000001';
        }

        public function get_billing_postcode()
        {
            return '00100';
        }

        public function get_billing_state()
        {
            return 'Nairobi County';
        }

        public function get_billing_company()
        {
            return 'Acme Ltd';
        }

        public function get_billing_email()
        {
            return 'buyer@example.test';
        }

        public function get_billing_first_name()
        {
            return 'Amina';
        }

        public function get_billing_last_name()
        {
            return 'Otieno';
        }

        public function get_shipping_country()
        {
            return 'KE';
        }

        public function get_shipping_address_1()
        {
            return '456 Shipping Rd';
        }

        public function get_shipping_address_2()
        {
            return 'Floor 8';
        }

        public function get_shipping_city()
        {
            return 'Mombasa';
        }

        public function get_shipping_company()
        {
            return 'Acme Fulfillment';
        }

        public function get_shipping_first_name()
        {
            return 'Amina';
        }

        public function get_shipping_last_name()
        {
            return 'Otieno';
        }

        public function get_shipping_postcode()
        {
            return '80100';
        }

        public function get_shipping_state()
        {
            return 'Mombasa County';
        }
    }
}

if (!class_exists('WC_Geolocation')) {
    class WC_Geolocation
    {
        public static function get_ip_address()
        {
            return '127.0.0.1';
        }
    }
}

if (!class_exists('WC_PaymentGatewayCloud_CreditCard')) {
    class WC_PaymentGatewayCloud_CreditCard extends WC_Payment_Gateway
    {
        public function __construct(array $options = [])
        {
            $this->options = $options;
        }

        public function createOrderTransactionId($orderId)
        {
            return 'tx-' . $orderId;
        }

        public function getGatewayCallbackUrl()
        {
            return 'https://example.test/callback';
        }

        public function getPaymentSuccessUrl($order)
        {
            return 'https://example.test/success?order=' . $order->get_id();
        }

        public function getPaymentErrorUrl($order)
        {
            return 'https://example.test/error?order=' . $order->get_id();
        }
    }
}

require dirname(__DIR__) . '/src/classes/includes/payment-gateway-cloud-provider.php';
require dirname(__DIR__) . '/src/classes/includes/payment-gateway-cloud-customer-builder.php';
require dirname(__DIR__) . '/src/classes/includes/payment-gateway-cloud-transaction-factory.php';
require dirname(__DIR__) . '/src/classes/includes/payment-gateway-cloud-callback-handler.php';
