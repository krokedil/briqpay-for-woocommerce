<?php
/**
 *  Merchant Config helper class.
 *
 * @package Briqpay_For_WooCommerce/Classes/Requests/Helpers
 */


/**
 * Class Briqpay_Helper_Merchant_Config
 */
class Briqpay_Helper_Merchant_Config {

	/**
	 * Briqpay_Helper_Merchant_Config constructor.
	 */
	private function __construct() {}


	/**
	 * Returns the merchant configuration.
	 *
	 * @param  null|int $order_id The order id.
	 *
	 * @return array
	 */
	public static function get_config( $order_id = null ) {
		$settings = get_option( 'woocommerce_briqpay_settings' );
		$config   = array(
			'creditscoring' => isset( $settings['creditscoring'] ) && 'yes' === $settings['creditscoring'] ? true : false,
			'maxamount'     => isset( $settings['maxamount'] ) && 'yes' === $settings['maxamount'] ? true : false,
		);
		return apply_filters( 'briqpay_merchant_config', $config );
	}
}
