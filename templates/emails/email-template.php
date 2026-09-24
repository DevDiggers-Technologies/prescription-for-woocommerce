<?php
/**
 * Prescription for WooCommerce Notification email
 *
 * @package Prescription for WooCommerce
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit();

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core WordPress/WooCommerce hook.
do_action( 'woocommerce_email_header', $email_heading, $email );

// Main message content.
if ( ! empty( $email_message ) && is_array( $email_message ) ) {
	foreach ( $email_message as $ddwcmpa_message ) {
		if ( empty( $ddwcmpa_message ) ) {
			continue;
		}

		// A placeholder that had nothing to say leaves its paragraph behind, which
		// shows up as a blank line in the email. Drop the empty ones.
		$ddwcmpa_message = preg_replace( '#<p>(\s|&nbsp;)*</p>#i', '', $ddwcmpa_message );

		if ( '' === trim( $ddwcmpa_message ) ) {
			continue;
		}

		// The Visual editor stores paragraphs as blank lines, not <p> tags, the way
		// post content is stored. wpautop() turns those back into paragraphs and leaves
		// markup that already has them alone; wrapping the whole body in one <p>
		// printed every email as a single paragraph.
		echo wp_kses_post( wpautop( $ddwcmpa_message ) );
	}
}

// Order information. The greeting and the sign off are not printed here on
// purpose: the configured message carries the whole body, so what a merchant
// types in the Emails tab is exactly what the customer reads.
if ( $order && ! empty( $order_number ) ) {
	?>
	<div style="margin: 20px 0; padding: 15px; background-color: #f8f9fa; border-left: 4px solid #007cba;">
		<h3 style="margin: 0 0 10px 0; color: #333;"><?php esc_html_e( 'Order Information', 'prescription-for-woocommerce' ); ?></h3>
		<p style="margin: 5px 0;"><strong><?php esc_html_e( 'Order Number:', 'prescription-for-woocommerce' ); ?></strong> <?php echo esc_html( $order_number ); ?></p>
		<?php if ( ! empty( $order_date ) ) : ?>
			<p style="margin: 5px 0;"><strong><?php esc_html_e( 'Order Date:', 'prescription-for-woocommerce' ); ?></strong> <?php echo esc_html( $order_date ); ?></p>
		<?php endif; ?>
		<?php if ( ! empty( $status ) ) : ?>
			<p style="margin: 5px 0;"><strong><?php esc_html_e( 'Status:', 'prescription-for-woocommerce' ); ?></strong> <?php echo esc_html( $status ); ?></p>
		<?php endif; ?>
	</div>
	<?php
}

// Action buttons.
if ( $order && ! empty( $order_url ) ) {
	?>
	<div style="margin: 20px 0; text-align: center;">
		<a href="<?php echo esc_url( $order_url ); ?>" style="display: inline-block; padding: 12px 24px; background-color: #007cba; color: #ffffff; text-decoration: none; border-radius: 4px; font-weight: bold;">
			<?php esc_html_e( 'View Order', 'prescription-for-woocommerce' ); ?>
		</a>
	</div>
	<?php
}

// Additional content.
if ( ! empty( $additional_content ) ) {
	?>
	<div style="margin: 20px 0; padding: 15px; background-color: #f8f9fa; border-radius: 4px;">
		<?php echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) ); ?>
	</div>
	<?php
}

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core WordPress/WooCommerce hook.
do_action( 'woocommerce_email_footer', $email );
