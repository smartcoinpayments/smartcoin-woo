<?php
/**
 * Plugin Name: Smartcoin (payment Gateway)
 * Plugin URI: https://smartcoin.com.br
 * Description: Provides payment method for credit card through Smartcoin to WooCommerce.
 * Version: 0.2.2
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
add_action( 'wp_enqueue_scripts', 'enqueue_script_and_styles',5 );

function register_and_build_fields() {
	register_setting('smartcoin_options', 'sc_debug');
	register_setting('smartcoin_options', 'sc_test_api_key');
	register_setting('smartcoin_options', 'sc_live_api_key');
}

function enqueue_script_and_styles() {
	wp_enqueue_script( 'smartcoin_api_key',  plugin_dir_url(__FILE__) . '/js/smartcoin_api_key.js', array( 'jquery'));
	wp_localize_script( 'smartcoin_api_key', 'smartcoin_api_key', ((strcmp(get_option('sc_debug'),'yes') == 0) ? get_option('sc_test_api_key') : get_option('sc_live_api_key')));
	wp_enqueue_script( 'smartcoin_js',  plugin_dir_url(__FILE__) . '/js/smartcoin.js', array( 'jquery', 'smartcoin_api_key' ));
	wp_enqueue_script( 'smartcoin_checkout_form_js',  plugin_dir_url(__FILE__) . '/js/smartcoin_checkout_form.js', array( 'jquery', 'smartcoin_api_key', 'smartcoin_js'), false, true);
}
