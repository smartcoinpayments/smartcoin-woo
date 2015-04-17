<?php
require_once('lib/smartcoin-php/lib/Smartcoin.php');


class Smartcoin extends WC_Payment_Gateway {

  protected $GATEWAY_NAME               = "Smartcoin";
  protected $use_test_api               = true;
  protected $order                      = null;
  protected $transaction_id             = null;
  protected $transaction_error_message  = null;

  public function __construct() {
    $this->id = 'Smartcoin';
    $this->has_fields = true;

    $this->init_form_fields();
    $this->init_settings();
    $this->supports = array(
        'refunds'
      );

    $this->title                = 'Smartcoin';
    $this->description          = '';
    $this->icon                 = '';
    $this->use_test_api         = strcmp($this->get_option('sc_debug'),'yes') == 0;
    $this->test_api_key         = $this->get_option('sc_test_api_key');
    $this->test_api_secret      = $this->get_option('sc_test_api_secret');
    $this->live_api_key         = $this->get_option('sc_live_api_key');
    $this->live_api_secret      = $this->get_option('sc_live_api_secret');
    $this->api_key              = $this->use_test_api ? $this->test_api_key : $this->live_api_key;
    $this->api_secret           = $this->use_test_api ? $this->test_api_secret : $this->live_api_secret;
    $this->allowInstallments    = strcmp($this->get_option('sc_allow_installments'),'yes') == 0;
    $this->numberOfInstallments = $this->get_option('sc_number_of_installments');

    add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
    add_action('admin_notices', array(&$this, 'check_ssl'));
    update_option('sc_debug', $this->get_option('sc_debug'));
    update_option('sc_test_api_key', $this->test_api_key);
    update_option('sc_live_api_key', $this->live_api_key);
  }

  public function check_ssl() {
    if(!$this->use_test_api && get_option('woocommerce_force_ssl_checkout') == 'no' && $this->enabled == 'yes') {
      echo __('<div class="error"><p>Smartcoin test API is disable and force SSL option is disable. Please enable SSL and ensure your server has valid SSL certificate.</p></div>','woothemes');
    }
  }

