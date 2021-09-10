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

				
			</div>
		</div>
		<div class="save-separator"></div>
		<?php
	}
}
