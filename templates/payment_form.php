<?php

?>
<div id="smartcoin_api_key" class="hidden" style="display:none" data-apikey="<?=$this->api_key; ?>"></div>
<div class="clear"></div>
<span class="payment-errors required"></span>
<p class="form-row">
  <label>Número do Cartão <span class="required">*</span></label>
  <input class="input-text" type="text" size="19" maxlength="19" data-smartcoin="number" style="width:400px;"/>
</p>
<div class="clear"></div>
<p class="form-row">
  <label>Nome como no cartão <span class="required">*</span></label>
  <input class="input-text" type="text" data-smartcoin="name" id="smartcoin_card_name" style="width:400px;"/>
</p>
<div class="clear"></div>
<p class="form-row form-row-first" style="width:125px;">
  <label>Mês de Expiração<span class="required">*</span></label>
  <select data-smartcoin="exp_month">
    <option value=1>01</option>
    <option value=2>02</option>
    <option value=3>03</option>
    <option value=4>04</option>
    <option value=5>05</option>
    <option value=6>06</option>
    <option value=7>07</option>
    <option value=8>08</option>
    <option value=9>09</option>
    <option value=10>10</option>
    <option value=11>11</option>
    <option value=12>12</option>
  </select>
</p>
<p class="form-row form-row-first" style="width:125px;">
  <label>Ano de Expiração<span class="required">*</span></label>
  <select data-smartcoin="exp_year" >
    <?php
      $today = (int)date('Y',time()) + 1;
      for($i = 0; $i < 10; $i++) {
        echo "<option value=${today}>${today}</option>";
        $today++;
      }
    ?>
  </select>
</p>
<p class="form-row form-row-last" style="width:150px;">
<label>Código de Segurança <span class="required">*</span></label>
<input class="input-text" type="text" size="4" maxlength="4" data-smartcoin="cvc" style="width:65px;"/>
</p>
<div class="clear"></div>

<script>
  init_smartcoin = function() {
    jQuery(function($){
      var $form = $('form.checkout,form#order_review');

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

      $('input[data-smartcoin="number"]').payment('formatCardNumber');
      $('input[data-smartcoin=cvc]').payment('formatCardCVC');

      var smartcoin_response_handler = function(response) {
        if (response.error) {
          $form.find('.payment-errors').text(response.error.message);
          $form.unblock();
        } else {
          $form.append($('<input type="hidden" name="smartcoin_token" />').val(response.id));
          $form.submit();
        }
      };

      var smartcoin_validate_payment_form = function() {
        var msg = '';
        if(!$.payment.validateCardNumber($('input[data-smartcoin="number"]').val()))
          msg += 'Número do cartão inválido; ' ;

        if($('input[data-smartcoin="name"]').val() == 0)
          msg += 'Nome não pode estar vazio; ' ;

        if(!$.payment.validateCardExpiry($('select[data-smartcoin="exp_month"]').val(),$('select[data-smartcoin="exp_year"]').val()))
          msg += 'Data de expiração inválida; ';

        if(!$.payment.validateCardCVC($('input[data-smartcoin="cvc"]').val()))
          msg += 'Código de segurança inválido; ';

        $form.find('.payment-errors').text(msg);

        return msg.length == '';
      };

      $('body').on('click', '#place_order,form#order_review input:submit', function(){
        if($('input[name=payment_method]:checked').val() != 'SmartCoin'){
            return true;
        }

        if(!smartcoin_validate_payment_form()) {
          return false;
        }

        $form.find('.payment-errors').html('');
        $form.block({message: null,overlayCSS: {background: "#fff url(" + woocommerce_params.ajax_loader_url + ") no-repeat center",backgroundSize: "16px 16px",opacity: .6}});

        if( $form.find('[name=smartcoin_token]').length)
          return true;


        $('form.checkout').find('[name=smartcoin_token]').remove();
        SmartCoin.set_api_key($('#smartcoin_api_key').data('apikey'));
        SmartCoin.create_token($form, smartcoin_response_handler);
        return false;
      });

    });
  };

  if(typeof $=='undefined')
    $ = jQuery;

  var headTag = document.getElementsByTagName("head")[0];
  var jqTag = document.createElement('script');
  jqTag.type = 'text/javascript';
  jqTag.src = 'https://js.smartcoin.com.br/v1/jquery.payment.js';
  jqTag.onload = init_smartcoin;
  headTag.appendChild(jqTag);
</script>