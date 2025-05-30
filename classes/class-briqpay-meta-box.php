<?php
/**
 * Metabox class file.
 *
 * @package Briqpay_For_WooCommerce/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Meta box class.
 */
class Briqpay_Meta_Box {
	/**
	 * Class constructor.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ), 10, 2 );
		add_action( 'woocommerce_process_shop_order_meta', array( $this, 'briqpay_hpp_save_handler' ) );
	}

	/**
	 * Adds meta box to the side of a Briqpay order.
	 *
	 * @param string $post_type The WordPress post type.
	 * @return void
	 */
	public function add_meta_box( $post_type, $post ) {
		if ( ! briqpay_is_order_type( $post ) ) {
			return;
		}

		$order_id = briqpay_get_the_ID();
		$order    = wc_get_order( $order_id );
		if ( 'briqpay' === $order->get_payment_method() ) {
			add_meta_box( 'briqpay_meta_box', __( 'Briqpay', 'briqpay-for-woocommerce' ), array( $this, 'meta_box_content' ), $post_type, 'side', 'core' );
		}
	}


	/**
	 * Adds content for the meta box.
	 *
	 * @return void
	 */
	public function meta_box_content() {
		$order                = wc_get_order( briqpay_get_the_ID() );
		$payment_method       = $order->get_meta( '_briqpay_payment_method' );
		$psp_name             = $order->get_meta( '_briqpay_psp_name' );
		$title_payment_method = __( 'Payment method', 'briqpay-for-woocommerce' );
		$title_psp_name       = __( 'PSP name', 'briqpay-for-woocommerce' );

		$keys_for_meta_box = array(
			array(
				'title' => esc_html( $title_payment_method ),
				'value' => esc_html( $payment_method ),
			),
			array(
				'title' => esc_html( $title_psp_name ),
				'value' => esc_html( $psp_name ),
			),
		);
		$keys_for_meta_box = apply_filters( 'briqpay_meta_box_keys', $keys_for_meta_box );
		include BRIQPAY_WC_PLUGIN_PATH . '/templates/briqpay-meta-box.php';
	}

	/**
	 * Create a HPP order with Briqpay.
	 *
	 * @param int $post_id The WordPress Post ID.
	 * @return void
	 */
	public function briqpay_hpp_save_handler( $post_id ) {

		// Only run from the admin dashboard on order update.
		if ( ! is_admin() ) {
			return;
		}

		$hpp = filter_input( INPUT_POST, 'briqpay_hpp_send_field', FILTER_SANITIZE_SPECIAL_CHARS );

		if ( empty( $hpp ) ) {
			return;
		}

		$hpp_order = create_hpp_order( $post_id, $hpp );
		$order     = wc_get_order( $post_id );

		if ( is_wp_error( $hpp_order ) ) {
			$order->add_order_note( __( 'Could not create a HPP order with Briqpay.', 'briqpay-for-woocommerce' ) );
			return;
		}

		$order->update_meta_data( '_briqpay_hpp_session_id', $hpp_order['hppsessionid'] );
		$order->update_meta_data( '_briqpay_hpp_url', $hpp_order['paymenturl'] );

		$order->add_order_note( __( 'Hosted payment page created with Briqpay.', 'briqpay-for-woocommerce' ) . "<br /><a href='{$hpp_order['paymenturl']}' target='_blank'>{$hpp_order['paymenturl']}</a>" );
		$order->save();

		// Send the customer invoice email.
		WC()->payment_gateways();
		WC()->shipping();
		WC()->mailer()->customer_invoice( $order );
	}

	/**
	 * Checks if any failed rules exists in the results.
	 *
	 * @param array $rules_results List of rules for the credit check an their outcome.
	 * @return bool
	 */
	public function check_failed_rules( $rules_results ) {
		if ( ! empty( $rules_results ) ) {
			foreach ( $rules_results as $psp_rules ) {
				foreach ( $psp_rules['rulesResult'] as $rules_result ) {
					if ( isset( $rules_result['outcome'] ) && ! $rules_result['outcome'] ) {
						return true;
					}
				}
			}
		}
		return false;
	}
} new Briqpay_Meta_Box();
