<?php
/**
 * @package Briqpay_For_WooCommerce/Classes/Requests/Post
 */


/**
 * Class Briqpay_Request_Create
 */
class Briqpay_Request_Capture extends Briqpay_Request_Post {


	/**
	 * Returns the arguments request.
	 *
	 * @return array
	 */
	protected function get_body() {
		$order = wc_get_order( $this->arguments['order_id'] );
		return array(
			'sessionid' => get_post_meta( $this->arguments['order_id'], '_briqpay_session_id', true ),
			'amount'    => Briqpay_Helper_Order_Lines::get_order_amount( $order ),
			'cart'      => Briqpay_Helper_Order_Lines::get_order_lines( $order ),
		);
	}

	/**
	 * Get the request url.
	 *
	 * @return string
	 */
	protected function get_request_url() {
		return $this->get_api_url_base() . 'order-management/v1/capture-order';
	}
}
