<?php
/**
 * Plugin Name: SmartCoin (payment Gateway)
 * Plugin URI: https://www.smartcoin.com.br
 * Description: Provides payment method for credit card through SmartCoin to WooCommerce.
 * Version: 0.1
 * Author: Arthur Granado
 * Author URI: https://www.smartcoin.com.br
 * License: MIT
*/

function smartcoin_init_your_gateway() {
  if(class_exists('WC_Payment_Gateway')) {
    include_once('smartcoin_gateway.php');
  }
}

add_action('plugins_loaded', 'smartcoin_init_your_gateway',0);