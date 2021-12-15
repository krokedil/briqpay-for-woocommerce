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
	public function create_predefined_briqpay_order($order_id) {
		$request  = new Briqpay_Request_Create_Predefined($order_id);
		$response = $request->request();

		return $this->check_for_api_error( $response );
	}
	public function create_briqpay_hpp($order_id ,$type){
		$briqpay_order = $this->create_predefined_briqpay_order($order_id);

		 $this->patch_briqpay_order(
			array(
				'session_id' => $briqpay_order['sessionid'],
				'order_id'   => $order_id,
			)); 
			$order                  = wc_get_order( $order_id );
		$email = $order->get_billing_email();
		 $phone = $order->get_billing_phone();
		 $destination = $type === "email" ? $email : $phone;
		$request  = new Briqpay_Request_Create_HPP(array("session_id"=>$briqpay_order['sessionid'],"destination_type" =>$type,"destination" =>  $destination ),true);
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

	/**
	 * Updates a Briqpay Checkout order
	 *
	 * @param  array $args  The request arguments.
	 *
	 * @return array|false|mixed
	 */
	public function update_briqpay_order( array $args = array() ) {
		$response = ( new Briqpay_Request_Update( $args, true ) )->request();
		return $this->check_for_api_error( $response );
	}

	/**
	 * Updates merchant urls and ref.
	 *
	 * @param  array $args  The request arguments.
	 *
	 * @return array|false|mixed
	 */
	public function patch_briqpay_order( array $args = array() ) {
		$response = ( new Briqpay_Request_Patch( $args, true ) )->request();
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
