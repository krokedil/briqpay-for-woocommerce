<?php //phpcs:ignore
/**
 * Functions file for the plugin.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;

/**
 * Gets a Briqpay order. Either creates or updates existing order
 *
 * @param  int $order_id  The WooCommerce order id.
 *
 * @return array|mixed|void
 */
function briqpay_create_or_update_order( int $order_id = 0 ) {
	$cart    = WC()->cart;
	$session = WC()->session;
	$api     = BRIQPAY()->api;
	$cart->calculate_fees();
	$cart->calculate_shipping();
	$cart->calculate_totals();
	$briqpay_session_id = $session->get( 'briqpay_session_id' );
	if ( $briqpay_session_id ) {
		$args          = array(
			'session_id' => $session->get( 'briqpay_session_id' ),
		);
		$briqpay_order = $api->update_briqpay_order( $args );
		if ( ! $briqpay_order ) {
			// If update order failed try to create new order.
			$briqpay_order = $api->create_briqpay_order();
			if ( ! $briqpay_order ) {
				// If failed then bail.
				return;
			}
			$session->set( 'briqpay_session_id', $briqpay_order['sessionid'] );
			return $briqpay_order;
		}
		return $briqpay_order;
	}

	$briqpay_order = $api->create_briqpay_order();

	if ( is_wp_error( $briqpay_order ) || ! isset( $briqpay_order['sessionid'] ) ) {
		return;
	}
	$session->set( 'briqpay_session_id', $briqpay_order['sessionid'] );

	return $briqpay_order;
}

/**
 * Create a Briqpay HPP Order.
 *
 * @param int    $order_id The WooCommerce order id.
 * @param string $type The type of HPP. Email or SMS.
 * @return array
 */
function create_hpp_order( $order_id, $type ) {
	$api           = BRIQPAY()->api;
	$briqpay_order = $api->create_briqpay_hpp( $order_id, $type );
	return $briqpay_order;
}

/**
 * Echoes Briqpay Checkout iframe snippet.
 */
function briqpay_wc_show_snippet() {
	$briqpay_order = briqpay_create_or_update_order();
	do_action( 'briqpay_wc_show_snippet', $briqpay_order );
	echo $briqpay_order['snippet'];//phpcs:ignore
}

/**
 * Check if we are on the confirmation page for Briqpay.
 *
 * @return boolean
 */
function is_briqpay_confirmation() {
	return isset( $_GET['confirm'], $_GET['briqpay_wc_order_id'] ) && 'yes' === $_GET['confirm'];//phpcs:ignore
}

/**
 * Print the error message.
 *
 * @param WP_Error $wp_error The WordPress Error.
 * @return void
 */
function briqpay_extract_error_message( $wp_error ) {
	if ( is_admin() ) {
		return;
	}

	$error_message = $wp_error->get_error_message();

	if ( is_array( $error_message ) ) {
		// Rather than assuming the first element is a string, we'll force a string conversion instead.
		$error_message = implode( ' ', $error_message );
	}

	if ( is_ajax() ) {
		if ( function_exists( 'wc_add_notice' ) ) {
			wc_add_notice( $error_message, 'error' );
		}
	} elseif ( function_exists( 'wc_print_notice' ) ) {
			wc_print_notice( $error_message, 'error' );
	}
}

/**
 * Unsets all sessions for the plugin.
 *
 * @return void
 */
function briqpay_wc_unset_sessions() {
	WC()->session->__unset( 'briqpay_session_id' );
}

/**
 * Prints the HTML for the show another gateway button.
 *
 * @return void
 */
function briqpay_wc_show_another_gateway_button() {
	$available_gateways = WC()->payment_gateways()->get_available_payment_gateways();

	if ( count( $available_gateways ) > 1 ) {
		$settings                   = get_option( 'woocommerce_briqpay_settings' );
		$select_another_method_text = isset( $settings['select_another_method_text'] ) && '' !== $settings['select_another_method_text'] ? $settings['select_another_method_text'] : __( 'Select another payment method', 'briqpay-for-woocommerce' );

		?>
		<p class="briqpay-select-other-wrapper">
			<a class="checkout-button button" href="#" id="briqpay-select-other">
				<?php echo esc_html( $select_another_method_text ); ?>
			</a>
		</p>
		<?php
	}
}

/**
 *
 * Checks if credentials are empty
 *
 * @return bool
 */
function validate_credentials() {
	$settings = get_option( 'woocommerce_briqpay_settings' );
	if ( 'yes' === $settings['testmode'] ) {
		if ( ! empty( $settings['test_merchant_id'] ) && ! empty( $settings['test_shared_secret'] ) ) {
			return true;
		}
	} elseif ( ! empty( $settings['merchant_id'] ) && ! empty( $settings['shared_secret'] ) ) {
		return true;
	}

	return false;
}

/**
 * Similar to WP's get_the_ID() with HPOS support. Used for retrieving the current order/post ID.
 *
 * Unlike get_the_ID() function, if `id` is missing, we'll default to the `post` query parameter when HPOS is disabled.
 *
 * @return int|false the order ID or false.
 */
//phpcs:ignore
function briqpay_get_the_ID() {
	$hpos_enabled = briqpay_is_hpos_enabled();
	$order_id     = $hpos_enabled ? filter_input( INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT ) : get_the_ID();
	if ( empty( $order_id ) ) {
		if ( ! $hpos_enabled ) {
			$order_id = absint( filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT ) );
			return empty( $order_id ) ? false : $order_id;
		}
		return false;
	}

	return absint( $order_id );
}

/**
 * Whether HPOS is enabled.
 *
 * @return bool true if HPOS is enabled, otherwise false.
 */
function briqpay_is_hpos_enabled() {
	// CustomOrdersTableController was introduced in WC 6.4.
	if ( class_exists( CustomOrdersTableController::class ) ) {
		return wc_get_container()->get( CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled();
	}

	return false;
}

/**
 * Retrieves the post type of the current post or of a given post.
 *
 * Compatible with HPOS.
 *
 * @param int|WP_Post|WC_Order|null $post Order ID, post object or order object.
 * @return string|null|false Return type of passed id, post or order object on success, false or null on failure.
 */
function briqpay_get_post_type( $post = null ) {
	if ( ! briqpay_is_hpos_enabled() ) {
		return get_post_type( $post );
	}

	return ! class_exists( 'Automattic\WooCommerce\Utilities\OrderUtil' ) ? false : Automattic\WooCommerce\Utilities\OrderUtil::get_order_type( $post );
}


/**
 * Retrieves the post type of the current post or of a given post.
 *
 * @param int|WP_Post|WC_Order|null $post Order ID, post object or order object.
 * @return true if order type, otherwise false.
 */
function briqpay_is_order_type( $post = null ) {
	return in_array( briqpay_get_post_type( $post ), array( 'woocommerce_page_wc-orders', 'shop_order' ), true );
}

/**
 * Get a order id from the merchant reference.
 *
 * @param string $merchant_reference The merchant reference from Briqpay.
 * @return int The WC order ID or 0 if no match was found.
 */
function briqpay_get_order_id_by_session_id( $session_id ) {
	$key    = '_briqpay_session_id';
	$orders = wc_get_orders(
		array(
			'meta_key'   => $key,
			'meta_value' => $session_id,
			'limit'      => 1,
			'orderby'    => 'date',
			'order'      => 'DESC',
		)
	);

	$order = reset( $orders );
	if ( empty( $order ) || $session_id !== $order->get_meta( $key ) ) {
		return 0;
	}

	return $order->get_id() ?? 0;
}