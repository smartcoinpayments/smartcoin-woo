<?php
/**
 * Thankyou page
 *
 * @author 		Smartcoin
 * @package 	Smartcoin/Templates
 * @version     0.2.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( $order ) : ?>

	<?php if ( $order->has_status( 'failed' ) ) : ?>
		<p><?php _e( 'Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction.', 'smartcoin-woo' ); ?></p>

		<p><?php
			if ( is_user_logged_in() )
				_e( 'Please attempt your purchase again or go to your account page.', 'smartcoin-woo' );
			else
				_e( 'Please attempt your purchase again.', 'smartcoin-woo' );
		?></p>

		<p>
			<a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="button pay"><?php _e( 'Pay', 'smartcoin-woo' ) ?></a>
			<?php if ( is_user_logged_in() ) : ?>
			<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="button pay"><?php _e( 'My Account', 'smartcoin-woo' ); ?></a>
			<?php endif; ?>
		</p>

	<?php else : ?>

		<?php if(get_post_meta( $order->id, 'charge_type', true) == 'bank_slip') : ?>
			<p><?php echo apply_filters( 'smartcoin_thankyou_order_received_text', __( 'Thank you. Your order has been received. But we are awaiting the payment confirmation.', 'smartcoin-woo' ), $order ); ?></p>
			<p><?php _e( 'Your bank slip bar code is:', 'smartcoin-woo' ); ?></p>
			<strong><?php echo get_post_meta( $order->id, 'bank_slip_number', true); ?></strong>
			<p><a href="<?php echo get_post_meta( $order->id, 'bank_slip_link', true); ?>" target="_blank" class="button pay"><?php _e( 'Print Bank Slip', 'smartcoin-woo' ); ?></a></p>
		<?php else : ?>
			<p><?php echo apply_filters( 'smartcoin_thankyou_order_received_text', __( 'Thank you. Your order has been received.', 'smartcoin-woo' ), $order ); ?></p>
		<?php endif; ?>
		
		<ul class="order_details">
			<li class="order">
				<?php _e( 'Order Number:', 'smartcoin-woo' ); ?>
				<strong><?php echo $order->get_order_number(); ?></strong>
			</li>
			<li class="date">
				<?php _e( 'Date:', 'smartcoin-woo' ); ?>
				<strong><?php echo date_i18n( get_option( 'date_format' ), strtotime( $order->order_date ) ); ?></strong>
			</li>
			<li class="total">
				<?php _e( 'Total:', 'smartcoin-woo' ); ?>
				<strong><?php echo $order->get_formatted_order_total(); ?></strong>
			</li>
			<?php if ( $order->payment_method_title ) : ?>
			<li class="method">
				<?php _e( 'Payment Method:', 'smartcoin-woo' ); ?>
				<strong><?php echo ((get_post_meta( $order->id, 'charge_type', true) == 'bank_slip') ? _e( 'Bank Slip', 'smartcoin-woo' ) : _e( 'Credit Card', 'smartcoin-woo' )); ?></strong>
			</li>
			<?php endif; ?>
		</ul>
		<div class="clear"></div>

	<?php endif; ?>

	<?php do_action( 'smartcoin_thankyou_' . $order->payment_method, $order->id ); ?>
	<?php do_action( 'smartcoin_thankyou', $order->id ); ?>

<?php else : ?>
	<p><?php echo apply_filters( 'smartcoin_thankyou_order_received_text', __( 'Thank you. Your order has been received.', 'smartcoin-woo' ), null ); ?></p>

<?php endif; ?>
