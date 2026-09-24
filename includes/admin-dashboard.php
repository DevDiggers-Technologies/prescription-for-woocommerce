<?php
/**
 * This file handles all admin dashboard functionalities.
 *
 * @package Prescription for WooCommerce
 * @version 1.0.0
 */

namespace DDWCMedicalPrescriptionAttachment\Includes;

use DevDiggers\Framework\Includes\DDFW_Plugin_Dashboard;
use DevDiggers\Framework\Includes\DDFW_Assets;
use DevDiggers\Framework\Includes\DDFW_SVG;
use DDWCMedicalPrescriptionAttachment\Templates\Admin;
use DDWCMedicalPrescriptionAttachment\Helper\DDWCMPA_Security_Helper;
use DDWCMedicalPrescriptionAttachment\Helper\DDWCMPA_Icon_Helper;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCMPA_Admin_Dashboard' ) ) {
	/**
	 * Admin Dashboard Class
	 */
	class DDWCMPA_Admin_Dashboard {
		/**
		 * Configuration Variable
		 *
		 * @var array
		 */
		protected $ddwcmpa_configuration;

		/**
		 * Dashboard Variable
		 *
		 * @var DDFW_Plugin_Dashboard
		 */
		protected $dashboard;

		/**
		 * Construct
		 *
		 * @param array $ddwcmpa_configuration Plugin configuration.
		 */
		public function __construct( $ddwcmpa_configuration ) {
			$this->ddwcmpa_configuration = $ddwcmpa_configuration;
			$this->ddwcmpa_add_dashboard_menu();
			add_action( 'admin_enqueue_scripts', [ $this, 'ddwcmpa_enqueue_admin_scripts' ] );
			add_filter( 'admin_footer_text', [ $this, 'ddwcmpa_set_admin_footer_text' ], 99 );
		}

		/**
		 * Replace the WordPress footer credit on this plugin's own screens.
		 *
		 * @param  string $footer_text Text to be rendered in the footer.
		 * @return string
		 */
		public function ddwcmpa_set_admin_footer_text( $footer_text ) {
			$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

			if ( empty( $screen->base ) || 'devdiggers-plugins_page_ddwcmpa-dashboard' !== $screen->base ) {
				return $footer_text;
			}

			$stars = '<a href="https://wordpress.org/support/plugin/prescription-for-woocommerce/reviews/#new-post" target="_blank" title="' . esc_attr__( 'Review', 'prescription-for-woocommerce' ) . '" aria-label="' . esc_attr__( 'Review', 'prescription-for-woocommerce' ) . '"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 192 32" height="10"><path d="M16 26.534L6.111 32 8 20.422l-8-8.2 11.056-1.688L16 0l4.944 10.534L32 12.223l-8 8.2L25.889 32zm40 0L46.111 32 48 20.422l-8-8.2 11.056-1.688L56 0l4.944 10.534L72 12.223l-8 8.2L65.889 32zm40 0L86.111 32 88 20.422l-8-8.2 11.056-1.688L96 0l4.944 10.534L112 12.223l-8 8.2L105.889 32zm40 0L126.111 32 128 20.422l-8-8.2 11.056-1.688L136 0l4.944 10.534L152 12.223l-8 8.2L145.889 32zm40 0L166.111 32 168 20.422l-8-8.2 11.056-1.688L176 0l4.944 10.534L192 12.223l-8 8.2L185.889 32z" fill="#F5A623" fill-rule="evenodd"/></svg></a>';

			return sprintf(
				/* translators: %s: a link showing five stars. */
				esc_html__( 'If you like Prescription for WooCommerce, please leave us a %s rating. We really appreciate it!', 'prescription-for-woocommerce' ),
				$stars
			);
		}

		/**
		 * Add Admin menu function
		 *
		 * @return void
		 */
		public function ddwcmpa_add_dashboard_menu() {
			ob_start();
			?>
			<svg class="ddwcmpa-mark" width="32" height="32" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg">
				<path class="ddwcmpa-mark-bg" d="M10.5 3.2h7.1L25.8 11.4v15.1a3.3 3.3 0 0 1-3.3 3.3h-12a3.3 3.3 0 0 1-3.3-3.3v-20a3.3 3.3 0 0 1 3.3-3.3Z"/>
				<path class="ddwcmpa-mark-line" d="M10.5 3.2h7.1L25.8 11.4v15.1a3.3 3.3 0 0 1-3.3 3.3h-12a3.3 3.3 0 0 1-3.3-3.3v-20a3.3 3.3 0 0 1 3.3-3.3Z"/>
				<path class="ddwcmpa-mark-line" d="M17.4 3.4v6.2a2 2 0 0 0 2 2h6.2"/>
				<path class="ddwcmpa-mark-line" d="M12.4 25.2v-9.4h3.3a2.7 2.7 0 0 1 0 5.4h-3.3"/>
				<path class="ddwcmpa-mark-line" d="m15.2 21.2 5.4 4M20.8 20.9l-5.5 4.4"/>
			</svg>
			<?php esc_html_e( 'Prescriptions', 'prescription-for-woocommerce' ); ?>
			<?php
			$plugin_name = ob_get_clean();

			$args = [
				'page_title'              => esc_html__( 'Prescriptions', 'prescription-for-woocommerce' ),
				'menu_title'              => esc_html__( 'Prescriptions', 'prescription-for-woocommerce' ),
				'slug'                    => 'ddwcmpa-dashboard',
				'plugin_name'             => $plugin_name,
				'upgrade_url'             => 'https://devdiggers.com/product/woocommerce-medical-prescription-attachment/',
				'screen_options_callback' => [ $this, 'add_screen_options' ],
				'menus'                   => [
					'dashboard'     => [
						'label'    => esc_html__( 'Dashboard', 'prescription-for-woocommerce' ),
						'callback' => [ $this, 'ddwcmpa_get_dashboard_template' ],
						'layout'   => 'full-width',
					],
					'orders'        => [
						'label'    => esc_html__( 'Orders', 'prescription-for-woocommerce' ),
						'callback' => [ $this, 'ddwcmpa_get_orders_template' ],
						'layout'   => 'full-width',
					],
					'review'        => [
						'label'    => esc_html__( 'Review', 'prescription-for-woocommerce' ),
						'callback' => [ $this, 'ddwcmpa_get_review_template' ],
						'layout'   => 'full-width',
					],
					'configuration' => [
						'label'  => esc_html__( 'Configuration', 'prescription-for-woocommerce' ),
						'layout' => 'sidebar',
						'tabs'   => [
							'general'             => [
								'label'    => esc_html__( 'General', 'prescription-for-woocommerce' ),
								'icon'     => DDFW_SVG::get_svg_icon( 'general', true, [ 'size' => 18 ] ),
								'callback' => [ $this, 'ddwcmpa_get_general_configuration_template' ],
							],
							'design'              => [
								'label'    => esc_html__( 'Design', 'prescription-for-woocommerce' ),
								'icon'     => $this->ddwcmpa_get_tab_icon( 'image' ),
								'callback' => [ $this, 'ddwcmpa_get_design_configuration_template' ],
							],
							'prescription-rules'  => [
								'label'    => esc_html__( 'Prescription Rules', 'prescription-for-woocommerce' ),
								'icon'     => $this->ddwcmpa_get_tab_icon( 'prescription' ),
								'callback' => [ $this, 'ddwcmpa_get_prescription_rules_configuration_template' ],
							],
							'review-workflow'     => [
								'label'    => esc_html__( 'Review Workflow', 'prescription-for-woocommerce' ),
								'icon'     => $this->ddwcmpa_get_tab_icon( 'check' ),
								'callback' => [ $this, 'ddwcmpa_get_review_workflow_configuration_template' ],
							],
							'customer-experience' => [
								'label'    => esc_html__( 'Customer Experience', 'prescription-for-woocommerce' ),
								'icon'     => $this->ddwcmpa_get_tab_icon( 'user' ),
								'callback' => [ $this, 'ddwcmpa_get_customer_experience_configuration_template' ],
							],
							'emails'              => [
								'label'    => esc_html__( 'Emails', 'prescription-for-woocommerce' ),
								'icon'     => $this->ddwcmpa_get_tab_icon( 'email' ),
								'callback' => [ $this, 'ddwcmpa_get_emails_configuration_template' ],
							],
							'compliance'          => [
								'label'    => esc_html__( 'Compliance', 'prescription-for-woocommerce' ),
								'icon'     => $this->ddwcmpa_get_tab_icon( 'shield' ),
								'callback' => [ $this, 'ddwcmpa_get_compliance_configuration_template' ],
							],
						],
					],
				],
			];

			$this->dashboard = new DDFW_Plugin_Dashboard( $args );
		}

		/**
		 * Add screen options for the admin dashboard
		 *
		 * @return void
		 */
		public function add_screen_options() {
			// The list table has to be built before add_screen_option() runs, or the
			// per page setting is never registered. Nothing else reads this global.
			global $ddwcmpa_list_table;

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read only admin routing parameter.
			$current_menu = ! empty( $_GET['menu'] ) ? sanitize_title( wp_unslash( $_GET['menu'] ) ) : 'dashboard';

			$args = [
				'label'    => esc_html__( 'Results Per Page', 'prescription-for-woocommerce' ),
				'default'  => 20,
				'hidden'   => 'id',
				'sanitize' => 'intval',
			];

			switch ( $current_menu ) {
				case 'orders':
					$args['option']     = 'ddwcmpa_orders_per_page';
					$ddwcmpa_list_table = new Admin\Orders\DDWCMPA_Orders_List_Template();
					add_screen_option( 'per_page', $args );
					break;
			}
		}

		/**
		 * Dashboard Template Submenu
		 *
		 * @return void
		 */
		public function ddwcmpa_get_dashboard_template() {
			new Admin\Dashboard\DDWCMPA_Dashboard_Template( $this->ddwcmpa_configuration );
		}

		/**
		 * Orders Template Submenu
		 *
		 * @return void
		 */
		public function ddwcmpa_get_orders_template() {
			$obj = new Admin\Orders\DDWCMPA_Orders_List_Template();

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read only admin routing parameter.
			$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read only admin routing parameter.
			$menu = isset( $_GET['menu'] ) ? sanitize_text_field( wp_unslash( $_GET['menu'] ) ) : '';
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read only admin routing parameter.
			$prescription_status = isset( $_GET['prescription_status'] ) ? sanitize_key( wp_unslash( $_GET['prescription_status'] ) ) : '';
			?>
			<?php // The framework only lifts the search box into the title row when the heading opens the card, so it sits outside the form. ?>
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Orders with Prescriptions', 'prescription-for-woocommerce' ); ?></h1>
			<hr class="wp-header-end" />
			<form method="GET">
				<input type="hidden" name="page" value="<?php echo esc_attr( $page ); ?>" />
				<input type="hidden" name="menu" value="<?php echo esc_attr( $menu ); ?>" />
				<input type="hidden" name="prescription_status" value="<?php echo esc_attr( $prescription_status ); ?>" />
				<?php
				$obj->prepare_items();
				$obj->views();
				$obj->search_box( esc_html__( 'Search', 'prescription-for-woocommerce' ), 'search-id' );
				$obj->display();
				?>
			</form>
			<?php
		}

		/**
		 * Reviewer workspace submenu.
		 *
		 * The workspace is a Pro screen. Free keeps the tab so it can be found, and
		 * shows what it does; decisions are still made on the order edit screen.
		 *
		 * @return void
		 */
		public function ddwcmpa_get_review_template() {
			ddfw_upgrade_to_pro_section(
				[
					'image_url'     => DDWCMPA_PLUGIN_URL . 'assets/images/pro/review-workspace.webp',
					'heading'       => esc_html__( 'Clear the whole queue from one screen with Pro', 'prescription-for-woocommerce' ),
					'description'   => esc_html__( 'In Free each prescription is reviewed on its own order screen. The Pro review workspace puts the file, the details and the decision side by side and moves straight on to the next one.', 'prescription-for-woocommerce' ),
					'list_features' => [
						esc_html__( 'A document viewer with zoom and rotate, so a phone photo taken sideways is still readable', 'prescription-for-woocommerce' ),
						esc_html__( 'Approve, reject or ask for more, then land on the next prescription without a page load', 'prescription-for-woocommerce' ),
						esc_html__( 'Approve or reject a batch of orders at once from the Orders screen', 'prescription-for-woocommerce' ),
						esc_html__( 'Warnings before you decide: a file uploaded on another order, a blocked prescriber, a lapsed prescription', 'prescription-for-woocommerce' ),
						esc_html__( 'Send the prescriber a private link to confirm they wrote it, answered without an account', 'prescription-for-woocommerce' ),
						esc_html__( 'Assign prescriptions to a named pharmacist, with an overdue badge when one waits too long', 'prescription-for-woocommerce' ),
						esc_html__( 'One click saved replies, a full audit trail and a log of who opened each file', 'prescription-for-woocommerce' ),
					],
					'upgrade_url'   => 'https://devdiggers.com/product/woocommerce-medical-prescription-attachment/',
				]
			);
		}

		/**
		 * One of the plugin's own tab icons.
		 *
		 * The framework ships a handful of generic icons only, so the tabs that have
		 * no counterpart there draw from the plugin's own set.
		 *
		 * @param string $name Icon name.
		 * @return string
		 */
		protected function ddwcmpa_get_tab_icon( $name ) {
			return DDWCMPA_Icon_Helper::get( $name, [ 'size' => 18 ] );
		}

		/**
		 * Get General Configuration Template
		 *
		 * @return void
		 */
		public function ddwcmpa_get_general_configuration_template() {
			new Admin\Configuration\DDWCMPA_Configuration_Template( $this->ddwcmpa_configuration );
		}

		/**
		 * Get Prescription Rules Configuration Template
		 *
		 * @return void
		 */
		public function ddwcmpa_get_prescription_rules_configuration_template() {
			new Admin\Configuration\DDWCMPA_Prescription_Rules_Configuration_Template( $this->ddwcmpa_configuration );
		}

		/**
		 * Get Review Workflow Configuration Template
		 *
		 * @return void
		 */
		public function ddwcmpa_get_review_workflow_configuration_template() {
			new Admin\Configuration\DDWCMPA_Review_Workflow_Configuration_Template( $this->ddwcmpa_configuration );
		}

		/**
		 * Get Customer Experience Configuration Template
		 *
		 * @return void
		 */
		public function ddwcmpa_get_customer_experience_configuration_template() {
			new Admin\Configuration\DDWCMPA_Customer_Experience_Configuration_Template( $this->ddwcmpa_configuration );
		}

		/**
		 * Get Design Configuration Template
		 *
		 * @return void
		 */
		public function ddwcmpa_get_design_configuration_template() {
			new Admin\Configuration\DDWCMPA_Design_Configuration_Template( $this->ddwcmpa_configuration );
		}

		/**
		 * Get Emails Configuration Template
		 *
		 * @return void
		 */
		public function ddwcmpa_get_emails_configuration_template() {
			new Admin\Configuration\DDWCMPA_Emails_Configuration_Template( $this->ddwcmpa_configuration );
		}

		/**
		 * Get Compliance Configuration Template
		 *
		 * @return void
		 */
		public function ddwcmpa_get_compliance_configuration_template() {
			new Admin\Configuration\DDWCMPA_Compliance_Configuration_Template( $this->ddwcmpa_configuration );
		}

		/**
		 * Enqueue admin scripts function
		 *
		 * @return void
		 */
		public function ddwcmpa_enqueue_admin_scripts() {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read only admin routing parameter.
			$page = ! empty( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read only admin routing parameter.
			$action = ! empty( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';

			// One order, open for editing: HPOS routes it through wc-orders with an
			// edit action, the legacy post table through post.php.
			$is_order_edit = ( 'wc-orders' === $page && 'edit' === $action )
				|| ( 'shop_order' === get_post_type() && 'post.php' === $GLOBALS['pagenow'] );

			// The orders list: wc-orders on HPOS, edit.php for the shop_order post type on
			// the posts table. The status column's badges need the stylesheet on both.
			$is_orders_list = ( 'wc-orders' === $page && ! $is_order_edit )
				|| ( 'edit.php' === $GLOBALS['pagenow'] && 'shop_order' === ( isset( $_GET['post_type'] ) ? sanitize_key( wp_unslash( $_GET['post_type'] ) ) : '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read only admin routing parameter.

			if ( ! $this->dashboard->is_a_plugin_page() && ! $is_orders_list && ! $is_order_edit ) {
				return;
			}

			// The order edit screen gets its own pair. It shows the review panel and
			// nothing else, so there is no reason to make it download the dashboard.
			$handle = $is_order_edit ? 'ddwcmpa-order' : 'ddwcmpa-admin';
			$bundle = $is_order_edit ? 'order' : 'admin';

			wp_enqueue_style( $handle . '-style', DDWCMPA_PLUGIN_URL . 'assets/css/' . $bundle . '.css', [ DDFW_Assets::$framework_css_handle ], filemtime( DDWCMPA_PLUGIN_FILE . 'assets/css/' . $bundle . '.css' ) );

			wp_enqueue_script( DDFW_Assets::$framework_js_handle );

			wp_enqueue_script( $handle . '-script', DDWCMPA_PLUGIN_URL . 'assets/js/' . $bundle . '.js', [ DDFW_Assets::$framework_js_handle, 'wp-util' ], filemtime( DDWCMPA_PLUGIN_FILE . 'assets/js/' . $bundle . '.js' ), true );

			wp_localize_script(
				$handle . '-script',
				'ddwcmpaAdminObj',
				[
					'i18n' => [
						'reasonRequired' => esc_html__( 'Please give the customer a reason before rejecting this prescription.', 'prescription-for-woocommerce' ),
					],
				]
			);

			$custom_css = ':root {
				--ddwcmpa-label-font-color: ' . esc_attr( $this->ddwcmpa_configuration['product_label_font_color'] ) . ';
				--ddwcmpa-label-background-color: ' . esc_attr( $this->ddwcmpa_configuration['product_label_background_color'] ) . ';
			}';

			wp_add_inline_style( $handle . '-style', $custom_css );
		}
	}
}
