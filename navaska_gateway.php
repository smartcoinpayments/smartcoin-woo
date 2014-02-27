<?php
require_once('lib/navaska-php/lib/Navaska.php');


class Navaska extends WC_Payment_Gateway {

  protected $GATEWAY_NAME               = "Navaska";
  protected $use_test_api               = true;
  protected $order                      = null;
  protected $transaction_id             = null;
  protected $transaction_error_message  = null;

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
    $this->api_key          = $this->use_test_api ? $this->test_api_key : $this->live_api_key;
    $this->api_secret       = $this->use_test_api ? $this->test_api_secret : $this->live_api_secret;
    $this->capture            = strcmp($this->settings['capture'], 'yes') == 0;



    add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
    add_action('admin_notices', array(&$this, 'check_ssl'));

    wp_enqueue_script('the_navaska_js', 'https://js.navaska.com.br/v1/navaska.js');
  }

  public function check_ssl() {
    if(!$this->use_test_api && get_option('woocommerce_force_ssl_checkout') == 'no' && $this->enabled == 'yes') {
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

  public function process_payment( $order_id ) {
    global $woocommerce;
    $this->order = new WC_Order( $order_id );

    if ($this->navaska_processing()) {
      $this->complete_order();

      $result = array(
          'result' => 'success',
          'redirect' => $this->get_return_url($this->order)
      );
      return $result;
    }
    else{
      $this->mark_as_failed_payment();
      $woocommerce->add_error(__('Transaction Error: Could not complete your payment'), 'woothemes');
    }
  }

  protected function navaska_processing() {
    global $woocommerce;

    $api_keys = $this->api_key . ":" . $this->api_secret;
    $data = $this->get_params();

    try {
      $charge = Charge::create(array(
                  "amount"      => $data['amount'], // amount in cents, again
                  "currency"    => $data['currency'],
                  "card"        => $data['token'],
                  "description" => $data['description'],
                  "capture"     => !$this->capture
                ),
              $api_keys);

      $this->transaction_id = $charge['id'];

      update_post_meta( $this->order->id, 'transaction_id', $this->transaction_id);
      update_post_meta( $this->order->id, 'key', $this->api_key);
      update_post_meta( $this->order->id, 'auth_capture', $this->capture);
      return true;

    } catch(\Navaska\Error $e) {
      $body = $e->get_json_body();
      $err  = $body['error'];
      error_log('Navaska Error:' . $err['message'] . "\n");
      $woocommerce->add_error(__('Payment error:', 'woothemes') . $err['message']);
      return false;
    }
  }

  protected function get_params() {
    if ($this->order AND $this->order != null) {
      return array(
          "amount"      => (float)$this->order->get_total() * 100,
          "currency"    => strtolower(get_woocommerce_currency()),
          "token"       => $_POST['navaska_token'],
          "description" => sprintf("Pagamento para %s", $this->order->billing_email),
          "card"        => array(
              "name"            => sprintf("%s %s", $this->order->billing_first_name, $this->order->billing_last_name),
              "address_line1"   => $this->order->billing_address_1,
              "address_line2"   => $this->order->billing_address_2,
              "address_zip"     => $this->order->billing_postcode,
              "address_state"   => $this->order->billing_state,
              "address_country" => $this->order->billing_country
          )
      );
    }
    return false;
  }

  protected function complete_order() {
    global $woocommerce;

    if ($this->order->status == 'completed')
        return;

    $this->order->payment_complete();
    $woocommerce->cart->empty_cart();

    $this->order->add_order_note(
        sprintf(
            "Pagamento processado pelo Navaska: Transação: '%s'",
            $this->transaction_id
        )
    );
    unset($_SESSION['order_awaiting_payment']);
  }

  protected function mark_as_failed_payment() {
      $this->order->add_order_note(
          sprintf(
            "O Pagamento com Cartão Falhou com a seguinte mensagem: '%s'",
            $this->transaction_error_message
          )
      );
    }
}

function navaska_add_credit_card_gateway_class( $methods ) {
  $methods[] = 'Navaska';
  return $methods;
}

add_filter( 'woocommerce_payment_gateways', 'navaska_add_credit_card_gateway_class' );