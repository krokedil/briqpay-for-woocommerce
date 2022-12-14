<?php
/**
 * Class for Briqpay assets.
 *
 * @package Briqpay_For_WooCommerce/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Briqpay_Assets
 */
class Briqpay_Assets {

	/**
	 * Briqpay_Assets constructor.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_css' ) );
	}


	/**
	 * Enqueue payment scripts.
	 *
	 * @hook wp_enqueue_scripts
	 */
	public function enqueue_scripts() {
		$settings = get_option( 'woocommerce_briqpay_settings' );
		if ( 'yes' !== $settings['enabled'] ) {
			return;
		}

		if ( ! is_checkout() ) {
			return;
		}

		if ( is_order_received_page() ) {
			return;
		}

		if ( is_wc_endpoint_url( 'order-pay' ) ) {
			return;
		}

		wp_register_script(
			'briqpay',
			plugins_url( 'assets/js/briqpay-for-woocommerce.js', BRIQPAY_WC_MAIN_FILE ),
			array( 'jquery', 'wc-cart', 'jquery-blockui' ),
			BRIQPAY_WC_PLUGIN_VERSION,
			true
		);

		wp_register_script(
			'devbriqpay',
			'https://api.briqpay.com/briq.min.js',
			array(),
			null,
			false
		);

		wp_register_style(
			'briqpay',
			plugins_url( 'assets/css/briqpay-for-woocommerce.css', BRIQPAY_WC_MAIN_FILE ),
			array(),
			BRIQPAY_WC_PLUGIN_VERSION
		);

		$form = false;
		if ( WC()->session->get( 'briqpay_checkout_form' ) ) {
			$form = WC()->session->get( 'briqpay_checkout_form' );
		}

		$email_exists = 'no';
		if ( method_exists( WC()->customer ?? new stdClass(), 'get_billing_email' ) && ! empty( WC()->customer->get_billing_email() ) ) {
			if ( email_exists( WC()->customer->get_billing_email() ) ) {
				// Email exist in a user account.
				$email_exists = 'yes';
			}
		}

		$standard_woo_checkout_fields = array(
			'billing_first_name',
			'billing_last_name',
			'billing_address_1',
			'billing_address_2',
			'billing_postcode',
			'billing_city',
			'billing_phone',
			'billing_email',
			'billing_state',
			'billing_country',
			'billing_company',
			'shipping_first_name',
			'shipping_last_name',
			'shipping_address_1',
			'shipping_address_2',
			'shipping_postcode',
			'shipping_city',
			'shipping_state',
			'shipping_country',
			'shipping_company',
			'terms',
			'terms-field',
			'_wp_http_referer',
		);

		$checkout_localize_params = array(
			'requiredFieldsText'          => __( 'Please fill in all required checkout fields.', 'briqpay-for-woocommerce' ),
			'mustLoginMessage'            => apply_filters( 'woocommerce_registration_error_email_exists', __( 'An account is already registered with your email address. Please log in.', 'woocommerce' ) ),
			'timeoutMessage'              => __( 'Please try again, something went wrong with processing your order.', 'briqpay-for-woocommerce' ),
			'timeoutTime'                 => apply_filters( 'briqpay_checkout_timeout_duration', 20 ),
			'standardWooCheckoutFields'   => $standard_woo_checkout_fields,
			'submitOrder'                 => WC_AJAX::get_endpoint( 'checkout' ),
			'get_order_url'               => WC_AJAX::get_endpoint( 'briqpay_get_order' ),
			'get_order_nonce'             => wp_create_nonce( 'briqpay_get_order' ),
			'log_to_file_url'             => WC_AJAX::get_endpoint( 'briqpay_wc_log_js' ),
			'get_log_nonce'               => wp_create_nonce( 'briqpay_wc_log_js' ),
			'update_order_url'            => WC_AJAX::get_endpoint( 'briqpay_wc_update_checkout' ),
			'update_order_nonce'          => wp_create_nonce( 'briqpay_wc_update_checkout' ),
			'change_payment_method_url'   => WC_AJAX::get_endpoint( 'briqpay_wc_change_payment_method' ),
			'change_payment_method_nonce' => wp_create_nonce( 'briqpay_wc_change_payment_method' ),

			'update_order_orm'            => WC_AJAX::get_endpoint( 'update_order_orm' ),
			'update_order_orm_url_nonce'  => wp_create_nonce( 'update_order_orm' ),
		);

		if ( version_compare( WC_VERSION, '3.9', '>=' ) ) {
			$checkout_localize_params['force_update'] = true;
		}

		// wp_enqueue_script( 'devbriqpay' );
		wp_localize_script( 'briqpay', 'briqpayParams', $checkout_localize_params );

		wp_enqueue_script( 'briqpay' );
		wp_enqueue_style( 'briqpay' );
	}

	/**
	 * Enqueues admin page scripts.
	 *
	 * @hook admin_enqueue_scripts
	 */
	public function enqueue_admin_scripts() {

		wp_register_script( 'briqpay-admin', BRIQPAY_WC_PLUGIN_URL . '/assets/js/briqpay-order-meta-box.js', true, BRIQPAY_WC_PLUGIN_VERSION, true );
		wp_localize_script(
			'briqpay-admin',
			'briqpayParamsMeta',
			array(
				'update_order_orm'           => WC_AJAX::get_endpoint( 'update_order_orm' ),
				'update_order_orm_url_nonce' => wp_create_nonce( 'update_order_orm' ),
			)
		);
		wp_enqueue_script( 'briqpay-admin' );
	}

	/**
	 * Loads admin CSS file.
	 */
	public function enqueue_admin_css( $hook ) {

		if ( 'woocommerce_page_wc-settings' !== $hook ) {
			return;
		}
		$section = filter_input( INPUT_GET, 'section', FILTER_SANITIZE_SPECIAL_CHARS );

		if ( empty( $section ) || 'briqpay' !== $section ) {
			return;
		}

		wp_enqueue_style(
			'briqpay-admin',
			plugins_url( 'assets/css/briqpay-admin.css', BRIQPAY_WC_MAIN_FILE ),
			array(),
			BRIQPAY_WC_PLUGIN_VERSION
		);
	}


} new Briqpay_Assets();
