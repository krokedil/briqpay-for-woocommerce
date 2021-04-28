<?php
/**
 * Confirmation class file.
 *
 * @package Briqpay_For_WooCommerce/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Confirmation class.
 */
class Briqpay_Confirmation {

	/**
	 * The reference the *Singleton* instance of this class.
	 *
	 * @var $instance
	 */
	protected static $instance;

	/**
	 * Returns the *Singleton* instance of this class.
	 *
	 * @return self::$instance The *Singleton* instance.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Class constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'briqpay_confirm_order' ) );
	}

	/**
	 * Confirm order
	 */
	public function briqpay_confirm_order() {
		$briqpay_confirm = filter_input( INPUT_GET, 'briqpay_confirm', FILTER_SANITIZE_STRING );

		$order_key = filter_input( INPUT_GET, 'key', FILTER_SANITIZE_STRING );

		// Return if we dont have our parameters set.
		if ( empty( $briqpay_confirm ) || empty( $order_key ) ) {
			return;
		}

		$order_id = wc_get_order_id_by_order_key( $order_key );

		// Return if we cant find an order id.
		if ( empty( $order_id ) ) {
			return;
		}

		$order = wc_get_order( $order_id );

		// Check that the order status is correct before continuing.
		if ( $order->has_status( array( 'on-hold', 'processing', 'completed' ) ) ) {
			return;
		}
		$this->confirm_briqpay_order( $order_id );

		briqpay_wc_unset_sessions();
	}


	/**
	 * Confirm a normal WooCommerce order.
	 *
	 * @param int $order_id The WooCommerce order id.
	 * @return void
	 */
	public function confirm_briqpay_order( $order_id ) {
		$order         = wc_get_order( $order_id );
		$session_id    = get_post_meta( $order_id, '_briqpay_session_id', true );
		$briqpay_order = BRIQPAY()->api->get_briqpay_order( array( 'session_id' => $session_id ) );
		// Set post meta and complete order.
		update_post_meta( $order_id, '_shipping_phone', $briqpay_order['shippingaddress']['cellno'] );
		update_post_meta( $order_id, '_shipping_email', $briqpay_order['shippingaddress']['email'] );
		update_post_meta( $order_id, '_briqpay_payment_method', $briqpay_order['purchasepaymentmethod']['name'] );
		update_post_meta( $order_id, '_briqpay_psp_name', $briqpay_order['purchasepaymentmethod']['pspname'] );
		update_post_meta( $order_id, '_briqpay_rules_result', wp_json_encode( $briqpay_order['rulesresult'] ) );
		update_post_meta( $order_id, '_billing_org_nr', $briqpay_order['orgnr'] );
		$order->set_payment_method_title( 'Briqpay - ' . $briqpay_order['purchasepaymentmethod']['name'] );
		$order->add_order_note( __( 'Payment via Briqpay, session ID: ', 'briqpay-for-woocommerce' ) . $session_id );
		$order->payment_complete( $session_id );
		do_action( 'briqpay_order_confirmed', $briqpay_order, $order );
	}

}

Briqpay_Confirmation::get_instance();
