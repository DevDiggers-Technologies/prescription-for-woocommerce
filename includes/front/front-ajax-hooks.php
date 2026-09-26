<?php
/**
 * Front end ajax action hooks.
 *
 * @author DevDiggers
 * @package DevDiggers Prescription for WooCommerce
 * @version 1.0.0
 */

namespace DDWCMedicalPrescriptionAttachment\Includes\Front;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCMPA_Front_Ajax_Hooks' ) ) {
	/**
	 * Front ajax end hooks class
	 */
	class DDWCMPA_Front_Ajax_Hooks extends DDWCMPA_Front_Ajax_Functions {
		/**
		 * Construct
		 *
		 * @param array $ddwcmpa_configuration Plugin configuration.
		 */
		public function __construct( $ddwcmpa_configuration ) {
			parent::__construct( $ddwcmpa_configuration );

			add_action( 'wp_ajax_nopriv_ddwcmpa_handle_prescription_session', [ $this, 'ddwcmpa_handle_prescription_session' ] );
			add_action( 'wp_ajax_ddwcmpa_handle_prescription_session', [ $this, 'ddwcmpa_handle_prescription_session' ] );

			add_action( 'wp_ajax_nopriv_ddwcmpa_get_prescription_ui', [ $this, 'ddwcmpa_get_prescription_ui' ] );
			add_action( 'wp_ajax_ddwcmpa_get_prescription_ui', [ $this, 'ddwcmpa_get_prescription_ui' ] );
		}
	}
}
