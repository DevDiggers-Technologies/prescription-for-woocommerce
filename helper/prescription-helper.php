<?php
/**
 * Prescription helper class
 *
 * @package Prescription for WooCommerce
 * @version 1.0.0
 */

namespace DDWCMedicalPrescriptionAttachment\Helper;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCMPA_Prescription_Helper' ) ) {
	/**
	 * Prescription helper class
	 */
	class DDWCMPA_Prescription_Helper {
		/**
		 * Configuration variable
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
		}

		/**
		 * Check prescription exists function
		 *
		 * @param string     $product_id Product or variation ID.
		 * @param int|string $order_id   Order ID.
		 * @return boolean
		 */
		public function ddwcmpa_check_prescription_exists( $product_id = '', $order_id = '' ) {
			$prescription_exists = false;

			if ( ! empty( $product_id ) ) {
				// A per product rule always wins over the category rules.
				$override = $this->ddwcmpa_get_product_requirement( $product_id );

				if ( 'yes' === $override ) {
					return true;
				}

				if ( 'no' === $override ) {
					return false;
				}

				if ( in_array( $product_id, $this->ddwcmpa_configuration['excluded_products'], true ) ) {
					$prescription_exists = false;
				} elseif ( empty( $this->ddwcmpa_configuration['allowed_categories'] ) ) {
						$prescription_exists = true;
				} else {
					$product = wc_get_product( $product_id );

					if ( ! $product ) {
						return false;
					}

					if ( 'variation' === $product->get_type() ) {
						$product_cats = wp_get_post_terms( $product->get_parent_id(), 'product_cat' );
					} else {
						$product_cats = wp_get_post_terms( $product->get_id(), 'product_cat' );
					}

					if ( ! empty( $product_cats ) ) {
						foreach ( $product_cats as $product_cat ) {
							if ( in_array( strval( $product_cat->term_id ), $this->ddwcmpa_configuration['allowed_categories'], true ) ) {
								$prescription_exists = true;
								break;
							}
						}
					}
				}
			} elseif ( ! empty( $order_id ) ) {
				$order = wc_get_order( $order_id );

				if ( ! $order ) {
					return false;
				}

				foreach ( $order->get_items() as $key => $order_item ) {
					$order_data = $order_item->get_data();
					$product_id = strval( ! empty( $order_data['variation_id'] ) ? $order_data['variation_id'] : $order_data['product_id'] );

					if ( $this->ddwcmpa_check_prescription_exists( $product_id ) ) {
						$prescription_exists = true;
						break;
					}
				}
			} elseif ( ! empty( WC()->cart ) ) {
				foreach ( WC()->cart->get_cart() as $cart_item ) {
					$product_id = strval( ! empty( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : $cart_item['product_id'] );

					if ( $this->ddwcmpa_check_prescription_exists( $product_id ) ) {
						$prescription_exists = true;
						break;
					}
				}
			}

			return $prescription_exists;
		}

		/**
		 * Read the per product prescription rule.
		 *
		 * Variations inherit from their parent unless they set their own rule.
		 *
		 * @param int|string $product_id Product or variation ID.
		 * @return string One of 'yes', 'no' or '' for inherit.
		 */
		public function ddwcmpa_get_product_requirement( $product_id ) {
			$product = wc_get_product( $product_id );

			if ( ! $product ) {
				return '';
			}

			$requirement = $product->get_meta( '_ddwcmpa_requires_prescription', true );

			if ( empty( $requirement ) && $product->get_parent_id() ) {
				$parent = wc_get_product( $product->get_parent_id() );

				if ( $parent ) {
					$requirement = $parent->get_meta( '_ddwcmpa_requires_prescription', true );
				}
			}

			return in_array( $requirement, [ 'yes', 'no' ], true ) ? $requirement : '';
		}

		/**
		 * Validate prescription order function
		 *
		 * @return boolean
		 */
		public function ddwcmpa_validate_prescription_order() {
			return '' === $this->ddwcmpa_get_checkout_error();
		}

		/**
		 * Why this cart cannot be checked out yet, in words the customer can act on.
		 *
		 * One place decides, so the Place Order button, the classic checkout, the block
		 * checkout and the order write all say exactly the same thing.
		 *
		 * @return string Empty when nothing is blocking checkout.
		 */
		public function ddwcmpa_get_checkout_error() {
			$attachment_when = WC()->session->get( 'ddwcmpa_attachment_when' );
			$attachments     = WC()->session->get( 'ddwcmpa_attachments' );
			$attach_now      = empty( $attachment_when ) || 'now' === $attachment_when;

			if ( 'later' === $attachment_when && ! is_user_logged_in() ) {
				return esc_html__( 'You need to log in before you can attach the medical prescription later.', 'prescription-for-woocommerce' );
			}

			if ( $attach_now && empty( $attachments ) ) {
				return esc_html__( 'Kindly upload the medical prescription first in order to place an order.', 'prescription-for-woocommerce' );
			}

			return '';
		}

		/**
		 * Change a prescription status, once, through a single path.
		 *
		 * Every caller (meta box, customer submit) routes through here
		 * so the order note, order status sync and customer email can never drift
		 * apart.
		 *
		 * @param \WC_Order|int $order  Order or order ID.
		 * @param string        $status New prescription status.
		 * @param array         $args   Optional: 'reason', 'notify'.
		 * @return bool True when the status actually changed.
		 */
		public function ddwcmpa_set_status( $order, $status, $args = [] ) {
			$order = is_a( $order, 'WC_Order' ) ? $order : wc_get_order( $order );

			if ( ! $order ) {
				return false;
			}

			$statuses = $this->ddwcmpa_get_statuses();

			if ( ! isset( $statuses[ $status ] ) ) {
				return false;
			}

			$args = wp_parse_args(
				$args,
				[
					'reason' => '',
					'notify' => true,
				]
			);

			$old_status = $order->get_meta( '_ddwcmpa_status', true );

			if ( $old_status === $status ) {
				return false;
			}

			$order->update_meta_data( '_ddwcmpa_status', $status );
			$order->update_meta_data( '_ddwcmpa_status_updated', current_time( 'mysql' ) );

			if ( in_array( $status, [ 'rejected', 'info_required' ], true ) ) {
				// One field holds whatever the reviewer wants the customer to read,
				// whether that is a refusal or a request for a clearer scan.
				$order->update_meta_data( '_ddwcmpa_rejection_reason', sanitize_textarea_field( $args['reason'] ) );
			} elseif ( 'approved' === $status ) {
				$order->delete_meta_data( '_ddwcmpa_rejection_reason' );
				$order->update_meta_data( '_ddwcmpa_approved_date', current_time( 'mysql' ) );
			}

			if ( ! empty( $this->ddwcmpa_configuration['order_notes_enabled'] ) ) {
				$order->add_order_note(
					sprintf(
						/* translators: 1: previous prescription status, 2: new prescription status */
						esc_html__( 'Prescription status is changed from %1$s to %2$s.', 'prescription-for-woocommerce' ),
						! empty( $statuses[ $old_status ] ) ? $statuses[ $old_status ] : esc_html__( 'None', 'prescription-for-woocommerce' ),
						$statuses[ $status ]
					) . ( ! empty( $args['reason'] ) ? ' ' . sprintf( /* translators: %s: reason text */ esc_html__( 'Reason: %s', 'prescription-for-woocommerce' ), $args['reason'] ) : '' )
				);
			}

			$order->save();

			$this->ddwcmpa_sync_order_status( $order, $status );

			if ( $args['notify'] ) {
				$this->ddwcmpa_notify_customer( $order, $status, $args['reason'] );
			}

			do_action( 'ddwcmpa_prescription_status_changed', $order, $status, $old_status, $args );

			return true;
		}

		/**
		 * Read an array shaped order meta value safely.
		 *
		 * Casting the meta directly is not safe: WooCommerce returns an empty string
		 * when the key has never been written, and (array) '' is [ '' ], not [].
		 *
		 * @param \WC_Order $order Order object.
		 * @param string    $key   Meta key.
		 * @return array
		 */
		public static function ddwcmpa_get_array_meta( $order, $key ) {
			$value = $order->get_meta( $key, true );

			return is_array( $value ) ? $value : [];
		}

		/**
		 * Move the WooCommerce order status to match the prescription decision.
		 *
		 * @param \WC_Order $order  Order object.
		 * @param string    $status Prescription status.
		 * @return void
		 */
		protected function ddwcmpa_sync_order_status( $order, $status ) {
			$map = [
				'approved'      => $this->ddwcmpa_configuration['approved_order_status'],
				'rejected'      => $this->ddwcmpa_configuration['rejected_order_status'],
				// Asking for more information is not a decision, so the order goes back
				// to wherever the store parks work in progress.
				'info_required' => $this->ddwcmpa_configuration['hold_order_status'],
			];

			if ( empty( $map[ $status ] ) ) {
				return;
			}

			$target = $map[ $status ];

			if ( 'wc-' . $order->get_status() === $target || $order->get_status() === $target ) {
				return;
			}

			$order->update_status(
				$target,
				sprintf(
					/* translators: %s: prescription status label */
					esc_html__( 'Prescription %s.', 'prescription-for-woocommerce' ),
					$status
				)
			);
		}

		/**
		 * Email the customer about a prescription decision.
		 *
		 * @param \WC_Order $order  Order object.
		 * @param string    $status Prescription status.
		 * @param string    $reason Rejection reason, when there is one.
		 * @return void
		 */
		protected function ddwcmpa_notify_customer( $order, $status, $reason = '' ) {
			if ( empty( $this->ddwcmpa_configuration['customer_email_enabled'] ) ) {
				return;
			}

			$statuses = $this->ddwcmpa_get_statuses();

			// The configured wording is the whole body. The reviewer's reason and the
			// re-upload invitation travel as placeholders instead of extra
			// paragraphs, so they land where the merchant put them in the message.
			$email_message = array_filter( [ $this->ddwcmpa_get_email_intro( $status ) ] );

			$reason_note = '';

			if ( ! empty( $reason ) ) {
				$reason_note = sprintf(
					/* translators: %s: reason given by the reviewer */
					esc_html__( 'Reason: %s', 'prescription-for-woocommerce' ),
					'<em>' . esc_html( $reason ) . '</em>'
				);
			}

			$reupload_note = '';

			if ( in_array( $status, [ 'rejected', 'info_required' ], true ) && ! empty( $this->ddwcmpa_configuration['reupload_enabled'] ) ) {
				$reupload_note = esc_html__( 'You can upload a replacement prescription from your order page.', 'prescription-for-woocommerce' );
			}

			// The label, not the key: it fills the Status row in the template's order
			// box and the {status} placeholder a merchant can use in their wording.
			$label = isset( $statuses[ $status ] ) ? $statuses[ $status ] : $status;

			do_action(
				'ddwcmpa_mail',
				[
					'email'         => $order->get_billing_email(),
					'message'       => $email_message,
					'order_id'      => $order->get_id(),
					'subject'       => $this->ddwcmpa_get_email_subject( $status ),
					'heading'       => $this->ddwcmpa_get_email_heading( $status ),
					'customer_data' => [
						'status'        => $label,
						'reason'        => $reason_note,
						'reupload_note' => $reupload_note,
					],
				]
			);
		}

		/**
		 * The subject line configured for one prescription status.
		 *
		 * @param string $status Prescription status.
		 * @return string
		 */
		public function ddwcmpa_get_email_subject( $status ) {
			return $this->ddwcmpa_get_email_part( 'subject', $status );
		}

		/**
		 * The heading configured for one prescription status.
		 *
		 * @param string $status Prescription status.
		 * @return string
		 */
		public function ddwcmpa_get_email_heading( $status ) {
			return $this->ddwcmpa_get_email_part( 'heading', $status );
		}

		/**
		 * The email body for one prescription status.
		 *
		 * @param string $status Prescription status.
		 * @return string
		 */
		public function ddwcmpa_get_email_intro( $status ) {
			return $this->ddwcmpa_get_email_part( 'message', $status );
		}

		/**
		 * One configured piece of a per status email.
		 *
		 * @param string $part   One of subject, heading or message.
		 * @param string $status Prescription status.
		 * @return string
		 */
		protected function ddwcmpa_get_email_part( $part, $status ) {
			$key = 'email_' . $part . '_' . $status;

			return ! empty( $this->ddwcmpa_configuration[ $key ] ) ? $this->ddwcmpa_configuration[ $key ] : '';
		}

		/**
		 * Get prescription content function
		 *
		 * @param string $order_id Order ID when rendering an existing order.
		 * @return void
		 */
		public function ddwcmpa_get_upload_medical_prescription_content( $order_id = '' ) {
			$status = '';
			$reason = '';

			if ( ! empty( $order_id ) ) {
				$order       = wc_get_order( $order_id );
				$attachments = self::ddwcmpa_get_array_meta( $order, '_ddwcmpa_attachments' );
				$status      = $order->get_meta( '_ddwcmpa_status', true );
				$reason      = $order->get_meta( '_ddwcmpa_rejection_reason', true );
			} else {
				$attachments = WC()->session->get( 'ddwcmpa_attachments' );
			}

			wp_enqueue_style( 'ddwcmpa-front-style' );
			wp_enqueue_script( 'ddwcmpa-front-script' );

			$statuses = $this->ddwcmpa_get_statuses();
			$editable = empty( $status ) || 'attachments_pending' === $status || ( in_array( $status, [ 'rejected', 'info_required' ], true ) && ! empty( $this->ddwcmpa_configuration['reupload_enabled'] ) );

			$attachment_when = is_admin() ? 'now' : WC()->session->get( 'ddwcmpa_attachment_when' );

			if ( empty( $attachment_when ) ) {
				$attachment_when = 'now';
			}

			$can_defer = ! empty( $this->ddwcmpa_configuration['attach_later_enabled'] ) && empty( $order_id );
			?>
			<div class="ddwcmpa-prescription-attachment-container">
				<div class="ddwcmpa-card-header">
					<span class="ddwcmpa-card-mark"><?php DDWCMPA_Icon_Helper::render( 'prescription', [ 'size' => 22 ] ); ?></span>

					<div class="ddwcmpa-card-heading">
						<h3>
							<?php
							if ( ! empty( $order_id ) ) {
								esc_html_e( 'Attached Medical Prescription(s)', 'prescription-for-woocommerce' );
							} else {
								esc_html_e( 'Attach Medical Prescription', 'prescription-for-woocommerce' );
							}
							?>
						</h3>

						<?php if ( ! empty( $this->ddwcmpa_configuration['prescription_description'] ) ) : ?>
							<p class="ddwcmpa-card-subtitle"><?php echo esc_html( $this->ddwcmpa_configuration['prescription_description'] ); ?></p>
						<?php endif; ?>
					</div>

					<div class="ddwcmpa-card-header-meta">
						<?php if ( ! empty( $status ) ) : ?>
							<span class="ddwcmpa-status ddwcmpa-status-<?php echo esc_attr( $status ); ?>"><?php echo esc_html( isset( $statuses[ $status ] ) ? $statuses[ $status ] : $status ); ?></span>
						<?php endif; ?>
					</div>
				</div>

				<?php if ( ! empty( $reason ) && 'rejected' === $status ) : ?>
					<div class="ddwcmpa-notice ddwcmpa-notice-error">
						<?php DDWCMPA_Icon_Helper::render( 'cross', [ 'size' => 18 ] ); ?>
						<span>
							<strong><?php esc_html_e( 'Why this was rejected', 'prescription-for-woocommerce' ); ?></strong>
							<?php echo esc_html( $reason ); ?>
						</span>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $reason ) && 'info_required' === $status ) : ?>
					<div class="ddwcmpa-notice ddwcmpa-notice-info">
						<?php DDWCMPA_Icon_Helper::render( 'info', [ 'size' => 18 ] ); ?>
						<span>
							<strong><?php esc_html_e( 'The pharmacist needs a little more', 'prescription-for-woocommerce' ); ?></strong>
							<?php echo esc_html( $reason ); ?>
						</span>
					</div>
				<?php endif; ?>

				<?php if ( $can_defer ) : ?>
					<ul class="ddwcmpa-tabs">
						<li class="ddwcmpa-tab <?php echo esc_attr( 'now' === $attachment_when ? 'ddwcmpa-active' : '' ); ?>" data-attachment="now">
							<?php DDWCMPA_Icon_Helper::render( 'upload', [ 'size' => 17 ] ); ?>
							<?php esc_html_e( 'Attach Now', 'prescription-for-woocommerce' ); ?>
						</li>
						<li class="ddwcmpa-tab <?php echo esc_attr( 'later' === $attachment_when ? 'ddwcmpa-active' : '' ); ?>" data-attachment="later">
							<?php DDWCMPA_Icon_Helper::render( 'clock', [ 'size' => 17 ] ); ?>
							<?php esc_html_e( 'Attach later', 'prescription-for-woocommerce' ); ?>
						</li>
					</ul>

					<div class="ddwcmpa-notice ddwcmpa-notice-info ddwcmpa-attachment-later-description <?php echo esc_attr( 'now' === $attachment_when ? 'ddwcmpa-hide' : '' ); ?>">
						<?php DDWCMPA_Icon_Helper::render( 'info', [ 'size' => 18 ] ); ?>
						<span>
							<?php
							if ( is_user_logged_in() ) {
								esc_html_e( 'You need to attach the prescription later to the order for the approval.', 'prescription-for-woocommerce' );
							} else {
								esc_html_e( 'You need to login in order to attach the medical prescription later to the order for the approval.', 'prescription-for-woocommerce' );
							}
							?>
						</span>
					</div>
				<?php endif; ?>

				<form class="ddwcmpa-upload-form <?php echo esc_attr( 'later' === $attachment_when ? 'ddwcmpa-hide' : '' ); ?>" method="post" enctype="multipart/form-data">
					<?php if ( ! empty( $order_id ) ) : ?>
						<input type="hidden" name="ddwcmpa_order_id" value="<?php echo esc_attr( $order_id ); ?>" />
					<?php endif; ?>

					<?php if ( $editable ) : ?>
						<label class="ddwcmpa-dropzone" tabindex="0">
							<span class="ddwcmpa-dropzone-mark"><?php DDWCMPA_Icon_Helper::render( 'upload', [ 'size' => 26 ] ); ?></span>
							<span class="ddwcmpa-dropzone-title"><?php esc_html_e( 'Upload your prescription', 'prescription-for-woocommerce' ); ?></span>
							<span class="ddwcmpa-dropzone-hint"><?php esc_html_e( 'Drag and drop, or click to browse', 'prescription-for-woocommerce' ); ?></span>
							<span class="ddwcmpa-dropzone-formats">
								<?php
								printf(
									/* translators: %d: how many files the customer may attach */
									esc_html( _n( 'JPG, PNG or PDF. Up to %d file.', 'JPG, PNG or PDF. Up to %d files.', absint( $this->ddwcmpa_configuration['max_files'] ), 'prescription-for-woocommerce' ) ),
									absint( $this->ddwcmpa_configuration['max_files'] )
								);
								?>
							</span>
							<input type="file" name="ddwcmpa_prescription_attachment[]" id="ddwcmpa-prescription-attachment" class="ddwcmpa-hide" accept="image/*,application/pdf,.doc,.docx,.txt" multiple>
						</label>
					<?php endif; ?>

					<div class="ddwcmpa-prescription-attachment-box">
						<?php
						if ( ! empty( $attachments ) ) {
							foreach ( $attachments as $attachment_id ) {
								$this->ddwcmpa_render_attachment( $attachment_id, $order_id, $editable );
							}
						}
						?>
					</div>
				</form>

				<?php
				if ( ! empty( $status ) && ( 'attachments_pending' === $status || ( in_array( $status, [ 'rejected', 'info_required' ], true ) && ! empty( $this->ddwcmpa_configuration['reupload_enabled'] ) ) ) ) {
					?>
					<button type="submit" class="button ddwcmpa-approval-submit">
						<?php DDWCMPA_Icon_Helper::render( 'check', [ 'size' => 17 ] ); ?>
						<?php esc_html_e( 'Submit for Approval', 'prescription-for-woocommerce' ); ?>
					</button>
					<?php
				}
				?>
			</div>
			<?php
		}

		/**
		 * Render one attachment tile.
		 *
		 * @param int        $attachment_id Attachment ID.
		 * @param int|string $order_id      Order ID, when the file belongs to an order.
		 * @param bool       $editable      Whether the customer may still remove it.
		 * @return void
		 */
		public function ddwcmpa_render_attachment( $attachment_id, $order_id = '', $editable = false ) {
			$attachment = get_post( $attachment_id );

			if ( ! $attachment ) {
				return;
			}

			$url = DDWCMPA_Security_Helper::get_file_url( $attachment_id, $order_id );
			?>
			<label data-attachment-id="<?php echo esc_attr( $attachment_id ); ?>">
				<?php
				if ( $editable ) {
					?>
					<button type="button" class="ddwcmpa-remove-prescription" aria-label="<?php esc_attr_e( 'Remove this file', 'prescription-for-woocommerce' ); ?>"><?php DDWCMPA_Icon_Helper::render( 'cross', [ 'size' => 18 ] ); ?></button>
					<?php
				}

				if ( strpos( $attachment->post_mime_type, 'image' ) !== false ) {
					?>
					<a href="<?php echo esc_url( $url ); ?>" class="ddwcmpa-lightbox" title="<?php esc_attr_e( 'View Attachment', 'prescription-for-woocommerce' ); ?>" target="_blank" rel="noopener noreferrer">
						<img src="<?php echo esc_url( $url ); ?>" alt="<?php echo esc_attr( get_the_title( $attachment_id ) ); ?>" title="<?php echo esc_attr( get_the_title( $attachment_id ) ); ?>" />
					</a>
					<?php
				} else {
					?>
					<a class="ddwcmpa-attachment-name" href="<?php echo esc_url( $url ); ?>" title="<?php esc_attr_e( 'View Attachment', 'prescription-for-woocommerce' ); ?>" target="_blank" rel="noopener noreferrer">
						<?php DDWCMPA_Icon_Helper::render( 'document', [ 'size' => 26 ] ); ?>
						<span><?php echo esc_html( get_the_title( $attachment_id ) ); ?></span>
					</a>
					<?php
				}
				?>
			</label>
			<?php
		}

		/**
		 * Get statuses function
		 *
		 * @return array
		 */
		public function ddwcmpa_get_statuses() {
			return apply_filters(
				'ddwcmpa_modify_statuses',
				[
					'attachments_pending' => esc_html__( 'Attachment Pending', 'prescription-for-woocommerce' ),
					'pending'             => esc_html__( 'Pending', 'prescription-for-woocommerce' ),
					'approved'            => esc_html__( 'Approved', 'prescription-for-woocommerce' ),
					'rejected'            => esc_html__( 'Rejected', 'prescription-for-woocommerce' ),
					'info_required'       => esc_html__( 'Information Requested', 'prescription-for-woocommerce' ),
				]
			);
		}
	}
}
