$ = jQuery;
var submitButtonOriginalColor;
var $form = $('form.checkout,form#order_review');
var installments = 1;
var card;

var smartcoin_payment_method_selected = function() {
  var result = 'credit_card';
  if($("#smartcoin_payment_method_bank_slip").attr("checked")) {
    result = 'bank_slip';
  }
  return result;
};

var smartcoin_restore_button = function() {
  $('input#place_order').removeAttr('disabled');
  $('input#place_order').removeClass('smartcoin-loading');
  $('input#place_order').css('color', submitButtonOriginalColor);
  $('div#loading').remove();
}

var smartcoin_disable_button = function(){
  $('input#place_order').addClass('smartcoin-loading');
  $('input#place_order').attr('disabled','disabled');
  submitButtonOriginalColor = $('input#place_order').css('color');
  $('input#place_order').css('color', $('input#place_order').css('background-color'));
}

var smartcoin_validate_payment_form = function(form) {
  var msg = '<ul>';
  if(!$.payment.validateCardNumber($('input[data-smartcoin="number"]').val()))
    msg += '<li>Número do cartão inválido</li>' ;

  if($('input[data-smartcoin="name"]').val() == 0)
    msg += '<li>Nome não pode estar vazio</li>' ;

  if(!$.payment.validateCardExpiry($('select[data-smartcoin="exp_month"]').val(),$('select[data-smartcoin="exp_year"]').val()))
    msg += '<li>Data de expiração inválida</li>';

  if(!$.payment.validateCardCVC($('input[data-smartcoin="cvc"]').val(),$.payment.cardType($('input[data-smartcoin="number"]').val())))
    msg += '<li>Código de segurança inválido</li>';
  msg += '</ul>';
  form.find('.payment-errors').html(msg);

  return msg.length === '<ul></ul>'.length;
};

var smartcoin_response_handler = function(response) {
  if (response.error) {
    $form.find('.payment-errors').text(response.error.message);
    $form.unblock();
  } else {
    $('form.checkout').find('[name=smartcoin_session_id]').remove();
    $('form.checkout').find('[name=smartcoin_user_id]').remove();
    
    $form.append($('<input type="hidden" name="smartcoin_installments" />').val(installments));
    $form.append($('<input type="hidden" name="smartcoin_token" />').val(response.id));
    
    $form.submit();
  }
};

var smartcoin_charge_credit_card = function(form) {
  if(!smartcoin_validate_payment_form(form)) {
    return false;
  }

  form.find('.payment-errors').html('');
  smartcoin_disable_button();

  if(_user_id == "") {
    _user_id = $('#billing_email').val();
  }

  form.append($('<input type="hidden" name="smartcoin_session_id" data-smartcoin="session_id" />').val(_session_id));
  form.append($('<input type="hidden" name="smartcoin_user_id" data-smartcoin="user_id" />').val(_user_id));
  $('form.checkout').find('[name=smartcoin_token]').remove();

  if($('form.checkout').find('[name=smartcoin_installments]')){
    installments = $('form.checkout').find('[name=smartcoin_installments]').val();
    $('form.checkout').find('[name=smartcoin_installments]').remove();
  }

  SmartCoin.set_api_key($('#smartcoin_api_key').data('apikey'));
  SmartCoin.create_token(form, smartcoin_response_handler);
}

var smartcoin_charge_bank_slip = function(form){
  smartcoin_disable_button();
  form.submit();
}

var smartcoin_setting_credit_card_form = function() {
  $('.smartcoin-card-wrapper').html('');
  card = new Card({
    form: 'form[name="checkout"]',
    container: '.smartcoin-card-wrapper',

    formSelectors: {
        numberInput: 'input[data-smartcoin="number"]',
        expiryInput: 'select[data-smartcoin="exp_year"], select[data-smartcoin="exp_month"]',
        cvcInput: 'input[data-smartcoin="cvc"]',
        nameInput: 'input[data-smartcoin="name"]'
    },
    values: {
        name: 'Nome Completo'
    },
    // if true, will log helpful messages for setting up Card
    debug: true // optional - default false
  });

  var smartcoin_map = {

    billing_address_1:  'address_line1',
    billing_address_2:  'address_line2',
    billing_city:       'address_city',
    billing_country:    'address_country',
    billing_state:      'address_state',
    billing_postcode:   'address_cep',
  }

  $('form.checkout').find('input[id*=billing_],select[id*=billing_]').each(function(idx,el) {
    var mapped = smartcoin_map[el.id];
    if (mapped) {
      $(el).attr('data-smartcoin',mapped);
    }
  });

  $('body').on('click','input:submit#place_order', function(event){
    event.preventDefault();
    if($('input[name=payment_method]:checked').val() != 'Smartcoin'){
        return true;
    }

    if(smartcoin_payment_method_selected() === 'credit_card'){
      smartcoin_charge_credit_card($form);
    }
    else {
      smartcoin_charge_bank_slip($form);
    }
    return false;
  });

  setTimeout(function() {
    $('input[data-smartcoin="number"]').payment('formatCardNumber');
    $('input[data-smartcoin=cvc]').payment('formatCardCVC');
  }, 1000);
};

var initSmartcoinJS = function() {
  if(!_smartcoin_show_radio_button){
    $('input#payment_method_Smartcoin').hide();  
    $('label[for="payment_method_Smartcoin"]').hide();
  }
  
  $("input[name=smartcoin_payment_method]:radio").change(function() {
    if($("#smartcoin_payment_method_credit_card").attr("checked")) {
      $("#smartcoin_credit_card_section").fadeIn();
      $("#smartcoin_bank_slip_section").fadeOut();
    }
    else {
      $("#smartcoin_credit_card_section").fadeOut();
      $("#smartcoin_bank_slip_section").fadeIn();
    }
  });

  smartcoin_setting_credit_card_form();
}

jQuery(document).ready(function($) {
  $('body').on('init_add_payment_method', initSmartcoinJS);
  $('form.checkout').on('checkout_place_order_' + $('#order_review input[name=payment_method]:checked').val() ,initSmartcoinJS);
  $('body').on('updated_checkout', initSmartcoinJS);
  $('body').on('checkout_error', smartcoin_restore_button);
});