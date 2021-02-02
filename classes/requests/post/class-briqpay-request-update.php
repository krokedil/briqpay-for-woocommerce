<?php
/**
 * @package Briqpay_For_WooCommerce/Classes/Requests/Post
 */


/**
 * Class Briqpay_Request_Update
 */
class Briqpay_Request_Update extends Briqpay_Request_Post {


	/**
	 * Returns the arguments request.
	 *
	 * @return array
	 */
	protected function get_body() {
		$amount    = intval( round( WC()->cart->get_total( 'calculations' ) * 100 ) );
		$order_id  = isset( $this->arguments['order_id'] ) ? $this->arguments['order_id'] : null;
		$body_args = array(
			'sessionid'    => $this->arguments['session_id'],
			'currency'     => get_woocommerce_currency(),
			'locale'       => str_replace( '_', '-', strtolower( get_locale() ) ),
			'country'      => WC()->customer->get_billing_country(),
			'merchanturls' => Briqpay_Helper_MerchantUrls::get_urls( $order_id ),
			'cart'         => Briqpay_Helper_Cart::get_cart_items(),
			'amount'       => $amount,
			// 'billingaddress'  => Briqpay_Helper_Customer::get_billing_data(),.
			// 'shippingaddress' => Briqpay_Helper_Customer::get_shipping_data(),.
			'reference'    => array(
				'reference1' => '',
				'reference2' => '',
			),
		);
		if ( null !== $order_id ) {
			$order                  = wc_get_order( $order_id );
			$body_args['reference'] = array(
				'reference1' => $order->get_order_number(),
				'reference2' => $order_id,
			);
		}
		return $body_args;
	}

	/**
	 * Get the request url.
	 *
	 * @return string
	 */
	protected function get_request_url() {
		return $this->get_api_url_base() . 'checkout/v1/sessions/update';
	}
}
