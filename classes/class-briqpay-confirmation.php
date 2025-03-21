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
		$briqpay_confirm = filter_input( INPUT_GET, 'briqpay_confirm', FILTER_SANITIZE_SPECIAL_CHARS );
		$order_key       = filter_input( INPUT_GET, 'key', FILTER_SANITIZE_SPECIAL_CHARS );

		// Return if we dont have our parameters set.
		if ( empty( $briqpay_confirm ) || empty( $order_key ) ) {
			return;
		}

		$order_id = wc_get_order_id_by_order_key( $order_key );

		// Return if we cant find an order id.
		if ( empty( $order_id ) ) {
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
		$order = wc_get_order( $order_id );

		if ( ! empty( $order->get_date_paid() ) ) {
			return;
		}

		$session_id    = $order->get_meta( '_briqpay_session_id' );
		$briqpay_order = BRIQPAY()->api->get_briqpay_order( array( 'session_id' => $session_id ) );

		// Set post meta and complete order.
		$order->set_shipping_phone( '_shipping_phone', $briqpay_order['shippingaddress']['cellno'] );
		$order->update_meta_data( '_shipping_email', $briqpay_order['shippingaddress']['email'] );
		$order->update_meta_data( '_briqpay_payment_method', $briqpay_order['purchasepaymentmethod']['name'] );
		$order->update_meta_data( '_briqpay_psp_name', $briqpay_order['purchasepaymentmethod']['pspname'] );
		$order->update_meta_data( '_briqpay_autocapture', $briqpay_order['purchasepaymentmethod']['autocapture'] );
		$order->update_meta_data( '_briqpay_rules_result', wp_json_encode( $briqpay_order['rulesresult'] ?? array() ) );
		$order->update_meta_data( '_billing_org_nr', $briqpay_order['orgnr'] );

		$purchase_payment_method = $briqpay_order['purchasepaymentmethod'];
		if ( isset( $purchase_payment_method['pspSupportedOrderOperations'] ) ) {
			if ( true === $purchase_payment_method['pspSupportedOrderOperations']['updateOrderSupported'] ) {
				$order->update_meta_data( '_briqpay_psp_update_order_supported', $purchase_payment_method['pspSupportedOrderOperations']['updateOrderSupported'] );
			}
		}

		$order->set_payment_method_title( $briqpay_order['purchasepaymentmethod']['name'] );
		$order->add_order_note( __( 'Payment via Briqpay, session ID: ', 'briqpay-for-woocommerce' ) . $session_id );
		$order->save();

		if ( 'purchasecomplete' == $briqpay_order['state'] ) {
			$order->payment_complete( $session_id );
		}
		do_action( 'briqpay_order_confirmed', $briqpay_order, $order );
	}
}

Briqpay_Confirmation::get_instance();
