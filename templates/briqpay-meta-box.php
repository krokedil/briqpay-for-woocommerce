<?php
/**
 * The HTML for the admin order metabox content.
 *
 * @package Briqpay_For_WooCommerce/Templates
 */

foreach ( $keys_for_meta_box as $item ) {
	?>
	<p><b><?php echo $item['title']; ?></b>: <?php echo $item['value']; ?></p>
	<?php
}
?>
<?php
if ( ! empty( $rules_results ) && $failed_rules ) {
	?>
	<button type="button" id="briqpay_show_rules" class="button"><?php esc_html_e( 'Show rules results' ); ?></button>
	<div id="briqpay_rules_result_wrapper" class="briqpay_hide_rules">
		<div id="briqpay_rules_results">
			<?php
			foreach ( $rules_results as $psp_rules ) {
				?>
				<h4><?php echo esc_html( $psp_rules['pspname'] ); ?></h4>
				<span><?php esc_html_e( 'This method was not shown because of the following rule was triggered', 'briqpay-for-woocommerce' ); ?></span>
				<ul style="list-style: disc;
	margin-left: 20px;">
				<?php
				foreach ( $psp_rules['rulesResult'] as $rules_result ) {
					if ( isset( $rules_result['outcome'] ) && ! $rules_result['outcome'] ) {
						?>
						<li><?php echo esc_html( $rules_result['friendlyname'] ); ?></li>
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

