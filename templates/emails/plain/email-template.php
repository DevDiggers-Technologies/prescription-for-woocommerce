<?php
/**
 * DevDiggers Prescription for WooCommerce Notification email
 *
 * @package DevDiggers Prescription for WooCommerce
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit();

echo '= ' . esc_html( $email_heading ) . " =\n\n";

// Main message content.
if ( ! empty( $email_message ) && is_array( $email_message ) ) {
	foreach ( $email_message as $ddwcmpa_message ) {
		if ( ! empty( $ddwcmpa_message ) ) {
			// The configured body is HTML. Closing block tags become line breaks
			// first, or the whole message arrives as one unreadable run of text,
			// and the indentation the editor leaves behind is collapsed away.
			$ddwcmpa_plain = str_ireplace( [ '</p>', '<br>', '<br/>', '<br />' ], "\n", $ddwcmpa_message );
			$ddwcmpa_plain = wp_strip_all_tags( $ddwcmpa_plain );
			$ddwcmpa_plain = preg_replace( '/[ \t]*\n[ \t]*/', "\n", trim( $ddwcmpa_plain ) );

			// An empty placeholder leaves a run of blank lines behind it.
			$ddwcmpa_plain = preg_replace( '/\n{3,}/', "\n\n", $ddwcmpa_plain );

			// This is the text/plain part. The markup is gone by now, and esc_html()
			// on top would put &amp; and &#039; into a body no client will decode.
			echo $ddwcmpa_plain . "\n\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Stripped above, and html escaping a plain text body would show entities.
		}
	}
}

// Order information. No greeting and no sign off here: the configured message
// is the whole body, exactly as in the HTML template.
if ( $order && ! empty( $order_number ) ) {
	echo esc_html__( 'ORDER INFORMATION', 'devdiggers-prescription-for-woocommerce' ) . "\n";
	echo esc_html( str_repeat( '=', 20 ) ) . "\n";
	echo esc_html__( 'Order Number:', 'devdiggers-prescription-for-woocommerce' ) . ' ' . esc_html( $order_number ) . "\n";

	if ( ! empty( $order_date ) ) {
		echo esc_html__( 'Order Date:', 'devdiggers-prescription-for-woocommerce' ) . ' ' . esc_html( $order_date ) . "\n";
	}

	if ( ! empty( $status ) ) {
		echo esc_html__( 'Status:', 'devdiggers-prescription-for-woocommerce' ) . ' ' . esc_html( $status ) . "\n";
	}

	echo "\n";
}

// Order URL.
if ( $order && ! empty( $order_url ) ) {
	echo esc_html__( 'View your order:', 'devdiggers-prescription-for-woocommerce' ) . "\n";
	echo esc_url( $order_url ) . "\n\n";
}

// Additional content.
if ( ! empty( $additional_content ) ) {
	echo esc_html__( 'ADDITIONAL INFORMATION', 'devdiggers-prescription-for-woocommerce' ) . "\n";
	echo esc_html( str_repeat( '-', 25 ) ) . "\n";
	echo esc_html( wp_strip_all_tags( wptexturize( $additional_content ) ) ) . "\n\n";
}

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

// Same reasoning as the body above: the footer belongs in a plain text part.
echo wp_strip_all_tags( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core WooCommerce hook. Tags stripped, and entity escaping a plain text body would show the entities.
