<?php
/**
 * @package Briqpay_For_WooCommerce/Classes/Requests/Post
 */


/**
 * Class Briqpay_Request_Patch
 */
class Briqpay_Request_Patch extends Briqpay_Request_Post {


	/**
	 * Returns the arguments request.
	 *
	 * @return array
	 */
	protected function get_body() {
		$order_id = $this->arguments['order_id'] ?? null;
		$order    = wc_get_order( $order_id );
		return array(
			'sessionid'    => $this->arguments['session_id'],
			'merchanturls' => Briqpay_Helper_MerchantUrls::get_urls( $order_id ),
			'reference'    => array(
				'reference1' => $order->get_order_number(),
				'reference2' => $order_id,
			),
		);
	}

	/**
	 * Get the request url.
	 *
	 * @return string
	 */
	protected function get_request_url() {
		return $this->get_api_url_base() . 'checkout/v1/sessions/patch';
	}
}
