<?php
/**
 * Handles all AJAX callbacks for front-end prescription attachment actions.
 *
 * @author DevDiggers
 * @version 1.0.0
 * @package DevDiggers Prescription for WooCommerce
 */

namespace DDWCMedicalPrescriptionAttachment\Includes\Front;

use DDWCMedicalPrescriptionAttachment\Helper\DDWCMPA_Prescription_Helper;
use DDWCMedicalPrescriptionAttachment\Helper\DDWCMPA_Security_Helper;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCMPA_Front_Ajax_Functions' ) ) {
	/**
	 * Front ajax functions class
	 */
	class DDWCMPA_Front_Ajax_Functions {
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
		 * Can the current visitor act on this order's prescription?
		 *
		 * @param \WC_Order|false $order Order object.
		 * @return bool
		 */
		protected function ddwcmpa_can_edit_order( $order ) {
			if ( ! $order ) {
				return false;
			}

			if ( current_user_can( DDWCMPA_Security_Helper::MANAGE_CAP ) ) {
				return true;
			}

			$customer_id = $order->get_customer_id();

			return $customer_id && get_current_user_id() === $customer_id;
		}

		/**
		 * Handle prescription session function
		 *
		 * @return void
		 */
		public function ddwcmpa_handle_prescription_session() {
			if ( ! check_ajax_referer( 'ddwcmpa-nonce', 'nonce', false ) ) {
				wp_send_json(
					[
						'success' => false,
						'message' => esc_html__( 'Security Check Failed!!', 'devdiggers-prescription-for-woocommerce' ),
					]
				);
			}

			$type     = ! empty( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';
			$order_id = ! empty( $_POST['ddwcmpa_order_id'] ) ? intval( $_POST['ddwcmpa_order_id'] ) : '';
			$order    = ! empty( $order_id ) ? wc_get_order( $order_id ) : false;

			// Anything touching an existing order has to belong to the person asking.
			if ( ! empty( $order_id ) && ! $this->ddwcmpa_can_edit_order( $order ) ) {
				wp_send_json(
					[
						'success' => false,
						'message' => esc_html__( 'You are not allowed to change this order.', 'devdiggers-prescription-for-woocommerce' ),
					]
				);
			}

			$response = [
				'success' => false,
				'message' => esc_html__( 'Invalid request.', 'devdiggers-prescription-for-woocommerce' ),
			];

			switch ( $type ) {
				case 'upload':
					$response = $this->ddwcmpa_handle_upload( $order, $order_id );
					break;

				case 'remove':
					$response = $this->ddwcmpa_handle_remove( $order, $order_id );
					break;

				case 'when':
					$attachment_when = ! empty( $_POST['attachment_when'] ) ? sanitize_text_field( wp_unslash( $_POST['attachment_when'] ) ) : '';

					if ( in_array( $attachment_when, [ 'now', 'later' ], true ) ) {
						WC()->session->set( 'ddwcmpa_attachment_when', $attachment_when );

						$response = [
							'success' => true,
							'message' => '',
						];
					}
					break;

				case 'send_for_approval':
					$response = $this->ddwcmpa_handle_send_for_approval( $order, $order_id );
					break;
			}

			wp_send_json( $response );
			exit();
		}

		/**
		 * Store uploaded prescriptions in the protected vault.
		 *
		 * @param \WC_Order|false $order    Order object when attaching to an order.
		 * @param int|string      $order_id Order ID.
		 * @return array
		 */
		protected function ddwcmpa_handle_upload( $order, $order_id ) {
			check_ajax_referer( 'ddwcmpa-nonce', 'nonce' );

			if ( empty( $_FILES['ddwcmpa_prescription_attachment']['name'][0] ) ) {
				return [
					'success' => false,
					'message' => esc_html__( 'Kindly select a file to upload.', 'devdiggers-prescription-for-woocommerce' ),
				];
			}

			$attachments = $order ? DDWCMPA_Prescription_Helper::ddwcmpa_get_array_meta( $order, '_ddwcmpa_attachments' ) : (array) WC()->session->get( 'ddwcmpa_attachments' );
			$attachments = array_filter( $attachments );

			$attachments_html = [];
			$error_message    = '';
			$user_id          = get_current_user_id();
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Each member is validated below.
			$files     = $_FILES['ddwcmpa_prescription_attachment'];
			$max_files = absint( $this->ddwcmpa_configuration['max_files'] );

			$allowed_doc_type = apply_filters(
				'ddwcmpa_allowed_mime_types',
				[
					'image/jpg',
					'image/jpeg',
					'image/png',
					'image/webp',
					'image/heic',
					'text/plain',
					'application/msword',
					'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
					'application/pdf',
				]
			);

			$original_files = $_FILES; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- The array is restored untouched after the upload loop.

			foreach ( $files['name'] as $key => $file ) {
				if ( $max_files && count( $attachments ) >= $max_files ) {
					$error_message = sprintf(
						/* translators: %d: maximum number of files */
						esc_html__( 'You can attach a maximum of %d file(s).', 'devdiggers-prescription-for-woocommerce' ),
						$max_files
					);
					break;
				}

				$doc_type = ! empty( $files['tmp_name'][ $key ] ) ? mime_content_type( $files['tmp_name'][ $key ] ) : '';

				if ( apply_filters( 'ddwcmpa_check_attachments_mime_type', true, $file ) && ! empty( $doc_type ) && ! in_array( $doc_type, $allowed_doc_type, true ) ) {
					$error_message = esc_html__( 'Sorry, jpg, jpeg, png, webp, pdf, doc, docx files are allowed.', 'devdiggers-prescription-for-woocommerce' );
					break;
				}

				if ( ! empty( $files['size'][ $key ] ) && $files['size'][ $key ] > wp_max_upload_size() ) {
					$error_message = sprintf(
						/* translators: %s: maximum upload size in megabytes */
						esc_html__( 'Attachment size too large, the limit is %s MB.', 'devdiggers-prescription-for-woocommerce' ),
						number_format( wp_max_upload_size() / 1048576 )
					);
					break;
				}

				if ( apply_filters( 'ddwcmpa_check_custom_validation_for_attachment', false, $file ) ) {
					$error_message = apply_filters( 'ddwcmpa_custom_validation_error_message', esc_html__( 'Invalid Attachment.', 'devdiggers-prescription-for-woocommerce' ), $file );
					break;
				}

				do_action( 'ddwcmpa_after_attachment_validation' );

				$_FILES = [
					'ddwcmpa_temp_file' => [
						'name'     => $files['name'][ $key ],
						'type'     => $files['type'][ $key ],
						'tmp_name' => $files['tmp_name'][ $key ],
						'error'    => $files['error'][ $key ],
						'size'     => $files['size'][ $key ],
					],
				];

				include_once ABSPATH . 'wp-admin/includes/image.php';
				include_once ABSPATH . 'wp-admin/includes/file.php';
				include_once ABSPATH . 'wp-admin/includes/media.php';

				// Everything written between these two calls lands in the deny-all vault.
				DDWCMPA_Security_Helper::start_vault_upload();
				$attachment_id = media_handle_upload( 'ddwcmpa_temp_file', $user_id );
				DDWCMPA_Security_Helper::end_vault_upload();

				if ( is_wp_error( $attachment_id ) ) {
					$error_message = $attachment_id->get_error_message();
					break;
				}

				DDWCMPA_Security_Helper::mark_protected( $attachment_id );

				$attachments[] = $attachment_id;

				ob_start();
				$this->prescription_helper->ddwcmpa_render_attachment( $attachment_id, $order_id, true );
				$attachments_html[] = ob_get_clean();
			}

			$_FILES = $original_files;

			if ( ! empty( $error_message ) ) {
				return [
					'success' => false,
					'message' => $error_message,
				];
			}

			$attachments = array_values( array_unique( array_map( 'absint', $attachments ) ) );

			if ( $order ) {
				$order->update_meta_data( '_ddwcmpa_attachments', $attachments );
				$order->save();
			} else {
				WC()->session->set( 'ddwcmpa_attachments', $attachments );
			}

			return [
				'success'          => true,
				'attachments_html' => $attachments_html,
				'message'          => esc_html__( 'Uploaded successfully.', 'devdiggers-prescription-for-woocommerce' ),
			];
		}

		/**
		 * Remove one uploaded prescription.
		 *
		 * @param \WC_Order|false $order    Order object.
		 * @param int|string      $order_id Order ID.
		 * @return array
		 */
		protected function ddwcmpa_handle_remove( $order, $order_id ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Dispatched with the same two arguments as every sibling handler.
			check_ajax_referer( 'ddwcmpa-nonce', 'nonce' );

			$attachment_id = ! empty( $_POST['attachment_id'] ) ? intval( wp_unslash( $_POST['attachment_id'] ) ) : 0;

			if ( empty( $attachment_id ) ) {
				return [
					'success' => false,
					'message' => esc_html__( 'Invalid attachment.', 'devdiggers-prescription-for-woocommerce' ),
				];
			}

			$attachments = $order ? DDWCMPA_Prescription_Helper::ddwcmpa_get_array_meta( $order, '_ddwcmpa_attachments' ) : (array) WC()->session->get( 'ddwcmpa_attachments' );

			// Only delete a file that is genuinely part of this basket or order.
			if ( ! in_array( $attachment_id, array_map( 'absint', $attachments ), true ) ) {
				return [
					'success' => false,
					'message' => esc_html__( 'That attachment does not belong to this order.', 'devdiggers-prescription-for-woocommerce' ),
				];
			}

			$key = array_search( $attachment_id, array_map( 'absint', $attachments ), true );

			if ( false !== $key ) {
				unset( $attachments[ $key ] );
			}

			wp_delete_attachment( $attachment_id, true );

			$attachments = array_values( array_filter( $attachments ) );

			if ( $order ) {
				$order->update_meta_data( '_ddwcmpa_attachments', $attachments );
				$order->save();
			} else {
				WC()->session->set( 'ddwcmpa_attachments', $attachments );
			}

			return [
				'success' => true,
				'message' => esc_html__( 'Attachment removed successfully.', 'devdiggers-prescription-for-woocommerce' ),
			];
		}

		/**
		 * Send an order's attached prescriptions off for review.
		 *
		 * @param \WC_Order|false $order    Order object.
		 * @param int|string      $order_id Order ID.
		 * @return array
		 */
		protected function ddwcmpa_handle_send_for_approval( $order, $order_id ) {
			if ( ! $order ) {
				return [
					'success' => false,
					'message' => esc_html__( 'Order not found.', 'devdiggers-prescription-for-woocommerce' ),
				];
			}

			$attachments = array_filter( DDWCMPA_Prescription_Helper::ddwcmpa_get_array_meta( $order, '_ddwcmpa_attachments' ) );

			if ( empty( $attachments ) ) {
				return [
					'success' => false,
					'message' => esc_html__( 'Kindly upload the attachment first.', 'devdiggers-prescription-for-woocommerce' ),
				];
			}

			$this->prescription_helper->ddwcmpa_set_status( $order, 'pending', [ 'notify' => false ] );

			$this->ddwcmpa_notify_admin( $order, $attachments );

			ob_start();
			$this->prescription_helper->ddwcmpa_get_upload_medical_prescription_content( $order_id );

			return [
				'success' => true,
				'data'    => ob_get_clean(),
				'message' => esc_html__( 'Your prescription has been submitted for review.', 'devdiggers-prescription-for-woocommerce' ),
			];
		}

		/**
		 * Tell the store that a prescription is waiting for review.
		 *
		 * @param \WC_Order $order       Order object.
		 * @param array     $attachments Attachment IDs.
		 * @return void
		 */
		protected function ddwcmpa_notify_admin( $order, $attachments ) {
			if ( empty( $this->ddwcmpa_configuration['admin_email_enabled'] ) ) {
				return;
			}

			$review_url = admin_url( 'admin.php?page=ddwcmpa-dashboard&menu=orders' );

			$email_message   = [];
			$email_message[] = sprintf(
				/* translators: %s: order number */
				esc_html__( 'A customer has uploaded medical prescription(s) for order #%s.', 'devdiggers-prescription-for-woocommerce' ),
				$order->get_order_number()
			);
			$email_message[] = sprintf(
				/* translators: %s: review queue URL */
				esc_html__( 'Review queue: %s', 'devdiggers-prescription-for-woocommerce' ),
				'<a href="' . esc_url( $review_url ) . '">' . esc_url( $review_url ) . '</a>'
			);

			do_action(
				'ddwcmpa_mail',
				[
					'message'     => $email_message,
					'attachments' => array_filter( array_map( 'get_attached_file', $attachments ) ),
				]
			);
		}

		/**
		 * Get prescription UI function
		 *
		 * @return void
		 */
		public function ddwcmpa_get_prescription_ui() {
			if ( check_ajax_referer( 'ddwcmpa-nonce', 'nonce', false ) ) {
				if ( $this->prescription_helper->ddwcmpa_check_prescription_exists() ) {
					ob_start();
					$this->prescription_helper->ddwcmpa_get_upload_medical_prescription_content();
					$response = [
						'success' => true,
						'data'    => ob_get_clean(),
					];
				} else {
					$response = [
						'success' => false,
						'data'    => '',
					];
				}
			} else {
				$response = [
					'success' => false,
					'message' => esc_html__( 'Security Check Failed!!', 'devdiggers-prescription-for-woocommerce' ),
				];
			}

			wp_send_json( $response );
			exit();
		}
	}
}
