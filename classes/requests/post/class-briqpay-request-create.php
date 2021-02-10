<?php
/**
 * @package Briqpay_For_WooCommerce/Classes/Requests/Post
 */


/**
 * Class Briqpay_Request_Create
 */
class Briqpay_Request_Create extends Briqpay_Request_Post {

	/**
	 * Class constructor.
	 */
	public function __construct() {
		parent::__construct();
		$this->log_title = 'Create a session';
	}
	/**
	 * Returns the arguments request.
	 *
	 * @return array
	 */
	protected function get_body() {
		$amount = intval( round( WC()->cart->get_total( 'calculations' ) * 100 ) );
		return array(
			'currency'     => get_woocommerce_currency(),
			'locale'       => str_replace( '_', '-', strtolower( get_locale() ) ),
			'country'      => WC()->customer->get_billing_country(),
			'merchanturls' => Briqpay_Helper_MerchantUrls::get_urls(),
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
