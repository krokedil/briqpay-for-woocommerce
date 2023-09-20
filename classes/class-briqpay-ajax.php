<?php
/**
 * Ajax class file.
 *
 * @package Briqpay_For_WooCommerce/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Ajax class.
 */
class Briqpay_Ajax extends WC_AJAX {
	/**
	 * Hook in ajax handlers.
	 */
	public static function init() {
		self::add_ajax_events();
	}

	/**
	 * Hook in methods - uses WordPress ajax handlers (admin-ajax).
	 */
	public static function add_ajax_events() {
		$ajax_events = array(
			'briqpay_get_order'                => true,
			'briqpay_wc_log_js'                => true,
			'briqpay_wc_change_payment_method' => true,
			'update_order_orm'                 => true,
		);
		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			add_action( 'wp_ajax_woocommerce_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_woocommerce_' . $ajax_event, array( __CLASS__, $ajax_event ) );
				// WC AJAX can be used for frontend ajax requests.
				add_action( 'wc_ajax_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			}
		}
	}


	/**
	 * Gets the Briqpay order.
	 */
	public static function briqpay_get_order() {
		$briqpay_order = BRIQPAY()->api->get_briqpay_order(
			array(
				'session_id' => WC()->session->get( 'briqpay_session_id' ),
			)
		);

		wp_send_json_success(
			array(
				'billing_address'  => $briqpay_order['billingaddress'],
				'shipping_address' => $briqpay_order['shippingaddress'],
			)
		);
		wp_die();
	}

	/**
	 * Logs messages from the JavaScript to the server log.
	 *
	 * @return void
	 */
	public static function briqpay_wc_log_js() {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_key( $_POST['nonce'] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'briqpay_wc_log_js' ) ) {
			wp_send_json_error( 'bad_nonce' );
			exit;
		}
		$posted_message     = isset( $_POST['message'] ) ? sanitize_text_field( wp_unslash( $_POST['message'] ) ) : '';
		$briqpay_session_id = WC()->session->get( 'briqpay_session_id' );
		$message            = "Frontend JS $briqpay_session_id: $posted_message";
		Briqpay_Logger::log( $message );
		wp_send_json_success();
		wp_die();
	}

	/**
	 * Refresh checkout fragment.
	 */
	public static function briqpay_wc_change_payment_method() {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_key( $_POST['nonce'] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'briqpay_wc_change_payment_method' ) ) {
			wp_send_json_error( 'bad_nonce' );
			exit;
		}
		$available_gateways = WC()->payment_gateways()->get_available_payment_gateways();
		$switch_to_briqpay  = isset( $_POST['briqpay'] ) ? sanitize_text_field( wp_unslash( $_POST['briqpay'] ) ) : '';

		if ( 'false' === $switch_to_briqpay ) {
			// Set chosen payment method to first gateway that is not Briqpay for WooCommerce.
			$first_gateway = reset( $available_gateways );
			if ( 'briqpay' !== $first_gateway->id ) {
				WC()->session->set( 'chosen_payment_method', $first_gateway->id );
			} else {
				$second_gateway = next( $available_gateways );
				WC()->session->set( 'chosen_payment_method', $second_gateway->id );
			}
		} else {
			WC()->session->set( 'chosen_payment_method', 'briqpay' );
		}

		WC()->payment_gateways()->set_current_gateway( $available_gateways );

		$redirect = wc_get_checkout_url();
		$data     = array(
			'redirect' => $redirect,
		);

		wp_send_json_success( $data );
		wp_die();
	}

	/**
	 * Updates a completed order.
	 *
	 * @return void
	 */
	public static function update_order_orm() {

		$nonce = isset( $_POST['nonce'] ) ? sanitize_key( $_POST['nonce'] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'update_order_orm' ) ) {
			wp_send_json_error( 'bad_nonce' );
			exit;
		}

		$settings = get_option( 'woocommerce_briqpay_settings' );
		$order_id = filter_input( INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT );
		$order    = wc_get_order( $order_id );
		if ( 'yes' !== $settings['order_management'] ) {
			wp_send_json_error( 'Briqpay order management is not activated.' );
			wp_die();
		}

		if ( empty( $order->get_date_paid() ) ) {
			wp_send_json_error( 'Can not sync Briqpay order since it has not been marked as paid yet.' );
			wp_die();
		}

		// Not going to do this for non-briqpay orders.
		if ( 'briqpay' !== $order->get_payment_method() ) {
			wp_send_json_error( 'Payment method is not Briqpay' );
			wp_die();
		}

		$response = BRIQPAY()->api->update_briqpay_order_orm( $order_id );
		if ( ! is_wp_error( $response ) ) {
			$order->add_order_note( 'Briqpay order successfully synced.' );
		} else {
			$order_note = 'Could not update Briqpay order lines.';
			$errors     = $response->get_error_messages();
			foreach ( $errors as $error ) {
				$order_note .= ' ' . $error['reason'] . '.';
				$order->add_order_note( $order_note );
			}
			wp_send_json_error( 'Could not update Briqpay order.' );
			wp_die();
		}
		wp_send_json_success();
		wp_die();
	}
}
Briqpay_Ajax::init();
