<?php
/**
 * API Class file.
 *
 * @package Briqpay_For_WooCommerce/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Briqpay_API class.
 *
 * Class that has functions for the briqpay communication.
 */
class Briqpay_API {

	/**
	 * Creates a Briqpay Checkout order.
	 *
	 * @return mixed
	 */
	public function create_briqpay_order() {
		$request  = new Briqpay_Request_Create();
		$response = $request->request();

		WC()->session->set( 'briqpay_session_id', $response['sessionid'] );

		// if ( empty( get_transient( 'briqpay_bearer_token_read_session' ) ) ) {
			// NOTE: The Bearer Token is good for 48 hours (172800 seconds).
			// TODO save token to the wc session.
			set_transient( 'briqpay_bearer_token_read_session', $response['token'], 172600 );
		// }

		return $this->check_for_api_error( $response );
	}

	/**
	 * Gets a Briqpay Checkout order
	 *
	 * @param  array $args The request arguments.
	 *
	 * @return mixed
	 */
	public function get_briqpay_order( array $args ) {
		$request  = new Briqpay_Request_Read( $args, true );
		$response = $request->request();
		return $this->check_for_api_error( $response );
	}

	/**
	 * Checks for WP Errors and returns either the response as array or a false.
	 *
	 * @param array $response The response from the request.
	 * @return mixed
	 */
	private function check_for_api_error( $response ) {
		if ( is_wp_error( $response ) ) {
			briqpay_extract_error_message( $response );
			return false;
		}
		return $response;
	}
}
