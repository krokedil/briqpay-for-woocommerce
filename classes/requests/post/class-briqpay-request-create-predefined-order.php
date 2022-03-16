<?php
/**
 * @package Briqpay_For_WooCommerce/Classes/Requests/Post
 */


/**
 * Class Briqpay_Request_Create
 */
class Briqpay_Request_Create_Predefined extends Briqpay_Request_Post {

	/**
	 * Class constructor.
	 */
	public function __construct($order_id) {
		parent::__construct(array("order_id"=>$order_id));
		$this->log_title = 'Create a session';
	}
	/**
	 * Returns the arguments request.
	 *
	 * @return array
	 */
	protected function get_body() {
		$order_id = $this->arguments["order_id"];
		$order                  = wc_get_order( $order_id );
		$amount = intval( round( $order->get_total( 'calculations' ) * 100 ) );
		$newcart = array();
		foreach ( $order->get_items() as $item_id => $item ) {
			$newcart[] = $item;
			
		 };
		return apply_filters(
			'briqpay_create_args',
			array(
				'currency'       => get_woocommerce_currency(),
				'locale'         => str_replace( '_', '-', strtolower( get_locale() ) ),
				'country'        => $order->get_billing_country(),
				'merchanturls'   => Briqpay_Helper_MerchantUrls::get_urls($order_id),
				'merchantconfig' => Briqpay_Helper_Merchant_Config::get_config($order_id),
				'cart'           => Briqpay_Helper_Cart::get_order_items($newcart),
				'amount'         => $amount,
			// 'billingaddress'  => Briqpay_Helper_Customer::get_billing_data(),
			// 'shippingaddress' => Briqpay_Helper_Customer::get_shipping_data(),
			)
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
