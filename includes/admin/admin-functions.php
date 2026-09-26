<?php
/**
 * This file contains all callback functions for admin-side actions and hooks.
 *
 * @author DevDiggers
 * @version 1.0.0
 * @package DevDiggers Prescription for WooCommerce
 */

namespace DDWCMedicalPrescriptionAttachment\Includes\Admin;

use DDWCMedicalPrescriptionAttachment\Helper\DDWCMPA_Prescription_Helper;
use DDWCMedicalPrescriptionAttachment\Helper\DDWCMPA_Security_Helper;
use DDWCMedicalPrescriptionAttachment\Helper\DDWCMPA_Icon_Helper;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCMPA_Admin_Functions' ) ) {
	/**
	 * Admin Functions Class
	 */
	class DDWCMPA_Admin_Functions {
		/**
		 * Configuration Variable
		 *
		 * @var array
		 */
		protected $ddwcmpa_configuration;

		/**
		 * Prescription helper.
		 *
		 * @var DDWCMPA_Prescription_Helper
		 */
		protected $prescription_helper;

		/**
		 * Construct
		 *
		 * @param array $ddwcmpa_configuration Plugin configuration.
		 */
		public function __construct( $ddwcmpa_configuration ) {
			$this->ddwcmpa_configuration = $ddwcmpa_configuration;
			$this->prescription_helper   = new DDWCMPA_Prescription_Helper( $ddwcmpa_configuration );
		}

		/**
		 * Register settings function
		 *
		 * @return void
		 */
		public function ddwcmpa_register_settings() {
			// One group per configuration tab, so saving a tab never wipes the fields
			// that live on the other tabs.
			register_setting(
				'ddwcmpa-general-configuration-fields',
				'_ddwcmpa_enabled',
				[
					'type'              => 'string',
					'sanitize_callback' => [ $this, 'ddwcmpa_sanitize_yes_no' ],
				]
			);
			register_setting(
				'ddwcmpa-general-configuration-fields',
				'_ddwcmpa_allowed_categories',
				[
					'type'              => 'array',
					'sanitize_callback' => [ $this, 'ddwcmpa_sanitize_id_list' ],
				]
			);
			register_setting(
				'ddwcmpa-general-configuration-fields',
				'_ddwcmpa_excluded_products',
				[
					'type'              => 'array',
					'sanitize_callback' => [ $this, 'ddwcmpa_sanitize_id_list' ],
				]
			);
			register_setting(
				'ddwcmpa-design-configuration-fields',
				'_ddwcmpa_product_label',
				[
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				]
			);
			register_setting(
				'ddwcmpa-design-configuration-fields',
				'_ddwcmpa_product_label_font_color',
				[
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_hex_color',
				]
			);
			register_setting(
				'ddwcmpa-design-configuration-fields',
				'_ddwcmpa_product_label_background_color',
				[
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_hex_color',
				]
			);
			register_setting(
				'ddwcmpa-design-configuration-fields',
				'_ddwcmpa_prescription_description',
				[
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_textarea_field',
				]
			);
			register_setting(
				'ddwcmpa-design-configuration-fields',
				'_ddwcmpa_product_page_position',
				[
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_key',
				]
			);
			register_setting(
				'ddwcmpa-design-configuration-fields',
				'_ddwcmpa_shop_page_position',
				[
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_key',
				]
			);
			register_setting(
				'ddwcmpa-design-configuration-fields',
				'_ddwcmpa_cart_page_position',
				[
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_key',
				]
			);
			register_setting(
				'ddwcmpa-design-configuration-fields',
				'_ddwcmpa_checkout_page_position',
				[
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_key',
				]
			);
			register_setting(
				'ddwcmpa-design-configuration-fields',
				'_ddwcmpa_order_page_position',
				[
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_key',
				]
			);
			register_setting(
				'ddwcmpa-prescription-rules-configuration-fields',
				'_ddwcmpa_max_files',
				[
					'type'              => 'integer',
					'sanitize_callback' => 'absint',
				]
			);
			register_setting(
				'ddwcmpa-review-workflow-configuration-fields',
				'_ddwcmpa_hold_order_status',
				[
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_key',
				]
			);
			register_setting(
				'ddwcmpa-review-workflow-configuration-fields',
				'_ddwcmpa_approved_order_status',
				[
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_key',
				]
			);
			register_setting(
				'ddwcmpa-review-workflow-configuration-fields',
				'_ddwcmpa_rejected_order_status',
				[
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_key',
				]
			);
			register_setting(
				'ddwcmpa-review-workflow-configuration-fields',
				'_ddwcmpa_rejection_reason_required',
				[
					'type'              => 'string',
					'sanitize_callback' => [ $this, 'ddwcmpa_sanitize_yes_no' ],
				]
			);
			register_setting(
				'ddwcmpa-review-workflow-configuration-fields',
				'_ddwcmpa_order_notes_enabled',
				[
					'type'              => 'string',
					'sanitize_callback' => [ $this, 'ddwcmpa_sanitize_yes_no' ],
				]
			);
			register_setting(
				'ddwcmpa-customer-experience-configuration-fields',
				'_ddwcmpa_attach_later_enabled',
				[
					'type'              => 'string',
					'sanitize_callback' => [ $this, 'ddwcmpa_sanitize_yes_no' ],
				]
			);
			register_setting(
				'ddwcmpa-customer-experience-configuration-fields',
				'_ddwcmpa_self_service_enabled',
				[
					'type'              => 'string',
					'sanitize_callback' => [ $this, 'ddwcmpa_sanitize_yes_no' ],
				]
			);
			register_setting(
				'ddwcmpa-emails-configuration-fields',
				'_ddwcmpa_admin_email_enabled',
				[
					'type'              => 'string',
					'sanitize_callback' => [ $this, 'ddwcmpa_sanitize_yes_no' ],
				]
			);
			register_setting(
				'ddwcmpa-emails-configuration-fields',
				'_ddwcmpa_customer_email_enabled',
				[
					'type'              => 'string',
					'sanitize_callback' => [ $this, 'ddwcmpa_sanitize_yes_no' ],
				]
			);
		}

		/**
		 * Sanitize an on/off switch.
		 *
		 * @param mixed $value Submitted value.
		 * @return string
		 */
		public function ddwcmpa_sanitize_yes_no( $value ) {
			return 'yes' === $value ? 'yes' : '';
		}

		/**
		 * Sanitize a multi select of term or post IDs.
		 *
		 * @param mixed $value Submitted value.
		 * @return array
		 */
		public function ddwcmpa_sanitize_id_list( $value ) {
			// Kept as strings: the category rule compares against strval() term IDs.
			return array_values( array_filter( array_map( 'strval', array_map( 'absint', (array) $value ) ) ) );
		}

		/**
		 * Add the per product prescription controls to the product data panel.
		 *
		 * @return void
		 */
		public function ddwcmpa_add_product_fields() {
			global $post;

			$product = wc_get_product( $post->ID );

			if ( ! $product ) {
				return;
			}
			?>
			<div class="options_group ddwcmpa-product-options">
				<?php
				woocommerce_wp_select(
					[
						'id'          => '_ddwcmpa_requires_prescription',
						'label'       => esc_html__( 'Requires prescription', 'devdiggers-prescription-for-woocommerce' ),
						'description' => esc_html__( 'Overrides the category rules for this product. Leave on "Use category rules" to keep the global behaviour.', 'devdiggers-prescription-for-woocommerce' ),
						'desc_tip'    => true,
						'value'       => $product->get_meta( '_ddwcmpa_requires_prescription', true ),
						'options'     => [
							''    => esc_html__( 'Use category rules', 'devdiggers-prescription-for-woocommerce' ),
							'yes' => esc_html__( 'Always require a prescription', 'devdiggers-prescription-for-woocommerce' ),
							'no'  => esc_html__( 'Never require a prescription', 'devdiggers-prescription-for-woocommerce' ),
						],
					]
				);

				// Shown so the option can be found, never saved: nothing in Free reads it.
				woocommerce_wp_text_input(
					[
						'id'                => 'ddwcmpa-max-quantity-locked',
						'name'              => '',
						'label'             => esc_html__( 'Maximum quantity per order (Pro)', 'devdiggers-prescription-for-woocommerce' ),
						'description'       => esc_html__( 'Pro caps how much of a controlled product one order may contain.', 'devdiggers-prescription-for-woocommerce' ),
						'desc_tip'          => true,
						'type'              => 'number',
						'value'             => '',
						'custom_attributes' => [
							'disabled' => 'disabled',
						],
					]
				);
				?>
			</div>
			<?php
		}

		/**
		 * Save the per product prescription controls.
		 *
		 * Variations inherit from the parent.
		 *
		 * @param int $product_id Product ID.
		 * @return void
		 */
		public function ddwcmpa_save_product_fields( $product_id ) {
			if ( ! current_user_can( 'edit_product', $product_id ) ) {
				return;
			}

			if ( ! isset( $_POST['woocommerce_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce_meta_nonce'] ) ), 'woocommerce_save_data' ) ) {
				return;
			}

			$requires = isset( $_POST['_ddwcmpa_requires_prescription'] ) ? sanitize_key( wp_unslash( $_POST['_ddwcmpa_requires_prescription'] ) ) : '';

			$product = wc_get_product( $product_id );

			if ( ! $product ) {
				return;
			}

			$product->update_meta_data( '_ddwcmpa_requires_prescription', in_array( $requires, [ 'yes', 'no' ], true ) ? $requires : '' );
			$product->save();
		}

		/**
		 * Add a prescription column to the WooCommerce orders screen.
		 *
		 * @param array $columns Existing columns.
		 * @return array
		 */
		public function ddwcmpa_add_order_list_column( $columns ) {
			$new_columns = [];

			foreach ( $columns as $key => $label ) {
				$new_columns[ $key ] = $label;

				if ( 'order_status' === $key ) {
					$new_columns['ddwcmpa_status'] = esc_html__( 'Prescription', 'devdiggers-prescription-for-woocommerce' );
				}
			}

			if ( ! isset( $new_columns['ddwcmpa_status'] ) ) {
				$new_columns['ddwcmpa_status'] = esc_html__( 'Prescription', 'devdiggers-prescription-for-woocommerce' );
			}

			return $new_columns;
		}

		/**
		 * Render the prescription column on the WooCommerce orders screen.
		 *
		 * @param string $column            Column key.
		 * @param mixed  $post_or_order_id  Post ID or order object depending on storage mode.
		 * @return void
		 */
		public function ddwcmpa_render_order_list_column( $column, $post_or_order_id ) {
			if ( 'ddwcmpa_status' !== $column ) {
				return;
			}

			$order = is_a( $post_or_order_id, 'WC_Order' ) ? $post_or_order_id : wc_get_order( $post_or_order_id );

			if ( ! $order ) {
				return;
			}

			$status = $order->get_meta( '_ddwcmpa_status', true );

			if ( empty( $status ) ) {
				echo '<span class="ddwcmpa-muted">&ndash;</span>';
				return;
			}

			$statuses = $this->prescription_helper->ddwcmpa_get_statuses();
			$label    = isset( $statuses[ $status ] ) ? $statuses[ $status ] : $status;

			printf(
				'<span class="ddwcmpa-status ddwcmpa-status-%1$s">%2$s</span>',
				esc_attr( $status ),
				esc_html( $label )
			);
		}

		/**
		 * Add custom meta box function
		 *
		 * @param string $screen_id Current screen ID.
		 * @return void
		 */
		public function ddwcmpa_add_custom_meta_box( $screen_id ) {
			if ( 'woocommerce_page_wc-orders' === $screen_id || 'shop_order' === $screen_id ) {
				add_meta_box( 'ddwcmpa-meta-box', esc_html__( 'Medical Prescription Review', 'devdiggers-prescription-for-woocommerce' ), [ $this, 'ddwcmpa_add_content_in_meta_box' ], $screen_id, 'normal', 'high' );
			}
		}

		/**
		 * Add content in meta box function
		 *
		 * @param mixed $post_or_order_object Post or order object.
		 * @return void
		 */
		public function ddwcmpa_add_content_in_meta_box( $post_or_order_object ) {
			if ( $post_or_order_object instanceof \WP_Post ) {
				$order = wc_get_order( $post_or_order_object->ID );
			} elseif ( is_a( $post_or_order_object, 'WC_Order' ) ) {
				$order = $post_or_order_object;
			} else {
				global $post;

				$order = ( $post && $post->ID ) ? wc_get_order( $post->ID ) : false;
			}

			if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
				echo '<p>' . esc_html__( 'Order not found.', 'devdiggers-prescription-for-woocommerce' ) . '</p>';
				return;
			}

			$order_id = $order->get_id();

			if ( ! $this->prescription_helper->ddwcmpa_check_prescription_exists( '', $order_id ) ) {
				return;
			}

			$attachments  = array_filter( DDWCMPA_Prescription_Helper::ddwcmpa_get_array_meta( $order, '_ddwcmpa_attachments' ) );
			$status       = $order->get_meta( '_ddwcmpa_status', true );
			$reason       = $order->get_meta( '_ddwcmpa_rejection_reason', true );
			$all_statuses = $this->prescription_helper->ddwcmpa_get_statuses();
			$can_review   = current_user_can( DDWCMPA_Security_Helper::MANAGE_CAP );
			?>
			<div class="ddwcmpa-prescription-attachment-container ddwcmpa-review-panel">
				<input type="hidden" name="ddwcmpa_order_id" value="<?php echo esc_attr( $order_id ); ?>" />

				<div class="ddwcmpa-review-topbar">
					<span class="ddwcmpa-status ddwcmpa-status-<?php echo esc_attr( $status ); ?>"><?php echo esc_html( isset( $all_statuses[ $status ] ) ? $all_statuses[ $status ] : $status ); ?></span>

				</div>

				<div class="ddwcmpa-review-columns">
					<section class="ddwcmpa-review-panel-card">
						<h4 class="ddwcmpa-review-panel-title">
							<?php DDWCMPA_Icon_Helper::render( 'paperclip', [ 'size' => 15 ] ); ?>
							<?php
							printf(
								/* translators: %d: number of uploaded files */
								esc_html( _n( 'Uploaded File (%d)', 'Uploaded Files (%d)', count( $attachments ), 'devdiggers-prescription-for-woocommerce' ) ),
								count( $attachments )
							);
							?>
						</h4>

						<?php if ( empty( $attachments ) ) : ?>
							<p class="ddwcmpa-review-note"><?php esc_html_e( 'The customer has not uploaded a prescription yet.', 'devdiggers-prescription-for-woocommerce' ); ?></p>
						<?php else : ?>
							<div class="ddwcmpa-prescription-attachment-box">
								<?php
								foreach ( $attachments as $attachment_id ) {
									$this->prescription_helper->ddwcmpa_render_attachment( $attachment_id, $order_id, false );
								}
								?>
							</div>
						<?php endif; ?>
					</section>

					<section class="ddwcmpa-review-panel-card ddwcmpa-review-decide">
						<h4 class="ddwcmpa-review-panel-title">
							<?php DDWCMPA_Icon_Helper::render( 'check', [ 'size' => 15 ] ); ?>
							<?php esc_html_e( 'Decision', 'devdiggers-prescription-for-woocommerce' ); ?>
						</h4>

						<?php if ( $can_review ) : ?>
							<div class="ddwcmpa-review-controls">
								<label class="ddwcmpa-field ddwcmpa-status-select-label">
									<span><?php esc_html_e( 'Review decision', 'devdiggers-prescription-for-woocommerce' ); ?></span>
									<select name="ddwcmpa_status" id="ddwcmpa-status-select">
										<?php foreach ( $all_statuses as $key => $value ) : ?>
											<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $status ); ?>><?php echo esc_html( $value ); ?></option>
										<?php endforeach; ?>
									</select>
								</label>

								<label class="ddwcmpa-field ddwcmpa-reason-label <?php echo esc_attr( in_array( $status, [ 'rejected', 'info_required' ], true ) ? '' : 'ddwcmpa-hide' ); ?>">
									<span><?php esc_html_e( 'Message to the customer', 'devdiggers-prescription-for-woocommerce' ); ?></span>
									<textarea name="ddwcmpa_rejection_reason" id="ddwcmpa-rejection-reason" rows="3" placeholder="<?php esc_attr_e( 'e.g. the prescription is unreadable, or the prescriber details are missing.', 'devdiggers-prescription-for-woocommerce' ); ?>"><?php echo esc_textarea( $reason ); ?></textarea>
								</label>
							</div>

							<p class="ddwcmpa-review-hint">
								<?php DDWCMPA_Icon_Helper::render( 'info', [ 'size' => 14 ] ); ?>
								<span><?php esc_html_e( 'The decision is saved when you update the order, and the customer is emailed straight away.', 'devdiggers-prescription-for-woocommerce' ); ?></span>
							</p>
						<?php else : ?>
							<p class="ddwcmpa-review-note"><?php esc_html_e( 'You do not have permission to review prescriptions.', 'devdiggers-prescription-for-woocommerce' ); ?></p>
						<?php endif; ?>
					</section>
				</div>
			</div>
			<?php
		}

		/**
		 * Handle save shop order meta function
		 *
		 * @return void
		 */
		public function ddwcmpa_handle_save_shop_order_meta() {
			if ( ! isset( $_POST['woocommerce_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce_meta_nonce'] ) ), 'woocommerce_save_data' ) ) {
				return;
			}

			if ( empty( $_POST['ddwcmpa_order_id'] ) || empty( $_POST['ddwcmpa_status'] ) ) {
				return;
			}

			if ( ! current_user_can( DDWCMPA_Security_Helper::MANAGE_CAP ) ) {
				return;
			}

			$order_id = intval( wp_unslash( $_POST['ddwcmpa_order_id'] ) );
			$status = sanitize_key( wp_unslash( $_POST['ddwcmpa_status'] ) );
			$reason = ! empty( $_POST['ddwcmpa_rejection_reason'] ) ? sanitize_textarea_field( wp_unslash( $_POST['ddwcmpa_rejection_reason'] ) ) : '';

			if ( 'rejected' === $status && empty( $reason ) && ! empty( $this->ddwcmpa_configuration['rejection_reason_required'] ) ) {
				set_transient( 'ddwcmpa_admin_error_' . get_current_user_id(), esc_html__( 'A rejection reason is required, so the prescription status was left unchanged.', 'devdiggers-prescription-for-woocommerce' ), 60 );
				return;
			}

			$this->prescription_helper->ddwcmpa_set_status( $order_id, $status, [ 'reason' => $reason ] );
		}

		/**
		 * Show an error saved during an order save, once.
		 *
		 * @return void
		 */
		public function ddwcmpa_render_deferred_notice() {
			$key     = 'ddwcmpa_admin_error_' . get_current_user_id();
			$message = get_transient( $key );

			if ( empty( $message ) ) {
				return;
			}

			delete_transient( $key );
			?>
			<div class="notice notice-error is-dismissible"><p><?php echo esc_html( $message ); ?></p></div>
			<?php
		}
	}
}
