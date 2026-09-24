<?php
/**
 * Setup wizard integration.
 *
 * @author DevDiggers
 * @version 1.0.0
 * @package Prescription for WooCommerce
 */

namespace DDWCMedicalPrescriptionAttachment\Includes\Admin;

use DevDiggers\Framework\Includes\DDFW_Form_Field;
use DevDiggers\Framework\Includes\DDFW_Setup_Wizard;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCMPA_Setup_Wizard' ) ) {
	/**
	 * Setup wizard class.
	 */
	class DDWCMPA_Setup_Wizard {
		/**
		 * Plugin slug the framework keys the wizard state on.
		 *
		 * @var string
		 */
		const SLUG = 'prescription-for-woocommerce';

		/**
		 * The options the wizard steps are allowed to write.
		 *
		 * @var array
		 */
		const OPTIONS = [
			'_ddwcmpa_enabled',
			'_ddwcmpa_allowed_categories',
			'_ddwcmpa_product_label',
			'_ddwcmpa_hold_order_status',
			'_ddwcmpa_approved_order_status',
			'_ddwcmpa_rejected_order_status',
			'_ddwcmpa_customer_email_enabled',
			'_ddwcmpa_admin_email_enabled',
		];

		/**
		 * Construct.
		 */
		public function __construct() {
			if ( ! class_exists( '\\DevDiggers\\Framework\\Includes\\DDFW_Setup_Wizard' ) ) {
				return;
			}

			// A store that already configured the plugin should never be sent
			// back through onboarding after an update.
			if ( ! get_option( 'ddfw_setup_wizard_completed_' . self::SLUG ) && get_option( '_ddwcmpa_enabled' ) ) {
				update_option( 'ddfw_setup_wizard_completed_' . self::SLUG, true );
			}

			new DDFW_Setup_Wizard( $this->get_wizard_config() );
		}

		/**
		 * Wizard configuration.
		 *
		 * @return array
		 */
		public function get_wizard_config() {
			return [
				'plugin_slug'    => self::SLUG,
				'plugin_file'    => self::SLUG . '/functions.php',
				'dashboard_page' => 'ddwcmpa-dashboard',
				'redirect_url'   => admin_url( 'admin.php?page=ddwcmpa-dashboard&menu=dashboard' ),
				'logo'           => $this->get_logo(),
				'steps'          => [
					'welcome'  => [
						'label'         => esc_html__( 'Welcome', 'prescription-for-woocommerce' ),
						'view_callback' => [ $this, 'welcome_view' ],
					],
					'products' => [
						'label'         => esc_html__( 'Products', 'prescription-for-woocommerce' ),
						'title'         => esc_html__( 'What needs a prescription?', 'prescription-for-woocommerce' ),
						'description'   => esc_html__( 'Pick the categories that may only be sold against a prescription. Leave it empty to require one for every product.', 'prescription-for-woocommerce' ),
						'view_callback' => [ $this, 'products_view' ],
						'save_callback' => [ $this, 'save_fields' ],
					],
					'workflow' => [
						'label'         => esc_html__( 'Workflow', 'prescription-for-woocommerce' ),
						'title'         => esc_html__( 'How should orders behave?', 'prescription-for-woocommerce' ),
						'description'   => esc_html__( 'Decide what happens to an order while a pharmacist is still reviewing the prescription.', 'prescription-for-woocommerce' ),
						'view_callback' => [ $this, 'workflow_view' ],
						'save_callback' => [ $this, 'save_fields' ],
					],
					'ready'    => [
						'label'             => esc_html__( 'Ready!', 'prescription-for-woocommerce' ),
						'ready_title'       => esc_html__( 'Your prescription workflow is live.', 'prescription-for-woocommerce' ),
						'ready_description' => esc_html__( 'Prescriptions are stored in a protected folder and served only to the customer and your reviewers. Fine tune everything from the configuration screen.', 'prescription-for-woocommerce' ),
					],
				],
			];
		}

		/**
		 * Plugin mark used across the wizard.
		 *
		 * @return string
		 */
		protected function get_logo() {
			return '<svg width="32" height="32" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg">
				<path d="M10.5 3.2h7.1L25.8 11.4v15.1a3.3 3.3 0 0 1-3.3 3.3h-12a3.3 3.3 0 0 1-3.3-3.3v-20a3.3 3.3 0 0 1 3.3-3.3Z" fill="#e4ecff"/>
				<path d="M10.5 3.2h7.1L25.8 11.4v15.1a3.3 3.3 0 0 1-3.3 3.3h-12a3.3 3.3 0 0 1-3.3-3.3v-20a3.3 3.3 0 0 1 3.3-3.3Z" fill="none" stroke="var(--ddfw-primary-color, #0256ff)" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M17.4 3.4v6.2a2 2 0 0 0 2 2h6.2" fill="none" stroke="var(--ddfw-primary-color, #0256ff)" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M12.4 25.2v-9.4h3.3a2.7 2.7 0 0 1 0 5.4h-3.3" fill="none" stroke="var(--ddfw-primary-color, #0256ff)" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="m15.2 21.2 5.4 4M20.8 20.9l-5.5 4.4" fill="none" stroke="var(--ddfw-primary-color, #0256ff)" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>';
		}

		/**
		 * Welcome step.
		 *
		 * @return void
		 */
		public function welcome_view() {
			?>
			<div class="ddfw-setup-wizard-ready ddfw-setup-wizard-onboarding">
				<div class="ddfw-success-icon-wrap">
					<svg class="ddfw-success-svg" width="100" height="100" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg">
						<path d="M10.5 3.2h7.1L25.8 11.4v15.1a3.3 3.3 0 0 1-3.3 3.3h-12a3.3 3.3 0 0 1-3.3-3.3v-20a3.3 3.3 0 0 1 3.3-3.3Z" fill="#e4ecff"/>
						<path d="M10.5 3.2h7.1L25.8 11.4v15.1a3.3 3.3 0 0 1-3.3 3.3h-12a3.3 3.3 0 0 1-3.3-3.3v-20a3.3 3.3 0 0 1 3.3-3.3Z" fill="none" stroke="var(--ddfw-primary-color, #0256ff)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M17.4 3.4v6.2a2 2 0 0 0 2 2h6.2" fill="none" stroke="var(--ddfw-primary-color, #0256ff)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M12.4 25.2v-9.4h3.3a2.7 2.7 0 0 1 0 5.4h-3.3" fill="none" stroke="var(--ddfw-primary-color, #0256ff)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="m15.2 21.2 5.4 4M20.8 20.9l-5.5 4.4" fill="none" stroke="var(--ddfw-primary-color, #0256ff)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</div>
				<h2 class="ddfw-setup-wizard-ready-title"><?php esc_html_e( 'Sell regulated products with confidence', 'prescription-for-woocommerce' ); ?></h2>
				<p class="ddfw-setup-wizard-ready-desc">
					<?php esc_html_e( 'Customers upload a prescription at checkout, your pharmacist approves or rejects it, and every file is kept in a protected folder that only they and your reviewers can open. Three short steps and you are ready.', 'prescription-for-woocommerce' ); ?>
				</p>
			</div>
			<?php
		}

		/**
		 * Products step.
		 *
		 * @return void
		 */
		public function products_view() {
			$this->render_fields(
				[
					[
						'id'             => 'ddwcmpa-enabled',
						'name'           => '_ddwcmpa_enabled',
						'type'           => 'checkbox',
						'label'          => esc_html__( 'Enable prescriptions', 'prescription-for-woocommerce' ),
						'checkbox_label' => esc_html__( 'Ask customers for a prescription at checkout', 'prescription-for-woocommerce' ),
						'description'    => esc_html__( 'The master switch. With this off nothing is asked for, shown or held anywhere on the store front.', 'prescription-for-woocommerce' ),
						'value'          => get_option( '_ddwcmpa_enabled', 'yes' ),
					],
					[
						'id'                => 'ddwcmpa-allowed-categories',
						'name'              => '_ddwcmpa_allowed_categories[]',
						'type'              => 'categories',
						'label'             => esc_html__( 'Prescription categories', 'prescription-for-woocommerce' ),
						'description'       => esc_html__( 'Leave empty to require a prescription for every product in the store. Individual products can override this later from the product screen.', 'prescription-for-woocommerce' ),
						'value'             => (array) get_option( '_ddwcmpa_allowed_categories', [] ),
						'custom_attributes' => [ 'multiple' => true ],
					],
					[
						'id'          => 'ddwcmpa-product-label',
						'name'        => '_ddwcmpa_product_label',
						'type'        => 'text',
						'label'       => esc_html__( 'Product badge text', 'prescription-for-woocommerce' ),
						'description' => esc_html__( 'Shown on the shop and product pages so a customer knows before they add to cart. You can restyle it later under Configuration, Design.', 'prescription-for-woocommerce' ),
						'value'       => get_option( '_ddwcmpa_product_label', esc_html__( 'Requires Prescription', 'prescription-for-woocommerce' ) ),
					],
				]
			);
		}

		/**
		 * Workflow step.
		 *
		 * @return void
		 */
		public function workflow_view() {
			$this->render_fields(
				[
					[
						'id'          => 'ddwcmpa-hold-order-status',
						'name'        => '_ddwcmpa_hold_order_status',
						'type'        => 'select',
						'label'       => esc_html__( 'While under review', 'prescription-for-woocommerce' ),
						'description' => esc_html__( 'Park the order in this status until a pharmacist decides, so it is never fulfilled early, even after the customer pays.', 'prescription-for-woocommerce' ),
						'value'       => get_option( '_ddwcmpa_hold_order_status', 'wc-on-hold' ),
						'options'     => $this->get_status_options( esc_html__( 'Leave the order alone', 'prescription-for-woocommerce' ) ),
					],
					[
						'id'          => 'ddwcmpa-approved-order-status',
						'name'        => '_ddwcmpa_approved_order_status',
						'type'        => 'select',
						'label'       => esc_html__( 'On approval', 'prescription-for-woocommerce' ),
						'description' => esc_html__( 'Where the order goes the moment a pharmacist approves it. Processing releases it to your normal fulfilment flow.', 'prescription-for-woocommerce' ),
						'value'       => get_option( '_ddwcmpa_approved_order_status', 'wc-processing' ),
						'options'     => $this->get_status_options( esc_html__( 'Do not change the order', 'prescription-for-woocommerce' ) ),
					],
					[
						'id'          => 'ddwcmpa-rejected-order-status',
						'name'        => '_ddwcmpa_rejected_order_status',
						'type'        => 'select',
						'label'       => esc_html__( 'On rejection', 'prescription-for-woocommerce' ),
						'description' => esc_html__( 'Where the order goes when the prescription is refused. Leaving it alone lets the customer upload a replacement without you reopening anything.', 'prescription-for-woocommerce' ),
						'value'       => get_option( '_ddwcmpa_rejected_order_status', '' ),
						'options'     => $this->get_status_options( esc_html__( 'Do not change the order', 'prescription-for-woocommerce' ) ),
					],
					[
						'id'             => 'ddwcmpa-customer-email-enabled',
						'name'           => '_ddwcmpa_customer_email_enabled',
						'type'           => 'checkbox',
						'label'          => esc_html__( 'Customer emails', 'prescription-for-woocommerce' ),
						'checkbox_label' => esc_html__( 'Email the customer when their prescription is approved or rejected', 'prescription-for-woocommerce' ),
						'description'    => esc_html__( 'Sent with your WooCommerce email template, with a clear message for approvals, rejections and requests for more information.', 'prescription-for-woocommerce' ),
						'value'          => get_option( '_ddwcmpa_customer_email_enabled', 'yes' ),
					],
					[
						'id'             => 'ddwcmpa-admin-email-enabled',
						'name'           => '_ddwcmpa_admin_email_enabled',
						'type'           => 'checkbox',
						'label'          => esc_html__( 'Reviewer emails', 'prescription-for-woocommerce' ),
						'checkbox_label' => esc_html__( 'Email the store when a prescription is waiting for review', 'prescription-for-woocommerce' ),
						'description'    => esc_html__( 'Goes to the WooCommerce admin address with a link straight into the review screen, so nothing sits in the queue unnoticed.', 'prescription-for-woocommerce' ),
						'value'          => get_option( '_ddwcmpa_admin_email_enabled', 'yes' ),
					],
				]
			);
		}

		/**
		 * Order status options, with a "do nothing" entry first.
		 *
		 * @param string $none_label Label for the empty option.
		 * @return array
		 */
		protected function get_status_options( $none_label ) {
			return [ '' => $none_label ] + wc_get_order_statuses();
		}

		/**
		 * Render a set of framework fields inside the wizard shell.
		 *
		 * @param array $fields Field definitions.
		 * @return void
		 */
		protected function render_fields( $fields ) {
			?>
			<div class="ddfw-fields-section">
				<table class="form-table">
					<tbody>
						<?php
						foreach ( $fields as $field ) {
							DDFW_Form_Field::display_form_field( $field );
						}
						?>
					</tbody>
				</table>
			</div>
			<?php
		}

		/**
		 * Persist the fields a wizard step posted.
		 *
		 * @param array $form_data Posted field data.
		 * @return bool
		 */
		public function save_fields( $form_data ) {
			$grouped = [];

			foreach ( (array) $form_data as $field ) {
				$name = ! empty( $field['name'] ) ? (string) $field['name'] : '';

				// Only the options the wizard itself shows. Matching on the prefix alone
				// let a crafted step overwrite any plugin option, the vault key included.
				if ( ! in_array( rtrim( $name, '[]' ), self::OPTIONS, true ) ) {
					continue;
				}

				$field['value'] = isset( $field['value'] ) ? (string) $field['value'] : '';

				// Multi selects arrive as repeated name[] entries.
				if ( '[]' === substr( $name, -2 ) ) {
					$name = substr( $name, 0, -2 );

					if ( ! isset( $grouped[ $name ] ) ) {
						$grouped[ $name ] = [];
					}

					if ( '' !== $field['value'] ) {
						$grouped[ $name ][] = sanitize_text_field( $field['value'] );
					}

					continue;
				}

				$grouped[ $name ] = sanitize_text_field( $field['value'] );
			}

			foreach ( $grouped as $option => $value ) {
				update_option( $option, $value );
			}

			return true;
		}
	}
}
