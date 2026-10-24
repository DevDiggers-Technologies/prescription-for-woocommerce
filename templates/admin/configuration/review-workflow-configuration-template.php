<?php
/**
 * Review Workflow configuration template class
 *
 * @package DevDiggers Prescription for WooCommerce
 * @version 1.0.0
 */

namespace DDWCMedicalPrescriptionAttachment\Templates\Admin\Configuration;

use DevDiggers\Framework\Includes\DDFW_Layout;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCMPA_Review_Workflow_Configuration_Template' ) ) {
	/**
	 * Review Workflow configuration template class
	 */
	class DDWCMPA_Review_Workflow_Configuration_Template {
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
		 * Render the review workflow configuration.
		 *
		 * @return void
		 */
		public function ddwcmpa_render_configuration() {
			$order_status_options = [ '' => esc_html__( 'Leave the order unchanged', 'devdiggers-prescription-for-woocommerce' ) ] + wc_get_order_statuses();

			$args = [
				[
					'header' => [
						'heading'     => esc_html__( 'Order Status Automation', 'devdiggers-prescription-for-woocommerce' ),
						'description' => esc_html__( 'Control what happens to an order while a pharmacist is reviewing the prescription, and once a decision is made.', 'devdiggers-prescription-for-woocommerce' ),
					],
					'fields' => [
						[
							'type'        => 'select',
							'label'       => esc_html__( 'While Under Review', 'devdiggers-prescription-for-woocommerce' ),
							'description' => esc_html__( 'Park the order in this status until a decision is made, so it is never fulfilled before approval. The order stays here even when Cash on Delivery, bank transfer or a card payment would normally move it to Processing.', 'devdiggers-prescription-for-woocommerce' ),
							'id'          => 'ddwcmpa-hold-order-status',
							'name'        => '_ddwcmpa_hold_order_status',
							'value'       => $this->ddwcmpa_configuration['hold_order_status'],
							'options'     => $order_status_options,
						],
						[
							'type'        => 'select',
							'label'       => esc_html__( 'On Approval', 'devdiggers-prescription-for-woocommerce' ),
							'description' => esc_html__( 'Move the order to this status as soon as the prescription is approved.', 'devdiggers-prescription-for-woocommerce' ),
							'id'          => 'ddwcmpa-approved-order-status',
							'name'        => '_ddwcmpa_approved_order_status',
							'value'       => $this->ddwcmpa_configuration['approved_order_status'],
							'options'     => $order_status_options,
						],
						[
							'type'        => 'select',
							'label'       => esc_html__( 'On Rejection', 'devdiggers-prescription-for-woocommerce' ),
							'description' => esc_html__( 'Move the order to this status when the prescription is rejected.', 'devdiggers-prescription-for-woocommerce' ),
							'id'          => 'ddwcmpa-rejected-order-status',
							'name'        => '_ddwcmpa_rejected_order_status',
							'value'       => $this->ddwcmpa_configuration['rejected_order_status'],
							'options'     => $order_status_options,
						],
					],
				],
				[
					'header' => [
						'heading'     => esc_html__( 'Decisions', 'devdiggers-prescription-for-woocommerce' ),
						'description' => esc_html__( 'Decide what a reviewer has to supply, and what is recorded about each decision.', 'devdiggers-prescription-for-woocommerce' ),
					],
					'fields' => [
						[
							'type'           => 'checkbox',
							'label'          => esc_html__( 'Rejection Reason', 'devdiggers-prescription-for-woocommerce' ),
							'checkbox_label' => esc_html__( 'Require a Reason Before a Prescription Can Be Rejected', 'devdiggers-prescription-for-woocommerce' ),
							'description'    => esc_html__( 'The reason is sent to the customer and kept on the order, so nobody is refused without being told why.', 'devdiggers-prescription-for-woocommerce' ),
							'id'             => 'ddwcmpa-rejection-reason-required',
							'name'           => '_ddwcmpa_rejection_reason_required',
							'value'          => $this->ddwcmpa_configuration['rejection_reason_required'],
						],
						ddfw_locked_field(
							[
								'type'           => 'checkbox',
								'label'          => esc_html__( 'Audit Trail', 'devdiggers-prescription-for-woocommerce' ),
								'checkbox_label' => esc_html__( 'Record Every Review Decision', 'devdiggers-prescription-for-woocommerce' ),
								'description'    => esc_html__( 'Who decided, what they decided and when, kept as a structured trail on the order and shown in the review screen, beyond the plain order note.', 'devdiggers-prescription-for-woocommerce' ),
								'id'             => 'ddwcmpa-audit-log-enabled',
								'value'          => '',
							],
							'ddwcmpa'
						),
						[
							'type'           => 'checkbox',
							'label'          => esc_html__( 'Order Notes', 'devdiggers-prescription-for-woocommerce' ),
							'checkbox_label' => esc_html__( 'Write Each Decision to the Order Notes', 'devdiggers-prescription-for-woocommerce' ),
							'description'    => esc_html__( 'Puts the decision where the rest of your team already looks, in the order timeline in WooCommerce.', 'devdiggers-prescription-for-woocommerce' ),
							'id'             => 'ddwcmpa-order-notes-enabled',
							'name'           => '_ddwcmpa_order_notes_enabled',
							'value'          => $this->ddwcmpa_configuration['order_notes_enabled'],
						],
						ddfw_locked_field(
							[
								'type'           => 'checkbox',
								'label'          => esc_html__( 'Duplicate Warning', 'devdiggers-prescription-for-woocommerce' ),
								'checkbox_label' => esc_html__( 'Warn When the Same File Has Been Uploaded Before', 'devdiggers-prescription-for-woocommerce' ),
								'description'    => esc_html__( 'Compares the uploaded file against every prescription already on the site and flags a match in the review screen. It only warns, it never blocks an order.', 'devdiggers-prescription-for-woocommerce' ),
								'id'             => 'ddwcmpa-duplicate-check-enabled',
								'value'          => '',
							],
							'ddwcmpa'
						),
					],
				],
				[
					'header'            => [
						'heading'     => esc_html__( 'Assignment and Service Level', 'devdiggers-prescription-for-woocommerce' ),
						'description' => esc_html__( 'Share the queue out between reviewers and make it obvious when a prescription has been waiting too long.', 'devdiggers-prescription-for-woocommerce' ),
					],
					'after_header_html' => ddfw_get_upgrade_to_pro_section(
						[
							'heading'       => esc_html__( 'Run the queue as a team, in Pro', 'devdiggers-prescription-for-woocommerce' ),
							'description'   => esc_html__( 'When more than one pharmacist reviews, Pro makes sure every prescription has an owner and nothing waits past your target.', 'devdiggers-prescription-for-woocommerce' ),
							'list_features' => [
								esc_html__( 'Assign a prescription to a named reviewer, with an Assigned to me view above the queue', 'devdiggers-prescription-for-woocommerce' ),
								esc_html__( 'A review target in hours, with an overdue badge on anything that has waited too long', 'devdiggers-prescription-for-woocommerce' ),
								esc_html__( 'Missed targets and reviewer activity counted on the dashboard', 'devdiggers-prescription-for-woocommerce' ),
								esc_html__( 'A Pharmacist role that reviews prescriptions without shop manager access to the rest of your store', 'devdiggers-prescription-for-woocommerce' ),
							],
							'upgrade_url'   => 'https://devdiggers.com/product/woocommerce-medical-prescription-attachment/',
						]
					),
				],
				[
					'header'            => [
						'heading'     => esc_html__( 'Saved Replies', 'devdiggers-prescription-for-woocommerce' ),
						'description' => esc_html__( 'The handful of sentences your pharmacists write over and over.', 'devdiggers-prescription-for-woocommerce' ),
					],
					'after_header_html' => ddfw_get_upgrade_to_pro_section(
						[
							'heading'       => esc_html__( 'Stop retyping the same refusal, in Pro', 'devdiggers-prescription-for-woocommerce' ),
							'description'   => esc_html__( 'Blurry scan, missing signature, page cut off. Pro turns the messages you send every day into one click replies.', 'devdiggers-prescription-for-woocommerce' ),
							'list_features' => [
								esc_html__( 'Your own list of replies, shown as one click chips above the message box', 'devdiggers-prescription-for-woocommerce' ),
								esc_html__( 'A sensible built in set, ready from the first review', 'devdiggers-prescription-for-woocommerce' ),
								esc_html__( 'Still editable after picking one, so every message can be personal', 'devdiggers-prescription-for-woocommerce' ),
							],
							'upgrade_url'   => 'https://devdiggers.com/product/woocommerce-medical-prescription-attachment/',
						]
					),
				],
				[
					'header'            => [
						'heading'     => esc_html__( 'Prescriber Verification', 'devdiggers-prescription-for-woocommerce' ),
						'description' => esc_html__( 'When a prescription needs confirming, a reviewer can hand the prescriber a private link instead of chasing the surgery by phone.', 'devdiggers-prescription-for-woocommerce' ),
					],
					'after_header_html' => ddfw_get_upgrade_to_pro_section(
						[
							'heading'       => esc_html__( 'Let the doctor confirm it, in Pro', 'devdiggers-prescription-for-woocommerce' ),
							'description'   => esc_html__( 'Pro creates a private, expiring link that opens one prescription. The prescriber confirms or denies it without an account, and the answer lands on the order.', 'devdiggers-prescription-for-woocommerce' ),
							'list_features' => [
								esc_html__( 'One link per prescription, valid for seven days and spent the moment it is answered', 'devdiggers-prescription-for-woocommerce' ),
								esc_html__( 'The prescriber sees only that order\'s files, nothing else in your store', 'devdiggers-prescription-for-woocommerce' ),
								esc_html__( 'Their answer and note are written to the order for your records', 'devdiggers-prescription-for-woocommerce' ),
							],
							'upgrade_url'   => 'https://devdiggers.com/product/woocommerce-medical-prescription-attachment/',
						]
					),
				],
			];

			$layout = new DDFW_Layout();
			$layout->get_form_section_layout( $args, 'ddwcmpa-review-workflow-configuration-fields' );
		}
	}
}
