<?php

final class WC_PaymentGatewayCloud_ReceiptRenderer
{
    public static function render(WC_Order $order, WC_Payment_Gateway $gateway)
    {
        $template = self::getTemplate($gateway);
        $statusLabel = self::getStatusLabel($order);
        $accentClass = self::getAccentClass($template, $statusLabel);
        $total = function_exists('wc_price') ? wc_price($order->get_total(), ['currency' => $order->get_currency()]) : $order->get_total() . ' ' . $order->get_currency();
        $createdAt = method_exists($order, 'get_date_created') && $order->get_date_created()
            ? $order->get_date_created()->date_i18n(get_option('date_format') . ' ' . get_option('time_format'))
            : '';
        $referenceId = $order->get_meta('gatewayReferenceId');
        $transactionId = $order->get_meta('orderTxId');
        $email = method_exists($order, 'get_billing_email') ? $order->get_billing_email() : '';
        $brandName = self::getBrandName($gateway);
        $supportEmail = self::getSupportEmail($gateway);
        $accentColor = self::getAccentColor($gateway);

        wp_enqueue_style('payment_gateway_cloud_receipt');

        echo '<section class="payment-gateway-cloud-receipt payment-gateway-cloud-receipt--' . esc_attr($template) . ' ' . esc_attr($accentClass) . '" style="--payment-gateway-cloud-accent:' . esc_attr($accentColor) . ';">';
        echo '<div class="payment-gateway-cloud-receipt__hero">';
        echo '<div>';
        echo '<p class="payment-gateway-cloud-receipt__eyebrow">' . esc_html($brandName) . '</p>';
        echo '<h2>' . esc_html($gateway->get_title()) . '</h2>';
        echo '</div>';
        echo '<span class="payment-gateway-cloud-receipt__status">' . esc_html($statusLabel) . '</span>';
        echo '</div>';

        if ($template === 'summary') {
            self::renderSummary($order, $total, $referenceId, $transactionId, $supportEmail);
        } elseif ($template === 'ops') {
            self::renderOps($order, $gateway, $total, $createdAt, $referenceId, $transactionId, $email, $supportEmail);
        } else {
            self::renderClassic($order, $gateway, $total, $createdAt, $referenceId, $transactionId, $email, $supportEmail);
        }

        echo '</section>';
    }

    public static function renderEmail(WC_Order $order, WC_Payment_Gateway $gateway)
    {
        $template = self::getTemplate($gateway);
        $statusLabel = self::getStatusLabel($order);
        $brandName = self::getBrandName($gateway);
        $supportEmail = self::getSupportEmail($gateway);
        $accentColor = self::getAccentColor($gateway);
        $total = function_exists('wc_price') ? wp_strip_all_tags(wc_price($order->get_total(), ['currency' => $order->get_currency()])) : $order->get_total() . ' ' . $order->get_currency();
        $referenceId = $order->get_meta('gatewayReferenceId');
        $transactionId = $order->get_meta('orderTxId');
        $email = method_exists($order, 'get_billing_email') ? $order->get_billing_email() : '';

        $rows = [
            __('Order Number', 'woocommerce-payment-gateway-cloud') => '#' . $order->get_order_number(),
            __('Amount', 'woocommerce-payment-gateway-cloud') => $total,
            __('Status', 'woocommerce-payment-gateway-cloud') => $statusLabel,
            __('Payment Method', 'woocommerce-payment-gateway-cloud') => $gateway->get_title(),
            __('Customer Email', 'woocommerce-payment-gateway-cloud') => $email,
            __('Gateway Transaction', 'woocommerce-payment-gateway-cloud') => $transactionId,
            __('Gateway Reference', 'woocommerce-payment-gateway-cloud') => $referenceId,
        ];

        if ($template === 'summary') {
            $rows = [
                __('Amount', 'woocommerce-payment-gateway-cloud') => $total,
                __('Order', 'woocommerce-payment-gateway-cloud') => '#' . $order->get_order_number(),
                __('Transaction', 'woocommerce-payment-gateway-cloud') => $transactionId,
                __('Reference', 'woocommerce-payment-gateway-cloud') => $referenceId,
            ];
        }

        $html = '<div style="margin:24px 0;padding:24px;border-radius:18px;border:1px solid #dbe3ef;background:#f8fbff;">';
        $html .= '<p style="margin:0 0 6px;font-size:12px;letter-spacing:0.14em;text-transform:uppercase;color:#64748b;">' . esc_html($brandName) . '</p>';
        $html .= '<h3 style="margin:0 0 18px;color:#0f172a;">' . esc_html($gateway->get_title()) . '</h3>';
        $html .= '<div style="display:inline-block;margin-bottom:18px;padding:6px 12px;border-radius:999px;background:' . esc_attr($accentColor) . ';color:#0f172a;font-weight:700;">' . esc_html($statusLabel) . '</div>';
        $html .= '<table cellspacing="0" cellpadding="0" style="width:100%;border-collapse:collapse;">';

        foreach ($rows as $label => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $html .= '<tr>';
            $html .= '<td style="padding:10px 0;border-top:1px solid #e2e8f0;color:#64748b;">' . esc_html($label) . '</td>';
            $html .= '<td style="padding:10px 0;border-top:1px solid #e2e8f0;color:#0f172a;text-align:right;font-weight:600;">' . esc_html($value) . '</td>';
            $html .= '</tr>';
        }

        $html .= '</table>';

        if ($supportEmail !== '') {
            $html .= '<p style="margin:18px 0 0;color:#475569;">'
                . esc_html__('Support', 'woocommerce-payment-gateway-cloud')
                . ': <a href="mailto:' . esc_attr($supportEmail) . '" style="color:#0f172a;">'
                . esc_html($supportEmail)
                . '</a></p>';
        }

        $html .= '</div>';

        return $html;
    }

