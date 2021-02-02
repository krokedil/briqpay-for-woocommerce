<?php
/**
 * Class for Briqpay gateway settings.
 *
 * @package Briqpay_For_WooCommerce/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Briqpay_Fields class.
 *
 * Briqpay for WooCommerce settings fields.
 */
class Briqpay_Fields {

	/**
	 * Returns the fields.
	 */
	public static function fields() {
		$settings = array(
			'enabled'                    => array(
				'title'       => __( 'Enable/Disable', 'briqpay-for-woocommerce' ),
				'label'       => __( 'Enable Briqpay payment', 'briqpay-for-woocommerce' ),
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'no',
			),
			'title'                      => array(
				'title'       => __( 'Title', 'briqpay-for-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Payment method title.', 'briqpay-for-woocommerce' ),
				'default'     => 'Briqpay',
				'desc_tip'    => true,
			),
			'description'                => array(
				'title'       => __( 'Description', 'briqpay-for-woocommerce' ),
				'type'        => 'textarea',
				'description' => __( 'Payment method description.', 'briqpay-for-woocommerce' ),
				'default'     => 'Payment method description.',
				'desc_tip'    => true,
			),
			'select_another_method_text' => array(
				'title'             => __( 'Other payment method button text', 'briqpay-for-woocommerce' ),
				'type'              => 'text',
				'description'       => __( 'Customize the <em>Select another payment method</em> button text that is displayed in checkout if using other payment methods than Briqpay Checkout. Leave blank to use the default (and translatable) text.', 'briqpay-for-woocommerce' ),
				'default'           => '',
				'desc_tip'          => true,
				'custom_attributes' => array(
					'autocomplete' => 'off',
				),
			),

			'testmode'                   => array(
				'title'       => __( 'Test mode', 'briqpay-for-woocommerce' ),
				'label'       => __( 'Enable Test Mode', 'briqpay-for-woocommerce' ),
				'type'        => 'checkbox',
				'description' => __( 'Place the payment gateway in test mode using test API keys.', 'briqpay-for-woocommerce' ),
				'default'     => 'yes',
				'desc_tip'    => true,
			),
			'logging'                    => array(
				'title'       => __( 'Logging', 'briqpay-for-woocommerce' ),
				'label'       => __( 'Log debug messages', 'briqpay-for-woocommerce' ),
				'type'        => 'checkbox',
				'description' => __( 'Save debug messages to the WooCommerce System Status log.', 'briqpay-for-woocommerce' ),
				'default'     => 'yes',
				'desc_tip'    => true,
			),
			// credentials.
			'credentials'                => array(
				'title' => 'API Credentials',
				'type'  => 'title',
			),
			'merchant_id'                => array(
				'title'             => __( 'Production Briqpay API Username', 'briqpay-for-woocommerce' ),
				'type'              => 'text',
				'description'       => __( 'Use API username and API password you downloaded in the Briqpay Merchant Portal. Don’t use your email address.', 'briqpay-for-woocommerce' ),
				'default'           => '',
				'desc_tip'          => true,
				'custom_attributes' => array(
					'autocomplete' => 'off',
				),
			),
			'shared_secret'              => array(
				'title'             => __( 'Production Briqpay API Password', 'briqpay-for-woocommerce' ),
				'type'              => 'password',
				'description'       => __( 'Use API username and API password you downloaded in the Briqpay Merchant Portal. Don’t use your email address.', 'briqpay-for-woocommerce' ),
				'default'           => '',
				'desc_tip'          => true,
				'custom_attributes' => array(
					'autocomplete' => 'new-password',
				),
			),
			'test_merchant_id'           => array(
				'title'             => __( 'Test Briqpay API Username', 'briqpay-for-woocommerce' ),
				'type'              => 'text',
				'description'       => __( 'Use API username and API password you downloaded in the Briqpay Merchant Portal. Don’t use your email address.', 'briqpay-for-woocommerce' ),
				'default'           => '',
				'desc_tip'          => true,
				'custom_attributes' => array(
					'autocomplete' => 'off',
				),
			),
			'test_shared_secret'         => array(
				'title'             => __( 'Test Briqpay API Password', 'briqpay-for-woocommerce' ),
				'type'              => 'password',
				'description'       => __( 'Use API username and API password you downloaded in the Briqpay Merchant Portal. Don’t use your email address.', 'briqpay-for-woocommerce' ),
				'default'           => '',
				'desc_tip'          => true,
				'custom_attributes' => array(
					'autocomplete' => 'new-password',
				),
			),
			'order_management'           => array(
				'title'   => __( 'Enable Order Management', 'briqpay-for-woocommerce' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Briqpay order capture on WooCommerce order completion and Briqpay order cancellation on WooCommerce order cancellation', 'briqpay-for-woocommerce' ),
				'default' => 'yes',
			),
			// Shipping.
			'shipping_section'           => array(
				'title' => __( 'Shipping settings', 'briqpay-for-woocommerce' ),
				'type'  => 'title',
			),
			// Checkout.
			'checkout_section'           => array(
				'title' => __( 'Checkout settings', 'briqpay-for-woocommerce' ),
				'type'  => 'title',
			),

		);
		return apply_filters( 'briqpay_gateway_settings', $settings );
	}
}
