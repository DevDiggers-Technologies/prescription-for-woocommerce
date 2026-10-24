<?php
/**
 * This file contains all common end action hooks.
 *
 * @author DevDiggers
 * @version 1.0.0
 * @package DevDiggers Prescription for WooCommerce
 */

namespace DDWCMedicalPrescriptionAttachment\Includes\Common;

use DDWCMedicalPrescriptionAttachment\Helper\DDWCMPA_Security_Helper;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCMPA_Common_Hooks' ) ) {
	/**
	 * Common end hook handler class
	 */
	class DDWCMPA_Common_Hooks extends DDWCMPA_Common_Functions {
		/**
		 * Constructor
		 *
		 * @param array $ddwcmpa_configuration Plugin configuration.
		 */
		public function __construct( $ddwcmpa_configuration = [] ) {
			parent::__construct( $ddwcmpa_configuration );

			add_filter( 'woocommerce_email_classes', [ $this, 'ddwcmpa_add_new_email_notification' ] );
			add_filter( 'woocommerce_email_actions', [ $this, 'ddwcmpa_add_notification_actions' ] );

			// Serve vaulted prescriptions through the capability checked endpoint.
			add_action( 'template_redirect', [ DDWCMPA_Security_Helper::class, 'maybe_serve_file' ], 1 );

			// Keep prescriptions out of the media library browser.
			add_filter( 'ajax_query_attachments_args', [ DDWCMPA_Security_Helper::class, 'filter_media_library_query' ] );
			add_action( 'pre_get_posts', [ $this, 'ddwcmpa_hide_protected_from_media_list' ] );

			// Payment runs after the order is held for review, and every gateway sets its
			// own status on the way out. Without these the hold only survived gateways that
			// happened to choose on hold themselves.
			add_filter( 'woocommerce_payment_complete_order_status', [ $this, 'ddwcmpa_keep_order_on_hold' ], 20, 3 );
			add_filter( 'woocommerce_cod_process_payment_order_status', [ $this, 'ddwcmpa_keep_order_on_hold' ], 20, 2 );
			add_filter( 'woocommerce_bacs_process_payment_order_status', [ $this, 'ddwcmpa_keep_order_on_hold' ], 20, 2 );
			add_filter( 'woocommerce_cheque_process_payment_order_status', [ $this, 'ddwcmpa_keep_order_on_hold' ], 20, 2 );
		}
	}
}