  public function init_form_fields() {
    $this->form_fields = array(
        'enabled' => array(
            'type'        => 'checkbox',
            'title'       => __('Enable/Disable', 'woothemes'),
            'label'       => __('Enable Smartcoin Credit Card Payment', 'woothemes'),
            'default'     => 'yes'
          ),
        'sc_debug' => array(
            'type'        => 'checkbox',
            'title'       => __('Test mode (sandbox)', 'woothemes'),
            'label'       => __('Turn on the test mode', 'woothemes'),
            'default'     => 'yes'
          ),
        'sc_test_api_key' => array(
            'type'        => 'text',
            'title'       => __('Test API Key', 'woothemes'),
            'default'     => __('','woothemes')
          ),
        'sc_test_api_secret' => array(
            'type'        => 'password',
            'title'       => __('Test API Secret', 'woothemes'),
            'default'     => __('','woothemes')
          ),
        'sc_live_api_key' => array(
            'type'        => 'text',
            'title'       => __('Live API Key', 'woothemes'),
            'default'     => __('','woothemes')
          ),
        'sc_live_api_secret' => array(
            'type'        => 'password',
            'title'       => __('Live API Secret', 'woothemes'),
            'default'     => __('','woothemes')
          ),
        'sc_allow_installments' => array(
            'type'        => 'checkbox',
            'title'       => __('Allow Installments', 'woothemes'),
            'default'     => __('yes','woothemes')
          ),
        'sc_number_of_installments' => array(
            'type'        => 'number',
            'title'       => __('Number max of installments', 'woothemes'),
            'default'     => __(6,'woothemes')
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
    }
  }

  protected function smartcoin_processing() {
    global $woocommerce;

    try {
      $data = $this->get_params();

      \Smartcoin\Smartcoin::api_key($this->api_key);
      \Smartcoin\Smartcoin::api_secret($this->api_secret);

      if($data['smartcoin_payment_method_type'] == 'credit_card'){
        $charge = \Smartcoin\Charge::create(array(
                  'amount' => $data['amount'],
                  'currency' => 'brl',
                  'card' => $data['token'],
                  'description' => $data['description'],
                  'reference'   => $data['reference'],
                  'installment' => $data['installments']
                ));  
        if($charge->paid){
          $this->charge = $charge;
          $this->transaction_id = $charge['id'];
          return true;  
        }else {
          $error_message = ' Desculpe, mas não conseguimos processar o seu cartão. Por favor, tente novamente com outro cartão de crédito.';
          $this->transaction_error_message = $error_message;
          wc_add_notice( __('Payment error:', 'woothemes') . $error_message, 'error' );
          return;
        }
      }else {
        $charge = \Smartcoin\Charge::create(array(
            'amount' => $data['amount'],
            'currency' => 'brl',
            'type' => 'bank_slip',
            'description' => $data['description'],
            'reference'   => $data['reference']
          ));  
      
        $this->charge = $charge;
        $this->transaction_id = $charge['id'];
        return true;  
      }
      
    } catch(\Smartcoin\Error $e) {
      $body = $e->get_json_body();
      $err  = $body['error'];
      error_log('Smartcoin Error:' . $err['message'] . "\n");
      $this->transaction_error_message = 'Smartcoin Error:' . $err['message'] . "\n";
      wc_add_notice( __('Payment error:', 'woothemes') . $err['message'], 'error' );
      return;
    }
  }

  protected function get_params() {
    if ($this->order AND $this->order != null) {
      return array(
          "smartcoin_payment_method_type" => $_POST['smartcoin_payment_method'],
          "amount"      => $this->order->get_total() * 100,
          "currency"    => strtolower(get_woocommerce_currency()),
          "token"       => $_POST['smartcoin_token'],
          "installments"       => $_POST['smartcoin_installments'],
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

  protected function complete_order() {
    global $woocommerce;

    if ($this->order->status == 'completed')
      return;

    $fee = 0;
    foreach ($this->charge->fees as $fee){
      $fee += $fee->amount;
    }
    $fee = $fee / 100.0;

    $status = ($this->charge->paid ? 'Pago' : 'Pendente');
    $processed = gmdate('d-m-Y h:i:s',$this->charge->created);
    $mode = (strcmp($this->get_option('sc_debug'),'yes') == 0 ? 'test' : 'live' );
    $type = ($this->charge->type == 'credit_card' ? 'Cartão de Crédito' : 'Boleto Bancário');
    update_post_meta( $this->order->id, 'charge_type', $this->charge->type);

    if($this->charge->type == 'credit_card'){
      $this->order->payment_complete($this->transaction_id);
      $this->order->add_order_note(
        sprintf(
          "Smartcoin Transaction Details: \n
          Tipo: %s
          Smartcoin ID: %s
          Amount: R$ %.2f
          Status: %s
          Processed on: %s
          Currency: BRL
          Credit card: %s (Exp.: %s/%s)
          Installments: %s
          Processing Fee: R$ %.2f
          Mode: %s
          ",
          $type,
          $this->charge->id,
          $this->charge->amount / 100.0,
          $status,
          $processed,
          $this->charge->card->type,
          $this->charge->card->exp_month,
          $this->charge->card->exp_year,
          $this->charge->installments,
          $fee,
          $mode
        )
      );
    }
    else {
      update_post_meta( $this->order->id, 'bank_slip_number', $this->charge->bank_slip->bar_code);
      update_post_meta( $this->order->id, 'bank_slip_link', $this->charge->bank_slip->link);

      $this->order->update_status('on-hold', __('Pending payment', 'smartcoin'));
      $this->msg['message'] = "Thank you for shopping with us. Right now your payment staus is pending, We will keep you posted regarding the status of your order through e-mail";
      $this->msg['class'] = 'woocommerce_message woocommerce_message_info';
                                               
      $this->order->add_order_note(
        sprintf(
          "Smartcoin Transaction Details: \n
          Tipo: %s
          Smartcoin ID: %s
          Amount: R$ %.2f
          Status: %s
          Processed on: %s
          Currency: BRL
          Processing Fee: R$ %.2f
          Mode: %s
          ",
          $type,
          $this->charge->id,
          $this->charge->amount / 100.0,
          $status,
          $processed,
          $fee,
          $mode
        )
      );
    }

    $woocommerce->cart->empty_cart();
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

  /**
   * Process refund
   *
   * Overriding refund method
   *
   * @access      public
   * @param       int $order_id
   * @param       float $amount
   * @param       string $reason
   * @return      mixed True or False based on success, or WP_Error
   */
  public function process_refund($order_id, $amount = null, $reason = '') {
    $this->order = new WC_Order($order_id);
    $this->transaction_id = $this->order->get_transaction_id();

    if (!$this->transaction_id) {
      return new WP_Error( 'scwc_refund_error',
        sprintf(
          __( '%s Credit Card Refund failed because the Transaction ID is missing.', 'smartcoin-woo' ),
          get_class( $this )
        )
      );
    }

    try {
      $refund_data = array();
      // If the amount is set, refund that amount, otherwise the entire amount is refunded
      if($amount){
        $refund_data['amount'] = $amount * 100;
      }
      // If a reason is provided, add it to the Smartcoin metadata for the refund
      // if($reason){
      //   $refund_data['metadata']['reason'] = $reason;
      // }

      \Smartcoin\Smartcoin::api_key($this->api_key);
      \Smartcoin\Smartcoin::api_secret($this->api_secret);
      $ch = \Smartcoin\Charge::retrieve($this->transaction_id);
      // Send the refund to the Smartcoin API
      $ch->refund($refund_data);
      return $ch->to_json();
    } catch(\Smartcoin\RequestError $e) {
      $body = $e->get_json_body();
      $err  = $body['error'];
      $this->transaction_error_message = $err['message'];
      $this->order->add_order_note(
        sprintf(
          __( '%s Credit Card Refund Failed with message: "%s"', 'smartcoin-woo' ),
          get_class( $this ),
          $this->transaction_error_message
        )
      );
      return new WP_Error( 'scwc_refund_error', $this->transaction_error_message );
    } catch(\Smartcoin\Error $e) {
      $body = $e->get_json_body();
      $err  = $body['error'];
      $this->transaction_error_message = $err['message'];
      $this->order->add_order_note(
        sprintf(
          __( '%s Credit Card Refund Failed with message: "%s"', 'smartcoin-woo' ),
          get_class( $this ),
          $this->transaction_error_message
        )
      );
      return new WP_Error( 'scwc_refund_error', $this->transaction_error_message );
    } catch(Exception $e) {
      return new WP_Error( 'scwc_refund_error', $e->getMessage());
    }
    return false;
  }
}

function smartcoin_add_credit_card_gateway_class( $methods ) {
  $methods[] = 'Smartcoin';
  return $methods;
}

function add_order_email_instructions($order, $sent_to_admin) {
  $output = '';
  if(!$sent_to_admin){
    if(get_post_meta( $order->id, 'charge_type', true) == 'bank_slip') {
      $output .= "<p><". _e( 'Your bank slip bar code is:', 'woocommerce' ) . "</p>";
      $output .= "<p><strong>" . get_post_meta( $order->id, 'bank_slip_number', true) . "</strong></p>";
      printf( __( '%s', 'woocommerce'), "<p><a href='" . get_post_meta( $order->id, 'bank_slip_link', true) . "' target='_blank' style='font-size: 100%; margin: 0; line-height: 1; cursor: pointer; position: relative; font-family: inherit; text-decoration: none; overflow: visible; padding: .618em 1em; font-weight: 700; border-radius: 3px; left: auto; color: #FFFFFF; background-color: #45B1E8; border: 0; white-space: nowrap; display: inline-block; background-image: none; box-shadow: none; -webkit-box-shadow: none; text-shadow: none;'>Print Bank Slip<a/></p>" );
    }
  }
  echo $output;
}

function smartcoin_woo_path() {
  return untrailingslashit(plugin_dir_path(__FILE__));
}

function smartcoin_woocommerce_locate_template( $template, $template_name, $template_path ) {
  global $woocommerce;
 
  $_template = $template;
  if(!$template_path) 
    $template_path = $woocommerce->template_url;
  
  $plugin_path  = smartcoin_woo_path() . '/woocommerce/';
 
  // Look within passed path within the theme - this is priority
  $template = locate_template(
    array(
      $template_path . $template_name,
      $template_name
    )
  );
 
  // Modification: Get the template from this plugin, if it exists
  if(file_exists($plugin_path . $template_name))
    $template = $plugin_path . $template_name;
 
  // Use default template
  if (!$template)
    $template = $_template;

  // Return what we found
  return $template;
}
