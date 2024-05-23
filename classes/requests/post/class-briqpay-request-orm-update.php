<?php
/**
 * Update a completed order
 *
 * @package Briqpay_For_WooCommerce/Classes/Requests/Post
 */

/**
 * Class Briqpay_Request_ORM_Update
 */
class Briqpay_Request_ORM_Update extends Briqpay_Request_Post {

	/**
	 * Class constructor.
	 *
	 * @param array $arguments The request arguments.
	 * @param  bool  $generate_token Checks whether generating the token based on an existing session is needed.
	 */
	public function __construct( $arguments, $generate_token ) {
		parent::__construct( $arguments, $generate_token );
		$this->log_title = 'Update a order ( ORM )';
	}
	/**
	 * Returns the arguments request.
	 *
	 * @return array
	 */
	protected function get_body() {
		$order_id = $this->arguments['order_id'];
		$order    = wc_get_order( $order_id );
		return apply_filters(
			'briqpay_update_orm_order',
			array(
				'sessionid'       => is_object( $order ) ? $order->get_meta( '_briqpay_session_id' ) : '',
				'amount'          => Briqpay_Helper_Order_Lines::get_order_amount( $order, false ),
				'billingaddress'  => Briqpay_Helper_Customer::get_billing_data_order( $order ),
				'shippingaddress' => Briqpay_Helper_Customer::get_shipping_data_order( $order ),
				'cart'            => Briqpay_Helper_Order_Lines::get_order_lines( $order ),
			),
			$order_id
		);
	}

	/**
	 * Get the request url.
	 *
	 * @return string
	 */
	protected function get_request_url() {
		return $this->get_api_url_base() . 'order-management/v1/update-order';
	}
}
