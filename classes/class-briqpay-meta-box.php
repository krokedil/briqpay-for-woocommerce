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
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
	}

	/**
	 * Adds meta box to the side of a Briqpay order.
	 *
	 * @param string $post_type The WordPress post type.
	 * @return void
	 */
	public function add_meta_box( $post_type ) {
		if ( 'shop_order' === $post_type ) {
			$order_id = get_the_ID();
			$order    = wc_get_order( $order_id );
			if ( 'briqpay' === $order->get_payment_method() ) {
				add_meta_box( 'briqpay_meta_box', __( 'Briqpay', 'briqpay-for-woocommerce' ), array( $this, 'meta_box_content' ), 'shop_order', 'side', 'core' );
			}
		}
	}

	/**
	 * Adds content for the meta box.
	 *
	 * @return void
	 */
	public function meta_box_content() {
		$order_id       = get_the_ID();
		$payment_method = get_post_meta( $order_id, '_briqpay_payment_method', true );
		$psp_name       = get_post_meta( $order_id, '_briqpay_psp_name', true );
		$rules_results  = json_decode( get_post_meta( $order_id, '_briqpay_rules_result', true ), true );
		$failed_rules   = $this->check_failed_rules( $rules_results );
		include BRIQPAY_WC_PLUGIN_PATH . '/templates/briqpay-meta-box.php';
	}

	/**
	 * Checks if any failed rules exists in the results.
	 *
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
