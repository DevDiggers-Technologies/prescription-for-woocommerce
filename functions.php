<?php
/**
 * Plugin Name: Prescription for WooCommerce
 * Description: Let customers upload a medical prescription at checkout, hold the order until a pharmacist approves it, and keep every file in a private, access-checked folder.
 * Plugin URI: https://devdiggers.com/product/woocommerce-medical-prescription-attachment/
 * Author: DevDiggers
 * Author URI: https://devdiggers.com/
 * Version: 1.0.0
 * Text Domain: prescription-for-woocommerce
 * Domain Path: /i18n
 * Requires at least: 6.5
 * Tested up to: 7.1
 * Requires PHP: 7.4
 * WC requires at least: 9.0
 * WC tested up to: 11.1
 * DevDiggersPrefix: ddwcmpa
 * Requires Plugins: woocommerce
 * License: GPLv3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package Prescription for WooCommerce
 */

// ddwcmpa: Prescription for WooCommerce.

use DDWCMedicalPrescriptionAttachment\Includes\DDWCMPA_File_Handler;

defined( 'ABSPATH' ) || exit();

/**
 * Point the plugin constants at this directory.
 *
 * @return void
 */
function ddwcmpa_free_define_constants() {
	defined( 'DDWCMPA_PLUGIN_FILE' ) || define( 'DDWCMPA_PLUGIN_FILE', plugin_dir_path( __FILE__ ) );
	defined( 'DDWCMPA_PLUGIN_URL' ) || define( 'DDWCMPA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! class_exists( 'DDWCMPA_Free_Init' ) ) {
	/**
	 * Free Init class.
	 */
	final class DDWCMPA_Free_Init {
		/**
		 * The single instance of this class.
		 *
		 * @var DDWCMPA_Free_Init|null
		 */
		private static $instance = null;

		/**
		 * Class constructor.
		 */
		public function __construct() {
			add_action( 'init', [ $this, 'ddwcmpa_init' ] );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), [ $this, 'ddwcmpa_plugin_settings_link' ] );
			add_filter( 'plugin_row_meta', [ $this, 'ddwcmpa_plugin_row_meta' ], 10, 2 );
		}

		/**
		 * Create a plugin instance.
		 *
		 * @return static
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();

				/**
				 * Fires when the main plugin instance is loaded.
				 *
				 * @since 1.0.0
				 */
				do_action( 'ddwcmpa_loaded' );
			}

			return self::$instance;
		}

		/**
		 * Init function.
		 *
		 * @return void
		 */
		public function ddwcmpa_init() {
			// WordPress.org loads plugin translations automatically.
			if ( ! class_exists( 'WooCommerce' ) ) {
				add_action(
					'admin_notices',
					function () {
						?>
						<div class="notice notice-error">
							<p>
								<?php
								/* translators: %1$s: opening link tag, %2$s: closing link tag */
								printf( esc_html__( 'Prescription for WooCommerce is activated but not effective. It requires %1$sWooCommerce%2$s in order to work.', 'prescription-for-woocommerce' ), '<a href="' . esc_url( 'https://wordpress.org/plugins/woocommerce/' ) . '" target="_blank">', '</a>' );
								?>
							</p>
						</div>
						<?php
					}
				);

				return;
			}

			require_once DDWCMPA_PLUGIN_FILE . 'autoload/autoload.php';
			new DDWCMPA_File_Handler();

			// Initialize review notice if the framework is available.
			if ( class_exists( '\DevDiggers\Framework\Includes\DDFW_Review_Notice' ) ) {
				new \DevDiggers\Framework\Includes\DDFW_Review_Notice(
					[
						'plugin_name'   => esc_html__( 'Prescription for WooCommerce', 'prescription-for-woocommerce' ),
						'plugin_prefix' => 'ddwcmpa',
						'review_url'    => 'https://wordpress.org/support/plugin/prescription-for-woocommerce/reviews/#new-post',
					]
				);
			}
		}

		/**
		 * Plugin settings link.
		 *
		 * @param array $links Links array.
		 * @return array
		 */
		public function ddwcmpa_plugin_settings_link( $links ) {
			ob_start();
			?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=ddwcmpa-dashboard' ) ); ?>"><?php esc_html_e( 'Dashboard', 'prescription-for-woocommerce' ); ?></a>
			|
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=ddwcmpa-dashboard&menu=configuration' ) ); ?>"><?php esc_html_e( 'Configuration', 'prescription-for-woocommerce' ); ?></a>
			|
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=ddwcmpa-dashboard&setup-wizard=true' ) ); ?>"><?php esc_html_e( 'Setup Wizard', 'prescription-for-woocommerce' ); ?></a>
			|
			<a href="<?php echo esc_url( 'https://devdiggers.com/product/woocommerce-medical-prescription-attachment/' ); ?>" style="color: #0256ff; font-weight: bold;" target="_blank"><?php esc_html_e( 'Upgrade to Pro', 'prescription-for-woocommerce' ); ?></a>
			<?php
			array_unshift( $links, ob_get_clean() );

			return $links;
		}

		/**
		 * Plugin row meta links.
		 *
		 * @param array  $links Links.
		 * @param string $file  Plugin file.
		 * @return array
		 */
		public function ddwcmpa_plugin_row_meta( $links, $file ) {
			if ( plugin_basename( __FILE__ ) === $file ) {
				$row_meta = [
					'support'       => '<a href="https://devdiggers.com/contact/" aria-label="' . esc_attr__( 'Support', 'prescription-for-woocommerce' ) . '">' . esc_html__( 'Support', 'prescription-for-woocommerce' ) . '</a>',
					'documentation' => '<a href="https://docs.devdiggers.com/woocommerce-medical-prescription-attachment/" aria-label="' . esc_attr__( 'Documentation', 'prescription-for-woocommerce' ) . '">' . esc_html__( 'Documentation', 'prescription-for-woocommerce' ) . '</a>',
					'review'        => '<a href="https://wordpress.org/support/plugin/prescription-for-woocommerce/reviews/#new-post" target="_blank" title="' . esc_attr__( 'Review', 'prescription-for-woocommerce' ) . '" aria-label="' . esc_attr__( 'Review', 'prescription-for-woocommerce' ) . '"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 192 32" height="10"><path d="M16 26.534L6.111 32 8 20.422l-8-8.2 11.056-1.688L16 0l4.944 10.534L32 12.223l-8 8.2L25.889 32zm40 0L46.111 32 48 20.422l-8-8.2 11.056-1.688L56 0l4.944 10.534L72 12.223l-8 8.2L65.889 32zm40 0L86.111 32 88 20.422l-8-8.2 11.056-1.688L96 0l4.944 10.534L112 12.223l-8 8.2L105.889 32zm40 0L126.111 32 128 20.422l-8-8.2 11.056-1.688L136 0l4.944 10.534L152 12.223l-8 8.2L145.889 32zm40 0L166.111 32 168 20.422l-8-8.2 11.056-1.688L176 0l4.944 10.534L192 12.223l-8 8.2L185.889 32z" fill="#F5A623" fill-rule="evenodd"/></svg></a>',
				];

				$links = array_merge( $links, $row_meta );
			}

			return $links;
		}
	}
}

