<?php
/**
 * Activation routine.
 *
 * @author DevDiggers
 * @package Prescription for WooCommerce
 * @version 1.0.0
 */

namespace DDWCMedicalPrescriptionAttachment\Includes;

use DDWCMedicalPrescriptionAttachment\Helper\DDWCMPA_Security_Helper;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCMPA_Install' ) ) {
	/**
	 * Install class.
	 */
	class DDWCMPA_Install {
		/**
		 * Run on plugin activation.
		 *
		 * Idempotent, so reactivating is always safe. There is no version check or upgrade
		 * routine: this is the first release, and the vault is also created on first upload.
		 *
		 * @return void
		 */
		public static function activate() {
			DDWCMPA_Security_Helper::prepare_vault();

			// Starts the clock on the review request, which waits two weeks after install.
			add_option( 'ddwcmpa_installed_at', time() );

			set_transient( 'ddfw_activation_redirect_' . Admin\DDWCMPA_Setup_Wizard::SLUG, true, 30 );
		}
	}
}
