<?php
/**
 * @package Briqpay_For_WooCommerce/Classes/Requests/Post
 */


/**
 * Class Briqpay_Request_Create
 */
class Briqpay_Request_Create extends Briqpay_Request_Post {


	/**
	 * Returns the arguments request.
	 *
	 * @return array
	 */
	protected function get_body() {
		$amount = intval( round( WC()->cart->get_total( 'calculations' ) * 100 ) );
		return array(
			'currency'     => 'SEK',
			'locale'       => 'sv-se',
			'country'      => WC()->customer->get_billing_country(),
			'merchanturls' => array(
				'terms'         => get_permalink( wc_get_page_id( 'terms' ) ),
				'notifications' => home_url( '/wc-api/BRIQPAY_WC_NOTIFICATION' ),
				'redirecturl'   => home_url( '?briqpay-success' ),
			),
			'cart'         => Briqpay_Helper_Cart::get_cart_items(),
			'amount'       => $amount,
		// 'billingaddress'  => Briqpay_Helper_Customer::get_billing_data(),
		// 'shippingaddress' => Briqpay_Helper_Customer::get_shipping_data(),
		);
	}

	/**
	 * Get the request url.
	 *
	 * @return string
	 */
	protected function get_request_url() {
		return $this->get_api_url_base() . 'checkout/v1/sessions';
	}
}
