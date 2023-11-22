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
	 * Create a predefined Briqpay Order.
	 *
	 * @param int $order_id The WooCommerce Order Id.
	 * @return mixed
	 */
	public function create_predefined_briqpay_order( $order_id ) {
		$request  = new Briqpay_Request_Create_Predefined_Order( $order_id );
		$response = $request->request();

		return $this->check_for_api_error( $response );
	}

	/**
	 * Create a Hosted Payment Page order with Briqpay.
	 *
	 * @param int $order_id The WooCommerce order id.
	 * @return array
	 */
	public function create_briqpay_hpp( $order_id, $type ) {
		$order         = wc_get_order( $order_id );
		$briqpay_order = $this->create_predefined_briqpay_order( $order_id );

		$order->update_meta_data( '_briqpay_session_id', $briqpay_order['sessionid'] );
		$order->save();

		$this->patch_briqpay_order(
			array(
				'session_id' => $briqpay_order['sessionid'],
				'order_id'   => $order_id,
			)
		);

		$order       = wc_get_order( $order_id );
		$phone       = $order->get_billing_phone();
		$destination = $phone;

		$request = new Briqpay_Request_Create_HPP(
			array(
				'session_id'       => $briqpay_order['sessionid'],
				'destination_type' => 'sms' === $type ? 'sms' : 'link',
				'destination'      => $destination,
			),
			true
		);

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
	 * Sends a purchase decision for the v2 iFrame.
	 *
	 * @param bool $decision True if approved, false if declined.
	 * @return mixed
	 */
	public function send_purchase_decision( $decision, $session_id ) {
		$args     = array(
			'decision'   => $decision,
			'session_id' => $session_id,
		);
		$request  = new Briqpay_Request_Purchase_Decision( $args, true );
		$response = $request->request();
		return $this->check_for_api_error( $response );
	}


	/**
	 * Update a completed order ( On hold )
	 *
	 * @param int $order_id The WooCommerce order id.
	 *
	 * @return array|mixed
	 */
	public function update_briqpay_order_orm( $order_id ) {
		$request  = new Briqpay_Request_ORM_Update(
			array(
				'order_id'   => $order_id,
				'session_id' => get_post_meta(
					$order_id,
					'_briqpay_session_id',
					true
				),
			),
			true
		);
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
		}
		return $response;
	}
}
