<?php
/*
Plugin Name: Beem Payment Gateway
Description: WooCommerce payment gateway integration for Beem.
Version: 1.0
*/

if (!defined('ABSPATH')) {
    exit;
}

// Add the Beem payment gateway to WooCommerce
add_filter('woocommerce_payment_gateways', 'add_beem_payment_gateway');
function add_beem_payment_gateway($gateways) {
    $gateways[] = 'WC_Beem_Payment_Gateway';
    return $gateways;
}

// Define the Beem payment gateway class
class WC_Beem_Payment_Gateway extends WC_Payment_Gateway {

    public function __construct() {
        $this->id = 'beem_payment';
        $this->method_title = 'Beem Payment';
        $this->icon = ''; // URL to payment gateway icon
        $this->has_fields = false;

        $this->init_form_fields();
        $this->init_settings();

        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->api_key = $this->get_option('api_key');
        $this->secret_key = $this->get_option('secret_key');

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
		
		// Add scripts and iframes for Beem Checkout
        add_action('wp_enqueue_scripts', array($this, 'enqueue_beem_checkout_scripts'));
        add_action('woocommerce_before_checkout_form', array($this, 'output_beem_checkout_iframe'));
    }
	
	undefined_function();
	
 public function enqueue_beem_checkout_scripts() {
        wp_enqueue_style('beem-checkout-css', 'https://checkout.beem.africa/dist/0.1_alpha/bpay.min.css');
        wp_enqueue_script('beem-checkout-js', 'https://checkout.beem.africa/dist/0.1_alpha/bpay.min.js', array(), null, true);
    }

public function output_beem_checkout_iframe() {
        if (is_checkout()) {
            $order = wc_get_order(wc_get_order_id_by_order_key($_GET['order-pay']));
            $price = $order->get_total();
            $reference = 'SAMPLE-' . $order->get_id();
            $transaction_id = wp_generate_uuid4();
            $mobile = ''; // Get mobile number from order
            $secure_token = ''; // Get secure token from your system

            echo "<div id='beem-button' data-price='$price' data-reference='$reference' data-transaction='$transaction_id' data-mobile='$mobile' data-token='$secure_token'></div>";
            echo "<div id='beem-page' class='beem-page'></div>";
            echo "<script type='text/javascript'>InitializeBeem();</script>";
        }
    }
	
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title' => 'Enable/Disable',
                'type' => 'checkbox',
                'label' => 'Enable Beem Payment Gateway',
                'default' => 'yes'
            ),
            'title' => array(
                'title' => 'Title',
                'type' => 'text',
                'description' => 'This controls the title which the user sees during checkout.',
                'default' => 'Beem Payment',
                'desc_tip' => true
            ),
            'description' => array(
                'title' => 'Description',
                'type' => 'textarea',
                'description' => 'This controls the description which the user sees during checkout.',
                'default' => 'Pay with Beem'
            ),
            'api_key' => array(
                'title' => 'API Key',
                'type' => 'text',
                'description' => 'Enter your Beem API key.',
                'default' => '',
                'desc_tip' => true
            ),
            'secret_key' => array(
                'title' => 'Secret Key',
                'type' => 'text',
                'description' => 'Enter your Beem Secret key.',
                'default' => '',
                'desc_tip' => true
            )
        );
    }

    public function process_payment($order_id) {
        global $woocommerce;

        $order = wc_get_order($order_id);

        // Generate checkout URL with parameters
        $checkout_url = 'https://checkout.beem.africa/v1/checkout';
        $checkout_params = array(
            'amount' => $order->get_total(),
            'transaction_id' => wp_generate_uuid4(),
            'reference_number' => 'SAMPLE-' . $order->get_id(),
            'mobile' => '', // Get mobile number from order
            'sendSource' => true // Change to false if not handling redirect from backend
        );

        $checkout_url = add_query_arg($checkout_params, $checkout_url);

        return array(
            'result' => 'success',
            'redirect' => $checkout_url
        );
    }
}

// Add the Beem payment gateway class to WooCommerce
add_action('woocommerce_payment_gateways', 'add_beem_payment_gateway_class');
function add_beem_payment_gateway_class($methods) {
    $methods[] = 'WC_Beem_Payment_Gateway';
    return $methods;
}
