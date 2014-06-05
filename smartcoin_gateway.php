<?php
require_once('lib/smartcoin-php/lib/SmartCoin.php');


class SmartCoin extends WC_Payment_Gateway {

  protected $GATEWAY_NAME               = "SmartCoin";
  protected $use_test_api               = true;
  protected $order                      = null;
  protected $transaction_id             = null;
  protected $transaction_error_message  = null;

  public function __construct() {
    $this->id = 'SmartCoin';
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

    //tmp fields
    $this->test_sift_account = $this->get_option('test_sift_account');
    $this->test_sift_api_key = $this->get_option('test_sift_api_key');
    $this->live_sift_account = $this->get_option('live_sift_account');
    $this->live_sift_api_key = $this->get_option('live_sift_api_key');
    $this->sift_account      = $this->use_test_api ? $this->test_sift_account : $this->live_sift_account;
    $this->sift_api_key      = $this->use_test_api ? $this->test_sift_api_key : $this->live_sift_api_key;


    add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
    add_action('admin_notices', array(&$this, 'check_ssl'));

    wp_enqueue_script('the_smartcoin_js', 'https://js.smartcoin.com.br/v1/smartcoin.js');
  }

  public function check_ssl() {
    if(!$this->use_test_api && get_option('woocommerce_force_ssl_checkout') == 'no' && $this->enabled == 'yes') {
      echo __('<div class="error"><p>SmartCoin test API is disable and force SSL option is disable. Please enable SSL and ensure your server has valid SSL certificate.</p></div>','woothemes');
    }
  }

