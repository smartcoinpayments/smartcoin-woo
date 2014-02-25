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

    $this->title            = $this->get_option('title');
    $this->description      = '';
    $this->icon             = '';
    $this->use_test_api     = strcmp($this->get_option('debug'),'yes') == 0;
    $this->test_api_key     = $this->get_option('test_api_key');
    $this->test_api_secret  = $this->get_option('test_api_secret');
    $this->live_api_key     = $this->get_option('live_api_key');
    $this->live_api_secret  = $this->get_option('live_api_secret');


    add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
    add_action('admin_notices', array(&$this, 'check_ssl'));

  }

  public function check_ssl() {
    if(!$this->use_test_api && get_option('woocommerce_force_ssl_checkout') && $this->enable == 'yes') {
      echo __('<div class="error"><p>Navaska test API is disable and force SSL option is disable. Please enable SSL and ensure your server has valid SSL certificate.</p></div>','woothemes');
    }
  }

  public function init_form_fields() {
    $this->form_fields = array(
        'enabled' => array(
            'type'        => 'checkbox',
            'title'       => __('Enable/Disable', 'woothemes'),
            'label'       => __('Enable Navaska Credit Card Payment', 'woothemes'),
            'default'     => 'yes'
          ),
        'debug' => array(
            'type'        => 'checkbox',
            'title'       => __('Test mode (sandbox)', 'woothemes'),
            'label'       => __('Turn on the test mode', 'woothemes'),
            'default'     => 'yes'
          ),
        'capture' => array(
            'type'        => 'checkbox',
            'title'       => __('Authorize & Capture', 'woothemes'),
            'label'       => __('Enable Authorization and Capture', 'woothemes'),
            'default'     => 'yes'
          ),
        'title' => array(
            'type'        => 'text',
            'title'       => __('Title on Checkout Form', 'woothemes'),
            'description' => __('This controls the title which the user sees during checkout.', 'woothemes'),
            'default'     => __('Cartão de Crédito', 'woothemes')
          ),
        'test_api_key' => array(
            'type'        => 'text',
            'title'       => __('Test API Key', 'woothemes'),
            'default'     => __('','woothemes')
          ),
        'test_api_secret' => array(
            'type'        => 'text',
            'title'       => __('Test API Secret', 'woothemes'),
            'default'     => __('','woothemes')
          ),
        'live_api_key' => array(
            'type'        => 'text',
            'title'       => __('Live API Key', 'woothemes'),
            'default'     => __('','woothemes')
          ),
        'live_api_secret' => array(
            'type'        => 'text',
            'title'       => __('Live API Secret', 'woothemes'),
            'default'     => __('','woothemes')
          )
      );
  }

  public function admin_options() {
    include_once('templates/admin.php');
  }

  public function payment_fields() {
    include_once('templates/payment_form.php');
  }

}

function navaska_add_credit_card_gateway_class( $methods ) {
  $methods[] = 'Navaska';
  return $methods;
}

add_filter( 'woocommerce_payment_gateways', 'navaska_add_credit_card_gateway_class' );