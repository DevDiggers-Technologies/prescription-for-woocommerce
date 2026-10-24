<?php
/**
 * Customer Experience configuration template class
 *
 * @package DevDiggers Prescription for WooCommerce
 * @version 1.0.0
 */

namespace DDWCMedicalPrescriptionAttachment\Templates\Admin\Configuration;

use DevDiggers\Framework\Includes\DDFW_Layout;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCMPA_Customer_Experience_Configuration_Template' ) ) {
	/**
	 * Customer Experience configuration template class
	 */
	class DDWCMPA_Customer_Experience_Configuration_Template {
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
		 * Render the customer experience configuration.
		 *
		 * @return void
		 */
		public function ddwcmpa_render_configuration() {
			$args = [
				[
					'header' => [
						'heading'     => esc_html__( 'Customer Features', 'devdiggers-prescription-for-woocommerce' ),
						'description' => esc_html__( 'Configure additional features for customers regarding prescription management.', 'devdiggers-prescription-for-woocommerce' ),
					],
					'fields' => [
						[
							'type'           => 'checkbox',
							'label'          => esc_html__( 'Attach Later', 'devdiggers-prescription-for-woocommerce' ),
							'checkbox_label' => esc_html__( 'Let Logged-in Customers Attach the Prescription After Ordering', 'devdiggers-prescription-for-woocommerce' ),
							'description'    => esc_html__( 'The order can be placed without a prescription and the file uploaded later from the order page. The order still waits for a review before it moves on.', 'devdiggers-prescription-for-woocommerce' ),
							'id'             => 'ddwcmpa-attach-later-enabled',
							'name'           => '_ddwcmpa_attach_later_enabled',
							'value'          => $this->ddwcmpa_configuration['attach_later_enabled'],
						],
						ddfw_locked_field(
							[
								'type'           => 'checkbox',
								'label'          => esc_html__( 'My Account', 'devdiggers-prescription-for-woocommerce' ),
								'checkbox_label' => esc_html__( 'Show a Prescriptions Tab in My Account', 'devdiggers-prescription-for-woocommerce' ),
								'description'    => esc_html__( 'Customers get their own list of prescriptions with the review status, the details and the files for each one, and can fix a refused one from there.', 'devdiggers-prescription-for-woocommerce' ),
								'id'             => 'ddwcmpa-my-account-enabled',
								'value'          => 'yes',
							],
							'ddwcmpa'
						),
						[
							'type'           => 'checkbox',
							'label'          => esc_html__( 'Self Service', 'devdiggers-prescription-for-woocommerce' ),
							'checkbox_label' => esc_html__( 'Let Customers Fix and Resubmit a Refused Prescription', 'devdiggers-prescription-for-woocommerce' ),
							'description'    => esc_html__( 'Adds the upload box to the order page, so a customer who was refused or asked for a better scan can act on it without contacting you.', 'devdiggers-prescription-for-woocommerce' ),
							'id'             => 'ddwcmpa-self-service-enabled',
							'name'           => '_ddwcmpa_self_service_enabled',
							'value'          => $this->ddwcmpa_configuration['self_service_enabled'],
						],
						ddfw_locked_field(
							[
								'type'           => 'checkbox',
								'label'          => esc_html__( 'Reuse', 'devdiggers-prescription-for-woocommerce' ),
								'checkbox_label' => esc_html__( 'Let Customers Reuse an Approved Prescription', 'devdiggers-prescription-for-woocommerce' ),
								'description'    => esc_html__( 'Repeat customers can pick a prescription that is still valid instead of uploading the same file again. The new order is approved straight away.', 'devdiggers-prescription-for-woocommerce' ),
								'id'             => 'ddwcmpa-reuse-enabled',
								'value'          => '',
							],
							'ddwcmpa'
						),
					],
				],
				[
					'header' => [
						'heading'     => esc_html__( 'My Account Section', 'devdiggers-prescription-for-woocommerce' ),
						'description' => esc_html__( 'The prescriptions screen inside the WooCommerce My Account area, with its own endpoint slug, menu title and sidebar handling, is part of the Pro plugin.', 'devdiggers-prescription-for-woocommerce' ),
					],
					'fields' => [
						ddfw_locked_field(
							[
								'type'        => 'text',
								'label'       => esc_html__( 'Endpoint Slug', 'devdiggers-prescription-for-woocommerce' ),
								'description' => esc_html__( 'The URL identifier for the prescriptions screen, for example "medical-prescriptions".', 'devdiggers-prescription-for-woocommerce' ),
								'id'          => 'ddwcmpa-my-account-endpoint',
								'value'       => 'medical-prescriptions',
							],
							'ddwcmpa'
						),
						ddfw_locked_field(
							[
								'type'        => 'text',
								'label'       => esc_html__( 'Menu Title', 'devdiggers-prescription-for-woocommerce' ),
								'description' => esc_html__( 'The label shown in the My Account sidebar navigation.', 'devdiggers-prescription-for-woocommerce' ),
								'id'          => 'ddwcmpa-my-account-endpoint-title',
								'value'       => 'Prescriptions',
							],
							'ddwcmpa'
						),
						ddfw_locked_field(
							[
								'type'           => 'checkbox',
								'label'          => esc_html__( 'Sidebar Widgets', 'devdiggers-prescription-for-woocommerce' ),
								'checkbox_label' => esc_html__( 'Keep the Theme Sidebar Widgets on the Prescriptions Screen', 'devdiggers-prescription-for-woocommerce' ),
								'description'    => esc_html__( 'Turn this off for a full width prescriptions screen without the theme sidebar.', 'devdiggers-prescription-for-woocommerce' ),
								'id'             => 'ddwcmpa-enable-widgets-my-account-endpoint',
								'value'          => 'yes',
							],
							'ddwcmpa'
						),
					],
				],
			];

			$layout = new DDFW_Layout();
			$layout->get_form_section_layout( $args, 'ddwcmpa-customer-experience-configuration-fields' );
		}
	}
}
