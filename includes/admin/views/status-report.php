<?php
/**
 * Admin View: Page - Status Report.
 *
 * @package Briqpay\Includes\Admin\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<table class="wc_status_table widefat" cellspacing="0">
	<thead>
	<tr>
		<th colspan="6" data-export-label="Briqpay Request Log">
			<h2><?php esc_html_e( 'Briqpay', 'briqpay-for-woocommerce' ); ?><?php echo wc_help_tip( esc_html__( 'Briqpay System Status.', 'briqpay-for-woocommerce' ) ); /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */ ?></h2>
		</th>
	</tr>

	<?php
	$db_logs = get_option( 'krokedil_debuglog_briqpay', array() );
	if ( ! empty( $db_logs ) ) {
		$db_logs = array_reverse( json_decode( $db_logs, true ) );
		?>
			<tr>
				<td ><strong><?php esc_html_e( 'Time', 'briqpay-for-woocommerce' ); ?></strong></td>
				<td class="help"></td>
				<td ><strong><?php esc_html_e( 'Request', 'briqpay-for-woocommerce' ); ?></strong></td>
				<td ><strong><?php esc_html_e( 'Response Code', 'briqpay-for-woocommerce' ); ?></strong></td>
				<td ><strong><?php esc_html_e( 'Request URL', 'briqpay-for-woocommerce' ); ?></strong></td>
				<td ><strong><?php esc_html_e( 'Response Message', 'briqpay-for-woocommerce' ); ?></strong></td>
			</tr>
		</thead>
		<tbody>
		<?php
		foreach ( $db_logs as $log ) {
			$timestamp      = isset( $log['timestamp'] ) ? $log['timestamp'] : '';
			$log_title      = isset( $log['title'] ) ? $log['title'] : '';
			$code           = isset( $log['response']['code'] ) ? $log['response']['code'] : '';
			$request_url    = isset( $log['request_url'] ) ? $log['request_url'] : '';
			$body           = isset( $log['response']['body'] ) ? wp_json_encode( $log['response']['body'] ) : '';
			$error_messages = isset( $log['response']['body']['response']['message'] ) ? 'Error messages: ' . wp_json_encode( $log['response']['body']['response']['message'] ) : '';

			?>
			<tr>
				<td><?php echo esc_html( $timestamp ); ?></td>
				<td class="help"></td>
				<td><?php echo esc_html( $log_title ); ?>
					<span style="display: none;">,
						Response code: <?php echo esc_html( $code ); ?>,
						Response url: <?php echo esc_html( $request_url ); ?>,
						Response message: <?php echo esc_html( $body ); ?>
					</span</td>
				<td><?php echo esc_html( $code ); ?></td>
				<td><?php echo esc_html( $request_url ); ?></td>
				<td><?php echo esc_html( $error_messages ); ?></td>
			</tr>
			<?php
		}
	} else {
		?>
		</thead>
		<tbody>
			<tr>
				<td colspan="6" data-export-label="No Briqpay errors"><?php esc_html_e( 'No error logs', 'briqpay-for-woocommerce' ); ?></td>
			</tr>
		<?php
	}
	?>
		</tbody>
	</table>
