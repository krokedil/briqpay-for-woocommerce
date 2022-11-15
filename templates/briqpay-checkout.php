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

$validate_credentials = validate_credentials();
if ( false === $validate_credentials ) {
	wc_add_notice( __( 'The Briqpay Credentials are incorrect! Please enter the valid credentials and try again.', 'briqpay-for-woocommerce' ), 'error' );
}
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
			if ( true === $validate_credentials ) {
				briqpay_wc_show_snippet();
			}
			?>
			<?php do_action( 'briqpay_wc_after_snippet' ); ?>
		</div>
	</div>
	<?php do_action( 'briqpay_wc_after_wrapper' ); ?>
</form>
<?php do_action( 'briqpay_wc_after_checkout_form' ); ?>
