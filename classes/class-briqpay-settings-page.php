<?php
/**
 * Class for Briqpay gateway settings page.
 *
 * @package Briqpay_For_WooCommerce/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Briqpay_Settings_Page class.
 *
 * Briqpay for WooCommerce settings page.
 */
class Briqpay_Settings_Page {

	/**
	 * Renders the settings page including a right column sidebar.
	 *
	 * @param array $parent_options The parent options.
	 */
	public static function render( $parent_options ) {
		?>

		<div id="briqpay-settings-wrapper">
			<div id="briqpay-settings-main">
                <?php echo $parent_options; // phpcs:ignore?>
			</div>

			<div id="briqpay-settings-sidebar">

					<div class="briqpay-settings-sidebar-section">
							<div class="briqpay-settings-sidebar-content">
							<img class="briqpay-logo-img" src="<?php echo esc_url( BRIQPAY_WC_PLUGIN_URL ); ?>/assets/img/briqpay-logo-color.png">
							<p id="briqpay-settings-sidebar-main-text">
								<a href="https://app.briqpay.com/" target="_blank">Log in to your Briqpay account</a> to view data and orders. <br/><br/>
								No Briqpay account? <br/>
								<a href="https://app.briqpay.com/" target="_blank">Register for an account here</a>.
							</p>
							<h1 id="briqpay-settings-sidebar-title">Get started</h1>
								<p id="briqpay-settings-sidebar-main-text">
									<a href="https://docs.krokedil.com/briqpay-for-woocommerce/get-started/" target="_blank">Documentation</a> <br/>
									<a href="https://krokedil.com/product/briqpay-for-woocommerce/" target="_blank">Plugin site</a>
								</p>
						<h1 id="briqpay-settings-sidebar-title">Support</h1>
								<p id="briqpay-settings-sidebar-main-text">
									If you have technical questions about the plugin and can't find the answer in the plugin documentation, you are welcome to contact <a href="https://www.krokedil.com/support" target="_blank">the Krokedil support</a>.
								</p>
							</div>

								<div id="briqpay-settings-sidebar-bottom-holder">
									<p id="briqpay-settings-sidebar-logo-follow-up-text">
										Developed by:
									</p>
									<img id="briqpay-settings-sidebar-krokedil-logo-right"
									src="<?php echo esc_url( BRIQPAY_WC_PLUGIN_URL ); ?>/assets/img/krokedil-logo-color.png">
								</div>
					</div>
				</div>

		</div>
		<div class="save-separator"></div>
		<?php
	}
}
