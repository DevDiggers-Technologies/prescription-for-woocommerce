<?php
/**
 * Compliance configuration template class
 *
 * @package DevDiggers Prescription for WooCommerce
 * @version 1.0.0
 */

namespace DDWCMedicalPrescriptionAttachment\Templates\Admin\Configuration;

use DevDiggers\Framework\Includes\DDFW_Layout;
use DDWCMedicalPrescriptionAttachment\Helper\DDWCMPA_Security_Helper;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCMPA_Compliance_Configuration_Template' ) ) {
	/**
	 * Compliance configuration template class
	 */
	class DDWCMPA_Compliance_Configuration_Template {
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
		 * Render the compliance configuration.
		 *
		 * @return void
		 */
		public function ddwcmpa_render_configuration() {
			$vault = DDWCMPA_Security_Helper::get_vault_dir();

			$args = [
				[
					'header' => [
						'heading'     => esc_html__( 'Where Prescriptions Are Stored', 'devdiggers-prescription-for-woocommerce' ),
						'description' => esc_html__( 'Uploaded prescriptions never sit in the public uploads folder. They are written to a private, deny-all directory with an unguessable name, and served only through a capability checked endpoint.', 'devdiggers-prescription-for-woocommerce' ),
					],
					'fields' => [
						[
							'type'        => 'field_html',
							'label'       => esc_html__( 'Vault Location', 'devdiggers-prescription-for-woocommerce' ),
							'description' => esc_html__( 'Back this folder up with the same care as your database. Its name is unique to this site.', 'devdiggers-prescription-for-woocommerce' ),
							'id'          => 'ddwcmpa-vault-path',
							'html'        => '<code class="ddwcmpa-vault-path">' . esc_html( str_replace( ABSPATH, '', $vault['path'] ) ) . '</code>',
						],
					],
				],
				[
					'header'            => [
						'heading'     => esc_html__( 'Data Retention', 'devdiggers-prescription-for-woocommerce' ),
						'description' => esc_html__( 'Health records should not be kept longer than the reason you collected them.', 'devdiggers-prescription-for-woocommerce' ),
					],
					'after_header_html' => ddfw_get_upgrade_to_pro_section(
						[
							'heading'       => esc_html__( 'Delete old prescriptions on schedule, in Pro', 'devdiggers-prescription-for-woocommerce' ),
							'description'   => esc_html__( 'Data protection rules expect health data to be removed once you no longer need it. Pro does it for you, every day, without touching open orders.', 'devdiggers-prescription-for-woocommerce' ),
							'list_features' => [
								esc_html__( 'Choose how many days prescriptions are kept after an order is finished', 'devdiggers-prescription-for-woocommerce' ),
								esc_html__( 'Delete only the files, or the files and the patient and prescriber details', 'devdiggers-prescription-for-woocommerce' ),
								esc_html__( 'The decision and audit trail survive, so you can still prove an order was checked', 'devdiggers-prescription-for-woocommerce' ),
							],
							'upgrade_url'   => 'https://devdiggers.com/product/woocommerce-medical-prescription-attachment/',
						]
					),
				],
				[
					'header'            => [
						'heading'     => esc_html__( 'Access Log', 'devdiggers-prescription-for-woocommerce' ),
						'description' => esc_html__( 'Record every time a prescription file is opened, so you can answer who looked at a patient record and when.', 'devdiggers-prescription-for-woocommerce' ),
					],
					'after_header_html' => ddfw_get_upgrade_to_pro_section(
						[
							'heading'       => esc_html__( 'Answer who opened a patient record, in Pro', 'devdiggers-prescription-for-woocommerce' ),
							'description'   => esc_html__( 'Pro writes the user, the time and the IP address against the order each time a prescription file is served.', 'devdiggers-prescription-for-woocommerce' ),
							'list_features' => [
								esc_html__( 'A per order log of every view, shown in the review workspace', 'devdiggers-prescription-for-woocommerce' ),
								esc_html__( 'Repeat views collapsed, so the log shows people rather than page refreshes', 'devdiggers-prescription-for-woocommerce' ),
								esc_html__( 'A full, structured audit trail of every review decision alongside it', 'devdiggers-prescription-for-woocommerce' ),
							],
							'upgrade_url'   => 'https://devdiggers.com/product/woocommerce-medical-prescription-attachment/',
						]
					),
				],
				[
					'header'            => [
						'heading'     => esc_html__( 'Customer Consent', 'devdiggers-prescription-for-woocommerce' ),
						'description' => esc_html__( 'Ask the customer to agree to their prescription being handled before they place the order, and keep the timestamped proof on the order.', 'devdiggers-prescription-for-woocommerce' ),
					],
					'after_header_html' => ddfw_get_upgrade_to_pro_section(
						[
							'heading'       => esc_html__( 'Keep proof the customer agreed, in Pro', 'devdiggers-prescription-for-woocommerce' ),
							'description'   => esc_html__( 'Prescriptions are health data. Pro asks for explicit consent under the upload box and stores exactly what the customer agreed to.', 'devdiggers-prescription-for-woocommerce' ),
							'list_features' => [
								esc_html__( 'Checkout waits until the customer ticks your consent statement, in the classic and block checkout', 'devdiggers-prescription-for-woocommerce' ),
								esc_html__( 'Your own wording, stored on the order as it read on the day, with the date and IP address', 'devdiggers-prescription-for-woocommerce' ),
								esc_html__( 'A consent badge on the review screen, so a pharmacist sees it before deciding', 'devdiggers-prescription-for-woocommerce' ),
							],
							'upgrade_url'   => 'https://devdiggers.com/product/woocommerce-medical-prescription-attachment/',
						]
					),
				],
			];

			$layout = new DDFW_Layout();
			$layout->get_form_section_layout( $args );
		}
	}
}
