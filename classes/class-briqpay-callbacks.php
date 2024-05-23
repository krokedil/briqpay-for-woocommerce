<?php
/**
 * Handles callbacks for the plugin.
 *
 * @package Briqpay_For_WooCommerce/Classes/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Callback class.
 */
class Briqpay_Callbacks {

	/**
	 * Class constructor.
	 */
	public function __construct() {
		add_action( 'woocommerce_api_briqpay_wc_notification', array( $this, 'notification_cb' ) );
		add_action( 'briqpay_wc_punted_notification', array( $this, 'briqpay_wc_punted_notification_cb' ), 10, 2 );
	}

	/**
	 * Handles notification callbacks.
	 *
	 * @return void
	 */
	public function notification_cb() {
		$order_id           = '';
		$briqpay_session_id = filter_input( INPUT_GET, 'sessionid', FILTER_SANITIZE_SPECIAL_CHARS );
		if ( ! empty( $briqpay_session_id ) ) {
			$order_id = briqpay_get_order_id_by_session_id( $briqpay_session_id );
			Briqpay_Logger::log( 'Notification callback hit for Briqpay session ID: ' . $briqpay_session_id . '. WC order ID: ' . $order_id );

			if ( ! empty( $order_id ) ) {
				as_schedule_single_action( time() + 120, 'briqpay_wc_punted_notification', array( $order_id, $briqpay_session_id ) );
			}
		}
		header( 'HTTP/1.1 200 OK' );
	}

	/**
	 * Punted notification callback.
	 *
	 * @param  int    $order_id WooCommerce order id.
	 * @param  string $session_id  Briqpay session id.
	 */
	public function briqpay_wc_punted_notification_cb( $order_id, $session_id ) {
		Briqpay_Logger::log( 'Execute notification callback. Briqpay session ID: ' . $session_id . '. WC order ID: ' . $order_id );

		// get order.
		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return;
		}

		// get briqpay order.
		$briqpay_order = BRIQPAY()->api->get_briqpay_order(
			array(
				'session_id' => $session_id,
			)
		);

		if ( 'purchasecomplete' !== $briqpay_order['state'] &&
		'paymentprocessing' !== $briqpay_order['state'] &&
		'purchaserejected' !== $briqpay_order['state'] ) {
			return;
		}
		if ( 'purchaserejected' === $briqpay_order['state'] ) {
			$order->add_order_note(
				__(
					'Payment could not be completed by the underlying PSP',
					'briqpay-for-woocommerce'
				)
			);
				$order->set_status( 'on-hold' );
				$order->save();

				return;
		}

		Briqpay_Confirmation::get_instance()->confirm_briqpay_order( $order_id );
	}
}
new Briqpay_Callbacks();
