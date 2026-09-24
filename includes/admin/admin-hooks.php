<?php
/**
 * This file contains all admin end action hooks.
 *
 * @author DevDiggers
 * @version 1.0.0
 * @package Prescription for WooCommerce
 */

namespace DDWCMedicalPrescriptionAttachment\Includes\Admin;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCMPA_Admin_Hooks' ) ) {
	/**
	 * Admin end hook handler class
	 */
	class DDWCMPA_Admin_Hooks extends DDWCMPA_Admin_Functions {
		/**
		 * Construct
		 *
		 * @param array $ddwcmpa_configuration Plugin configuration.
		 */
		public function __construct( $ddwcmpa_configuration ) {
			parent::__construct( $ddwcmpa_configuration );

			add_action( 'admin_init', [ $this, 'ddwcmpa_register_settings' ] );

			add_action( 'add_meta_boxes', [ $this, 'ddwcmpa_add_custom_meta_box' ] );
			add_action( 'woocommerce_process_shop_order_meta', [ $this, 'ddwcmpa_handle_save_shop_order_meta' ] );
			add_action( 'admin_notices', [ $this, 'ddwcmpa_render_deferred_notice' ] );

			// Per product prescription rules.
			add_action( 'woocommerce_product_options_general_product_data', [ $this, 'ddwcmpa_add_product_fields' ] );
			add_action( 'woocommerce_process_product_meta', [ $this, 'ddwcmpa_save_product_fields' ] );

			// A prescription column on the WooCommerce orders screen itself.
			add_filter( 'manage_woocommerce_page_wc-orders_columns', [ $this, 'ddwcmpa_add_order_list_column' ] );
			add_filter( 'manage_edit-shop_order_columns', [ $this, 'ddwcmpa_add_order_list_column' ] );
			add_action( 'manage_woocommerce_page_wc-orders_custom_column', [ $this, 'ddwcmpa_render_order_list_column' ], 10, 2 );
			add_action( 'manage_shop_order_posts_custom_column', [ $this, 'ddwcmpa_render_order_list_column' ], 10, 2 );
		}
	}
}
