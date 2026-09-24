<?php
/**
 * Emails configuration template class
 *
 * Who is emailed about a prescription. The wording itself is fixed in Free.
 *
 * @package Prescription for WooCommerce
 * @version 1.0.0
 */

namespace DDWCMedicalPrescriptionAttachment\Templates\Admin\Configuration;

use DevDiggers\Framework\Includes\DDFW_Layout;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCMPA_Emails_Configuration_Template' ) ) {
	/**
	 * Emails configuration template class
	 */
	class DDWCMPA_Emails_Configuration_Template {
		/**
		 * Configuration Variable
		 *
		 * @var array
		 */
		protected $ddwcmpa_configuration;

		/**
		 * Construct
		 *
		 * @param array $ddwcmpa_configuration Plugin configuration.
		 */
		public function __construct( $ddwcmpa_configuration ) {
			$this->ddwcmpa_configuration = $ddwcmpa_configuration;
			$this->ddwcmpa_render_configuration();
		}

		/**
		 * Render the emails configuration.
		 *
		 * @return void
		 */
		public function ddwcmpa_render_configuration() {
			$args = [
				[
					'header' => [
						'heading'     => esc_html__( 'Email Delivery', 'prescription-for-woocommerce' ),
						'description' => esc_html__( 'Who hears about a prescription, and when. Recipients, footer text and HTML or plain text format are set in WooCommerce > Settings > Emails > Prescription Notification.', 'prescription-for-woocommerce' ),
					],
					'fields' => [
						[
							'type'           => 'checkbox',
							'label'          => esc_html__( 'Admin Emails', 'prescription-for-woocommerce' ),
							'checkbox_label' => esc_html__( 'Tell Me When a Customer Uploads a Prescription', 'prescription-for-woocommerce' ),
							'description'    => esc_html__( 'Sends the store admin a message whenever a prescription arrives and is waiting for review.', 'prescription-for-woocommerce' ),
							'id'             => 'ddwcmpa-admin-email-enabled',
							'name'           => '_ddwcmpa_admin_email_enabled',
							'value'          => $this->ddwcmpa_configuration['admin_email_enabled'],
						],
						[
							'type'           => 'checkbox',
							'label'          => esc_html__( 'Customer Emails', 'prescription-for-woocommerce' ),
							'checkbox_label' => esc_html__( 'Tell the Customer When Their Prescription Is Decided', 'prescription-for-woocommerce' ),
							'description'    => esc_html__( 'Sends the customer a message when their prescription is approved, rejected or needs more information.', 'prescription-for-woocommerce' ),
							'id'             => 'ddwcmpa-customer-email-enabled',
							'name'           => '_ddwcmpa_customer_email_enabled',
							'value'          => $this->ddwcmpa_configuration['customer_email_enabled'],
						],
					],
				],
				[
					'header'            => [
						'heading'     => esc_html__( 'Reminders', 'prescription-for-woocommerce' ),
						'description' => esc_html__( 'Chase the two things that stall a pharmacy queue: an order with no prescription on it, and an approval about to lapse.', 'prescription-for-woocommerce' ),
					],
					'after_header_html' => ddfw_get_upgrade_to_pro_section(
						[
							'heading'       => esc_html__( 'Recover the orders stuck waiting on a prescription, in Pro', 'prescription-for-woocommerce' ),
							'description'   => esc_html__( 'An order held for a prescription that never arrives is revenue you have already won and are about to lose. Pro follows up for you.', 'prescription-for-woocommerce' ),
							'list_features' => [
								esc_html__( 'One reminder per order to customers who chose to attach their prescription later', 'prescription-for-woocommerce' ),
								esc_html__( 'A warning before an approved prescription lapses, so the customer sends a fresh one in time', 'prescription-for-woocommerce' ),
								esc_html__( 'Your own delay in hours and days, sent by a scheduled task', 'prescription-for-woocommerce' ),
							],
							'upgrade_url'   => 'https://devdiggers.com/product/woocommerce-medical-prescription-attachment/',
						]
					),
				],
				[
					'header'            => [
						'heading'     => esc_html__( 'Email Wording', 'prescription-for-woocommerce' ),
						'description' => esc_html__( 'Free sends a complete, friendly message for every decision, written for a pharmacy.', 'prescription-for-woocommerce' ),
					],
					'after_header_html' => ddfw_get_upgrade_to_pro_section(
						[
							'heading'       => esc_html__( 'Write every email in your own voice, in Pro', 'prescription-for-woocommerce' ),
							'description'   => esc_html__( 'Every customer message is yours to reword in a rich text editor, from the subject line to the sign off.', 'prescription-for-woocommerce' ),
							'list_features' => [
								esc_html__( 'Subject, heading and body for approved, rejected, more information needed, expiring and reminder emails', 'prescription-for-woocommerce' ),
								esc_html__( 'Placeholders for the customer name, order number, reviewer message, validity date and more', 'prescription-for-woocommerce' ),
								esc_html__( 'Sent with your WooCommerce email template, so they match the rest of your store', 'prescription-for-woocommerce' ),
							],
							'upgrade_url'   => 'https://devdiggers.com/product/woocommerce-medical-prescription-attachment/',
						]
					),
				],
			];

			$layout = new DDFW_Layout();
			$layout->get_form_section_layout( $args, 'ddwcmpa-emails-configuration-fields' );
		}
	}
}
