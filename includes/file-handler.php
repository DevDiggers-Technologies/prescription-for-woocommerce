<?php
/**
 * File handler
 *
 * @author DevDiggers
 * @package Prescription for WooCommerce
 * @version 1.0.0
 */

namespace DDWCMedicalPrescriptionAttachment\Includes;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCMPA_File_Handler' ) ) {
	/**
	 * File handler class
	 */
	class DDWCMPA_File_Handler {
		/**
		 * The notifications the merchant can word themselves.
		 *
		 * @var array
		 */
		const EMAIL_STATUSES = [ 'approved', 'rejected', 'info_required' ];

		/**
		 * The wording every notification ships with.
		 *
		 * These are real defaults, not placeholders: every store sends a complete,
		 * sensible message without configuring anything.
		 *
		 * The message holds the whole email body, greeting and sign off included.
		 * The template only prints what it alone can know: the order box, the View
		 * Order button and the footer.
		 *
		 * Placeholders are substituted by WC_Email::format_string() on send, so
		 * {customer_name}, {reason} and {reupload_note} resolve to
		 * the values for that particular notification. {reupload_note} is empty
		 * when re-uploads are switched off, so the sentence disappears with the
		 * feature instead of promising something the store does not offer.
		 *
		 * @return array Status keyed subject, heading and message.
		 */
		public static function default_emails() {
			return [
				'approved'      => [
					'subject' => esc_html__( '[{site_title}] Prescription approved for order #{order_number}', 'prescription-for-woocommerce' ),
					'heading' => esc_html__( 'Your prescription has been approved', 'prescription-for-woocommerce' ),
					'message' => wp_kses_post(
						__(
							'<p>Hi {customer_name},</p>
							<p>Great news. A pharmacist at {site_title} has reviewed the prescription you uploaded for order #{order_number} and approved it.</p>
							<p>Your order is now being prepared for dispatch, so there is nothing further you need to do.</p>
							<p>Thank you for trusting us with your prescription.</p>
							<p>Best regards</p>',
							'prescription-for-woocommerce'
						)
					),
				],
				'rejected'      => [
					'subject' => esc_html__( '[{site_title}] Prescription rejected for order #{order_number}', 'prescription-for-woocommerce' ),
					'heading' => esc_html__( 'We could not accept your prescription', 'prescription-for-woocommerce' ),
					'message' => wp_kses_post(
						__(
							'<p>Hi {customer_name},</p>
							<p>Thank you for sending your prescription for order #{order_number}. A pharmacist at {site_title} was not able to accept it this time.</p>
							<p>{reason}</p>
							<p>You are very welcome to send us a replacement whenever you are ready and we will review it as soon as it arrives. {reupload_note}</p>
							<p>Thank you for your patience.</p>
							<p>Best regards</p>',
							'prescription-for-woocommerce'
						)
					),
				],
				'info_required' => [
					'subject' => esc_html__( '[{site_title}] More information needed for order #{order_number}', 'prescription-for-woocommerce' ),
					'heading' => esc_html__( 'We need a little more from you', 'prescription-for-woocommerce' ),
					'message' => wp_kses_post(
						__(
							'<p>Hi {customer_name},</p>
							<p>Thank you for uploading your prescription for order #{order_number}. Nothing has been refused. Before a pharmacist at {site_title} can finish the check we need one more thing from you.</p>
							<p>{reason}</p>
							<p>As soon as we have it we will pick your order straight back up. {reupload_note}</p>
							<p>Thank you for your help.</p>
							<p>Best regards</p>',
							'prescription-for-woocommerce'
						)
					),
				],
			];
		}

		/**
		 * Constructor
		 */
		public function __construct() {
			// Before anything that queries orders, the upgrade routine included.
			add_filter( 'woocommerce_order_query_args', [ Common\DDWCMPA_Common_Functions::class, 'ddwcmpa_park_order_meta_query' ] );
			add_filter( 'woocommerce_order_data_store_cpt_get_orders_query', [ Common\DDWCMPA_Common_Functions::class, 'ddwcmpa_restore_order_meta_query' ], 10, 2 );

			$ddwcmpa_configuration = $this->ddwcmpa_set_globals();

			new Front\DDWCMPA_Front_Ajax_Hooks( $ddwcmpa_configuration );
			new Common\DDWCMPA_Common_Hooks( $ddwcmpa_configuration );

			if ( is_admin() ) {
				new DDWCMPA_Admin_Dashboard( $ddwcmpa_configuration );
				new Admin\DDWCMPA_Admin_Hooks( $ddwcmpa_configuration );
				new Admin\DDWCMPA_Setup_Wizard();
			} elseif ( ! empty( $ddwcmpa_configuration['enabled'] ) ) {
				new Front\DDWCMPA_Front_Hooks( $ddwcmpa_configuration );
			}
		}

		/**
		 * Set globals function
		 *
		 * @return array
		 */
		public function ddwcmpa_set_globals() {
			global $ddwcmpa_configuration;

			$allowed_categories             = get_option( '_ddwcmpa_allowed_categories' );
			$excluded_products              = get_option( '_ddwcmpa_excluded_products' );
			$product_label                  = get_option( '_ddwcmpa_product_label' );
			$product_label_font_color       = get_option( '_ddwcmpa_product_label_font_color' );
			$product_label_background_color = get_option( '_ddwcmpa_product_label_background_color' );

			// Every setting carries the value the plugin needs to work out of the box.
			// A store that installs this and never opens the configuration screen still
			// gets a working, compliant prescription workflow, and anything a merchant
			// does want to turn off has a switch of its own.
			$ddwcmpa_configuration = [
				// The one switch that ships off. Everything else below is filled in
				// ready to run, so turning this on is all a merchant has to do.
				'enabled'                        => get_option( '_ddwcmpa_enabled', '' ),
				'allowed_categories'             => ! empty( $allowed_categories ) ? $allowed_categories : [],
				'excluded_products'              => ! empty( $excluded_products ) ? $excluded_products : [],
				'attach_later_enabled'           => get_option( '_ddwcmpa_attach_later_enabled', 'yes' ),
				'admin_email_enabled'            => get_option( '_ddwcmpa_admin_email_enabled', 'yes' ),
				'customer_email_enabled'         => get_option( '_ddwcmpa_customer_email_enabled', 'yes' ),
				'product_label'                  => ! empty( $product_label ) ? $product_label : esc_html__( 'Requires Prescription', 'prescription-for-woocommerce' ),
				'product_label_font_color'       => ! empty( $product_label_font_color ) ? $product_label_font_color : '#ffffff',
				'product_label_background_color' => ! empty( $product_label_background_color ) ? $product_label_background_color : '#0256ff',
				'prescription_description'       => get_option( '_ddwcmpa_prescription_description', '' ),
				'product_page_position'          => get_option( '_ddwcmpa_product_page_position', '3' ),
				'shop_page_position'             => get_option( '_ddwcmpa_shop_page_position', 'after_title' ),
				'cart_page_position'             => get_option( '_ddwcmpa_cart_page_position', 'after_cart_table' ),
				'checkout_page_position'         => get_option( '_ddwcmpa_checkout_page_position', 'before_checkout_form' ),
				'order_page_position'            => get_option( '_ddwcmpa_order_page_position', 'before_order_table' ),

				'max_files'                      => absint( get_option( '_ddwcmpa_max_files', 5 ) ),

				// Order status automation. Holding the order is the whole point of the
				// plugin, so it defaults to on hold rather than to doing nothing.
				'hold_order_status'              => get_option( '_ddwcmpa_hold_order_status', 'wc-on-hold' ),
				'approved_order_status'          => get_option( '_ddwcmpa_approved_order_status', 'wc-processing' ),
				'rejected_order_status'          => get_option( '_ddwcmpa_rejected_order_status', '' ),

				// Review decisions.
				'order_notes_enabled'            => get_option( '_ddwcmpa_order_notes_enabled', 'yes' ),
				'rejection_reason_required'      => get_option( '_ddwcmpa_rejection_reason_required', 'yes' ),

				// Customer experience.
				'self_service_enabled'           => get_option( '_ddwcmpa_self_service_enabled', 'yes' ),
				// Kept as an alias so the places that ask "may this customer replace
				// their file" do not all have to change name.
				'reupload_enabled'               => get_option( '_ddwcmpa_self_service_enabled', 'yes' ),
			];

			// Email wording is fixed in Free. The built in messages are complete, and
			// rewording them is a Pro feature, so nothing is read from the options table.
			foreach ( self::default_emails() as $ddwcmpa_status => $ddwcmpa_email ) {
				foreach ( $ddwcmpa_email as $ddwcmpa_part => $ddwcmpa_value ) {
					$ddwcmpa_configuration[ 'email_' . $ddwcmpa_part . '_' . $ddwcmpa_status ] = $ddwcmpa_value;
				}
			}

			return $ddwcmpa_configuration;
		}
	}
}
