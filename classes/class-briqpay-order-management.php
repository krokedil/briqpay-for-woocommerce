<?php
/**
 * Order management class file.
 *
 * @package @package Briqpay_For_WooCommerce/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Order management class.
 */
class Briqpay_Order_Management {

	/**
	 * The plugin settings.
	 *
	 * @var array
	 */
	protected $settings;

	/**
	 * Class constructor.
	 */
	public function __construct() {
		add_action( 'woocommerce_order_status_completed', array( $this, 'activate_reservation' ) );
		add_filter( 'woocommerce_get_checkout_payment_url', array( $this, 'replace_payment_url' ), 10, 2 );
		add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'add_order_org_number_field' ) );
		add_action( 'woocommerce_process_shop_order_meta', array( $this, 'save_org_nr_to_order' ), 45, 2 );
		$this->settings = get_option( 'woocommerce_briqpay_settings' );
	}

	/**
	 * Activate the order with Briqpay.
	 *
	 * @param  string $order_id  The WooCommerce order id.
	 *
	 * @return void
	 */
	public function activate_reservation( $order_id ) {
		$order = wc_get_order( $order_id );
		// If this order wasn't created using Briqpay payment method, bail.
		if ( 'briqpay' !== $order->get_payment_method() ) {
			return;
		}

		// Check briqpay settings to see if we have the order management enabled.
		$order_management = 'yes' === $this->settings['order_management'];
		if ( ! $order_management ) {
			return;
		}

		// Check if we have a payment id.
		$session_id = get_post_meta( $order_id, '_briqpay_session_id', true );
		// $session_id = $order->get_meta_data( '_briqpay_session_id', true );
		// Martin behöver hjälp
		if ( empty( $session_id ) ) {
			$order->add_order_note(
				__(
					'Briqpay reservation could not be activated. Missing Briqpay session id.',
					'briqpay-for-woocommerce'
				)
			);
				$order->set_status( 'on-hold' );
				$order->save();

				return;
		}

		// Check if this is an autocaptured order or not.
		$autocapture = $order->get_meta_data( '_briqpay_autocapture', true );
		if ( ! empty( $autocapture ) && $autocapture ) {
			$order->add_order_note(
				__(
					'Briqpay order has been autocaptured by Briqpay.',
					'briqpay-for-woocommerce'
				)
			);
			return;
		}

		// If this reservation was already activated, do nothing.
		if ( $order->get_meta_data( '_capture_id_', true ) ) {
			$order->add_order_note(
				__(
					'Could not activate Briqpay reservation, Briqpay reservation is already activated.',
					'briqpay-for-woocommerce'
				)
			);
			$order->set_status( 'on-hold' );
			$order->save();

			return;
		}

		$response = BRIQPAY()->api->capture_briqpay_order(
			array(
				'order_id'   => $order_id,
				'session_id' => $session_id,
			)
		);

		if ( is_array( $response ) && ! is_wp_error( $response ) ) {
			$capture_id = $response['captureid'];
			$order->update_meta_data( '_capture_id_', $capture_id );
			$order->add_order_note(
				__(
					'Briqpay reservation was successfully activated.',
					'briqpay-for-woocommerce'
				)
			);
			$order->save();
		} else {
			$order->add_order_note(
				__(
					'Briqpay reservation could not be activated.',
					'briqpay-for-woocommerce'
				)
			);
			$order->set_status( 'on-hold' );
			$order->save();
		}
	}


	/**
	 * Process refunds for Briqpay.
	 *
	 * @param int   $order_id The WooCommerce order id.
	 * @param float $amount The amount to be refunded.
	 * @return bool
	 */
	public function refund( $order_id, $amount ) {
		$query_args = array(
			'fields'         => 'id=>parent',
			'post_type'      => 'shop_order_refund',
			'post_status'    => 'any',
			'posts_per_page' => - 1,
		);

		$refunds         = get_posts( $query_args );
		$refund_order_id = array_search( $order_id, $refunds, true );
		if ( is_array( $refund_order_id ) ) {
			foreach ( $refund_order_id as $key => $value ) {
				$refund_order_id = $value;
				break;
			}
		}
		$order = wc_get_order( $order_id );

		$args     = array(
			'order_id'   => $refund_order_id,
			'session_id' => $order->get_meta_data( '_briqpay_session_id', true ),
		);
		$response = BRIQPAY()->api->refund_briqpay_order( $args );

		if ( is_wp_error( $response ) ) {
			// TODO add error handler.
			$order->add_order_note( __( 'Failed to refund the order with Briqpay', 'briqpay-for-woocommerce' ) );
			return false;
		}
		// translators: refund amount, refund id.
		$text           = __( '%1$s successfully refunded in Briqpay.. RefundID: %2$s', 'briqpay-for-woocommerce' );
		$formatted_text = sprintf( $text, wc_price( $amount ), $response['refundid'] );
		$order->add_order_note( $formatted_text );
		return true;
	}

	/**
	 * Maybe replaces the payment URL for HPP orders.
	 *
	 * @param string   $url The Order payment url.
	 * @param WC_Order $order The WooCommerce order.
	 * @return string
	 */
	public function replace_payment_url( $url, $order ) {
		if ( 'briqpay' !== $order->get_payment_method() ) {
			return $url;
		}

		$hpp_url = $order->get_meta_data( '_briqpay_hpp_url', true );

		if ( ! empty( $hpp_url ) ) {
			$url = $hpp_url;
		}

		return $url;
	}

	/**
	 * Shows the Organization Number for the order.
	 *
	 * @param WC_Order $order The WooCommerce order.
	 * @return void
	 */
	public function add_order_org_number_field( $order ) {
		?>
		<div class="order_data_column" style="clear:both; float:none; width:100%;">
				<div class="edit_address">
					<?php
						woocommerce_wp_text_input(
							array(
								'id'            => '_billing_org_nr',
								'label'         => __( 'Billing Organization Number', 'briqpay-for-woocommerce' ),
								'wrapper_class' => '_billing_company_field',
							)
						);
					?>
				</div>
			</div>
		<?php
	}

	/**
	 * Saves the Billing org number.
	 *
	 * @param int $post_id WordPress post id.
	 * @return void
	 */
	public function save_org_nr_to_order( $post_id ) {
		$order      = wc_get_order( $post_id );
		$org_number = filter_input( INPUT_POST, '_billing_org_nr', FILTER_SANITIZE_SPECIAL_CHARS );
		$order->update_meta_data( '_billing_org_nr', $org_number );
		$order->save();
	}
}