  public function init_form_fields() {
    $this->form_fields = array(
        'enabled' => array(
            'type'        => 'checkbox',
            'title'       => __('Enable/Disable', 'woothemes'),
            'label'       => __('Enable SmartCoin Credit Card Payment', 'woothemes'),
            'default'     => 'yes'
          ),
        'debug' => array(
            'type'        => 'checkbox',
            'title'       => __('Test mode (sandbox)', 'woothemes'),
            'label'       => __('Turn on the test mode', 'woothemes'),
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
          ),
        //temp field
        'test_sift_account' => array(
            'type'        => 'text',
            'title'       => __('Test Sift Account', 'woothemes'),
            'default'     => __('','woothemes')
          ),
        'test_sift_api_key' => array(
            'type'        => 'text',
            'title'       => __('Test Sift API Key', 'woothemes'),
            'default'     => __('','woothemes')
          ),
        'live_sift_account' => array(
            'type'        => 'text',
            'title'       => __('Live Sift Account', 'woothemes'),
            'default'     => __('','woothemes')
          ),
        'live_sift_api_key' => array(
            'type'        => 'text',
            'title'       => __('Live Sift API Key', 'woothemes'),
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

    if ($this->smartcoin_processing()) {
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

  protected function smartcoin_processing() {
    global $woocommerce;

    $api_keys = $this->api_key . ":" . $this->api_secret;
    $data = $this->get_params();

    try {
      $this->send_data_to_sift();
      $charge = Charge::create(array(
                  "amount"      => $data['amount'], // amount in cents, again
                  "currency"    => $data['currency'],
                  "card"        => $data['token'],
                  "description" => $data['description'],
                  "reference"   => $data['reference']
                ),
              $api_keys);

      $this->transaction_id = $charge['id'];

      update_post_meta( $this->order->id, 'transaction_id', $this->transaction_id);
      update_post_meta( $this->order->id, 'key', $this->api_key);
      return true;

    } catch(\SmartCoin\Error $e) {
      $body = $e->get_json_body();
      $err  = $body['error'];
      error_log('SmartCoin Error:' . $err['message'] . "\n");
      $woocommerce->add_error(__('Payment error:', 'woothemes') . $err['message']);
      return false;
    }
  }

  protected function get_params() {
    if ($this->order AND $this->order != null) {
      return array(
          "amount"      => $this->order->get_total() * 100,
          "currency"    => strtolower(get_woocommerce_currency()),
          "token"       => $_POST['smartcoin_token'],
          "description" => sprintf("Pagamento para %s", $this->order->billing_email),
          "reference"   => $this->order->id,
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

  protected function send_data_to_sift() {
    if ($this->order AND $this->order != null) {
      $items_list = array();
      error_log('Sift order params:' . $this->order->id . "\n");
      foreach ($this->order->get_items() as $i ) {
        $item = array(
          '$item_id'        => $i->item_meta,
          '$product_title'  => $i->name,
          '$price'          => $i->line_total * 1000000,
          //'$upc'            => '097564307560',
          //'$sku'            => '03586005',
          //'$brand'          => 'Peters Kettle Corn',
          //'$category'       => 'Food and Grocery',
          '$quantity'       => $i->qty
        );

        error_log('Sift item params:' . implode (',' , $item) . '\n');
        $items_list[] = $item;
      }

      error_log('Sift item list:' . implode (',' ,$items_list) . '\n');

      $sift_params = array(
        '$type'             => '$create_order',
        '$api_key'          => $this->sift_api_key,
        '$session_id'       => $_COOKIE['smartcoin_sys'],
        '$user_id'          => $this->order->billing_email,
        '$order_id'         => $this->order->id,
        '$user_email'       => $this->order->billing_email,
        '$amount'           => $this->order->get_total() * 1000000,
        '$currency_code'    => strtolower(get_woocommerce_currency()),
        '$billing_address'  => array(
            '$name'             => sprintf("%s %s", $this->order->billing_first_name, $this->order->billing_last_name),
            '$address_1'         => $this->order->billing_address_1,
            '$address_2'         => $this->order->billing_address_2,
            '$city'             => $this->order->billing_city,
            '$region'           => $this->order->billing_state,
            '$country'          => $this->order->billing_country,
            '$zipcode'          => $this->order->billing_postcode,
            '$phone'            => $this->order->billing_phone
          ),
        '$payment_methods'  => array(
            '$payment_type'     => '$credit_card',
            '$payment_gateway'  => '$stripe',
            '$card_last4'       => $_POST['card_last4']
          ),
        '$shipping_address' => array(
            '$address_1'         => $this->order->shipping_address_1,
            '$address_2'         => $this->order->shipping_address_2,
            '$city'             => $this->order->shipping_city,
            '$region'           => $this->order->shipping_state,
            '$country'          => $this->order->shipping_country,
            '$zipcode'          => $this->order->shipping_postcode
          ),
        '$expedited_shipping' => true,
        '$items'              => $items_list,
        '$is_first_time_buyer' => false 
      );

      error_log('Sift params before:' . implode (',' ,$sift_params) . "\n");
      error_log('Sift params billing:' . implode (',' ,$sift_params['$billing_address']) . "\n");
      error_log('Sift params shipping:' . implode (',' ,$sift_params['$shipping_address']) . "\n");
      error_log('Sift params payment:' . implode (',' ,$sift_params['$payment_methods']) . "\n");
      error_log('Sift params items:' . implode (',' ,$sift_params['$items'][0]) . "\n");

      $url = 'https://api.siftscience.com/v203/events';
      $ch = curl_init($url);
       
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $sift_params);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept: application/json"));

      error_log('Sift params:' . implode (',' ,$sift_params) . "\n");
      if($response = curl_exec($ch)) {
        unset($_COOKIE['smartcoin_sys']);
        setcookie('smartcoin_sys', null, -1, '/');
        error_log('Sift response: ' . $response);  
      }
      curl_close($ch);
      return true;
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
            "Pagamento processado pelo SmartCoin: Transação: '%s'",
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

function smartcoin_add_credit_card_gateway_class( $methods ) {
  $methods[] = 'SmartCoin';
  return $methods;
}

add_filter( 'woocommerce_payment_gateways', 'smartcoin_add_credit_card_gateway_class' );