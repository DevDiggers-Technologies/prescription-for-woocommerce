<?php
/**
 * Prescription security helper.
 *
 * Prescriptions are protected health information. They must never sit in the
 * public uploads tree where anyone holding (or guessing) the URL can read them.
 * This helper keeps every prescription file inside a deny-all directory and
 * serves it back through a capability checked endpoint.
 *
 * @author DevDiggers
 * @package DevDiggers Prescription for WooCommerce
 * @version 1.0.0
 */

namespace DDWCMedicalPrescriptionAttachment\Helper;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCMPA_Security_Helper' ) ) {
	/**
	 * Prescription security helper class.
	 */
	class DDWCMPA_Security_Helper {
		/**
		 * Folder name prefix, relative to the uploads base directory.
		 *
		 * The real folder gets a per site random suffix appended. Apache and IIS
		 * honour the deny files written into it, but nginx ignores both, so the
		 * folder name itself has to be unguessable for the files to stay private on
		 * every server. This is the same posture WooCommerce core uses for its own
		 * protected uploads.
		 *
		 * @var string
		 */
		const VAULT_DIRNAME = 'ddwcmpa-prescriptions';

		/**
		 * Option holding the random suffix for this site's vault folder.
		 *
		 * @var string
		 */
		const VAULT_KEY_OPTION = '_ddwcmpa_vault_key';

		/**
		 * Attachment meta key flagging a file as vaulted.
		 *
		 * @var string
		 */
		const PROTECTED_META = '_ddwcmpa_protected';

		/**
		 * Query variable used by the download endpoint.
		 *
		 * @var string
		 */
		const FILE_QUERY_VAR = 'ddwcmpa_file';

		/**
		 * Capability required to review any customer's prescription.
		 *
		 * WooCommerce's own: administrators and shop managers review. A dedicated
		 * Pharmacist role is part of Pro.
		 *
		 * @var string
		 */
		const MANAGE_CAP = 'manage_woocommerce';

		/**
		 * Whether the uploads directory is currently redirected into the vault.
		 *
		 * @var bool
		 */
		protected static $redirecting = false;

		/**
		 * Get the vault directory path and URL.
		 *
		 * @return array{path:string,url:string}
		 */
		public static function get_vault_dir() {
			return self::build_vault_dir( wp_get_upload_dir() );
		}

		/**
		 * The vault paths that sit inside a given uploads directory.
		 *
		 * Split out from get_vault_dir() on purpose. filter_upload_dir() runs on the
		 * upload_dir filter, and calling get_vault_dir() from inside it re-entered
		 * wp_get_upload_dir(), which fires upload_dir again: every prescription
		 * upload recursed until PHP ran out of memory and returned an empty body.
		 * The filter already has the uploads array, so it hands it straight here.
		 *
		 * @param array $uploads Uploads directory data, as returned by wp_get_upload_dir().
		 * @return array{path:string,url:string}
		 */
		protected static function build_vault_dir( $uploads ) {
			$folder = self::VAULT_DIRNAME . '-' . self::get_vault_key();

			return [
				'path' => trailingslashit( $uploads['basedir'] ) . $folder,
				'url'  => trailingslashit( $uploads['baseurl'] ) . $folder,
			];
		}

		/**
		 * The random suffix that makes this site's vault folder unguessable.
		 *
		 * Generated once and kept forever, because it is part of every stored file path.
		 *
		 * @return string
		 */
		public static function get_vault_key() {
			$key = get_option( self::VAULT_KEY_OPTION );

			if ( empty( $key ) ) {
				$key = wp_generate_password( 32, false, false );

				update_option( self::VAULT_KEY_OPTION, $key, false );
			}

			return $key;
		}

		/**
		 * Create the vault directory and drop the server level deny rules in it.
		 *
		 * Safe to call repeatedly. Apache reads .htaccess, IIS reads web.config and
		 * nginx ignores both, so the endpoint capability check stays the real gate
		 * and these files are defence in depth.
		 *
		 * @return bool True when the directory exists and is protected.
		 */
		public static function prepare_vault() {
			$vault = self::get_vault_dir();

			if ( ! wp_mkdir_p( $vault['path'] ) ) {
				return false;
			}

			$guards = [
				'.htaccess'  => "Order Allow,Deny\nDeny from all\n<IfModule mod_authz_core.c>\n\tRequire all denied\n</IfModule>\n",
				'web.config' => "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<configuration>\n\t<system.webServer>\n\t\t<authorization>\n\t\t\t<deny users=\"*\" />\n\t\t</authorization>\n\t</system.webServer>\n</configuration>\n",
				'index.php'  => "<?php\n// Silence is golden.\n",
			];

			foreach ( $guards as $filename => $contents ) {
				$file = trailingslashit( $vault['path'] ) . $filename;

				if ( ! file_exists( $file ) ) {
					// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents, WordPress.PHP.NoSilencedErrors.Discouraged -- Writing our own guard files inside the uploads directory. A host that forbids the write must not fatal the request; the caller checks the directory afterwards.
					@file_put_contents( $file, $contents );
				}
			}

			return true;
		}

		/**
		 * Point WordPress uploads at the vault for the duration of one upload.
		 *
		 * @return void
		 */
		public static function start_vault_upload() {
			if ( self::$redirecting ) {
				return;
			}

			self::prepare_vault();

			self::$redirecting = true;

			add_filter( 'upload_dir', [ __CLASS__, 'filter_upload_dir' ] );
		}

		/**
		 * Stop redirecting uploads into the vault.
		 *
		 * @return void
		 */
		public static function end_vault_upload() {
			if ( ! self::$redirecting ) {
				return;
			}

			remove_filter( 'upload_dir', [ __CLASS__, 'filter_upload_dir' ] );

			self::$redirecting = false;
		}

		/**
		 * Rewrite the uploads directory to the vault.
		 *
		 * @param array $dirs Upload directory data.
		 * @return array
		 */
		public static function filter_upload_dir( $dirs ) {
			$vault = self::build_vault_dir( $dirs );

			$dirs['path']   = $vault['path'];
			$dirs['url']    = $vault['url'];
			$dirs['subdir'] = '';

			return $dirs;
		}

		/**
		 * Flag an attachment as a vaulted prescription.
		 *
		 * @param int $attachment_id Attachment ID.
		 * @return void
		 */
		public static function mark_protected( $attachment_id ) {
			update_post_meta( absint( $attachment_id ), self::PROTECTED_META, 1 );
		}

		/**
		 * Is this attachment a vaulted prescription?
		 *
		 * @param int $attachment_id Attachment ID.
		 * @return bool
		 */
		public static function is_protected( $attachment_id ) {
			return (bool) get_post_meta( absint( $attachment_id ), self::PROTECTED_META, true );
		}

		/**
		 * Get a viewable URL for a prescription attachment.
		 *
		 * Vaulted files go through the gated endpoint. Files uploaded before the
		 * vault existed keep working on their original URL until the migration
		 * routine moves them.
		 *
		 * @param int        $attachment_id Attachment ID.
		 * @param int|string $order_id      Order the attachment belongs to, when known.
		 * @return string
		 */
		public static function get_file_url( $attachment_id, $order_id = '' ) {
			$attachment_id = absint( $attachment_id );

			if ( ! self::is_protected( $attachment_id ) ) {
				return (string) wp_get_attachment_url( $attachment_id );
			}

			$args = [ self::FILE_QUERY_VAR => $attachment_id ];

			if ( ! empty( $order_id ) ) {
				$args['order'] = absint( $order_id );
			}

			return add_query_arg( $args, home_url( '/' ) );
		}

		/**
		 * Serve a vaulted prescription to a viewer who is allowed to see it.
		 *
		 * Hooked early so the file streams before any theme output starts.
		 *
		 * @return void
		 */
		public static function maybe_serve_file() {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read only file request, authorised by capability and ownership below.
			if ( empty( $_GET[ self::FILE_QUERY_VAR ] ) ) {
				return;
			}

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read only file request.
			$attachment_id = absint( wp_unslash( $_GET[ self::FILE_QUERY_VAR ] ) );
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read only file request.
			$order_id = ! empty( $_GET['order'] ) ? absint( wp_unslash( $_GET['order'] ) ) : 0;

			if ( ! self::can_view( $attachment_id, $order_id ) ) {
				wp_die(
					esc_html__( 'You are not allowed to view this prescription.', 'devdiggers-prescription-for-woocommerce' ),
					esc_html__( 'Access denied', 'devdiggers-prescription-for-woocommerce' ),
					[ 'response' => 403 ]
				);
			}

			$file = get_attached_file( $attachment_id );

			if ( empty( $file ) || ! file_exists( $file ) ) {
				wp_die(
					esc_html__( 'Prescription file not found.', 'devdiggers-prescription-for-woocommerce' ),
					esc_html__( 'Not found', 'devdiggers-prescription-for-woocommerce' ),
					[ 'response' => 404 ]
				);
			}

			$mime = get_post_mime_type( $attachment_id );

			nocache_headers();
			header( 'Content-Type: ' . ( $mime ? $mime : 'application/octet-stream' ) );
			header( 'Content-Length: ' . filesize( $file ) );
			header( 'Content-Disposition: inline; filename="' . rawurlencode( basename( $file ) ) . '"' );
			header( 'X-Content-Type-Options: nosniff' );
			header( 'X-Robots-Tag: noindex, nofollow' );
			header( 'Referrer-Policy: no-referrer' );

			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile -- Streaming a local file we just authorised. WP_Filesystem would read the whole prescription into memory before sending it.
			readfile( $file );
			exit();
		}

		/**
		 * Can the current viewer see this prescription?
		 *
		 * @param int $attachment_id Attachment ID.
		 * @param int $order_id      Order ID the link was generated for.
		 * @return bool
		 */
		public static function can_view( $attachment_id, $order_id = 0 ) {
			if ( empty( $attachment_id ) ) {
				return false;
			}

			// Reviewers see everything.
			if ( current_user_can( self::MANAGE_CAP ) ) {
				return true;
			}

			// The customer who owns the order the file is attached to.
			if ( ! empty( $order_id ) ) {
				$order = wc_get_order( $order_id );

				if ( $order ) {
					$attachments = DDWCMPA_Prescription_Helper::ddwcmpa_get_array_meta( $order, '_ddwcmpa_attachments' );

					if ( in_array( $attachment_id, array_map( 'absint', $attachments ), true ) ) {
						$customer_id = $order->get_customer_id();

						if ( $customer_id && get_current_user_id() === $customer_id ) {
							return true;
						}
					}
				}
			}

			// The customer who uploaded it, whether or not it is on an order yet.
			$uploader = (int) get_post_field( 'post_author', $attachment_id );

			if ( $uploader && get_current_user_id() === $uploader ) {
				return true;
			}

			// A guest still holding the upload in their own checkout session.
			if ( ! is_user_logged_in() && function_exists( 'WC' ) && WC()->session ) {
				$session_attachments = (array) WC()->session->get( 'ddwcmpa_attachments' );

				if ( in_array( $attachment_id, array_map( 'absint', $session_attachments ), true ) ) {
					return true;
				}
			}

			return (bool) apply_filters( 'ddwcmpa_can_view_prescription', false, $attachment_id, $order_id );
		}

		/**
		 * Keep vaulted prescriptions out of the media library browser.
		 *
		 * @param array $query Media query arguments.
		 * @return array
		 */
		public static function filter_media_library_query( $query ) {
			if ( current_user_can( 'manage_options' ) ) {
				return $query;
			}

			$meta_query   = ! empty( $query['meta_query'] ) ? $query['meta_query'] : [];
			$meta_query[] = [
				'key'     => self::PROTECTED_META,
				'compare' => 'NOT EXISTS',
			];

			$query['meta_query'] = $meta_query;

			return $query;
		}
	}
}
