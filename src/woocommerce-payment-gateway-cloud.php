<?php
/**
 * Plugin Name: WooCommerce Payment Gateway Cloud Extension
 * Description: Payment Gateway Cloud for WooCommerce
 * Version: X.Y.Z
 * Author: Payment Gateway Cloud
 * Requires Plugins: woocommerce
 * Requires PHP: 7.1
 * WC requires at least: 3.6.0
 * WC tested up to: 3.7.0
 * Text Domain: woocommerce-payment-gateway-cloud
 */
if (!defined('ABSPATH')) {
    exit;
}

define('PAYMENT_GATEWAY_CLOUD_EXTENSION_URL', 'https://gateway.paymentgateway.cloud/');
define('PAYMENT_GATEWAY_CLOUD_EXTENSION_NAME', 'Payment Gateway Cloud');
define('PAYMENT_GATEWAY_CLOUD_EXTENSION_VERSION', 'X.Y.Z');
define('PAYMENT_GATEWAY_CLOUD_EXTENSION_UID_PREFIX', 'payment_gateway_cloud_');
define('PAYMENT_GATEWAY_CLOUD_EXTENSION_BASEDIR', plugin_dir_path(__FILE__));
define('PAYMENT_GATEWAY_CLOUD_EXTENSION_BASEURL', plugin_dir_url(__FILE__));

final class WC_PaymentGatewayCloud_Bootstrap
{
    public static function init()
    {
        add_action('plugins_loaded', [self::class, 'boot']);
    }

    public static function boot()
    {
        if (!class_exists('WooCommerce') || !class_exists('WC_Payment_Gateway')) {
            add_action('admin_notices', [self::class, 'renderWooCommerceNotice']);
            return;
        }

        self::loadGatewayClasses();

        add_filter('woocommerce_payment_gateways', [self::class, 'registerPaymentGateways'], 0);
        add_filter('the_content', [self::class, 'maybeRenderGatewayNotice'], 0, 1);
        add_action('init', [self::class, 'maybeClearCart']);
    }

    public static function renderWooCommerceNotice()
    {
        echo '<div class="notice notice-error"><p>'
            . esc_html__('WooCommerce Payment Gateway Cloud Extension requires WooCommerce to be installed and active.', 'woocommerce-payment-gateway-cloud')
            . '</p></div>';
    }

    public static function registerPaymentGateways($methods)
    {
        foreach (WC_PaymentGatewayCloud_Provider::paymentMethods() as $paymentMethod) {
            if (class_exists($paymentMethod)) {
                $methods[] = $paymentMethod;
            }
        }

        return $methods;
    }

    public static function maybeRenderGatewayNotice($content)
    {
        if ((is_checkout_pay_page() || is_checkout()) && !empty($_GET['gateway_return_result']) && $_GET['gateway_return_result'] === 'error') {
            wc_print_notice(__('Payment failed or was declined', 'woocommerce-payment-gateway-cloud'), 'error');
        }

        return $content;
    }

    public static function maybeClearCart()
    {
        if (isset($_GET['clear-cart']) && is_order_received_page() && function_exists('WC')) {
            $cart = WC()->cart;
            if ($cart) {
                $cart->empty_cart();
            }
        }
    }

    private static function loadGatewayClasses()
    {
        require_once PAYMENT_GATEWAY_CLOUD_EXTENSION_BASEDIR . 'classes/includes/payment-gateway-cloud-provider.php';
        require_once PAYMENT_GATEWAY_CLOUD_EXTENSION_BASEDIR . 'classes/includes/payment-gateway-cloud-client-factory.php';
        require_once PAYMENT_GATEWAY_CLOUD_EXTENSION_BASEDIR . 'classes/includes/payment-gateway-cloud-transaction-factory.php';
        require_once PAYMENT_GATEWAY_CLOUD_EXTENSION_BASEDIR . 'classes/includes/payment-gateway-cloud-callback-handler.php';
        require_once PAYMENT_GATEWAY_CLOUD_EXTENSION_BASEDIR . 'classes/includes/payment-gateway-cloud-customer-builder.php';
        require_once PAYMENT_GATEWAY_CLOUD_EXTENSION_BASEDIR . 'classes/includes/payment-gateway-cloud-creditcard.php';
        require_once PAYMENT_GATEWAY_CLOUD_EXTENSION_BASEDIR . 'classes/includes/payment-gateway-cloud-creditcard-amex.php';
        require_once PAYMENT_GATEWAY_CLOUD_EXTENSION_BASEDIR . 'classes/includes/payment-gateway-cloud-creditcard-diners.php';
        require_once PAYMENT_GATEWAY_CLOUD_EXTENSION_BASEDIR . 'classes/includes/payment-gateway-cloud-creditcard-discover.php';
        require_once PAYMENT_GATEWAY_CLOUD_EXTENSION_BASEDIR . 'classes/includes/payment-gateway-cloud-creditcard-jcb.php';
        require_once PAYMENT_GATEWAY_CLOUD_EXTENSION_BASEDIR . 'classes/includes/payment-gateway-cloud-creditcard-maestro.php';
        require_once PAYMENT_GATEWAY_CLOUD_EXTENSION_BASEDIR . 'classes/includes/payment-gateway-cloud-creditcard-mastercard.php';
        require_once PAYMENT_GATEWAY_CLOUD_EXTENSION_BASEDIR . 'classes/includes/payment-gateway-cloud-creditcard-unionpay.php';
        require_once PAYMENT_GATEWAY_CLOUD_EXTENSION_BASEDIR . 'classes/includes/payment-gateway-cloud-creditcard-visa.php';
    }
}

WC_PaymentGatewayCloud_Bootstrap::init();
