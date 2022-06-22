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
		$order = wc_get_order( $this->arguments['order_id'] );
		return apply_filters(
			'briqpay_update_orm_order',
			array(
				'sessionid' => get_post_meta( $order->get_id(), '_briqpay_session_id', true ),
				'amount'    => Briqpay_Helper_Order_Lines::get_order_amount( $order, false ),
				'cart'      => Briqpay_Helper_Order_Lines::get_order_lines( $order ),
			)
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
