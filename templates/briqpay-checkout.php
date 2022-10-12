<?php
/**
 * Briqpay Checkout page.
 *
 * Overrides /checkout/form-checkout.php
 *
 * @package Briqpay_For_WooCommerce/Templates
 */

/**
 * Checkout reference.
 *
 * @var $checkout WC_Checkout
 */
wc_print_notices();

do_action( 'woocommerce_before_checkout_form', WC()->checkout() );

// if checkout registration is disabled and not logged in, the user cannot checkout.
if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html(
		apply_filters(
			'woocommerce_checkout_must_be_logged_in_message',
			__( 'You must be logged in to checkout.', 'woocommerce' )
		)
	);

	return;
}
$show_snippet = false;
$settings     = get_option( 'woocommerce_briqpay_settings' );
if ( 'yes' === $settings['testmode'] ) {
	if ( ! empty( $settings['test_merchant_id'] ) && ! empty( $settings['test_shared_secret'] ) ) {
		$show_snippet = true;
	}
} elseif ( ! empty( $settings['merchant_id'] ) && ! empty( $settings['shared_secret'] ) ) {
	$show_snippet = true;
}
?>
<form name="checkout" class="checkout woocommerce-checkout">
	<?php do_action( 'briqpay_wc_before_wrapper' ); ?>
	<div id="briqpay-wrapper">
		<div id="briqpay-order-review">
			<?php do_action( 'briqpay_wc_before_order_review' ); ?>
			<?php woocommerce_order_review(); ?>
			<?php do_action( 'briqpay_wc_after_order_review' ); ?>
		</div>
		<div id="briqpay-iframe-wrapper">
			<?php do_action( 'briqpay_wc_before_snippet' ); ?>
			<?php
			( true === $show_snippet ) ? briqpay_wc_show_snippet() : wc_print_notice( 'The Briqpay Credentials are incorrect! Please enter the valid credentials and try again.', 'error' );
			?>
			<?php do_action( 'briqpay_wc_after_snippet' ); ?>
		</div>
	</div>
	<?php do_action( 'briqpay_wc_after_wrapper' ); ?>
</form>
<?php do_action( 'briqpay_wc_after_checkout_form' ); ?>
