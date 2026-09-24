<?php
/**
 * General Configuration template class
 *
 * @package Prescription for WooCommerce
 * @version 1.0.0
 */

namespace DDWCMedicalPrescriptionAttachment\Templates\Admin\Configuration;

use DevDiggers\Framework\Includes\DDFW_Layout;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCMPA_Configuration_Template' ) ) {
	/**
	 * General Configuration template class
	 */
	class DDWCMPA_Configuration_Template {
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
			$this->ddwcmpa_render_general_configuration();
		}

		/**
		 * Render general configuration
		 *
		 * @return void
		 */
		public function ddwcmpa_render_general_configuration() {
			$args = [
				[
					'header' => [
						'heading'     => esc_html__( 'Plugin Settings', 'prescription-for-woocommerce' ),
						'description' => esc_html__( 'Switch the prescription workflow on and choose which products need a prescription.', 'prescription-for-woocommerce' ),
					],
					'fields' => [
						[
							'type'           => 'checkbox',
							'label'          => esc_html__( 'Enable/Disable', 'prescription-for-woocommerce' ),
							'checkbox_label' => esc_html__( 'Enable Prescription for WooCommerce', 'prescription-for-woocommerce' ),
							'description'    => esc_html__( 'This allows the module functionality to be used on the frontend.', 'prescription-for-woocommerce' ),
							'id'             => 'ddwcmpa-enabled',
							'name'           => '_ddwcmpa_enabled',
							'value'          => $this->ddwcmpa_configuration['enabled'],
						],
					],
				],
				[
					'header' => [
						'heading'     => esc_html__( 'Product Categories and Exclusions', 'prescription-for-woocommerce' ),
						'description' => esc_html__( 'Configure which product categories require prescription approval and which products to exclude.', 'prescription-for-woocommerce' ),
					],
					'fields' => [
						[
							'type'              => 'categories',
							'label'             => esc_html__( 'Allowed Categories', 'prescription-for-woocommerce' ),
							'description'       => esc_html__( 'Prescription approval is mandatory for products which exists in the selected categories. If there are no selected category, then it will get applied for all products.', 'prescription-for-woocommerce' ),
							'id'                => 'ddwcmpa-allowed-categories',
							'name'              => '_ddwcmpa_allowed_categories[]',
							'value'             => $this->ddwcmpa_configuration['allowed_categories'],
							'custom_attributes' => [
								'multiple' => true,
							],
						],
						[
							'type'              => 'products',
							'label'             => esc_html__( 'Excluded Products', 'prescription-for-woocommerce' ),
							'description'       => esc_html__( 'Prescription won\'t be needed for these selected products even if they exists in the above allowed categories.', 'prescription-for-woocommerce' ),
							'id'                => 'ddwcmpa-excluded-products',
							'name'              => '_ddwcmpa_excluded_products[]',
							'value'             => $this->ddwcmpa_configuration['excluded_products'],
							'custom_attributes' => [
								'multiple' => true,
							],
						],
					],
				],
			];

			$layout = new DDFW_Layout();
			$layout->get_form_section_layout( $args, 'ddwcmpa-general-configuration-fields' );
		}
	}
}
