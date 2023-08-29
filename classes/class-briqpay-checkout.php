<?php
/**
 * Class for managing actions during the checkout process.
 *
 * @package Briqpay_For_WooCommerce/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for managing actions during the checkout process.
 */
class Briqpay_Checkout
{
    public function __construct() {
        add_action( 'woocommerce_after_calculate_totals', array( $this, 'update_briqpay_order' ), 9999 );
    }

    /**
	 * Update the Briqpay order after calculations from WooCommerce has run.
	 *
	 * @return void
	 */
	public function update_briqpay_order() {
		if ( ! is_checkout() ) {
			return;
		}

		if ( 'briqpay' !== WC()->session->get( 'chosen_payment_method' ) ) {
			return;
		}

		$session_id = WC()->session->get( 'briqpay_session_id' );
		if ( empty( $session_id ) ) {
			Briqpay_Logger::log( 'Missing WC session briqpay_session_id during update Briqpay order sequence.' );
			WC()->session->reload_checkout = true;
			return;
		}

        $args = array(
            'session_id' => $session_id
        );
        $briqpay_order = BRIQPAY()->api->get_briqpay_order( $args );

		if ( $briqpay_order && 'purchasecomplete' !== $briqpay_order['state'] ) {
			// If it is, update order.
			$briqpay_order = BRIQPAY()->api->update_briqpay_order( $args  );
		}

		// If cart doesn't need payment anymore - reload the checkout page.
		if ( ! WC()->cart->needs_payment() && 'purchasecomplete' !== $briqpay_order['state'] ) {
			WC()->session->reload_checkout = true;
		}
	}
}
new Briqpay_Checkout();