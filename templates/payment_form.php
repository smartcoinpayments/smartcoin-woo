<?php

?>
<div id="navaska_api_key" class="hidden" style="display:none" data-apikey="<?=$this->api_key; ?>"></div>
<div class="clear"></div>
<span class="payment-errors required"></span>
<p class="form-row">
  <label>Número do Cartão <span class="required">*</span></label>
  <input class="input-text" type="text" size="19" maxlength="19" data-navaska="number" style="width:400px;"/>
</p>
<div class="clear"></div>
<p class="form-row form-row-first" style="width:125px;">
  <label>Mês de Expiração<span class="required">*</span></label>
  <select data-navaska="exp_month">
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
  <select data-navaska="exp_year" >
    <?php
      $today = (int)date('Y',time());
      for($i = 0; $i < 10; $i++) {
        echo "<option value=${today}>${today}</option>";
        $today++;
      }
    ?>
  </select>
</p>
<p class="form-row form-row-last" style="width:150px;">
<label>Código de Segurança <span class="required">*</span></label>
<input class="input-text" type="text" size="4" maxlength="4" data-navaska="cvc" style="width:65px;"/>
</p>
<div class="clear"></div>

<script>
  init_navaska = function() {
    jQuery(function($){
      var $form = $('form.checkout,form#order_review');

      var navaska_map = {

        billing_address_1:  'address_line1',
        billing_address_2:  'address_line2',
        billing_city:       'address_city',
        billing_country:    'address_country',
        billing_state:      'address_state',
        billing_postcode:   'address_cep',
      }

      var card_name = '';
      $('form.checkout').find('input[id*=billing_],select[id*=billing_]').each(function(idx,el) {
        var mapped = navaska_map[el.id];
        if (mapped) {
            $(el).attr('data-navaska',mapped);
        }
        if(el.id == 'billing_first_name' || el.id == 'billing_last_name') {
            card_name += $(el).val() + ' ';
        }
      });

      if (!$('#navaska_card_name').length) {
        $('<input id="navaska_card_name" class="input-text" type="hidden" data-navaska="name" value="'+card_name+'"/>').appendTo($form);
      }

      $('input[data-navaska="number"]').payment('formatCardNumber');
      $('input[data-navaska=cvc]').payment('formatCardCVC');

      var navaska_response_handler = function(response) {
        if (response.error) {
          $form.find('.payment-errors').text(response.error.message);
          $form.unblock();
        } else {
          $form.append($('<input type="hidden" name="navaska_token" />').val(response.id));
          $form.submit();
        }
      };

      var navaska_validate_payment_form = function() {
        var msg = '';
        if(!$.payment.validateCardNumber($('input[data-navaska="number"]').val()))
          msg += 'Número do cartão inválido; ' ;

        if(!$.payment.validateCardExpiry($('select[data-navaska="exp_month"]').val(),$('select[data-navaska="exp_year"]').val()))
          msg += 'Data de expiração inválida; ';

        if(!$.payment.validateCardCVC($('input[data-navaska="cvc"]').val()))
          msg += 'Código de segurança inválido; ';

        $form.find('.payment-errors').text(msg);

        return msg.length == '';
      };

      $('body').on('click', '#place_order,form#order_review input:submit', function(){
        if($('input[name=payment_method]:checked').val() != 'Navaska'){
            return true;
        }

        if(!navaska_validate_payment_form()) {
          return false;
        }

        $form.find('.payment-errors').html('');
        $form.block({message: null,overlayCSS: {background: "#fff url(" + woocommerce_params.ajax_loader_url + ") no-repeat center",backgroundSize: "16px 16px",opacity: .6}});

        if( $form.find('[name=navaska_token]').length)
          return true;


        $('form.checkout').find('[name=navaska_token]').remove();
        Navaska.set_api_key($('#navaska_api_key').data('apikey'));
        Navaska.create_token($form, navaska_response_handler);
        return false;
      });

    });
  };

  if(typeof $=='undefined')
    $ = jQuery;

  var headTag = document.getElementsByTagName("head")[0];
  var jqTag = document.createElement('script');
  jqTag.type = 'text/javascript';
  jqTag.src = 'https://js.navaska.com.br/v1/jquery.payment.js';
  jqTag.onload = init_navaska;
  headTag.appendChild(jqTag);
</script>