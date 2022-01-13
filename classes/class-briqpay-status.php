<?php
/**
 * WooCommerce status page extension
 *
 * @class    Briqpay_For_WooCommerce_Status
 * @version  1.0.0
 * @package  Briqpay/Classes
 * @category Class
 * @author   Krokedil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Class for WooCommerce status page.
 */
class Briqpay_Status {
	/**
	 * Class constructor.
	 */
	public function __construct() {
		add_action( 'woocommerce_system_status_report', array( $this, 'add_status_page_box' ) );
	}

	/**
	 * Adds status page box for Briqpay.
	 *
	 * @return void
	 */
	public function add_status_page_box() {
		include_once BRIQPAY_WC_PLUGIN_PATH . '/includes/admin/views/status-report.php';
	}
}
$briqpay_status = new Briqpay_Status();
