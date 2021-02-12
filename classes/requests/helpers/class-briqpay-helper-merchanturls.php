<?php
/**
 *  Merchant helper class.
 *
 * @package Briqpay_For_WooCommerce/Classes/Requests/Helpers
 */


/**
 * Class Briqpay_Helper_MerchantUrls
 */
class Briqpay_Helper_MerchantUrls {

	/**
	 * Briqpay_Helper_MerchantUrls constructor.
	 */
	private function __construct() {}


	/**
	 * Returns a merchant urls.
	 *
	 * @param  null|int $order_id The order id.
	 *
	 * @return array
	 */
	public static function get_urls( $order_id = null ) {
		$urls = array(
			'terms'          => get_permalink( wc_get_page_id( 'terms' ) ),
			'notifications'  => home_url( '/wc-api/BRIQPAY_WC_NOTIFICATION' ),
			'redirecturl'    => home_url( '?briqpay-success' ),
			'backtocheckout' => wc_get_checkout_url(),
		);
		if ( null !== $order_id ) {
			$order            = wc_get_order( $order_id );
			$confirmation_url = add_query_arg(
				array(
					'briqpay_confirm' => '1',
				),
				$order->get_checkout_order_received_url()
			);

			$urls['redirecturl'] = $confirmation_url;
		}
		return apply_filters( 'briqpay_merchant_urls', $urls );
	}
}
