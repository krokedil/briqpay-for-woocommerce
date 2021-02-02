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

	/**Updates  a Briqpay Checkout order
	 *
	 * @param  array $args The request arguments.
	 */
	public function update_briqpay_order( array $args = array() ) {
		$response = ( new Briqpay_Request_Update( $args, true ) )->request();
		return $this->check_for_api_error( $response );
	}

	/**
	 * Capture a placed order.
	 *
	 * @param  array $args  .
	 *
	 * @return array|false
	 */
	public function capture_briqpay_order( array $args = array() ) {
		$response = ( new Briqpay_Request_Capture( $args, true ) )->request();
		return $response;
	}

	/**
	 * Refunding a captured order
	 *
	 * @param  array $args The request args.
	 *
	 * @return array|object|WP_Error
	 */
	public function refund_briqpay_order( array $args = array() ) {
		$response = ( new Briqpay_Request_Refund( $args, true ) )->request();
		return $response;
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
