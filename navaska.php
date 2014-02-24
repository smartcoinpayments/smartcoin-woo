<?php
/**
 * Plugin Name: Navaska (payment Gateway)
 * Plugin URI: https://www.navaska.com.br
 * Description: Provides payment method for credit card through Navaska to WooCommerce.
 * Version: 0.1
 * Author: Arthur Granado
 * Author URI: https://www.navaska.com.br
 * License: MIT
*/

function navaska_init_your_gateway() {
  if(class_exists('WC_Payment_Gateway')) {
    include_once('navaska_gateway.php');
  }
}

add_action('plugins_loaded', 'navaska_init_your_gateway',0);