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
		$this->method_title       = __( 'Briqpay', 'briqpay-for-woocommerce' );
		$this->method_description = __( 'Briqpay replaces the standard WooCommerce checkout page.', 'briqpay-for-woocommerce' );
		$this->supports           = apply_filters(
			'briqpay_gateway_supports',
			array(
				'products',
				'refunds',
			)
		);
		$this->has_fields         = false;
		$this->init_form_fields();
		$this->init_settings();
		$this->title            = $this->get_option( 'title' );
		$this->description      = $this->get_option( 'description' );
		$this->enabled          = $this->get_option( 'enabled' );
		$this->testmode         = 'yes' === $this->get_option( 'testmode' );
		$this->logging          = 'yes' === $this->get_option( 'logging' );
		$this->order_management = 'yes' === $this->get_option( 'order_management' );
		add_action(
			'woocommerce_update_options_payment_gateways_briqpay',
			array(
				$this,
				'process_admin_options',
			)
		);
		add_action( 'woocommerce_update_options_payment_gateways_briqpay', array( $this, 'delete_briqpay_bearer_token_transient' ) );
		add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'add_billing_org_nr' ) );
		add_action( 'woocommerce_admin_order_data_after_shipping_address', array( $this, 'add_shipping_email' ) );
		add_action( 'woocommerce_admin_order_data_after_shipping_address', array( $this, 'add_shipping_phone' ) );
	}

	/**
	 * Process the payment.
	 *
	 * @param int $order_id WooCommerce order id.
	 * @return array
	 */
	public function process_payment( $order_id ) {
		$session_id = WC()->session->get( 'briqpay_session_id' );
		$response   = BRIQPAY()->api->patch_briqpay_order(
			array(
				'session_id' => $session_id,
				'order_id'   => $order_id,
			)
		);
		if ( is_wp_error( $response ) ) {
			$this->maybe_handle_v2_result( false, $session_id );
			return array(
				'result' => 'error',
			);
		}

		$order = wc_get_order( $order_id );
		$order->update_meta_data( '_briqpay_session_id', $response['sessionid'] );
		$order->save();

		$v2_result = $this->maybe_handle_v2_result( true, $session_id );

		if ( null !== $v2_result && is_wp_error( $v2_result ) ) {
			return array(
				'result' => 'error',
			);
		}

		return array(
			'result' => 'success',
		);
	}

	/**
	 * If the session was a v2 session, we need to send a HTTP request with the result.
	 *
	 * @param bool   $decision The decision.
	 * @param string $session_id The session id.
	 * @return void
	 */
	public function maybe_handle_v2_result( $decision, $session_id ) {
		$briqpay_version = filter_input( INPUT_POST, 'briqpay_checkout_version', FILTER_SANITIZE_NUMBER_INT );
		if ( ! empty( $briqpay_version ) && '2' === $briqpay_version ) {
			return BRIQPAY()->api->send_purchase_decision( $decision, $session_id );
		}

		return null;
	}

	/** Process refund request.
	 *
	 * @param int    $order_id The WooCommerce order ID.
	 * @param float  $amount The amount to be refunded.
	 * @param string $reason The reason given for the refund.
	 *
	 * @return bool|void
	 */
	public function process_refund( $order_id, $amount = null, $reason = '' ) {
		return BRIQPAY()->order_management->refund( $order_id, $amount );
	}


	/**
	 * Initialise settings fields.
	 */
	public function init_form_fields() {
		$this->form_fields = Briqpay_Fields::fields();
	}

	/**
	 * Admin Panel Options.
	 * Add sidebar to the settings page.
	 */
	public function admin_options() {
		ob_start();
		parent::admin_options();
		$parent_options = ob_get_contents();
		ob_end_clean();
		Briqpay_Settings_Page::render( $parent_options );
	}

	/**
	 * Checks if method should be available.
	 *
	 * @return boolean
	 */
	public function is_available() {
		return ! ( 'yes' !== $this->enabled );
	}

	/**
	 * Maybe adds the billing org number to the address in an order.
	 *
	 * @param WC_Order $order The WooCommerce order.
	 * @return void
	 */
	public function add_billing_org_nr( $order ) {
		if ( $this->id === $order->get_payment_method() ) {
			$org_nr = $order->get_meta( '_billing_org_nr' );
			if ( $org_nr ) {
				?>
				<p>
					<strong><?php esc_html_e( 'Organisation number:', 'briqpay-for-woocommerce' ); ?></strong>
					<?php echo esc_html( $org_nr ); ?>
				</p>
				<?php
			}
		}
	}

	/**
	 * Maybe adds the shipping reference to the address in an order.
	 *
	 * @param WC_Order $order The WooCommerce order.
	 * @return void
	 */
	public function add_shipping_email( $order ) {
		if ( $this->id === $order->get_payment_method() ) {
			$shipping_email = $order->get_meta( '_shipping_email' );
			if ( $shipping_email ) {
				?>
				<p>
					<strong> <?php esc_html_e( 'Email', 'woocommerce' ); ?>:</strong>
					<br>
					<a href="mailto:<?php echo esc_html( $shipping_email ); ?>"><?php echo esc_html( $shipping_email ); ?></a>
				</p>
				<?php
			}
		}
	}

	/**
	 * Maybe adds the shipping reference to the address in an order.
	 *
	 * @param WC_Order $order The WooCommerce order.
	 * @return void
	 */
	public function add_shipping_phone( $order ) {
		if ( $this->id === $order->get_payment_method() ) {
			$shipping_phone = $order->get_meta( '_shipping_phone' );
			if ( $shipping_phone ) {
				?>
				<p>
					<strong><?php esc_html_e( 'Phone', 'woocommerce' ); ?>:</strong>
					<br>
					<?php echo esc_html( $shipping_phone ); ?>
				</p>
				<?php
			}
		}
	}

	/**
	 * Delete the barer token transient when payment gateway settings is saved.
	 *
	 * @return void
	 */
	public function delete_briqpay_bearer_token_transient() {
		delete_transient( 'briqpay_bearer_token' );
	}
}
