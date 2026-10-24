<?php
/**
 * Design configuration template class
 *
 * Everything that decides how the plugin looks and where it appears. These
 * settings were previously scattered between the General and Customer
 * Experience tabs, which meant a merchant styling the label and a merchant
 * choosing where the upload box sits were on two different screens.
 *
 * @package DevDiggers Prescription for WooCommerce
 * @version 1.0.0
 */

namespace DDWCMedicalPrescriptionAttachment\Templates\Admin\Configuration;

use DevDiggers\Framework\Includes\DDFW_Layout;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCMPA_Design_Configuration_Template' ) ) {
	/**
	 * Design configuration template class
	 */
	class DDWCMPA_Design_Configuration_Template {
		/**
		 * Configuration Variable
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
			$this->ddwcmpa_render_configuration();
		}

		/**
		 * Render the design configuration.
		 *
		 * @return void
		 */
		public function ddwcmpa_render_configuration() {
			$shop_page_positions = [
				''             => esc_html__( 'Disabled', 'devdiggers-prescription-for-woocommerce' ),
				'before_title' => esc_html__( 'Before Product Title', 'devdiggers-prescription-for-woocommerce' ),
				'after_title'  => esc_html__( 'After Product Title', 'devdiggers-prescription-for-woocommerce' ),
				'before'       => esc_html__( 'Before Add to Cart Button', 'devdiggers-prescription-for-woocommerce' ),
				'after'        => esc_html__( 'After Add to Cart Button', 'devdiggers-prescription-for-woocommerce' ),
			];

			$product_page_positions = [
				''   => esc_html__( 'Disabled', 'devdiggers-prescription-for-woocommerce' ),
				'55' => esc_html__( 'Default', 'devdiggers-prescription-for-woocommerce' ),
				'10' => esc_html__( 'After Product Image', 'devdiggers-prescription-for-woocommerce' ),
				'8'  => esc_html__( 'After Product Title', 'devdiggers-prescription-for-woocommerce' ),
				'3'  => esc_html__( 'Before Product Title', 'devdiggers-prescription-for-woocommerce' ),
				'25' => esc_html__( 'After Short Description', 'devdiggers-prescription-for-woocommerce' ),
				'35' => esc_html__( 'After Add To Cart Button', 'devdiggers-prescription-for-woocommerce' ),
				'5'  => esc_html__( 'Before Tab Information', 'devdiggers-prescription-for-woocommerce' ),
			];

			$cart_page_positions = [
				''                  => esc_html__( 'Disabled', 'devdiggers-prescription-for-woocommerce' ),
				'before_cart_table' => esc_html__( 'Before Cart Products Table', 'devdiggers-prescription-for-woocommerce' ),
				'after_cart_table'  => esc_html__( 'After Cart Products Table', 'devdiggers-prescription-for-woocommerce' ),
				'10'                => esc_html__( 'Before Proceed To Checkout Button', 'devdiggers-prescription-for-woocommerce' ),
				'20'                => esc_html__( 'After Proceed To Checkout Button', 'devdiggers-prescription-for-woocommerce' ),
			];

			$checkout_page_positions = [
				''                     => esc_html__( 'Disabled', 'devdiggers-prescription-for-woocommerce' ),
				'before_checkout_form' => esc_html__( 'Before Checkout Form', 'devdiggers-prescription-for-woocommerce' ),
				'after_checkout_form'  => esc_html__( 'After Checkout Form', 'devdiggers-prescription-for-woocommerce' ),
			];

			$order_page_positions = [
				''                   => esc_html__( 'Disabled', 'devdiggers-prescription-for-woocommerce' ),
				'before_order_table' => esc_html__( 'Before Order Details Table', 'devdiggers-prescription-for-woocommerce' ),
				'after_order_table'  => esc_html__( 'After Order Details Table', 'devdiggers-prescription-for-woocommerce' ),
			];

			$args = [
				[
					'header' => [
						'heading'     => esc_html__( 'Product Information Display', 'devdiggers-prescription-for-woocommerce' ),
						'description' => esc_html__( 'Configure how prescription information is displayed on product pages.', 'devdiggers-prescription-for-woocommerce' ),
					],
					'fields' => [
						[
							'type'        => 'text',
							'label'       => esc_html__( 'Product Page Label', 'devdiggers-prescription-for-woocommerce' ),
							'description' => esc_html__( 'Enter the medical prescription label which will be visible at the single product page.', 'devdiggers-prescription-for-woocommerce' ),
							'id'          => 'ddwcmpa-product-label',
							'name'        => '_ddwcmpa_product_label',
							'value'       => $this->ddwcmpa_configuration['product_label'],
						],
						[
							'type'        => 'colorpicker',
							'label'       => esc_html__( 'Label Font Color', 'devdiggers-prescription-for-woocommerce' ),
							'description' => esc_html__( 'Select the label font color.', 'devdiggers-prescription-for-woocommerce' ),
							'id'          => 'ddwcmpa-product-label-font-color',
							'name'        => '_ddwcmpa_product_label_font_color',
							'value'       => $this->ddwcmpa_configuration['product_label_font_color'],
						],
						[
							'type'        => 'colorpicker',
							'label'       => esc_html__( 'Label Background Color', 'devdiggers-prescription-for-woocommerce' ),
							'description' => esc_html__( 'Select the label background color.', 'devdiggers-prescription-for-woocommerce' ),
							'id'          => 'ddwcmpa-product-label-background-color',
							'name'        => '_ddwcmpa_product_label_background_color',
							'value'       => $this->ddwcmpa_configuration['product_label_background_color'],
						],
						[
							'type'        => 'textarea',
							'label'       => esc_html__( 'Description', 'devdiggers-prescription-for-woocommerce' ),
							'description' => esc_html__( 'Enter the medical prescription description. If not entered then no description will be visible to customers.', 'devdiggers-prescription-for-woocommerce' ),
							'id'          => 'ddwcmpa-prescription-description',
							'name'        => '_ddwcmpa_prescription_description',
							'value'       => $this->ddwcmpa_configuration['prescription_description'],
							'rows'        => 4,
						],
					],
				],
				[
					'header' => [
						'heading'     => esc_html__( 'Display Positions', 'devdiggers-prescription-for-woocommerce' ),
						'description' => esc_html__( 'Configure where prescription information is displayed on different pages.', 'devdiggers-prescription-for-woocommerce' ),
					],
					'fields' => [
						[
							'type'        => 'select',
							'label'       => esc_html__( 'Product Page', 'devdiggers-prescription-for-woocommerce' ),
							'description' => esc_html__( 'Select the position to display requires medical prescription info for the product on the single product page.', 'devdiggers-prescription-for-woocommerce' ),
							'id'          => 'ddwcmpa-product-page-position',
							'name'        => '_ddwcmpa_product_page_position',
							'value'       => $this->ddwcmpa_configuration['product_page_position'],
							'options'     => $product_page_positions,
						],
						[
							'type'        => 'select',
							'label'       => esc_html__( 'Shop/Category Page', 'devdiggers-prescription-for-woocommerce' ),
							'description' => esc_html__( 'Select the position to display requires medical prescription info for products on the shop and category pages.', 'devdiggers-prescription-for-woocommerce' ),
							'id'          => 'ddwcmpa-shop-page-position',
							'name'        => '_ddwcmpa_shop_page_position',
							'value'       => $this->ddwcmpa_configuration['shop_page_position'],
							'options'     => $shop_page_positions,
						],
						[
							'type'        => 'select',
							'label'       => esc_html__( 'Cart Page', 'devdiggers-prescription-for-woocommerce' ),
							'description' => esc_html__( 'Select the position to display the attach medical prescription box on the cart page.', 'devdiggers-prescription-for-woocommerce' ),
							'id'          => 'ddwcmpa-cart-page-position',
							'name'        => '_ddwcmpa_cart_page_position',
							'value'       => $this->ddwcmpa_configuration['cart_page_position'],
							'options'     => $cart_page_positions,
						],
						[
							'type'        => 'select',
							'label'       => esc_html__( 'Checkout Page', 'devdiggers-prescription-for-woocommerce' ),
							'description' => esc_html__( 'Select the position to display the attach medical prescription box on the checkout page.', 'devdiggers-prescription-for-woocommerce' ),
							'id'          => 'ddwcmpa-checkout-page-position',
							'name'        => '_ddwcmpa_checkout_page_position',
							'value'       => $this->ddwcmpa_configuration['checkout_page_position'],
							'options'     => $checkout_page_positions,
						],
						[
							'type'        => 'select',
							'label'       => esc_html__( 'Order/Thank You Page', 'devdiggers-prescription-for-woocommerce' ),
							'description' => esc_html__( 'Select the position to display the attach medical prescription box on the order details section.', 'devdiggers-prescription-for-woocommerce' ),
							'id'          => 'ddwcmpa-order-page-position',
							'name'        => '_ddwcmpa_order_page_position',
							'value'       => $this->ddwcmpa_configuration['order_page_position'],
							'options'     => $order_page_positions,
						],
					],
				],
			];

			$layout = new DDFW_Layout();
			$layout->get_form_section_layout( $args, 'ddwcmpa-design-configuration-fields' );
		}
	}
}
