<?php
/**
 * The HTML for the admin order metabox content.
 *
 * @package Briqpay_For_WooCommerce/Templates
 */

// HPP Section.
if ( $order->get_status() === 'pending' && strtolower( $order->get_payment_method() ) === 'briqpay' && empty( $hpp_session_id ) ) {
	?>
	<label for="briqpay_hpp_send_field">Create hosted payment page</label>
	<select name="briqpay_hpp_send_field" id="briqpay_hpp_send_field" class="postbox">
		<option value="">Do not create</option>
		<option value="sms">Send sms to billing phone</option>
		<option value="email">Send to billing email</option>
	</select>
	<?php
} else {
	foreach ( $keys_for_meta_box as $item ) {
		?>
		<p><b><?php echo esc_html( $item['title'] ); ?></b>: <?php echo esc_html( $item['value'] ); ?></p>
		<?php
	}
	if ( get_post_meta( $order_id, '_briqpay_psp_updateOrderSupported', true ) ) {
		?>
		<div>
			<button class="button-primary sync-btn-briqpay">Sync order to Briqpay</button>
		</div>
		<?php
	}
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
					<ul style="list-style: disc; margin-left: 20px;">
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
}

