<?php

?>
<div id="smartcoin_api_key" class="hidden" style="display:none" data-apikey="<?=$this->api_key; ?>"></div>
<div class="clear"></div>

<!-- Bank Slip Form -->
<input type="radio" id="smartcoin_payment_method_bank_slip" name="smartcoin_payment_method" value="bank_slip" >
<label for="smartcoin_payment_method_bank_slip">Boleto Bancário</label></br>
<section id="smartcoin_bank_slip_section"  style="display: none;">
  <p class="form-row">
    Clique no botão abaixo para gerar o boleto.
  </p>
</section>

<!-- Credit Card Form -->
<input type="radio" id="smartcoin_payment_method_credit_card" name="smartcoin_payment_method" value="credit_card" checked> 
<label for="smartcoin_payment_method_credit_card">Cartão de Crédito</label></br>
<section id="smartcoin_credit_card_section">
  <div class="form-row form-row-first smartcoin-card-wrapper" ></div>
  <div class="clear"></div>
  <span class="payment-errors required"></span>
  <p class="form-row">
    <label>Número do Cartão <span class="required">*</span></label>
    <input class="input-text" type="text" data-smartcoin="number" style="width:400px;"/>
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
        $today = (int)date('Y',time());
        for($i = 0; $i < 10; $i++) {
          echo "<option value=${today}>${today}</option>";
          $today++;
        }
      ?>
    </select>
  </p>
  <p class="form-row form-row-first" style="width:150px;">
  <label>Código de Segurança <span class="required">*</span></label>
  <input class="input-text" type="text" data-smartcoin="cvc" style="width:65px;"/>
    <?php if($this->allowInstallments){ ?>
    <label>Parcelas<span class="required">*</span></label>
    <select name="smartcoin_installments" data-smartcoin="installments" >
      <?php
        for($i = 1; $i < ($this->numberOfInstallments+1); $i++) {
          echo "<option value=${i}>${i}</option>";
        }
      ?>
    </select>
  <?php }?>
  </p>
  <div class="clear"></div>
</section>

