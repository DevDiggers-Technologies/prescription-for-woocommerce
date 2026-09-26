<?php
/**
 * Prescription Rules configuration template class
 *
 * @package DevDiggers Prescription for WooCommerce
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
						'heading'     => esc_html__( 'Details to Collect', 'devdiggers-prescription-for-woocommerce' ),
						'description' => esc_html__( 'Collect the prescriber and patient information your pharmacy needs alongside the uploaded file.', 'devdiggers-prescription-for-woocommerce' ),
					],
					'after_header_html' => ddfw_get_upgrade_to_pro_section(
						[
							'heading'       => esc_html__( 'Know who the prescription is for and who wrote it, in Pro', 'devdiggers-prescription-for-woocommerce' ),
							'description'   => esc_html__( 'A file on its own is hard to check. Pro asks the customer for the details a pharmacist needs, right beside the upload box.', 'devdiggers-prescription-for-woocommerce' ),
							'list_features' => [
								esc_html__( 'Patient name and age, prescribing doctor, registration or licence number, date of issue and valid until date', 'devdiggers-prescription-for-woocommerce' ),
								esc_html__( 'Choose which details to ask for and which ones the order cannot be placed without', 'devdiggers-prescription-for-woocommerce' ),
								esc_html__( 'Shown on the review screen and in My Account, and checked against your prescriber registry', 'devdiggers-prescription-for-woocommerce' ),
							],
							'upgrade_url'   => 'https://devdiggers.com/product/woocommerce-medical-prescription-attachment/',
						]
					),
				],
				[
					'header' => [
						'heading'     => esc_html__( 'Validity and Limits', 'devdiggers-prescription-for-woocommerce' ),
						'description' => esc_html__( 'Decide how long a prescription stays valid and how many files one order may carry.', 'devdiggers-prescription-for-woocommerce' ),
					],
					'fields' => [
						ddfw_locked_field(
							[
								'type'           => 'checkbox',
								'label'          => esc_html__( 'Validity', 'devdiggers-prescription-for-woocommerce' ),
								'checkbox_label' => esc_html__( 'Expire Approved Prescriptions After a Set Period', 'devdiggers-prescription-for-woocommerce' ),
								'description'    => esc_html__( 'A daily task moves lapsed prescriptions to Expired so they can no longer be reused.', 'devdiggers-prescription-for-woocommerce' ),
								'id'             => 'ddwcmpa-expiry-enabled',
								'value'          => '',
							],
							'ddwcmpa'
						),
						ddfw_locked_field(
							[
								'type'              => 'number',
								'label'             => esc_html__( 'Valid For (Days)', 'devdiggers-prescription-for-woocommerce' ),
								'description'       => esc_html__( 'Counted from the date of issue the customer supplied, or from the upload date when they did not supply one. A date entered in the Valid Until field always wins.', 'devdiggers-prescription-for-woocommerce' ),
								'id'                => 'ddwcmpa-expiry-days',
								'value'             => 180,
								'custom_attributes' => [ 'min' => '1' ],
							],
							'ddwcmpa'
						),
						[
							'type'              => 'number',
							'label'             => esc_html__( 'Maximum Files', 'devdiggers-prescription-for-woocommerce' ),
							'description'       => esc_html__( 'How many prescription files one order may carry.', 'devdiggers-prescription-for-woocommerce' ),
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
						'heading'     => esc_html__( 'Refills', 'devdiggers-prescription-for-woocommerce' ),
						'description' => esc_html__( 'Give each approved prescription a fixed number of uses so a repeat customer cannot reuse the same paper indefinitely.', 'devdiggers-prescription-for-woocommerce' ),
					],
					'after_header_html' => ddfw_get_upgrade_to_pro_section(
						[
							'heading'       => esc_html__( 'Repeat orders without a second upload, in Pro', 'devdiggers-prescription-for-woocommerce' ),
							'description'   => esc_html__( 'Returning patients pick a prescription you already approved at checkout, and Pro counts every reuse against the refills the prescriber allowed.', 'devdiggers-prescription-for-woocommerce' ),
							'list_features' => [
								esc_html__( 'Customers reuse an approved, still valid prescription instead of uploading it again', 'devdiggers-prescription-for-woocommerce' ),
								esc_html__( 'The new order is approved on arrival, so repeat business never waits in the queue', 'devdiggers-prescription-for-woocommerce' ),
								esc_html__( 'A refill allowance per prescription, drawn down on every reuse and adjustable per order', 'devdiggers-prescription-for-woocommerce' ),
								esc_html__( 'Exhausted and lapsed prescriptions drop out of the reuse list on their own', 'devdiggers-prescription-for-woocommerce' ),
							],
							'upgrade_url'   => 'https://devdiggers.com/product/woocommerce-medical-prescription-attachment/',
						]
					),
				],
				[
					'header'            => [
						'heading'     => esc_html__( 'Prescriber Registry', 'devdiggers-prescription-for-woocommerce' ),
						'description' => esc_html__( 'The prescribers you already know about. A prescription naming one of them carries their standing into the review screen, so a pharmacist sees it before deciding rather than after.', 'devdiggers-prescription-for-woocommerce' ),
					],
					'after_header_html' => ddfw_get_upgrade_to_pro_section(
						[
							'heading'       => esc_html__( 'Know the prescriber before you approve, in Pro', 'devdiggers-prescription-for-woocommerce' ),
							'description'   => esc_html__( 'Keep a list of the doctors you have verified and the ones you never want to dispense for again. Every prescription is checked against it automatically.', 'devdiggers-prescription-for-woocommerce' ),
							'list_features' => [
								esc_html__( 'Mark prescribers as verified, on watch or blocked, matched by name or licence number', 'devdiggers-prescription-for-woocommerce' ),
								esc_html__( 'A warning on the review screen the moment a watched or blocked prescriber appears', 'devdiggers-prescription-for-woocommerce' ),
								esc_html__( 'A dashboard view of which prescribers your orders come from', 'devdiggers-prescription-for-woocommerce' ),
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
