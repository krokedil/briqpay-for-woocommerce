<?php
/**
 * Templates class for Briqpay checkout.
 *
 * @package  Briqpay_For_WooCommerce/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Briqpay_Templates class.
 */
class Briqpay_Templates {

	/**
	 * The reference the *Singleton* instance of this class.
	 *
	 *    @var $instance
	 */
	protected static $instance;

	/**
	 * Returns the *Singleton* instance of this class.
	 *
	 *    @return self::$instance The *Singleton* instance.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Plugin actions.
	 */
	public function __construct() {
		// Override template if Briqpay Checkout page.
		add_filter( 'wc_get_template', array( $this, 'override_template' ), 999, 2 );
		// add_action( 'wp_footer', array( $this, 'check_that_briqpay_template_has_loaded' ) );
		//
		// Template hooks.
		// add_action( 'briqpay_wc_after_order_review', 'briqpay_wc_show_another_gateway_button', 20 );
		add_action( 'briqpay_wc_after_order_review', array( $this, 'add_extra_checkout_fields' ), 10 );
		// add_action( 'briqpay_wc_before_snippet', 'briqpay_wc_prefill_consent', 10 );
		add_action(
			'briqpay_wc_before_snippet',
			array( $this, 'add_wc_form' ),
			10
		); // @TODO Look into changing this to briqpay_wc_after_wrapper later.
		add_action( 'briqpay_wc_before_snippet', array( $this, 'add_review_order_before_submit' ), 15 );
		// Unrequire WooCommerce Billing State field.
		add_filter( 'woocommerce_billing_fields', array( $this, 'briqpay_wc_unrequire_wc_billing_state_field' ) );
		// Unrequire WooCommerce Shipping State field.
		add_filter( 'woocommerce_shipping_fields', array( $this, 'briqpay_wc_unrequire_wc_shipping_state_field' ) );
	}

	/**
	 * Override checkout form template if Briqpay Checkout is the selected payment method.
	 *
	 *    @param  string $template  Template.
	 *    @param  string $template_name  Template name.
	 *
	 *    @return string
	 */
	public function override_template( $template, $template_name ) {
		if ( is_checkout() ) {
			$confirm = filter_input( INPUT_GET, 'confirm', FILTER_SANITIZE_STRING );
			// Don't display briqpay template if we have a cart that doesn't needs payment.
			if ( apply_filters( 'briqpay_check_if_needs_payment', true ) ) {
				if ( ! WC()->cart->needs_payment() ) {
					return $template;
				}
			}

			// Briqpay Checkout.
			if ( 'checkout/form-checkout.php' === $template_name ) {
				$available_gateways = WC()->payment_gateways()->get_available_payment_gateways();

				if ( locate_template( 'woocommerce/briqpay-checkout.php' ) ) {
					$briqpay_checkout_template = locate_template( 'woocommerce/briqpay-checkout.php' );
				} else {
					$briqpay_checkout_template = BRIQPAY_WC_PLUGIN_PATH . '/templates/briqpay-checkout.php';
				}

				// Briqpay checkout page.
				if ( array_key_exists( 'briqpay', $available_gateways ) ) {
					// If chosen payment method exists.
					if ( 'briqpay' === WC()->session->get( 'chosen_payment_method' ) ) {
						if ( empty( $confirm ) ) {
								$template = $briqpay_checkout_template;
						}
					}

					// If chosen payment method does not exist and Briqpay is the first gateway.
					if ( null === WC()->session->get( 'chosen_payment_method' ) || '' === WC()->session->get( 'chosen_payment_method' ) ) {
						reset( $available_gateways );

						if ( 'briqpay' === key( $available_gateways ) ) {
							if ( empty( $confirm ) ) {
								$template = $briqpay_checkout_template;
							}
						}
					}

					// If another gateway is saved in session, but has since become unavailable.
					if ( WC()->session->get( 'chosen_payment_method' ) ) {
						if ( ! array_key_exists(
							WC()->session->get( 'chosen_payment_method' ),
							$available_gateways
						) ) {
							reset( $available_gateways );

							if ( 'briqpay' === key( $available_gateways ) ) {
								if ( empty( $confirm ) ) {
										$template = $briqpay_checkout_template;
								}
							}
						}
					}
				}
			}
		}

		return $template;
	}

