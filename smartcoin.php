<?php
/**
 * Plugin Name: Smartcoin (payment Gateway)
 * Plugin URI: https://smartcoin.com.br
 * Description: Provides payment method for credit card through Smartcoin to WooCommerce.
 * Version: 0.2.5
 * Author: Arthur Granado
 * Author URI: https://smartcoin.com.br
 * License: GPL2
*/

function smartcoin_init_your_gateway() {
  if(class_exists('WC_Payment_Gateway')) {
    include_once('smartcoin_gateway.php');
  }
}

add_action('plugins_loaded', 'smartcoin_init_your_gateway',0);
add_action('admin_init', 'register_and_build_fields');
add_action('wp_enqueue_scripts', 'enqueue_script_and_styles',5 );
add_action('woocommerce_email_before_order_table', 'add_order_email_instructions', 10, 3 );
add_action( 'woocommerce_api_callback', 'callback_handler' );

function callback_handler() {
	return "";
}

add_filter('woocommerce_locate_template', 'smartcoin_woocommerce_locate_template', 10, 3 );
add_filter('woocommerce_payment_gateways', 'smartcoin_add_credit_card_gateway_class' );

function register_and_build_fields() {
	register_setting('smartcoin_options', 'sc_debug');
	register_setting('smartcoin_options', 'sc_test_api_key');
	register_setting('smartcoin_options', 'sc_live_api_key');
}

function enqueue_script_and_styles() {
	wp_enqueue_script( 'smartcoin_api_key',  plugin_dir_url(__FILE__) . '/js/smartcoin_api_key.js', array( 'jquery'));
	$passedValues = array( 'smartcoin_api_key' => ((strcmp(get_option('sc_debug'),'yes') == 0) ? get_option('sc_test_api_key') : get_option('sc_live_api_key')));
	wp_localize_script( 'smartcoin_api_key', 'smartcoin_js_values', $passedValues);
	wp_enqueue_script( 'smartcoin_js',  plugin_dir_url(__FILE__) . '/js/smartcoin.js', array( 'jquery', 'smartcoin_api_key' ));
	wp_enqueue_script( 'smartcoin_checkout_form_js',  plugin_dir_url(__FILE__) . '/js/smartcoin_checkout_form.js', array( 'jquery', 'smartcoin_api_key', 'smartcoin_js'), false, true);
	wp_enqueue_script( 'smartcoin_card',  plugin_dir_url(__FILE__) . '/js/smartcoin_card.js', array( 'jquery', 'smartcoin_api_key', 'smartcoin_js', 'smartcoin_checkout_form_js'), false, true);
	wp_enqueue_style( 'smartcoin_card_css',  plugin_dir_url(__FILE__) . '/css/smartcoin_card.css');
	wp_enqueue_style( 'smartcoin_css',  plugins_url( 'css/smartcoin.css', __FILE__ ));
	wp_enqueue_style( 'smartcoin_loading_css',  plugins_url( 'css/loading.css', __FILE__ ));
	
}

function wpcontent_svg_mime_type( $mimes = array() ) {
  $mimes['svg']  = 'image/svg+xml';
  $mimes['svgz'] = 'image/svg+xml';
  return $mimes;
}
add_filter( 'upload_mimes', 'wpcontent_svg_mime_type' );
