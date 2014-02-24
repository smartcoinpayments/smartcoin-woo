<?php
if(!class_exists('Navaska')) {
  require_once('lib/navaska-php/Navaska.php');
}

class Navaska extends WC_Payment_Gateway {
  public function __construct() {
    $this->id = 'Navaska';
    $this->has_fields = true;

    $this->init_form_fields();
    $this->init_settings();

    $this->method_title = $this->get_option('title');
    $this->method_description = '';
    $this->icon = '';


    add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));

  }
}

function navaska_add_credit_card_gateway_class( $methods ) {
  $methods[] = 'Navaska';
  return $methods;
}

add_filter( 'woocommerce_payment_gateways', 'navaska_add_credit_card_gateway_class' );