<?php
/**
 * @package Briqpay_For_WooCommerce/Classes/Requests/Post
 */


/**
 * Class Briqpay_Request_Refund
 */
class Briqpay_Request_Refund extends Briqpay_Request_Post {


	/**
	 * Returns the arguments request.
	 *
	 * @return array
	 */
	protected function get_body() {
		$order = wc_get_order( $this->arguments['order_id'] );
		return array(
			'sessionid' => $this->arguments['session_id'],
			'amount'    => Briqpay_Helper_Order_Lines::get_order_amount( $order, true ),
			'cart'      => Briqpay_Helper_Order_Lines::get_order_lines( $order, true ),
		);
	}

	/**
	 * Get the request url.
	 *
	 * @return string
	 */
	protected function get_request_url() {
		return $this->get_api_url_base() . 'order-management/v1/refund-order';
	}
}
