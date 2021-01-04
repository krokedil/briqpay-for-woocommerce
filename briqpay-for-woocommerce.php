<?php // phpcs:ignore
/**
 * Plugin Name: Briqpay for WooCommerce
 * Plugin URI: https://krokedil.com/briqpay-for-woocommerce/
 * Description: Briqpay for WooCommerce.
 * Author: Krokedil
 * Author URI: https://krokedil.com/
 * Version: 0.0.1
 * Text Domain: briqpay-for-woocommerce
 * Domain Path: /languages
 *
 * WC requires at least: 3.8.0
 * WC tested up to: 4.7.1
 *
 * Copyright (c) 2020 Krokedil
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Constants
 */
define( 'BRIQPAY_WC_MAIN_FILE', __FILE__ );
define( 'BRIQPAY_WC_PLUGIN_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'BRIQPAY_WC_PLUGIN_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'BRIQPAY_WC_PLUGIN_VERSION', '0.4.1' );

if ( ! class_exists( 'Briqpay_For_WooCommerce' ) ) {
	/**
	 * Class Briqpay_For_WooCommerce
	 */
	class Briqpay_For_WooCommerce {

		/**
		 * The reference the *Singleton* instance of this class.
		 *
		 * @var $instance
		 */
		protected static $instance;


		/**
		 * Returns the *Singleton* instance of this class.
		 *
		 * @return self::$instance The *Singleton* instance.
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Private clone method to prevent cloning of the instance of the
		 * *Singleton* instance.
		 *
		 * @return void
		 */
		private function __clone() {
			wc_doing_it_wrong( __FUNCTION__, __( 'Nope' ), '1.0' );
		}

		/**
		 * Private unserialize method to prevent unserializing of the *Singleton*
		 * instance.
		 *
		 * @return void
		 */
		private function __wakeup() {
			wc_doing_it_wrong( __FUNCTION__, __( 'Nope' ), '1.0' );
		}

		/**
		 * Notices (array)
		 *
		 * @var array
		 */
		public $notices = array();

		/**
		 * Protected constructor to prevent creating a new instance of the
		 * *Singleton* via the `new` operator from outside of this class.
		 */
		protected function __construct() {
			add_action( 'admin_notices', array( $this, 'admin_notices' ), 15 );
			add_action( 'plugins_loaded', array( $this, 'init' ) );
		}

		/**
		 * Init the plugin after plugins_loaded so environment variables are set.
		 */
		public function init() {
			load_plugin_textdomain( 'briqpay-for-woocommerce', false, plugin_basename( __DIR__ ) . '/languages' );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
			add_filter( 'woocommerce_payment_gateways', array( $this, 'add_gateways' ) );
			$this->include_files();
		}

		/**
		 * Adds plugin action links
		 *
		 * @param array $links Plugin action link before filtering.
		 *
		 * @return array Filtered links.
		 */
		public function plugin_action_links( $links ) {
			$setting_link = $this->get_setting_link();
			$plugin_links = array(
				'<a href="' . $setting_link . '">' . __( 'Settings', 'briqpay-for-woocommerce' ) . '</a>',
				'<a href="http://krokedil.se/">' . __( 'Support', 'briqpay-for-woocommerce' ) . '</a>',
			);

			return array_merge( $plugin_links, $links );
		}

		/**
		 * Get setting link.
		 *
		 * @since 1.0.0
		 *
		 * @return string Setting link
		 */
		public function get_setting_link() {
			$section_slug = 'briqpay';

			$params = array(
				'page'    => 'wc-settings',
				'tab'     => 'checkout',
				'section' => $section_slug,
			);

			return add_query_arg( $params, 'admin.php' );
		}

		/**
		 * Display any notices we've collected thus far (e.g. for connection, disconnection)
		 */
		public function admin_notices() {
			foreach ( (array) $this->notices as $notice_key => $notice ) {
				echo "<div class='" . esc_attr( $notice['class'] ) . "'><p>";
				echo wp_kses( $notice['message'], array( 'a' => array( 'href' => array() ) ) );
				echo '</p></div>';
			}
		}
		/**
		 * Includes the files for the plugin
		 *
		 * @return void
		 */
		public function include_files() {
			include BRIQPAY_WC_PLUGIN_PATH . '/classes/class-briqpay-fields.php';
			include BRIQPAY_WC_PLUGIN_PATH . '/classes/class-briqpay-gateway.php';
		}

		/**
		 *
		 * Adds new gateway.
		 *
		 * @param array $methods list of supported methods.
		 *
		 * @return array
		 */
		public function add_gateways( $methods ) {
			$methods[] = Briqpay_Gateway::class;
			return $methods;
		}

	}
	Briqpay_For_WooCommerce::get_instance();
}

/**
 * Main instance Briqpay_For_WooCommerce.
 *
 * Returns the main instance of Briqpay_For_WooCommerce.
 *
 * @return Briqpay_For_WooCommerce
 */
function BRIQPAY() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName
	return Briqpay_For_WooCommerce::get_instance();
}
