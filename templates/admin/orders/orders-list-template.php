<?php
/**
 * Orders List Template
 *
 * Queries through wc_get_orders() so the screen works identically on HPOS and on
 * the legacy posts tables. The previous WP_Query on the shop_order post type
 * returned nothing at all once a store enabled high performance order storage.
 *
 * @package Prescription for WooCommerce
 * @version 1.0.0
 */

namespace DDWCMedicalPrescriptionAttachment\Templates\Admin\Orders;

use DDWCMedicalPrescriptionAttachment\Helper\DDWCMPA_Prescription_Helper;
use DDWCMedicalPrescriptionAttachment\Helper\DDWCMPA_Security_Helper;
use DDWCMedicalPrescriptionAttachment\Helper\DDWCMPA_Icon_Helper;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCMPA_Orders_List_Template' ) ) {
	/**
	 * Orders List class
	 */
	class DDWCMPA_Orders_List_Template extends \WP_List_Table {
		/**
		 * Prescription helper.
		 *
		 * @var DDWCMPA_Prescription_Helper
		 */
		protected $prescription_helper;

		/**
		 * Status labels.
		 *
		 * @var array
		 */
		protected $all_statuses = [];

		/**
		 * Plugin configuration.
		 *
		 * @var array
		 */
		protected $ddwcmpa_configuration = [];

		/**
		 * Construct
		 */
		public function __construct() {
			global $ddwcmpa_configuration;

			parent::__construct(
				[
					'singular' => esc_html__( 'Order', 'prescription-for-woocommerce' ),
					'plural'   => esc_html__( 'Orders', 'prescription-for-woocommerce' ),
					'ajax'     => false,
				]
			);

			$this->ddwcmpa_configuration = (array) $ddwcmpa_configuration;
			$this->prescription_helper   = new DDWCMPA_Prescription_Helper( $this->ddwcmpa_configuration );
			$this->all_statuses          = $this->prescription_helper->ddwcmpa_get_statuses();
		}

		/**
		 * The prescription status currently being filtered on.
		 *
		 * @return string
		 */
		protected function ddwcmpa_get_current_status() {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read only list filter.
			$status = ! empty( $_GET['prescription_status'] ) ? sanitize_key( wp_unslash( $_GET['prescription_status'] ) ) : '';

			return isset( $this->all_statuses[ $status ] ) ? $status : '';
		}

		/**
		 * The current search term.
		 *
		 * @return string
		 */
		protected function ddwcmpa_get_search_term() {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read only list filter.
			return ! empty( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
		}

		/**
		 * Build the wc_get_orders() arguments for the current screen state.
		 *
		 * @param string $status Prescription status to filter on.
		 * @param string $search Search term.
		 * @return array
		 */
		protected function ddwcmpa_build_query_args( $status = '', $search = '' ) {
			$meta_query = [
				[
					'key'     => '_ddwcmpa_status',
					'compare' => 'EXISTS',
				],
			];

			if ( ! empty( $status ) ) {
				$meta_query = [
					[
						'key'     => '_ddwcmpa_status',
						'value'   => $status,
						'compare' => '=',
					],
				];
			}

			$args = [
				'status'     => array_keys( wc_get_order_statuses() ),
				'type'       => 'shop_order',
				'meta_query' => $meta_query,
			];

			if ( '' !== $search ) {
				if ( \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled() ) {
					// HPOS matches the order number, customer, email and items on 's'.
					$args['s'] = $search;
				} else {
					// The posts store hands 's' to a post title search, which never
					// matches an order. WooCommerce's own order search covers the same
					// fields there.
					$ids              = wc_order_search( $search );
					$args['post__in'] = ! empty( $ids ) ? array_map( 'absint', $ids ) : [ 0 ];
				}
			}

			return $args;
		}

		/**
		 * Count the orders matching one prescription status.
		 *
		 * @param string $status Prescription status, empty for all.
		 * @return int
		 */
		public function ddwcmpa_get_orders_count( $status = '' ) {
			$args = $this->ddwcmpa_build_query_args( $status, $this->ddwcmpa_get_search_term() );

			$args['limit']    = 1;
			$args['paginate'] = true;
			$args['return']   = 'ids';

			$results = wc_get_orders( $args );

			return isset( $results->total ) ? absint( $results->total ) : 0;
		}

		/**
		 * Prepare the items for the table to process
		 *
		 * @return void
		 */
		public function prepare_items() {
			$this->_column_headers = [ $this->get_columns(), $this->get_hidden_columns(), $this->get_sortable_columns() ];

			$status   = $this->ddwcmpa_get_current_status();
			$search   = $this->ddwcmpa_get_search_term();
			$per_page = $this->get_items_per_page( 'ddwcmpa_orders_per_page', 20 );

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read only sort parameter.
			$orderby = ! empty( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : 'date';
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read only sort parameter.
			$order = ! empty( $_GET['order'] ) && 'asc' === strtolower( sanitize_key( wp_unslash( $_GET['order'] ) ) ) ? 'ASC' : 'DESC';

			$args = $this->ddwcmpa_build_query_args( $status, $search );

			$args['limit']    = $per_page;
			$args['paged']    = $this->get_pagenum();
			$args['paginate'] = true;
			// 'status' is the column the sort handle sits on now; it sorts by date.
			$args['orderby'] = in_array( $orderby, [ 'date', 'id', 'modified' ], true ) ? $orderby : 'date';
			$args['order']   = $order;

			$results = wc_get_orders( $args );

			$this->set_pagination_args(
				[
					'total_items' => isset( $results->total ) ? absint( $results->total ) : 0,
					'per_page'    => $per_page,
					'total_pages' => isset( $results->max_num_pages ) ? absint( $results->max_num_pages ) : 1,
				]
			);

			$this->items = $this->ddwcmpa_build_rows( isset( $results->orders ) ? $results->orders : [] );
		}

		/**
		 * Turn order objects into table rows.
		 *
		 * @param array $orders Order objects.
		 * @return array
		 */
		protected function ddwcmpa_build_rows( $orders ) {
			$data = [];

			foreach ( $orders as $order ) {
				if ( ! is_a( $order, 'WC_Order' ) ) {
					$order = wc_get_order( $order );
				}

				if ( ! $order ) {
					continue;
				}

				$order_id = $order->get_id();

				$data[] = [
					'id'                  => $order_id,
					'order'               => $this->ddwcmpa_render_order_cell( $order ),
					'status'              => $this->ddwcmpa_render_status_cell( $order ),
					'prescription'        => $this->ddwcmpa_render_prescription_cell( $order ),
					'prescription_status' => $this->ddwcmpa_render_prescription_status_cell( $order ),
					'total'               => $this->ddwcmpa_render_total_cell( $order ),
				];
			}

			return apply_filters( 'ddwcmpa_orders_list_data', $data );
		}

		/**
		 * Order column markup.
		 *
		 * @param \WC_Order $order Order object.
		 * @return string
		 */
		protected function ddwcmpa_render_order_cell( $order ) {
			$buyer = '';

			if ( $order->get_billing_first_name() || $order->get_billing_last_name() ) {
				/* translators: 1: first name 2: last name */
				$buyer = trim( sprintf( _x( '%1$s %2$s', 'full name', 'prescription-for-woocommerce' ), $order->get_billing_first_name(), $order->get_billing_last_name() ) );
			} elseif ( $order->get_billing_company() ) {
				$buyer = trim( $order->get_billing_company() );
			} elseif ( $order->get_customer_id() ) {
				$user = get_user_by( 'ID', $order->get_customer_id() );

				if ( $user ) {
					$buyer = ucwords( $user->display_name );
				}
			}

			/** This filter is documented by WooCommerce core. */
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core WordPress/WooCommerce hook.
			$buyer = apply_filters( 'woocommerce_admin_order_buyer_name', $buyer, $order );

			$label = '<strong>#' . esc_html( $order->get_order_number() ) . ' ' . esc_html( $buyer ) . '</strong>';

			if ( 'trash' !== $order->get_status() ) {
				$label = '<a href="' . esc_url( $order->get_edit_order_url() ) . '" class="order-view">' . $label . '</a>';
			}

			$email = $order->get_billing_email();

			return '<div class="ddwcmpa-cell-stack">' . $label
				. ( $email ? '<span class="ddwcmpa-cell-note">' . esc_html( $email ) . '</span>' : '' )
				. '</div>';
		}

		/**
		 * Date column markup.
		 *
		 * @param \WC_Order $order Order object.
		 * @return string
		 */
		protected function ddwcmpa_render_date_cell( $order ) {
			$created = $order->get_date_created();

			if ( ! $created ) {
				return '&ndash;';
			}

			$timestamp = $created->getTimestamp();

			if ( $timestamp > strtotime( '-1 day', time() ) && $timestamp <= time() ) {
				$show_date = sprintf(
					/* translators: %s = human-readable time difference */
					_x( '%s ago', '%s = human-readable time difference', 'prescription-for-woocommerce' ),
					human_time_diff( $timestamp, time() )
				);
			} else {
				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core WordPress/WooCommerce hook.
				$show_date = $created->date_i18n( apply_filters( 'woocommerce_admin_order_date_format', __( 'M j, Y', 'prescription-for-woocommerce' ) ) );
			}

			return sprintf(
				'<time datetime="%1$s" title="%2$s">%3$s</time>',
				esc_attr( $created->date( 'c' ) ),
				esc_attr( $created->date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) ),
				esc_html( $show_date )
			);
		}

		/**
		 * Order status column markup.
		 *
		 * @param \WC_Order $order Order object.
		 * @return string
		 */
		protected function ddwcmpa_render_status_cell( $order ) {
			return sprintf(
				'<div class="ddwcmpa-cell-stack"><mark class="order-status %1$s"><span>%2$s</span></mark><span class="ddwcmpa-cell-note">%3$s</span></div>',
				esc_attr( sanitize_html_class( 'status-' . $order->get_status() ) ),
				esc_html( wc_get_order_status_name( $order->get_status() ) ),
				$this->ddwcmpa_render_date_cell( $order )
			);
		}

		/**
		 * Prescription thumbnails column markup.
		 *
		 * @param \WC_Order $order Order object.
		 * @return string
		 */
		protected function ddwcmpa_render_prescription_cell( $order ) {
			$attachments = array_filter( DDWCMPA_Prescription_Helper::ddwcmpa_get_array_meta( $order, '_ddwcmpa_attachments' ) );

			if ( empty( $attachments ) ) {
				return '<span class="ddwcmpa-muted">' . esc_html__( 'Awaiting upload', 'prescription-for-woocommerce' ) . '</span>';
			}

			ob_start();
			?>
			<div class="ddwcmpa-thumb-strip">
				<?php
				foreach ( array_slice( $attachments, 0, 3 ) as $attachment_id ) {
					$url        = DDWCMPA_Security_Helper::get_file_url( $attachment_id, $order->get_id() );
					$attachment = get_post( $attachment_id );

					if ( ! $attachment ) {
						continue;
					}

					if ( false !== strpos( $attachment->post_mime_type, 'image' ) ) {
						?>
						<a href="<?php echo esc_url( $url ); ?>" class="ddwcmpa-thumb" target="_blank" rel="noopener noreferrer">
							<img src="<?php echo esc_url( $url ); ?>" alt="<?php echo esc_attr( get_the_title( $attachment_id ) ); ?>" />
						</a>
						<?php
					} else {
						?>
						<a href="<?php echo esc_url( $url ); ?>" class="ddwcmpa-thumb ddwcmpa-thumb-file" target="_blank" rel="noopener noreferrer">
							<?php DDWCMPA_Icon_Helper::render( 'paperclip', [ 'size' => 18 ] ); ?>
						</a>
						<?php
					}
				}

				if ( count( $attachments ) > 3 ) {
					?>
					<span class="ddwcmpa-thumb-more">+<?php echo esc_html( count( $attachments ) - 3 ); ?></span>
					<?php
				}
				?>
			</div>
			<?php
			return ob_get_clean();
		}

		/**
		 * Prescription status column markup, with the reviewer message.
		 *
		 * @param \WC_Order $order Order object.
		 * @return string
		 */
		protected function ddwcmpa_render_prescription_status_cell( $order ) {
			$status = $order->get_meta( '_ddwcmpa_status', true );
			$label  = isset( $this->all_statuses[ $status ] ) ? $this->all_statuses[ $status ] : $status;
			$reason = $order->get_meta( '_ddwcmpa_rejection_reason', true );

			ob_start();
			?>
			<div class="ddwcmpa-cell-stack">
			<span class="ddwcmpa-status ddwcmpa-status-<?php echo esc_attr( $status ); ?>"><?php echo esc_html( $label ); ?></span>
			<?php
			if ( ! empty( $reason ) && in_array( $status, [ 'rejected', 'info_required' ], true ) ) {
				?>
				<span class="ddwcmpa-cell-note" title="<?php echo esc_attr( $reason ); ?>"><?php echo esc_html( wp_trim_words( $reason, 8 ) ); ?></span>
				<?php
			}
			?>
			</div>
			<?php

			return ob_get_clean();
		}

		/**
		 * Order total column markup.
		 *
		 * @param \WC_Order $order Order object.
		 * @return string
		 */
		protected function ddwcmpa_render_total_cell( $order ) {
			if ( $order->get_payment_method_title() ) {
				return '<span class="tips" data-tip="' . esc_attr( sprintf( /* translators: %s: payment method */ __( 'via %s', 'prescription-for-woocommerce' ), $order->get_payment_method_title() ) ) . '">' . wp_kses_post( $order->get_formatted_order_total() ) . '</span>';
			}

			return wp_kses_post( $order->get_formatted_order_total() );
		}

		/**
		 * The current admin screen URL, without the paging or one-shot parameters.
		 *
		 * @return string
		 */
		protected function ddwcmpa_current_url() {
			$args = [
				'page' => 'ddwcmpa-dashboard',
				'menu' => 'orders',
			];

			$search = $this->ddwcmpa_get_search_term();

			if ( '' !== $search ) {
				$args['s'] = $search;
			}

			$status = $this->ddwcmpa_get_current_status();

			if ( '' !== $status ) {
				$args['prescription_status'] = $status;
			}

			return add_query_arg( $args, admin_url( 'admin.php' ) );
		}

		/**
		 * Status filter links above the table.
		 *
		 * @return array
		 */
		/**
		 * Render the status filter links.
		 *
		 * Core writes a literal " |" between the items, which the framework's pill
		 * styling has nowhere to put: the separators drop onto their own line and
		 * the row looks half built. The other DevDiggers screens hand write this
		 * list without separators, so this does the same.
		 *
		 * @return void
		 */
		public function views() {
			$views = $this->get_views();

			/** This filter is documented by WordPress core. */
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core WordPress/WooCommerce hook.
			$views = apply_filters( "views_{$this->screen->id}", $views );

			if ( empty( $views ) ) {
				return;
			}

			$this->screen->render_screen_reader_content( 'heading_views' );
			?>
			<ul class="subsubsub">
				<?php foreach ( $views as $ddwcmpa_class => $ddwcmpa_view ) : ?>
					<li class="<?php echo esc_attr( $ddwcmpa_class ); ?>"><?php echo wp_kses_post( $ddwcmpa_view ); ?></li>
				<?php endforeach; ?>
			</ul>
			<?php
		}

		/**
		 * The status filter links.
		 *
		 * @return array
		 */
		protected function get_views() {
			$current  = $this->ddwcmpa_get_current_status();
			$base_url = remove_query_arg( [ 'prescription_status', 'paged' ], $this->ddwcmpa_current_url() );

			$views = [
				'all' => sprintf(
					'<a href="%1$s" class="%2$s">%3$s <span class="count">(%4$d)</span></a>',
					esc_url( $base_url ),
					'' === $current ? 'current' : '',
					esc_html__( 'All', 'prescription-for-woocommerce' ),
					$this->ddwcmpa_get_orders_count()
				),
			];

			foreach ( $this->all_statuses as $status_key => $status_label ) {
				$count = $this->ddwcmpa_get_orders_count( $status_key );

				if ( empty( $count ) && $current !== $status_key ) {
					continue;
				}

				$views[ $status_key ] = sprintf(
					'<a href="%1$s" class="%2$s">%3$s <span class="count">(%4$d)</span></a>',
					esc_url( add_query_arg( 'prescription_status', $status_key, $base_url ) ),
					$current === $status_key ? 'current' : '',
					esc_html( $status_label ),
					$count
				);
			}

			return $views;
		}

		/**
		 * Order column with its row actions.
		 *
		 * @param array $item Row data.
		 * @return string
		 */
		public function column_order( $item ) {
			$order = wc_get_order( $item['id'] );

			$actions = [
				'view' => sprintf(
					'<a href="%s"><strong>%s</strong></a>',
					// get_edit_order_url() follows the store's storage mode; a hardcoded
					// wc-orders link 404s on stores still on the posts table.
					esc_url( $order ? $order->get_edit_order_url() : '' ),
					esc_html__( 'Review prescription', 'prescription-for-woocommerce' )
				),
			];

			return $item['order'] . $this->row_actions( $actions );
		}

		/**
		 * Where Pro's bulk decisions sit, shown locked.
		 *
		 * Inert markup only: no bulk action is registered, so there is nothing to submit.
		 *
		 * @param string $which Top or bottom tablenav.
		 * @return void
		 */
		protected function extra_tablenav( $which ) {
			if ( 'top' !== $which || ! current_user_can( DDWCMPA_Security_Helper::MANAGE_CAP ) ) {
				return;
			}
			?>
			<div class="alignleft actions ddwcmpa-bulk-locked">
				<select disabled="disabled" aria-label="<?php esc_attr_e( 'Bulk decisions', 'prescription-for-woocommerce' ); ?>">
					<option><?php esc_html_e( 'Bulk approve or reject', 'prescription-for-woocommerce' ); ?></option>
				</select>
				<a href="<?php echo esc_url( 'https://devdiggers.com/product/woocommerce-medical-prescription-attachment/' ); ?>" target="_blank" rel="noopener noreferrer"><?php echo wp_kses_post( ddfw_get_pro_tag() ); ?></a>
			</div>
			<?php
		}

		/**
		 * Associative array of columns
		 *
		 * @return array
		 */
		public function get_columns() {
			return apply_filters(
				'ddwcmpa_orders_list_columns',
				[
					'order'               => esc_html__( 'Order', 'prescription-for-woocommerce' ),
					'status'              => esc_html__( 'Status & Date', 'prescription-for-woocommerce' ),
					'prescription'        => esc_html__( 'Prescription', 'prescription-for-woocommerce' ),
					'prescription_status' => esc_html__( 'Review', 'prescription-for-woocommerce' ),
					'total'               => esc_html__( 'Total', 'prescription-for-woocommerce' ),
				]
			);
		}

		/**
		 * Empty state.
		 *
		 * @return void
		 */
		public function no_items() {
			if ( '' !== $this->ddwcmpa_get_search_term() ) {
				$heading = esc_html__( 'No matching orders', 'prescription-for-woocommerce' );
				$message = esc_html__( 'Nothing matches that search. Try an order number, a billing email or a customer name.', 'prescription-for-woocommerce' );
			} elseif ( '' !== $this->ddwcmpa_get_current_status() ) {
				$heading = esc_html__( 'No orders with this status', 'prescription-for-woocommerce' );
				$message = esc_html__( 'Choose another tab above to see the rest of the queue.', 'prescription-for-woocommerce' );
			} else {
				$heading = esc_html__( 'Nothing waiting for review', 'prescription-for-woocommerce' );
				$message = esc_html__( 'Orders appear here as soon as a customer attaches a medical prescription at checkout.', 'prescription-for-woocommerce' );
			}
			?>
			<div class="ddwcmpa-empty-state">
				<span class="ddwcmpa-empty-state-mark"><?php DDWCMPA_Icon_Helper::render( 'prescription', [ 'size' => 30 ] ); ?></span>
				<h3><?php echo esc_html( $heading ); ?></h3>
				<p><?php echo esc_html( $message ); ?></p>
			</div>
			<?php
		}

		/**
		 * Hidden Columns
		 *
		 * @return array
		 */
		public function get_hidden_columns() {
			return [];
		}

		/**
		 * Render a column when no column specific method exists.
		 *
		 * @param array  $item        Row data.
		 * @param string $column_name Column key.
		 * @return string
		 */
		public function column_default( $item, $column_name ) {
			return array_key_exists( $column_name, $item ) ? $item[ $column_name ] : '';
		}

		/**
		 * Columns to make sortable.
		 *
		 * Only the columns the order store can genuinely sort on are offered, so a
		 * sort never silently reorders a single page instead of the whole result set.
		 *
		 * @return array
		 */
		public function get_sortable_columns() {
			return apply_filters(
				'ddwcmpa_orders_list_sortable_columns',
				[
					'status' => [ 'date', true ],
				]
			);
		}
	}
}
