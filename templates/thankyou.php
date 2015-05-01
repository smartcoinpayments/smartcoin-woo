<div class="woocommerce-message">
	<?php if($charge_type == 'bank_slip'){ ?>
	  <span>
	  	<?php echo sprintf( __( 'Thank you. Your order has been received. But we are awaiting the payment confirmation.', 'smartcoin-woo' )); ?>
	  	</br>
	  	</br>
	  	<?php echo sprintf( __( 'Your bank slip bar code is:', 'smartcoin-woo' )); ?>
	  	<strong><?php echo sprintf('%s',$bank_slip_number, true); ?></strong>
	  	</br>
	  	</br>
			<a href="<?php echo $bank_slip_link; ?>" target="_blank" class="button pay"><?php echo sprintf(__( 'Print Bank Slip', 'smartcoin-woo' )); ?></a>  	
			</br>
			</br>
	  </span>    
	<?php }else { ?>
		  <span><?php echo sprintf( __( 'Thank you. Your order has been received.', 'smartcoin-woo' )); ?></span>
	<?php } ?>
</div>

