<?php
/**
 * Email Notification Handler
 *
 * @package DevDiggers Prescription for WooCommerce
 * @version 1.0.0
 */

namespace DDWCMedicalPrescriptionAttachment\Includes;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCMPA_Email_Notification_Handler' ) ) {
	/**
	 * Email Notification Handler class.
	 */
	class DDWCMPA_Email_Notification_Handler extends \WC_Email {
		/**
		 * Email message content.
		 *
		 * @var array
		 */
		public $email_message = [];

		/**
		 * Email attachments.
		 *
		 * @var array
		 */
		public $attachments = [];

		/**
		 * Order object.
		 *
		 * @var \WC_Order|null
		 */
		public $order = null;

		/**
		 * Email footer text.
		 *
		 * WC_Email does not declare this, so it has to be declared here or PHP 8.2
		 * and later warn about creating a dynamic property.
		 *
		 * @var string
		 */
		public $footer = '';

		/**
		 * Customer data.
		 *
		 * @var array
		 */
		public $customer_data = [];

		/**
		 * Per status subject and heading supplied by the current trigger.
		 *
		 * @var array
		 */
		protected $ddwcmpa_overrides = [];

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id             = 'ddwcmpa_notification';
			$this->title          = esc_html__( 'Prescription Notification', 'devdiggers-prescription-for-woocommerce' );
			$this->heading        = esc_html__( 'Prescription Notification', 'devdiggers-prescription-for-woocommerce' );
			$this->description    = esc_html__( 'Email notifications sent when medical prescriptions are uploaded or status changes.', 'devdiggers-prescription-for-woocommerce' );
			$this->subject        = '[' . get_option( 'blogname' ) . '] ' . esc_html__( 'Prescription Notification', 'devdiggers-prescription-for-woocommerce' );
			$this->template_html  = 'emails/email-template.php';
			$this->template_plain = 'emails/plain/email-template.php';
			$this->template_base  = DDWCMPA_PLUGIN_FILE . '/templates/';
			$this->footer         = esc_html__( 'Thank you for shopping with us.', 'devdiggers-prescription-for-woocommerce' );

			// Add placeholders.
			$this->placeholders = [
				'{site_title}'     => $this->get_blogname(),
				'{order_number}'   => '',
				'{order_date}'     => '',
				'{customer_name}'  => '',
				'{customer_email}' => '',
				'{order_url}'      => '',
				'{admin_url}'      => '',
				'{status}'         => '',
				'{reason}'         => '',
				'{reupload_note}'  => '',
			];

			add_action( 'ddwcmpa_mail_notification', [ $this, 'trigger' ] );

			// Call parent constructor.
			parent::__construct();

			// Initialize settings.
			$this->init_settings();
		}

		/**
		 * Trigger email notification.
		 *
		 * @param array $data Email data array.
		 * @return bool|WP_Error True on success, WP_Error on failure.
		 */
		public function trigger( $data ) {
			// Validate input data.
			if ( empty( $data ) || ! is_array( $data ) ) {
				$this->log_error( 'Invalid email data provided', $data );
				return new \WP_Error( 'invalid_data', esc_html__( 'Invalid email data provided.', 'devdiggers-prescription-for-woocommerce' ) );
			}

			try {
				// Reset properties.
				$this->attachments       = [];
				$this->email_message     = [];
				$this->order             = null;
				$this->customer_data     = [];
				$this->ddwcmpa_overrides = [];
				$this->subject           = $this->get_option( 'subject', $this->get_default_subject() );
				$this->heading           = $this->get_option( 'heading', $this->get_default_heading() );

				// Process email data.
				$this->process_email_data( $data );

				// Validate required fields.
				if ( ! $this->validate_email_data() ) {
					return new \WP_Error( 'validation_failed', esc_html__( 'Email validation failed.', 'devdiggers-prescription-for-woocommerce' ) );
				}

				// Check if email is enabled and recipient exists.
				if ( ! $this->is_enabled() ) {
					$this->log_info( 'Email notification is disabled' );
					return false;
				}

				if ( ! $this->get_recipient() ) {
					$this->log_error( 'No recipient specified for email notification' );
					return new \WP_Error( 'no_recipient', esc_html__( 'No recipient specified for email notification.', 'devdiggers-prescription-for-woocommerce' ) );
				}

				// Update placeholders.
				$this->update_placeholders();

				// Send email.
				$result = $this->send(
					$this->get_recipient(),
					$this->get_subject(),
					$this->get_content(),
					$this->get_headers(),
					$this->attachments
				);

				if ( $result ) {
					$this->log_info(
						'Email notification sent successfully',
						[
							'recipient' => $this->get_recipient(),
							'subject'   => $this->get_subject(),
						]
					);
				} else {
					$this->log_error( 'Failed to send email notification' );
				}

				return $result;
			} catch ( \Exception $e ) {
				$this->log_error( 'Exception in email trigger: ' . $e->getMessage(), $data );
				return new \WP_Error( 'email_exception', esc_html__( 'An error occurred while sending the email.', 'devdiggers-prescription-for-woocommerce' ) );
			}
		}

		/**
		 * Process email data and set properties.
		 *
		 * @param array $data Email data.
		 * @return void
		 */
		private function process_email_data( $data ) {
			// Set email message.
			if ( ! empty( $data['message'] ) && is_array( $data['message'] ) ) {
				$this->email_message = $data['message'];
			}

			if ( ! empty( $data['email'] ) && is_email( $data['email'] ) ) {
				$this->recipient = sanitize_email( $data['email'] );
			} else {
				$this->recipient = $this->get_option( 'recipient', get_option( 'admin_email' ) );
			}

			// Set attachments.
			if ( ! empty( $data['attachments'] ) && is_array( $data['attachments'] ) ) {
				$this->attachments = array_filter( $data['attachments'], 'file_exists' );
			}

			// Set order object.
			if ( ! empty( $data['order_id'] ) ) {
				$this->order = wc_get_order( absint( $data['order_id'] ) );
			}

			// Set customer data.
			if ( ! empty( $data['customer_data'] ) && is_array( $data['customer_data'] ) ) {
				$this->customer_data = $data['customer_data'];
			}

			// The built in per status subject and heading. A subject or heading the
			// merchant saves under WooCommerce > Settings > Emails always wins.
			if ( ! empty( $data['subject'] ) && '' === $this->get_option( 'subject', '' ) ) {
				$this->subject                      = wp_strip_all_tags( $data['subject'] );
				$this->ddwcmpa_overrides['subject'] = $this->subject;
			}

			if ( ! empty( $data['heading'] ) && '' === $this->get_option( 'heading', '' ) ) {
				$this->heading                      = wp_strip_all_tags( $data['heading'] );
				$this->ddwcmpa_overrides['heading'] = $this->heading;
			}
		}

		/**
		 * Validate email data.
		 *
		 * @return bool
		 */
		private function validate_email_data() {
			// Check if message is not empty.
			if ( empty( $this->email_message ) ) {
				$this->log_error( 'Email message is empty' );
				return false;
			}

			// Check if recipient is valid email (support multiple).
			$recipients      = explode( ',', $this->get_recipient() );
			$recipients      = array_map( 'trim', $recipients );
			$has_valid_email = false;

			foreach ( $recipients as $recipient ) {
				if ( is_email( $recipient ) ) {
					$has_valid_email = true;
					break;
				}
			}

			if ( ! $has_valid_email ) {
				$this->log_error( 'Invalid recipient email: ' . $this->get_recipient() );
				return false;
			}

			return true;
		}

		/**
		 * Update placeholders with current data.
		 *
		 * @return void
		 */
		private function update_placeholders() {
			$this->placeholders['{site_title}'] = $this->get_blogname();

			if ( $this->order ) {
				$this->placeholders['{order_number}']   = $this->order->get_order_number();
				$this->placeholders['{order_date}']     = wc_format_datetime( $this->order->get_date_created() );
				$this->placeholders['{customer_name}']  = $this->order->get_formatted_billing_full_name();
				$this->placeholders['{customer_email}'] = $this->order->get_billing_email();
				$this->placeholders['{order_url}']      = $this->order->get_view_order_url();
				// get_edit_order_url() routes through HPOS when the store has it on.
				// A hardcoded post.php link 404s on those stores.
				$this->placeholders['{admin_url}'] = $this->order->get_edit_order_url();
			}

			if ( ! empty( $this->customer_data['status'] ) ) {
				$this->placeholders['{status}'] = $this->customer_data['status'];
			}

			// The facts only the trigger knows. They are placeholders rather than extra
			// paragraphs appended after the body, so an empty one simply disappears
			// instead of leaving a stray line at the end of the email.
			foreach ( [ 'reason', 'reupload_note' ] as $ddwcmpa_token ) {
				if ( ! empty( $this->customer_data[ $ddwcmpa_token ] ) ) {
					$this->placeholders[ '{' . $ddwcmpa_token . '}' ] = $this->customer_data[ $ddwcmpa_token ];
				}
			}
		}

		/**
		 * Log error message.
		 *
		 * @param string $message Error message.
		 * @param mixed  $data    Additional data.
		 * @return void
		 */
		private function log_error( $message, $data = null ) {
			$this->log_write( 'error', $message, $data );
		}

		/**
		 * Log info message.
		 *
		 * @param string $message Info message.
		 * @param mixed  $data    Additional data.
		 * @return void
		 */
		private function log_info( $message, $data = null ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				$this->log_write( 'info', $message, $data );
			}
		}

		/**
		 * Write one line to the WooCommerce log.
		 *
		 * PHP's own error log goes wherever the host has pointed it, which on a managed
		 * store is often nowhere the merchant can reach. WooCommerce already keeps
		 * a log the merchant can open under WooCommerce > Status > Logs, so these
		 * lines go there under their own source.
		 *
		 * @param string $level   Log level, as understood by WC_Logger.
		 * @param string $message Message to record.
		 * @param mixed  $data    Additional data, encoded as JSON when present.
		 * @return void
		 */
		private function log_write( $level, $message, $data = null ) {
			if ( ! function_exists( 'wc_get_logger' ) ) {
				return;
			}

			if ( null !== $data ) {
				$message .= ' Data: ' . wp_json_encode( $data );
			}

			wc_get_logger()->log( $level, $message, [ 'source' => 'ddwcmpa-email' ] );
		}

		/**
		 * Initialise settings form fields.
		 */
		public function init_form_fields() {
			$placeholder_text = sprintf(
				/* translators: %s: list of placeholders */
				__( 'Available placeholders: %s', 'devdiggers-prescription-for-woocommerce' ),
				'<code>' . implode( '</code>, <code>', array_keys( $this->placeholders ) ) . '</code>'
			);

			$this->form_fields = [
				'enabled'            => [
					'title'    => __( 'Enable/Disable', 'devdiggers-prescription-for-woocommerce' ),
					'type'     => 'checkbox',
					'label'    => __( 'Enable this email notification', 'devdiggers-prescription-for-woocommerce' ),
					'default'  => 'yes',
					'desc_tip' => __( 'Enable or disable this email notification.', 'devdiggers-prescription-for-woocommerce' ),
				],
				'recipient'          => [
					'title'       => __( 'Recipient(s)', 'devdiggers-prescription-for-woocommerce' ),
					'type'        => 'text',
					'description' => sprintf(
						/* translators: %s: WP admin email */
						__( 'Enter recipients (comma separated) for this email. Defaults to %s.', 'devdiggers-prescription-for-woocommerce' ),
						'<code>' . esc_attr( get_option( 'admin_email' ) ) . '</code>'
					),
					'placeholder' => get_option( 'admin_email' ),
					'default'     => get_option( 'admin_email' ),
					'desc_tip'    => true,
					'class'       => 'ddwcmpa-email-recipient',
				],
				'subject'            => [
					'title'       => __( 'Subject', 'devdiggers-prescription-for-woocommerce' ),
					'type'        => 'text',
					'desc_tip'    => true,
					'description' => __( 'Used for the email the store receives when a prescription arrives. Customer emails use their own subject, written into the plugin.', 'devdiggers-prescription-for-woocommerce' ) . ' ' . $placeholder_text,
					'placeholder' => $this->get_default_subject(),
					'default'     => $this->get_default_subject(),
					'class'       => 'ddwcmpa-email-subject',
				],
				'heading'            => [
					'title'       => __( 'Email heading', 'devdiggers-prescription-for-woocommerce' ),
					'type'        => 'text',
					'desc_tip'    => true,
					'description' => __( 'Used for the email the store receives when a prescription arrives. Customer emails use their own heading, written into the plugin.', 'devdiggers-prescription-for-woocommerce' ) . ' ' . $placeholder_text,
					'placeholder' => $this->get_default_heading(),
					'default'     => $this->get_default_heading(),
					'class'       => 'ddwcmpa-email-heading',
				],
				'additional_content' => [
					'title'       => __( 'Additional content', 'devdiggers-prescription-for-woocommerce' ),
					'description' => __( 'Text to appear below the main email content.', 'devdiggers-prescription-for-woocommerce' ) . ' ' . $placeholder_text,
					'css'         => 'width:400px; height: 75px;',
					'placeholder' => __( 'N/A', 'devdiggers-prescription-for-woocommerce' ),
					'type'        => 'textarea',
					'default'     => $this->get_default_additional_content(),
					'desc_tip'    => true,
					'class'       => 'ddwcmpa-email-additional-content',
				],
				'email_type'         => [
					'title'       => __( 'Email type', 'devdiggers-prescription-for-woocommerce' ),
					'type'        => 'select',
					'description' => __( 'Choose which format of email to send.', 'devdiggers-prescription-for-woocommerce' ),
					'default'     => 'html',
					'class'       => 'email_type wc-enhanced-select',
					'options'     => $this->get_email_type_options(),
					'desc_tip'    => true,
				],
				'debug_mode'         => [
					'title'       => __( 'Debug Mode', 'devdiggers-prescription-for-woocommerce' ),
					'type'        => 'checkbox',
					'label'       => __( 'Enable debug logging for email notifications', 'devdiggers-prescription-for-woocommerce' ),
					'default'     => 'no',
					'description' => __( 'Enable this to log detailed information about email sending process.', 'devdiggers-prescription-for-woocommerce' ),
					'desc_tip'    => true,
				],
			];
		}

		/**
		 * The subject actually sent.
		 *
		 * WC_Email::get_subject() reads the WooCommerce email setting and ignores
		 * whatever the caller put on the instance, so the per status subject a
		 * merchant writes in the Emails tab never reached the inbox. When the
		 * trigger supplied one it wins here; otherwise WooCommerce decides.
		 *
		 * @return string
		 */
		public function get_subject() {
			if ( empty( $this->ddwcmpa_overrides['subject'] ) ) {
				return parent::get_subject();
			}

			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core WordPress/WooCommerce hook.
			return apply_filters( 'woocommerce_email_subject_' . $this->id, $this->format_string( $this->ddwcmpa_overrides['subject'] ), $this->object, $this );
		}

		/**
		 * The heading actually sent. Same reasoning as get_subject().
		 *
		 * @return string
		 */
		public function get_heading() {
			if ( empty( $this->ddwcmpa_overrides['heading'] ) ) {
				return parent::get_heading();
			}

			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core WordPress/WooCommerce hook.
			return apply_filters( 'woocommerce_email_heading_' . $this->id, $this->format_string( $this->ddwcmpa_overrides['heading'] ), $this->object, $this );
		}

		/**
		 * Get default subject.
		 *
		 * @return string
		 */
		public function get_default_subject() {
			return '[' . get_option( 'blogname' ) . '] ' . __( 'Prescription Notification', 'devdiggers-prescription-for-woocommerce' );
		}

		/**
		 * Get default heading.
		 *
		 * @return string
		 */
		public function get_default_heading() {
			return __( 'Prescription Notification', 'devdiggers-prescription-for-woocommerce' );
		}

		/**
		 * Get default additional content.
		 *
		 * @return string
		 */
		public function get_default_additional_content() {
			return '';
		}

		/**
		 * Get content html.
		 *
		 * @access public
		 * @return string
		 */
		public function get_content_html() {
			$template_data               = $this->get_template_data();
			$template_data['plain_text'] = false;

			return wc_get_template_html(
				$this->template_html,
				$template_data,
				'',
				$this->template_base
			);
		}

		/**
		 * Get content plain.
		 *
		 * @access public
		 * @return string
		 */
		public function get_content_plain() {
			$template_data               = $this->get_template_data();
			$template_data['plain_text'] = true;

			return wc_get_template_html(
				$this->template_plain,
				$template_data,
				'',
				$this->template_base
			);
		}

		/**
		 * Get template data for email templates.
		 *
		 * @return array
		 */
		private function get_template_data() {
			return [
				'email_heading'      => $this->get_heading(),
				// Run the body through the same placeholder pass the subject and.
				// heading get. Without it a merchant writing {customer_name} in the.
				// Emails tab would see the literal token land in the customer's inbox.
				'email_message'      => array_map( [ $this, 'format_string' ], (array) $this->email_message ),
				'additional_content' => $this->get_additional_content(),
				'customer_email'     => $this->get_recipient(),
				'customer_name'      => $this->get_customer_name(),
				'order'              => $this->order,
				'order_number'       => $this->order ? $this->order->get_order_number() : '',
				'order_date'         => $this->order ? wc_format_datetime( $this->order->get_date_created() ) : '',
				'order_url'          => $this->order ? $this->order->get_view_order_url() : '',
				'admin_url'          => $this->order ? $this->order->get_edit_order_url() : '',
				'status'             => $this->customer_data['status'] ?? '',
				'blogname'           => $this->get_blogname(),
				'sent_to_admin'      => $this->is_sent_to_admin(),
				'email'              => $this,
			];
		}

		/**
		 * Get customer name.
		 *
		 * @return string
		 */
		private function get_customer_name() {
			if ( $this->order ) {
				return $this->order->get_formatted_billing_full_name();
			}

			if ( ! empty( $this->customer_data['name'] ) ) {
				return $this->customer_data['name'];
			}

			return '';
		}

		/**
		 * Check if email is sent to admin.
		 *
		 * @return bool
		 */
		private function is_sent_to_admin() {
			$admin_email = get_option( 'admin_email' );
			$recipient   = $this->get_recipient();

			return $recipient === $admin_email || strpos( $recipient, $admin_email ) !== false;
		}

		/**
		 * Get formatted plain text message.
		 *
		 * @return string
		 */
		public function get_formatted_plain_message() {
			if ( empty( $this->email_message ) ) {
				return '';
			}

			return implode( "\n\n", $this->email_message );
		}
	}
}
