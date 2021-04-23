<?php
/**
 * The HTML for the admin order metabox content.
 *
 * @package Briqpay_For_WooCommerce/Templates
 */

?>

<p><b><?php esc_html_e( 'Payment method', 'briqpay-for-woocommerce' ); ?>:</b> <?php echo esc_html( $payment_method ); ?></p>
<p><b><?php esc_html_e( 'PSP name', 'briqpay-for-woocommerce' ); ?>:</b> <?php echo esc_html( $psp_name ); ?></p>
<?php
if ( ! empty( $rules_results ) ) {
	?>
	<button type="button" id="briqpay_show_rules" class="button"><?php esc_html_e( 'Show rules results' ); ?></button>
	<div id="briqpay_rules_result_wrapper" class="briqpay_hide_rules">
		<div id="briqpay_rules_results">
			<div id="briqpay_close_rules"href="#"><span class="dashicons dashicons-dismiss"></span></div>
			<h3><?php esc_html_e( 'Failed rules for this order.' ); ?></h3>
			<?php
			foreach ( $rules_results as $rules_result ) {
				?>
				<h4><?php echo esc_html( $rules_result['pspname'] ); ?></h4>
				<ul>
				<?php
				foreach ( $rules_result['rulesResult'] as $rule ) {
					if ( isset( $rule['outcome'] ) && ! $rule['outcome'] ) {
						?>
						<li><?php echo esc_html( $rule['friendlyname'] ); ?></li>
						<?php
					}
				}
				?>
				</ul>
				<?php
			}
			?>
		</div>
	</div>
	<?php
}