    public static function getTemplate(WC_Payment_Gateway $gateway)
    {
        $template = (string) $gateway->get_option('receiptTemplate');

        if (!in_array($template, ['classic', 'summary', 'ops'], true)) {
            return 'classic';
        }

        return $template;
    }

    public static function getStatusLabel(WC_Order $order)
    {
        $status = method_exists($order, 'get_status') ? (string) $order->get_status() : '';

        switch ($status) {
            case 'processing':
            case 'completed':
                return __('Paid', 'woocommerce-payment-gateway-cloud');
            case 'on-hold':
                return __('Pending Review', 'woocommerce-payment-gateway-cloud');
            case 'cancelled':
                return __('Cancelled', 'woocommerce-payment-gateway-cloud');
            case 'failed':
                return __('Failed', 'woocommerce-payment-gateway-cloud');
            default:
                return ucfirst($status ?: 'Pending');
        }
    }

    public static function getBrandName(WC_Payment_Gateway $gateway)
    {
        return (string) $gateway->get_option('receiptBrandName', PAYMENT_GATEWAY_CLOUD_EXTENSION_NAME);
    }

    public static function getSupportEmail(WC_Payment_Gateway $gateway)
    {
        return (string) $gateway->get_option('receiptSupportEmail', '');
    }

    public static function getAccentColor(WC_Payment_Gateway $gateway)
    {
        $color = (string) $gateway->get_option('receiptAccentColor', '#c7ff52');

        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
            return '#c7ff52';
        }

