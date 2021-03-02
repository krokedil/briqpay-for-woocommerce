<?php
/**
 * Order management class file.
 *
 * @package @package Briqpay_For_WooCommerce/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Order management class.
 */
class Briqpay_Order_Management {

	/**
	 * The plugin settings.
	 *
	 * @var array
	 */
	protected $settings;

	/**
	 * Class constructor.
	 */
	public function __construct() {
		add_action( 'woocommerce_order_status_completed', array( $this, 'activate_reservation' ) );
		$this->settings = get_option( 'woocommerce_briqpay_settings' );
	}

	/**
	 * Activate the order with Briqpay.
	 *
	 * @param  string $order_id  The WooCommerce order id.
	 *
	 * @return void
	 */
	public function activate_reservation( $order_id ) {
		$order = wc_get_order( $order_id );
		// If this order wasn't created using Briqpay payment method, bail.
		if ( 'briqpay' !== $order->get_payment_method() ) {
			return;
		}

		// Check briqpay settings to see if we have the order management enabled.
		$order_management = 'yes' === $this->settings['order_management'];
		if ( ! $order_management ) {
			return;
		}

		// Check if we have a payment id.
		$session_id = get_post_meta( $order_id, '_briqpay_session_id', true );
		if ( empty( $session_id ) ) {
			$order->add_order_note(
				__(
					'Briqpay reservation could not be activated. Missing Briqpay session id.',
					'briqpay-for-woocommerce'
				)
			);
			$order->set_status( 'on-hold' );
			$order->save();

			return;
		}

		// If this reservation was already activated, do nothing.
		if ( get_post_meta( $order_id, '_capture_id_', true ) ) {
			$order->add_order_note(
				__(
					'Could not activate Briqpay reservation, Briqpay reservation is already activated.',
					'briqpay-for-woocommerce'
				)
			);
			$order->set_status( 'on-hold' );
			$order->save();

			return;
		}

		$response = BRIQPAY()->api->capture_briqpay_order(
			array(
				'order_id'   => $order_id,
				'session_id' => $session_id,
			)
		);

		if ( is_array( $response ) && ! is_wp_error( $response ) ) {
			$capture_id = $response['captureid'];
			update_post_meta( $order_id, '_capture_id_', $capture_id );
			$order->add_order_note(
				__(
					'Briqpay reservation was successfully activated.',
					'briqpay-for-woocommerce'
				)
			);
		} else {
			$order->add_order_note(
				__(
					'Briqpay reservation could not be activated.',
					'briqpay-for-woocommerce'
				)
			);
			$order->set_status( 'on-hold' );
			$order->save();
		}
	}


	/**
	 *
	 */
	public function refund( $order_id, $amount ) {
		$query_args = array(
			'fields'         => 'id=>parent',
			'post_type'      => 'shop_order_refund',
			'post_status'    => 'any',
			'posts_per_page' => - 1,
		);

		$refunds         = get_posts( $query_args );
		$refund_order_id = array_search( $order_id, $refunds, true );
		if ( is_array( $refund_order_id ) ) {
			foreach ( $refund_order_id as $key => $value ) {
				$refund_order_id = $value;
				break;
			}
		}
		$order = wc_get_order( $order_id );

		$args     = array(
			'order_id'   => $refund_order_id,
			'session_id' => get_post_meta( $order_id, '_briqpay_session_id', true ),
		);
		$response = BRIQPAY()->api->refund_briqpay_order( $args );

		if ( is_wp_error( $response ) ) {
			// TODO add error handler.
			$order->add_order_note( __( 'Failed to refund the order with Briqpay', 'briqpay-for-woocommerce' ) );
			return false;
		}
		// translators: refund amount, refund id.
		$text           = __( '%1$s successfully refunded in Briqpay.. RefundID: %2$s', 'briqpay-for-woocommerce' );
		$formatted_text = sprintf( $text, wc_price( $amount ), $response['refundid'] );
		$order->add_order_note( $formatted_text );
		return true;

	}


}
