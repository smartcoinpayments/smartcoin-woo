 $ = jQuery;
 $(window).load(function() {
  var $form = $('form.checkout,form#order_review');
  var installments = 1;

  var smartcoin_payment_method_selected = function() {
    var result = 'credit_card';
    if($("#smartcoin_payment_method_bank_slip").attr("checked")) {
      result = 'bank_slip';
    }
    return result;
  };

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
      debugger;
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
    form.block({message: null,overlayCSS: {background: "#fff url(" + woocommerce_params.ajax_loader_url + ") no-repeat center",backgroundSize: "16px 16px",opacity: .6}});

    if( form.find('[name=smartcoin_token]').length)
      return true;

    if(_user_id == "") {
      _user_id = $('#billing_email').val();
    }

    debugger;
    if($('form.checkout').find('[name=smartcoin_installments]')){
      installments = $('form.checkout').find('[name=smartcoin_installments]').val();
      $('form.checkout').find('[name=smartcoin_installments]').remove();
    }

    form.append($('<input type="hidden" name="smartcoin_session_id" data-smartcoin="session_id" />').val(_session_id));
    form.append($('<input type="hidden" name="smartcoin_user_id" data-smartcoin="user_id" />').val(_user_id));
    $('form.checkout').find('[name=smartcoin_token]').remove();
    SmartCoin.set_api_key($('#smartcoin_api_key').data('apikey'));
    SmartCoin.create_token(form, smartcoin_response_handler);
  }

  var smartcoin_charge_bank_slip = function(form){
    form.submit();
  }

  var smartcoin_setting_credit_card_form = function() {
    $('form[name="checkout"]').card({
      container: $('.smartcoin-card-wrapper'),
      numberInput: 'input[data-smartcoin="number"]',
      expiryInput: 'select[data-smartcoin="exp_month"],select[data-smartcoin="exp_year"]',
      cvcInput: 'input[data-smartcoin="cvc"]',
      nameInput: 'input[data-smartcoin="name"]',
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

    $('body').on('click', '#place_order,form#order_review input:submit', function(){
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
    $('input#payment_method_Smartcoin').hide();
    $('label[for="payment_method_Smartcoin"]').hide();
    $("input[name=smartcoin_payment_method]:radio").change(function() {
      if($("#smartcoin_payment_method_credit_card").attr("checked")) {
        $("#smartcoin_credit_card_section").fadeIn();
      }
      else {
        $("#smartcoin_credit_card_section").fadeOut();
      }
    });

    smartcoin_setting_credit_card_form();
  }
  setTimeout(function() {
    initSmartcoinJS();
  }, 1500);
});