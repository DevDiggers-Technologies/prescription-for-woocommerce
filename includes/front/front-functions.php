<?php
/**
 * This file contains all callback functions for front-end actions and hooks.
 *
 * @author DevDiggers
 * @version 1.0.0
 * @package DevDiggers Prescription for WooCommerce
 */

namespace DDWCMedicalPrescriptionAttachment\Includes\Front;

use DDWCMedicalPrescriptionAttachment\Helper\DDWCMPA_Prescription_Helper;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCMPA_Front_Functions' ) ) {
	/**
	 * Front functions class
	 */
	class DDWCMPA_Front_Functions {
		/**
		 * Configuration Variable
		 *
		 * @var array
		 */
		protected $ddwcmpa_configuration;

		/**
		 * Prescription Helper Variable
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
			$this->prescription_helper   = new DDWCMPA_Prescription_Helper( $this->ddwcmpa_configuration );
		}

		/**
		 * Add content in single product page function
		 *
		 * @return void
		 */
		public function ddwcmpa_add_content_in_single_product_page() {
			if ( ! empty( $this->ddwcmpa_configuration['product_label'] ) ) {
				$product_id = strval( get_the_ID() );

				if ( $this->prescription_helper->ddwcmpa_check_prescription_exists( $product_id ) ) {
					wp_enqueue_style( 'ddwcmpa-front-style' );
					?>
					<span class="ddwcmpa-label"><?php echo esc_html( $this->ddwcmpa_configuration['product_label'] ); ?></span>
					<?php
				}
			}
		}

		/**
		 * Modify WooCommerce loop add to cart link button
		 *
		 * @param string      $button  Button HTML.
		 * @param \WC_Product $product Product object.
		 * @return string
		 */
		public function ddwcmpa_modify_woocommerce_loop_add_to_cart_link( $button, $product ) {
			$product_id = strval( $product->get_id() );

			if ( ! empty( $this->ddwcmpa_configuration['product_label'] ) ) {
				if ( $this->prescription_helper->ddwcmpa_check_prescription_exists( $product_id ) ) {
					wp_enqueue_style( 'ddwcmpa-front-style' );
					ob_start();
					?>
					<span class="ddwcmpa-label"><?php echo esc_html( $this->ddwcmpa_configuration['product_label'] ); ?></span>
					<?php
					$label = ob_get_clean();

					if ( 'before' === $this->ddwcmpa_configuration['shop_page_position'] ) {
						$button = $label . $button;
					} elseif ( 'after' === $this->ddwcmpa_configuration['shop_page_position'] ) {
						$button .= $label;
					}
				}
			}

			return $button;
		}

		/**
		 * Add shop loop content near product title function
		 *
		 * @return void
		 */
		public function ddwcmpa_add_shop_loop_content_near_product_title() {
			$product_id = strval( get_the_ID() );

			if ( ! empty( $this->ddwcmpa_configuration['product_label'] ) ) {
				if ( $this->prescription_helper->ddwcmpa_check_prescription_exists( $product_id ) ) {
					wp_enqueue_style( 'ddwcmpa-front-style' );
					?>
					<span class="ddwcmpa-label"><?php echo esc_html( $this->ddwcmpa_configuration['product_label'] ); ?></span>
					<?php
				}
			}
		}

		/**
		 * Add content after cart totals function
		 *
		 * @return void
		 */
		public function ddwcmpa_add_upload_medical_prescription_content() {
			// If checkout registration is disabled and not logged in, do not show the prescription container.
			if ( ! WC()->checkout()->is_registration_enabled() && WC()->checkout()->is_registration_required() && ! is_user_logged_in() ) {
				return;
			}

			if ( $this->prescription_helper->ddwcmpa_check_prescription_exists() ) {
				$this->prescription_helper->ddwcmpa_get_upload_medical_prescription_content();
			}
		}

		/**
		 * Front scripts enqueue function
		 *
		 * @return void
		 */
		public function ddwcmpa_front_scripts() {
			wp_register_style( 'ddwcmpa-front-style', DDWCMPA_PLUGIN_URL . 'assets/css/front.css', [], filemtime( DDWCMPA_PLUGIN_FILE . 'assets/css/front.css' ) );

			wp_register_script( 'ddwcmpa-front-script', DDWCMPA_PLUGIN_URL . 'assets/js/front.js', [], filemtime( DDWCMPA_PLUGIN_FILE . 'assets/js/front.js' ), true );

			wp_localize_script(
				'ddwcmpa-front-script',
				'ddwcmpaFrontObj',
				[
					'ajax'   => [
						'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
						'ajaxNonce' => wp_create_nonce( 'ddwcmpa-nonce' ),
					],
					'i18n'   => [
						'attachmentExtensionError' => esc_html__( 'Kindly select valid image or pdf file(s)', 'devdiggers-prescription-for-woocommerce' ),
						'maxFilesError'            => sprintf(
							/* translators: %d: maximum number of files */
							esc_html__( 'You can attach a maximum of %d file(s).', 'devdiggers-prescription-for-woocommerce' ),
							absint( $this->ddwcmpa_configuration['max_files'] )
						),
						'uploading'                => esc_html__( 'Uploading', 'devdiggers-prescription-for-woocommerce' ),
						'close'                    => esc_html__( 'Close', 'devdiggers-prescription-for-woocommerce' ),
					],
					'config' => [
						'cart_page_position'     => ! empty( $this->ddwcmpa_configuration['cart_page_position'] ) ? $this->ddwcmpa_configuration['cart_page_position'] : '',
						'checkout_page_position' => ! empty( $this->ddwcmpa_configuration['checkout_page_position'] ) ? $this->ddwcmpa_configuration['checkout_page_position'] : '',
						'maxFiles'               => absint( $this->ddwcmpa_configuration['max_files'] ),
					],
				]
			);

			$custom_css = '
				:root {
					--ddwcmpa-label-font-color: ' . esc_attr( $this->ddwcmpa_configuration['product_label_font_color'] ) . ';
					--ddwcmpa-label-background-color: ' . esc_attr( $this->ddwcmpa_configuration['product_label_background_color'] ) . ';
				}
			';
			wp_add_inline_style( 'ddwcmpa-front-style', $custom_css );
		}

		/**
		 * WooCommerce Blocks enqueue scripts function
		 *
		 * @return void
		 */
		public function ddwcmpa_blocks_enqueue_scripts() {
			wp_enqueue_style( 'ddwcmpa-front-style' );
			wp_enqueue_script( 'ddwcmpa-front-script' );
		}

		/**
		 * Modify WooCommerce Order Button HTML function
		 *
		 * @param string $order_button_html Place order button markup.
		 * @return string
		 */
		public function ddwcmpa_modify_woocommerce_order_button_html( $order_button_html ) {
			if ( ! $this->prescription_helper->ddwcmpa_check_prescription_exists() ) {
				return $order_button_html;
			}

			$error = $this->prescription_helper->ddwcmpa_get_checkout_error();

			if ( '' === $error ) {
				return $order_button_html;
			}

			ob_start();
			?>
			<div class="woocommerce-info"><?php echo esc_html( $error ); ?></div>
			<?php
			return ob_get_clean();
		}

		/**
		 * WooCommerce after checkout validation function
		 *
		 * @param array     $data   Posted checkout data.
		 * @param \WP_Error $errors Error collector.
		 * @return void
		 */
		public function ddwcmpa_woocommerce_after_checkout_validation( $data, $errors ) {
			if ( ! $this->prescription_helper->ddwcmpa_check_prescription_exists() ) {
				return;
			}

			$error = $this->prescription_helper->ddwcmpa_get_checkout_error();

			if ( '' !== $error ) {
				$errors->add( 'ddwcmpa_validation', $error );
			}
		}

		/**
		 * WooCommerce Blocks checkout validation function
		 *
		 * @param \WP_REST_Request $request Store API request.
		 * @return void
		 * @throws \Exception When the prescription is missing.
		 */
		public function ddwcmpa_blocks_checkout_validation( $request ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- Signature fixed by the Store API validation hook.
			if ( ! $this->prescription_helper->ddwcmpa_check_prescription_exists() ) {
				return;
			}

			$error = $this->prescription_helper->ddwcmpa_get_checkout_error();

			if ( '' !== $error ) {
				$this->ddwcmpa_throw_checkout_error( $error );
			}
		}

		/**
		 * WooCommerce checkout update order meta function
		 *
		 * @param mixed $order_id_or_object Order ID or order object.
		 * @return void
		 * @throws \Exception When the prescription is missing.
		 */
		public function ddwcmpa_woocommerce_checkout_update_order_meta( $order_id_or_object ) {
			if ( is_object( $order_id_or_object ) ) {
				$order    = $order_id_or_object;
				$order_id = $order->get_id();
			} else {
				$order_id = $order_id_or_object;
				$order    = wc_get_order( $order_id );
			}

			if ( ! $order || ! $this->prescription_helper->ddwcmpa_check_prescription_exists( '', $order_id ) ) {
				return;
			}

			$error = $this->prescription_helper->ddwcmpa_get_checkout_error();

			if ( '' !== $error ) {
				$this->ddwcmpa_throw_checkout_error( $error );
			}

			$attachment_when = WC()->session->get( 'ddwcmpa_attachment_when' );
			$attachments     = (array) WC()->session->get( 'ddwcmpa_attachments' );

			if ( empty( $attachment_when ) || 'now' === $attachment_when ) {
				$order->update_meta_data( '_ddwcmpa_attachments', array_values( array_filter( array_map( 'absint', $attachments ) ) ) );
				$order->update_meta_data( '_ddwcmpa_status', 'pending' );
				$order->update_meta_data( '_ddwcmpa_status_updated', current_time( 'mysql' ) );

				$this->ddwcmpa_notify_admin_of_new_prescription( $order, $attachments );
			} elseif ( 'later' === $attachment_when ) {
				$order->update_meta_data( '_ddwcmpa_status', 'attachments_pending' );

				foreach ( $attachments as $attachment_id ) {
					wp_delete_attachment( $attachment_id, true );
				}
			}

			$order->save();

			$this->ddwcmpa_hold_order( $order );

			WC()->session->__unset( 'ddwcmpa_attachment_when' );
			WC()->session->__unset( 'ddwcmpa_attachments' );
		}

		/**
		 * Park the order in the configured holding status until a pharmacist decides.
		 *
		 * @param \WC_Order $order Order object.
		 * @return void
		 */
		protected function ddwcmpa_hold_order( $order ) {
			$hold_status = $this->ddwcmpa_configuration['hold_order_status'];

			if ( empty( $hold_status ) ) {
				return;
			}

			$status = $order->get_meta( '_ddwcmpa_status', true );

			if ( ! in_array( $status, [ 'pending', 'attachments_pending' ], true ) ) {
				return;
			}

			$order->update_status(
				$hold_status,
				esc_html__( 'Held until the medical prescription is approved.', 'devdiggers-prescription-for-woocommerce' )
			);
		}

		/**
		 * Tell the store that a new prescription is waiting for review.
		 *
		 * @param \WC_Order $order       Order object.
		 * @param array     $attachments Attachment IDs.
		 * @return void
		 */
		protected function ddwcmpa_notify_admin_of_new_prescription( $order, $attachments ) {
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
		 * Stop checkout with a message the customer can act on.
		 *
		 * The Store API only turns a RouteException into a 400 carrying the message.
		 * Any other exception becomes a generic 500, and the block checkout then shows
		 * a vague server error instead of telling the customer what is missing.
		 *
		 * @param string $error Escaped message.
		 * @return void
		 * @throws \Exception Always.
		 */
		protected function ddwcmpa_throw_checkout_error( $error ) {
			if ( class_exists( '\Automattic\WooCommerce\StoreApi\Exceptions\RouteException' ) ) {
				throw new \Automattic\WooCommerce\StoreApi\Exceptions\RouteException( 'ddwcmpa_prescription_required', esc_html( $error ), 400 );
			}

			throw new \Exception( esc_html( $error ) );
		}

		/**
		 * Add content on order details page function
		 *
		 * @param \WC_Order $order Order object.
		 * @return void
		 */
		public function ddwcmpa_add_content_on_order_details_page( $order ) {
			$order_id = $order->get_id();

			if ( $this->prescription_helper->ddwcmpa_check_prescription_exists( '', $order_id ) ) {
				$this->prescription_helper->ddwcmpa_get_upload_medical_prescription_content( $order_id );
			}
		}

		/**
		 * Add Loader template
		 *
		 * @return void
		 */
		public function ddwcmpa_add_loader_template() {
			?>
			<div class="ddwcmpa-loader-wrap ddwcmpa-hide">
				<div class="ddwcmpa-loader">
					<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
						<circle cx="8" cy="8" r="7" stroke-width="2"/>
					</svg>
				</div>
			</div>
			<div class="ddwcmpa-lightbox-overlay ddwcmpa-hide" role="dialog" aria-modal="true">
				<button type="button" class="ddwcmpa-lightbox-close" aria-label="<?php esc_attr_e( 'Close', 'devdiggers-prescription-for-woocommerce' ); ?>">&times;</button>
				<img src="" alt="<?php esc_attr_e( 'Prescription preview', 'devdiggers-prescription-for-woocommerce' ); ?>" />
			</div>
			<?php
		}
	}
}
