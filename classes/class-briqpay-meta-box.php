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
		add_action( 'save_post', array( $this, 'briqpay_hpp_save_handler' ));
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
		$order_id          = get_the_ID();
		$payment_method    = get_post_meta( $order_id, '_briqpay_payment_method', true );
		$psp_name          = get_post_meta( $order_id, '_briqpay_psp_name', true );
		$rules_results     = json_decode( get_post_meta( $order_id, '_briqpay_rules_result', true ), true );
		$failed_rules      = $this->check_failed_rules( $rules_results );
		$keys_for_meta_box = array(
			array(
				'title' => esc_html( 'Payment method', 'briqpay-for-woocommerce' ),
				'value' => esc_html( $payment_method ),
			),
			array(
				'title' => esc_html( 'PSP name', 'briqpay-for-woocommerce' ),
				'value' => esc_html( $psp_name ),
			),
		);
		$order=wc_get_order( $order_id );
	if($order->get_status()==="pending" && strtolower($order->get_payment_method()) ==="briqpay"){

		?>
		<label for="briqpay_hpp_send_field">Send out payment link</label>
		<select name="briqpay_hpp_send_field" id="briqpay_hpp_send_field" class="postbox">
			<option value="">Dont send</option>
			<option value="sms">Send sms to billing phone</option>
			<option value="email">Send to billing email</option>
		</select>
		<?php
	}
		$keys_for_meta_box = apply_filters( 'briqpay_meta_box_keys', $keys_for_meta_box );
		include BRIQPAY_WC_PLUGIN_PATH . '/templates/briqpay-meta-box.php';
	}
	function briqpay_hpp_save_handler( $post_id ) {
		if ( array_key_exists( 'briqpay_hpp_send_field', $_POST ) ) {
			var_dump($_POST['briqpay_hpp_send_field']);
			
			if($_POST['briqpay_hpp_send_field'] === "email" || $_POST['briqpay_hpp_send_field'] === "sms"){

			}
			send_briqpay_hpp_link($post_id,$_POST['briqpay_hpp_send_field']);
		}
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
