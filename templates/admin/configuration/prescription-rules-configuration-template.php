<?php
/**
 * Prescription Rules configuration template class
 *
 * @package Prescription for WooCommerce
 * @version 1.0.0
 */

namespace DDWCMedicalPrescriptionAttachment\Templates\Admin\Configuration;

use DevDiggers\Framework\Includes\DDFW_Layout;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCMPA_Prescription_Rules_Configuration_Template' ) ) {
	/**
	 * Prescription Rules configuration template class
	 */
	class DDWCMPA_Prescription_Rules_Configuration_Template {
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
		 * Render the prescription rules configuration.
		 *
		 * @return void
		 */
		public function ddwcmpa_render_configuration() {
			$args = [
				[
					'header'            => [
						'heading'     => esc_html__( 'Details to Collect', 'prescription-for-woocommerce' ),
						'description' => esc_html__( 'Collect the prescriber and patient information your pharmacy needs alongside the uploaded file.', 'prescription-for-woocommerce' ),
					],
					'after_header_html' => ddfw_get_upgrade_to_pro_section(
						[
							'heading'       => esc_html__( 'Know who the prescription is for and who wrote it, in Pro', 'prescription-for-woocommerce' ),
							'description'   => esc_html__( 'A file on its own is hard to check. Pro asks the customer for the details a pharmacist needs, right beside the upload box.', 'prescription-for-woocommerce' ),
							'list_features' => [
								esc_html__( 'Patient name and age, prescribing doctor, registration or licence number, date of issue and valid until date', 'prescription-for-woocommerce' ),
								esc_html__( 'Choose which details to ask for and which ones the order cannot be placed without', 'prescription-for-woocommerce' ),
								esc_html__( 'Shown on the review screen and in My Account, and checked against your prescriber registry', 'prescription-for-woocommerce' ),
							],
							'upgrade_url'   => 'https://devdiggers.com/product/woocommerce-medical-prescription-attachment/',
						]
					),
				],
				[
					'header' => [
						'heading'     => esc_html__( 'Validity and Limits', 'prescription-for-woocommerce' ),
						'description' => esc_html__( 'Decide how long a prescription stays valid and how many files one order may carry.', 'prescription-for-woocommerce' ),
					],
					'fields' => [
						ddfw_locked_field(
							[
								'type'           => 'checkbox',
								'label'          => esc_html__( 'Validity', 'prescription-for-woocommerce' ),
								'checkbox_label' => esc_html__( 'Expire Approved Prescriptions After a Set Period', 'prescription-for-woocommerce' ),
								'description'    => esc_html__( 'A daily task moves lapsed prescriptions to Expired so they can no longer be reused.', 'prescription-for-woocommerce' ),
								'id'             => 'ddwcmpa-expiry-enabled',
								'value'          => '',
							],
							'ddwcmpa'
						),
						ddfw_locked_field(
							[
								'type'              => 'number',
								'label'             => esc_html__( 'Valid For (Days)', 'prescription-for-woocommerce' ),
								'description'       => esc_html__( 'Counted from the date of issue the customer supplied, or from the upload date when they did not supply one. A date entered in the Valid Until field always wins.', 'prescription-for-woocommerce' ),
								'id'                => 'ddwcmpa-expiry-days',
								'value'             => 180,
								'custom_attributes' => [ 'min' => '1' ],
							],
							'ddwcmpa'
						),
						[
							'type'              => 'number',
							'label'             => esc_html__( 'Maximum Files', 'prescription-for-woocommerce' ),
							'description'       => esc_html__( 'How many prescription files one order may carry.', 'prescription-for-woocommerce' ),
							'id'                => 'ddwcmpa-max-files',
							'name'              => '_ddwcmpa_max_files',
							'value'             => $this->ddwcmpa_configuration['max_files'],
							'custom_attributes' => [
								'min' => '1',
							],
						],
					],
				],
				[
					'header'            => [
						'heading'     => esc_html__( 'Refills', 'prescription-for-woocommerce' ),
						'description' => esc_html__( 'Give each approved prescription a fixed number of uses so a repeat customer cannot reuse the same paper indefinitely.', 'prescription-for-woocommerce' ),
					],
					'after_header_html' => ddfw_get_upgrade_to_pro_section(
						[
							'heading'       => esc_html__( 'Repeat orders without a second upload, in Pro', 'prescription-for-woocommerce' ),
							'description'   => esc_html__( 'Returning patients pick a prescription you already approved at checkout, and Pro counts every reuse against the refills the prescriber allowed.', 'prescription-for-woocommerce' ),
							'list_features' => [
								esc_html__( 'Customers reuse an approved, still valid prescription instead of uploading it again', 'prescription-for-woocommerce' ),
								esc_html__( 'The new order is approved on arrival, so repeat business never waits in the queue', 'prescription-for-woocommerce' ),
								esc_html__( 'A refill allowance per prescription, drawn down on every reuse and adjustable per order', 'prescription-for-woocommerce' ),
								esc_html__( 'Exhausted and lapsed prescriptions drop out of the reuse list on their own', 'prescription-for-woocommerce' ),
							],
							'upgrade_url'   => 'https://devdiggers.com/product/woocommerce-medical-prescription-attachment/',
						]
					),
				],
				[
					'header'            => [
						'heading'     => esc_html__( 'Prescriber Registry', 'prescription-for-woocommerce' ),
						'description' => esc_html__( 'The prescribers you already know about. A prescription naming one of them carries their standing into the review screen, so a pharmacist sees it before deciding rather than after.', 'prescription-for-woocommerce' ),
					],
					'after_header_html' => ddfw_get_upgrade_to_pro_section(
						[
							'heading'       => esc_html__( 'Know the prescriber before you approve, in Pro', 'prescription-for-woocommerce' ),
							'description'   => esc_html__( 'Keep a list of the doctors you have verified and the ones you never want to dispense for again. Every prescription is checked against it automatically.', 'prescription-for-woocommerce' ),
							'list_features' => [
								esc_html__( 'Mark prescribers as verified, on watch or blocked, matched by name or licence number', 'prescription-for-woocommerce' ),
								esc_html__( 'A warning on the review screen the moment a watched or blocked prescriber appears', 'prescription-for-woocommerce' ),
								esc_html__( 'A dashboard view of which prescribers your orders come from', 'prescription-for-woocommerce' ),
							],
							'upgrade_url'   => 'https://devdiggers.com/product/woocommerce-medical-prescription-attachment/',
						]
					),
					'fields'            => [],
				],
			];

			$layout = new DDFW_Layout();
			$layout->get_form_section_layout( $args, 'ddwcmpa-prescription-rules-configuration-fields' );
		}
	}
}
