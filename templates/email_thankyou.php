<span class="im">
	<p class="order_details">
		<?php if($charge_type == 'bank_slip'){ ?>
		  	<?php echo sprintf( __( 'Thank you. Your order has been received. But we are awaiting the payment confirmation.', 'smartcoin-woo' )); ?>
		<?php }else { ?>
			  <?php echo sprintf( __( 'Thank you. Your order has been received.', 'smartcoin-woo' )); ?>
		<?php } ?>
	</p>
	<?php if($charge_type == 'bank_slip'){ ?>
		<p class="order_details">
			<?php echo sprintf( __( 'Your bank slip bar code is:', 'smartcoin-woo' )); ?>
		  	<strong><?php echo sprintf('%s',$bank_slip_number, true); ?></strong>
		</p>
		<p class="order_details">
			<a href="<?php echo $bank_slip_link; ?>" target="_blank" style='font-size: 100%; margin: 0; line-height: 1; cursor: pointer; position: relative; font-family: inherit; text-decoration: none; overflow: visible; padding: .618em 1em; font-weight: 700; border-radius: 3px; left: auto; color: #FFFFFF; background-color: #45B1E8; border: 0; white-space: nowrap; display: inline-block; background-image: none; box-shadow: none; -webkit-box-shadow: none; text-shadow: none;'><?php echo sprintf(__( 'Print Bank Slip', 'smartcoin-woo' )); ?></a>
		</p>
	<?php } ?>
</span>