	/**
	 * Redirect customer to cart page if Briqpay Checkout is the selected (or first)
	 * payment method but the Briqpay template file hasn't been loaded.
	 */
	public function check_that_briqpay_template_has_loaded() {
		if ( is_checkout() && array_key_exists( 'briqpay', WC()->payment_gateways->get_available_payment_gateways() )
		// && 'briqpay' === briqpay_wc_get_selected_payment_method()
		&& ( method_exists( WC()->cart, 'needs_payment' ) && WC()->cart->needs_payment() ) ) {

			// Get checkout object.
			$checkout = WC()->checkout();
			$settings = get_option( 'woocommerce_briqpay_settings' );
			$enabled  = ( 'yes' === $settings['enabled'] ) ? true : false;

			// Bail if this is briqpay confirmation page, order received page, Briqpay page (briqpay_wc_show_snippet has run), user is not logged and registration is disabled or if woocommerce_cart_has_errors has run.
			if ( is_briqpay_confirmation()
			|| is_wc_endpoint_url( 'order-received' )
			|| did_action( 'briqpay_wc_show_snippet' )
			|| ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() )
			|| did_action( 'woocommerce_cart_has_errors' )
		|| isset( $_GET['change_payment_method'] ) // phpcs:ignore
			|| ! $enabled ) {
				return;
			}

			$url = add_query_arg(
				array(
					'briqpay-order' => 'error',
					'reason'        => base64_encode( // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
						__(
							'Failed to load Briqpay Checkout template file.',
							'briqpay-for-woocommerce'
						)
					),
				),
				wc_get_cart_url()
			);
			wp_safe_redirect( $url );
			exit;
		}
	}


	/**
	 * Adds the WC form and other fields to the checkout page.
	 *
	 *    @return void
	 */
	public function add_wc_form() {
		?>
<div aria-hidden="true" id="briqpay-wc-form" style="position:absolute; top:-99999px; left:-99999px;">
		<?php do_action( 'woocommerce_checkout_billing' ); ?>
		<?php do_action( 'woocommerce_checkout_shipping' ); ?>
<div id="briqpay-nonce-wrapper">
		<?php
		if ( version_compare( WOOCOMMERCE_VERSION, '3.4', '<' ) ) {
			wp_nonce_field( 'woocommerce-process_checkout' );
		} else {
			wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' );
		}
		wc_get_template( 'checkout/terms.php' );
		?>
</div>
<input id="payment_method_briqpay" type="radio" class="input-radio" name="payment_method" value="briqpay"
checked="checked"/></div>
		<?php
	}

	/**
	 * Adds the extra checkout field div to the checkout page.
	 */
	public function add_extra_checkout_fields() {
		do_action( 'briqpay_wc_before_extra_fields' );
		?>
		<div id="briqpay-extra-checkout-fields">
		</div>
		<?php
		do_action( 'briqpay_wc_after_extra_fields' );
	}

	/**
	 * Unrequire WC billing state field.
	 *
	 *    @param  array $fields  WC billing fields.
	 *
	 *    @return array $fields WC billing fields.
	 */
	public function briqpay_wc_unrequire_wc_billing_state_field( $fields ) {
		// Unrequire if chosen payment method is Briqpay Checkout.
		if ( method_exists( WC()->session, 'get' ) &&
		WC()->session->get( 'chosen_payment_method' ) &&
		'briqpay' === WC()->session->get( 'chosen_payment_method' )
		) {
			$fields['billing_state']['required'] = false;
		}

		return $fields;
	}

	/**
	 * Unrequire WC shipping state field.
	 *
	 *    @param  array $fields  WC shipping fields.
	 *
	 *    @return array $fields WC shipping fields.
	 */
	public function briqpay_wc_unrequire_wc_shipping_state_field( $fields ) {
		// Unrequire if chosen payment method is Briqpay Checkout.
		if ( method_exists(
			WC()->session,
			'get'
		) && WC()->session->get( 'chosen_payment_method' ) && 'briqpay' === WC()->session->get( 'chosen_payment_method' ) ) {
			$fields['shipping_state']['required'] = false;
		}

		return $fields;
	}


	/**
	 * Triggers WC action.
	 */
	public function add_review_order_before_submit() {
		do_action( 'woocommerce_review_order_before_submit' );
	}
}

Briqpay_Templates::get_instance();
