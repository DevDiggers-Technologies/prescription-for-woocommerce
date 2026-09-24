<?php
/**
 * This file defines and registers all frontend action and filter hooks for the plugin.
 *
 * @author DevDiggers
 * @version 1.0.0
 * @package Prescription for WooCommerce
 */

namespace DDWCMedicalPrescriptionAttachment\Includes\Front;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCMPA_Front_Hooks' ) ) {
	/**
	 * Front end hooks class
	 */
	class DDWCMPA_Front_Hooks extends DDWCMPA_Front_Functions {
		/**
		 * Construct
		 *
		 * @param array $ddwcmpa_configuration Plugin configuration.
		 */
		public function __construct( $ddwcmpa_configuration ) {
			parent::__construct( $ddwcmpa_configuration );

			if ( ! empty( $ddwcmpa_configuration['product_page_position'] ) ) {
				if ( '5' === $ddwcmpa_configuration['product_page_position'] ) {
					add_action( 'woocommerce_after_single_product_summary', [ $this, 'ddwcmpa_add_content_in_single_product_page' ], $ddwcmpa_configuration['product_page_position'] );
				} elseif ( '10' === $ddwcmpa_configuration['product_page_position'] ) {
					add_action( 'woocommerce_product_thumbnails', [ $this, 'ddwcmpa_add_content_in_single_product_page' ], $ddwcmpa_configuration['product_page_position'] );
				} else {
					add_action( 'woocommerce_single_product_summary', [ $this, 'ddwcmpa_add_content_in_single_product_page' ], $ddwcmpa_configuration['product_page_position'] );
				}
			}

			if ( ! empty( $ddwcmpa_configuration['shop_page_position'] ) ) {
				if ( 'before_title' === $ddwcmpa_configuration['shop_page_position'] ) {
					add_action( 'woocommerce_shop_loop_item_title', [ $this, 'ddwcmpa_add_shop_loop_content_near_product_title' ], 9 );
				} elseif ( 'after_title' === $ddwcmpa_configuration['shop_page_position'] ) {
					add_action( 'woocommerce_shop_loop_item_title', [ $this, 'ddwcmpa_add_shop_loop_content_near_product_title' ], 11 );
				} else {
					add_filter( 'woocommerce_loop_add_to_cart_link', [ $this, 'ddwcmpa_modify_woocommerce_loop_add_to_cart_link' ], 10, 2 );
				}
			}

			if ( ! empty( $ddwcmpa_configuration['cart_page_position'] ) ) {
				if ( 'before_cart_table' === $ddwcmpa_configuration['cart_page_position'] ) {
					add_action( 'woocommerce_before_cart', [ $this, 'ddwcmpa_add_upload_medical_prescription_content' ], 10 );
				} elseif ( 'after_cart_table' === $ddwcmpa_configuration['cart_page_position'] ) {
					add_action( 'woocommerce_before_cart_collaterals', [ $this, 'ddwcmpa_add_upload_medical_prescription_content' ], 10 );
				} elseif ( '10' === $ddwcmpa_configuration['cart_page_position'] ) {
					add_action( 'woocommerce_proceed_to_checkout', [ $this, 'ddwcmpa_add_upload_medical_prescription_content' ], $ddwcmpa_configuration['cart_page_position'] );
				} elseif ( '20' === $ddwcmpa_configuration['cart_page_position'] ) {
					add_action( 'woocommerce_proceed_to_checkout', [ $this, 'ddwcmpa_add_upload_medical_prescription_content' ], $ddwcmpa_configuration['cart_page_position'] );
				}
			}

			if ( ! empty( $ddwcmpa_configuration['checkout_page_position'] ) ) {
				if ( 'before_checkout_form' === $ddwcmpa_configuration['checkout_page_position'] ) {
					add_action( 'woocommerce_before_checkout_form', [ $this, 'ddwcmpa_add_upload_medical_prescription_content' ], 10 );
				} elseif ( 'after_checkout_form' === $ddwcmpa_configuration['checkout_page_position'] ) {
					add_action( 'woocommerce_after_checkout_form', [ $this, 'ddwcmpa_add_upload_medical_prescription_content' ], 10 );
				}
			}

			if ( ! empty( $ddwcmpa_configuration['order_page_position'] ) ) {
				add_action( 'woocommerce_order_details_' . $ddwcmpa_configuration['order_page_position'], [ $this, 'ddwcmpa_add_content_on_order_details_page' ] );
			}

			add_action( 'wp_enqueue_scripts', [ $this, 'ddwcmpa_front_scripts' ] );

			add_action( 'woocommerce_blocks_enqueue_cart_block_scripts_after', [ $this, 'ddwcmpa_blocks_enqueue_scripts' ] );
			add_action( 'woocommerce_blocks_enqueue_checkout_block_scripts_before', [ $this, 'ddwcmpa_blocks_enqueue_scripts' ] );

			add_filter( 'woocommerce_order_button_html', [ $this, 'ddwcmpa_modify_woocommerce_order_button_html' ], 99 );

			add_action( 'woocommerce_after_checkout_validation', [ $this, 'ddwcmpa_woocommerce_after_checkout_validation' ], 10, 2 );

			add_action( 'woocommerce_checkout_update_order_meta', [ $this, 'ddwcmpa_woocommerce_checkout_update_order_meta' ], 10, 2 );
			add_action( 'woocommerce_store_api_checkout_order_processed', [ $this, 'ddwcmpa_woocommerce_checkout_update_order_meta' ] );
			add_action( 'woocommerce_store_api_checkout_validation', [ $this, 'ddwcmpa_blocks_checkout_validation' ], 10, 2 );

			add_action( 'wp_footer', [ $this, 'ddwcmpa_add_loader_template' ] );
		}
	}
}
