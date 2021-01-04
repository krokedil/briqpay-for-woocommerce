<?php
/**
 * Class file for Briqpay_Gateway class.
 *
 * @package Briqpay_For_WooCommerce/Classes
 */

/**
 * Class Briqpay_Gateway
 */
class Briqpay_Gateway extends WC_Payment_Gateway {

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->id                 = 'briqpay';
		$this->method_title       = __( 'Briqpay Payment Gateway', 'briqpay-for-woocommerce' );
		$this->method_description = __( 'The current Briqpay Checkout replaces standard WooCommerce checkout page.', 'briqpay-for-woocommerce' );
		$this->supports           = apply_filters(
			'briqpay_gateway_supports',
			array(
				'products',
			)
		);
		$this->has_fields         = false;

		$this->init_form_fields();
		$this->init_settings();

		$this->title       = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );

		$this->enabled  = $this->get_option( 'enabled' );
		$this->testmode = 'yes' === $this->get_option( 'testmode' );
		$this->logging  = 'yes' === $this->get_option( 'logging' );
		add_action(
			'woocommerce_update_options_payment_gateways_briqpay',
			array(
				$this,
				'process_admin_options',
			)
		);
	}

	/**
	 * Process the payment.
	 *
	 * @param int $order_id WooCommerce order id.
	 * @return array
	 */
	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );
		return array(
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order ),
		);
	}

	/**
	 * Initialise settings fields.
	 */
	public function init_form_fields() {
		$this->form_fields = Briqpay_Fields::fields();
	}

	/**
	 * Checks if method should be available.
	 *
	 * @return boolean
	 */
	public function is_available() {
		return ! ( 'yes' !== $this->enabled );
	}
}