// Load the Free version and the DevDiggers Framework only when the Pro plugin is not active.
add_action(
	'plugins_loaded',
	function () {
		if ( class_exists( 'DDWCMPA_Init' ) ) {
			return;
		}

		ddwcmpa_free_define_constants();

		DDWCMPA_Free_Init::get_instance();

		// Load DevDiggers Framework if not loaded already.
		if ( ! defined( 'DDFW_LOADED' ) && file_exists( DDWCMPA_PLUGIN_FILE . 'devdiggers-framework/init.php' ) ) {
			$should_load = true;

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin routing input.
			if ( ! empty( $_GET['page'] ) ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin routing input.
				$current_page = sanitize_text_field( wp_unslash( $_GET['page'] ) );
				$prefix       = explode( '-', $current_page )[0];

				if ( 0 === strpos( $prefix, 'ddwc' ) || 0 === strpos( $prefix, 'ddwp' ) ) {
					$pro_class  = strtoupper( $prefix ) . '_Init';
					$free_class = strtoupper( $prefix ) . '_Free_Init';

					// Yield to a sibling DevDiggers plugin except on this plugin's own pages.
					if ( class_exists( $free_class ) && ! class_exists( $pro_class ) && 'ddwcmpa' !== $prefix ) {
						$should_load = false;
					}
				}
			}

			if ( $should_load ) {
				require DDWCMPA_PLUGIN_FILE . 'devdiggers-framework/init.php';
			}
		}
	},
	10
);

register_activation_hook(
	__FILE__,
	function () {
		ddwcmpa_free_define_constants();

		// Activation runs before init, so the autoloader has not been required yet.
		require_once DDWCMPA_PLUGIN_FILE . 'autoload/autoload.php';

		DDWCMedicalPrescriptionAttachment\Includes\DDWCMPA_Install::activate();
	}
);

// HPOS and Cart/Checkout Blocks compatibility.
add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
		}
	}
);
