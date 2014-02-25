<?php

?>
<div class="clear"></div>
<span class="payment-errors required"></span>
<p class="form-row">
  <label>Número do Cartão <span class="required">*</span></label>
  <input class="input-text" type="text" size="19" maxlength="19" style="width:400px;"/>
</p>
<div class="clear"></div>
<p class="form-row form-row-first" style="width:125px;">
  <label>Mês de Expiração<span class="required">*</span></label>
  <select>
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
  <select>
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
<input class="input-text" type="text" size="4" maxlength="4" style="width:65px;"/>
</p>
<div class="clear"></div>
