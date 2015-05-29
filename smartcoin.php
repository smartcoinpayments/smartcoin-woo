<?php
/**
 * Plugin Name: Smartcoin (payment Gateway)
 * Plugin URI: https://smartcoin.com.br
 * Description: Provides payment method for credit card through Smartcoin to WooCommerce.
 * Version: 0.3.8
 * Author: Arthur Granado
 * Author URI: https://smartcoin.com.br
 * License: GPL2
*/

define('SMARTCOIN_WOO_PLUGIN_VERSION', '0.3.8');

function smartcoin_init_your_gateway() {
  if(class_exists('WC_Payment_Gateway')) {
    include_once('smartcoin_gateway.php');
    smartcoin_load_plugin_textdomain();
  }
}

add_action('plugins_loaded', 'smartcoin_init_your_gateway',0);
add_action('admin_init', 'register_and_build_fields');
add_action('wp_enqueue_scripts', 'enqueue_script_and_styles',5 );

add_filter('woocommerce_payment_gateways', 'smartcoin_add_credit_card_gateway_class' );

function register_and_build_fields() {
	register_setting('smartcoin_options', 'sc_debug');
	register_setting('smartcoin_options', 'sc_test_api_key');
	register_setting('smartcoin_options', 'sc_live_api_key');
	register_setting('smartcoin_options', 'sc_show_radio_button');
	register_setting('smartcoin_options', 'sc_track_debug');
}

function enqueue_script_and_styles() {
	wp_enqueue_script( 'smartcoin_api_key',  plugin_dir_url(__FILE__) . '/js/smartcoin_api_key.js', array( 'jquery'), SMARTCOIN_WOO_PLUGIN_VERSION);
	$passedValues = array( 'smartcoin_api_key' => ((strcmp(get_option('sc_debug'),'yes') == 0) ? get_option('sc_test_api_key') : get_option('sc_live_api_key')),
		                      'smartcoin_path' => plugin_dir_url(__FILE__),
		                      'smartcoin_show_radio_button' => (strcmp(get_option('sc_show_radio_button'),'yes') == 0),
		                      'smartcoin_track_debug' => (strcmp(get_option('sc_track_debug'),'yes') == 0),);
	wp_localize_script( 'smartcoin_api_key', 'smartcoin_js_values', $passedValues);
	wp_enqueue_script( 'smartcoin_js',  plugin_dir_url(__FILE__) . '/js/smartcoin.js', array( 'jquery', 'smartcoin_api_key' ), SMARTCOIN_WOO_PLUGIN_VERSION);
	wp_enqueue_script( 'smartcoin_checkout_form_js',  plugin_dir_url(__FILE__) . '/js/smartcoin_checkout_form.js', array( 'jquery', 'smartcoin_api_key', 'smartcoin_js'), SMARTCOIN_WOO_PLUGIN_VERSION, true);
	wp_enqueue_script( 'smartcoin_card',  plugin_dir_url(__FILE__) . '/js/smartcoin_card.js', array( 'jquery', 'smartcoin_api_key', 'smartcoin_js', 'smartcoin_checkout_form_js'), SMARTCOIN_WOO_PLUGIN_VERSION, true);
	wp_enqueue_style( 'smartcoin_card_css',  plugin_dir_url(__FILE__) . '/css/smartcoin_card.css', SMARTCOIN_WOO_PLUGIN_VERSION);
	wp_enqueue_style( 'smartcoin_css',  plugins_url( 'css/smartcoin.css', __FILE__ ), SMARTCOIN_WOO_PLUGIN_VERSION);
	wp_enqueue_style( 'smartcoin_loading_css',  plugins_url( 'css/loading.css', __FILE__ ), SMARTCOIN_WOO_PLUGIN_VERSION);
	
}

function smartcoin_load_plugin_textdomain() {
  load_plugin_textdomain('smartcoin-woo', false, dirname(plugin_basename( __FILE__ )) . '/languages/');
}