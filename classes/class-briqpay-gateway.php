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

		$this->title       = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );

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
		$response = BRIQPAY()->api->patch_briqpay_order(
			array(
				'session_id' => WC()->session->get( 'briqpay_session_id' ),
				'order_id'   => $order_id,
			)
		);
		if ( is_wp_error( $response ) ) {
			return array(
				'result' => 'error',
			);
		}
		update_post_meta( $order_id, '_briqpay_session_id', $response['sessionid'] );
		return array(
			'result' => 'success',
		// 'redirect' => $this->get_return_url( $order ),
		);

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
			$order_id = $order->get_id();
			$org_nr   = get_post_meta( $order_id, '_billing_org_nr', true );
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
			$order_id  = $order->get_id();
			$shipping_email = get_post_meta( $order_id, '_shipping_email', true );
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
			$order_id  = $order->get_id();
			$shipping_phone = get_post_meta( $order_id, '_shipping_phone', true );
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
