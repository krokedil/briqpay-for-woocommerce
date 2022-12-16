<?php
/**
 * Customer helper class file.
 *
 * @package Briqpay_For_WooCommerce/Classes/Requests/Helpers
 */

/**
 * Class Briqpay_Helper_Customer
 */
class Briqpay_Helper_Customer {


	/**
	 * Briqpay_Helper_Customer constructor.
	 */
	private function __construct() {}

	/**
	 * Returns the billing data.
	 *
	 * @return array
	 */
	public static function get_billing_data() {
		return array(
			'companyname' => WC()->customer->get_billing_company(),
			'firstname'   => WC()->customer->get_billing_first_name(),
			'lastname'    => WC()->customer->get_billing_last_name(),
			'zip'         => WC()->customer->get_billing_postcode(),
			'city'        => WC()->customer->get_billing_city(),
			'cellno'      => WC()->customer->get_billing_phone(),
			'email'       => WC()->customer->get_billing_email(),
		);
	}

	/**
	 * Returns the shipping data.
	 *
	 * @return array
	 */
	public static function get_shipping_data() {
		return array(
			'companyname' => WC()->customer->get_shipping_company(),
			'firstname'   => WC()->customer->get_shipping_first_name(),
			'lastname'    => WC()->customer->get_shipping_last_name(),
			'zip'         => WC()->customer->get_shipping_postcode(),
			'city'        => WC()->customer->get_shipping_city(),
			'cellno'      => WC()->customer->get_billing_phone(),
			'email'       => WC()->customer->get_billing_email(),
		);
	}

		/**
		 * Returns the billing data from an order.
		 *
		 * @param WC_Order $order The WooCommerce order.
		 * @return array
		 */
	public static function get_billing_data_order( $order ) {
		return array(
			'companyname'    => $order->get_billing_company(),
			'firstname'      => $order->get_billing_first_name(),
			'lastname'       => $order->get_billing_last_name(),
			'streetaddress'  => $order->get_billing_address_1(),
			'streetaddress2' => $order->get_billing_address_2(),
			'zip'            => $order->get_billing_postcode(),
			'city'           => $order->get_billing_city(),
			'cellno'         => $order->get_billing_phone(),
			'email'          => $order->get_billing_email(),
		);
	}

	/**
	 * Returns the shipping data from an order.
	 *
	 * @param WC_Order $order The WooCommerce order.
	 * @return array
	 */
	public static function get_shipping_data_order( $order ) {
		return array(
			'companyname'    => $order->get_shipping_company(),
			'firstname'      => $order->get_shipping_first_name(),
			'lastname'       => $order->get_shipping_last_name(),
			'streetaddress'  => $order->get_shipping_address_1(),
			'streetaddress2' => $order->get_shipping_address_2(),
			'zip'            => $order->get_shipping_postcode(),
			'city'           => $order->get_shipping_city(),
			'cellno'         => $order->get_shipping_phone(),
			'email'          => $order->get_billing_email(),
		);
	}
}