        return $color;
    }

    private static function getAccentClass($template, $statusLabel)
    {
        if ($statusLabel === __('Failed', 'woocommerce-payment-gateway-cloud')) {
            return 'payment-gateway-cloud-receipt--rose';
        }

        if ($template === 'ops') {
            return 'payment-gateway-cloud-receipt--cyan';
        }

        return 'payment-gateway-cloud-receipt--lime';
    }

    private static function renderClassic(WC_Order $order, WC_Payment_Gateway $gateway, $total, $createdAt, $referenceId, $transactionId, $email, $supportEmail)
    {
        echo '<div class="payment-gateway-cloud-receipt__amount-row">';
        echo '<strong>' . wp_kses_post($total) . '</strong>';
        echo '<span>' . esc_html__('Order #', 'woocommerce-payment-gateway-cloud') . esc_html($order->get_order_number()) . '</span>';
        echo '</div>';

        echo '<ul class="payment-gateway-cloud-receipt__meta-list">';
        self::renderMeta(__('Payment Method', 'woocommerce-payment-gateway-cloud'), $gateway->get_title());
        self::renderMeta(__('Date', 'woocommerce-payment-gateway-cloud'), $createdAt);
        self::renderMeta(__('Customer Email', 'woocommerce-payment-gateway-cloud'), $email);
        self::renderMeta(__('Gateway Transaction', 'woocommerce-payment-gateway-cloud'), $transactionId);
        self::renderMeta(__('Gateway Reference', 'woocommerce-payment-gateway-cloud'), $referenceId);
        self::renderMeta(__('Support', 'woocommerce-payment-gateway-cloud'), $supportEmail);
        echo '</ul>';
    }

    private static function renderSummary(WC_Order $order, $total, $referenceId, $transactionId, $supportEmail)
    {
        echo '<div class="payment-gateway-cloud-receipt__summary-grid">';
        echo '<article class="payment-gateway-cloud-receipt__tile">';
        echo '<p>' . esc_html__('Amount', 'woocommerce-payment-gateway-cloud') . '</p>';
        echo '<strong>' . wp_kses_post($total) . '</strong>';
        echo '</article>';
        echo '<article class="payment-gateway-cloud-receipt__tile">';
        echo '<p>' . esc_html__('Order', 'woocommerce-payment-gateway-cloud') . '</p>';
        echo '<strong>#' . esc_html($order->get_order_number()) . '</strong>';
        echo '</article>';
        echo '<article class="payment-gateway-cloud-receipt__tile">';
        echo '<p>' . esc_html__('Transaction', 'woocommerce-payment-gateway-cloud') . '</p>';
        echo '<strong>' . esc_html($transactionId ?: 'n/a') . '</strong>';
        echo '</article>';
        echo '<article class="payment-gateway-cloud-receipt__tile">';
        echo '<p>' . esc_html__('Reference', 'woocommerce-payment-gateway-cloud') . '</p>';
        echo '<strong>' . esc_html($referenceId ?: 'n/a') . '</strong>';
        echo '</article>';
        echo '</div>';

        if ($supportEmail !== '') {
            echo '<p class="payment-gateway-cloud-receipt__support-line">';
            echo esc_html__('Support', 'woocommerce-payment-gateway-cloud') . ': ' . esc_html($supportEmail);
            echo '</p>';
        }
    }

    private static function renderOps(WC_Order $order, WC_Payment_Gateway $gateway, $total, $createdAt, $referenceId, $transactionId, $email, $supportEmail)
    {
        echo '<div class="payment-gateway-cloud-receipt__ops-header">';
        echo '<div>';
        echo '<p class="payment-gateway-cloud-receipt__eyebrow">' . esc_html__('Operations Summary', 'woocommerce-payment-gateway-cloud') . '</p>';
        echo '<h3>' . esc_html__('Reconciliation View', 'woocommerce-payment-gateway-cloud') . '</h3>';
        echo '</div>';
        echo '<span class="payment-gateway-cloud-receipt__ops-flag">' . esc_html__('Finance-ready', 'woocommerce-payment-gateway-cloud') . '</span>';
        echo '</div>';

        echo '<ul class="payment-gateway-cloud-receipt__meta-list">';
        self::renderMeta(__('Order Number', 'woocommerce-payment-gateway-cloud'), '#' . $order->get_order_number());
        self::renderMeta(__('Amount', 'woocommerce-payment-gateway-cloud'), wp_strip_all_tags($total));
        self::renderMeta(__('Method', 'woocommerce-payment-gateway-cloud'), $gateway->get_title());
        self::renderMeta(__('Created At', 'woocommerce-payment-gateway-cloud'), $createdAt);
        self::renderMeta(__('Customer Email', 'woocommerce-payment-gateway-cloud'), $email);
        self::renderMeta(__('Transaction ID', 'woocommerce-payment-gateway-cloud'), $transactionId);
        self::renderMeta(__('Reference ID', 'woocommerce-payment-gateway-cloud'), $referenceId);
        self::renderMeta(__('Support', 'woocommerce-payment-gateway-cloud'), $supportEmail);
        echo '</ul>';
    }

    private static function renderMeta($label, $value)
    {
        if ($value === null || $value === '') {
            return;
        }

        echo '<li><span>' . esc_html($label) . '</span><strong>' . esc_html($value) . '</strong></li>';
    }
}
