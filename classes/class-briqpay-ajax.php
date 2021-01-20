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
			'briqpay_get_order' => true,
			'briqpay_wc_log_js' => true,
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
	 *
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

}
Briqpay_Ajax::init();
