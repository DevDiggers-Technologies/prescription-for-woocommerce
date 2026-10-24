<?php
/**
 * Dashboard data helper.
 *
 * Everything the dashboard shows is derived from the orders themselves, so
 * there is no tracking table to install, migrate or keep in sync.
 *
 * @author DevDiggers
 * @package DevDiggers Prescription for WooCommerce
 * @version 1.0.0
 */

namespace DDWCMedicalPrescriptionAttachment\Helper;

use DevDiggers\Framework\Includes\DDFW_Dashboard_Data;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCMPA_Dashboard_Helper' ) ) {
	/**
	 * Dashboard helper class.
	 */
	class DDWCMPA_Dashboard_Helper {
		/**
		 * Configuration.
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
		 * Construct.
		 *
		 * @param array $ddwcmpa_configuration Plugin configuration.
		 */
		public function __construct( $ddwcmpa_configuration ) {
			$this->ddwcmpa_configuration = $ddwcmpa_configuration;
			$this->prescription_helper   = new DDWCMPA_Prescription_Helper( $ddwcmpa_configuration );
		}

		/**
		 * Build the whole dashboard payload.
		 *
		 * @return array
		 */
		public function get_dashboard_data() {
			$range = DDFW_Dashboard_Data::get_date_range();

			$current  = $this->ddwcmpa_fetch_orders( $range['from'], $range['to'] );
			$previous = $this->ddwcmpa_fetch_orders( ...$this->ddwcmpa_previous_range( $range['from'], $range['to'] ) );

			$current_stats  = $this->ddwcmpa_summarise( $current );
			$previous_stats = $this->ddwcmpa_summarise( $previous );

			// "Awaiting review" describes the queue right now, not something that
			// happened inside the selected period, so it is counted across all orders.
			// Otherwise the card reads zero while the review queue below it is full.
			return [
				'date_range' => $range,
				'summary'    => [
					'total'         => $this->ddwcmpa_compare( $current_stats['total'], $previous_stats['total'] ),
					'awaiting'      => [
						'value'       => $this->ddwcmpa_get_outstanding_count(),
						'change'      => 0,
						'is_positive' => true,
					],
					'approved'      => $this->ddwcmpa_compare( $current_stats['approved'], $previous_stats['approved'] ),
					'rejected'      => $this->ddwcmpa_compare( $current_stats['rejected'], $previous_stats['rejected'], false ),
					'info_required' => $this->ddwcmpa_compare( $current_stats['info_required'], $previous_stats['info_required'], false ),
				],
				'charts'     => [
					'trend'      => DDFW_Dashboard_Data::build_time_series( $range['from'], $range['to'], $current_stats['series'] ),
					'status_mix' => $this->ddwcmpa_status_mix( $current_stats['by_status'] ),
				],
				'tables'     => [
					'queue' => $this->ddwcmpa_get_review_queue(),
				],
			];
		}

		/**
		 * How many prescriptions are waiting on a pharmacist, whenever they were ordered.
		 *
		 * @return int
		 */
		protected function ddwcmpa_get_outstanding_count() {
			$results = wc_get_orders(
				[
					'limit'      => 1,
					'paginate'   => true,
					'return'     => 'ids',
					'type'       => 'shop_order',
					'status'     => array_keys( wc_get_order_statuses() ),
					'meta_query' => [
						[
							'key'     => '_ddwcmpa_status',
							'value'   => [ 'pending', 'attachments_pending' ],
							'compare' => 'IN',
						],
					],
				]
			);

			return isset( $results->total ) ? absint( $results->total ) : 0;
		}

		/**
		 * The equally long window immediately before the selected one.
		 *
		 * @param string $from Range start.
		 * @param string $to   Range end.
		 * @return array{0:string,1:string}
		 */
		protected function ddwcmpa_previous_range( $from, $to ) {
			$length = max( 1, ( strtotime( $to ) - strtotime( $from ) ) / DAY_IN_SECONDS + 1 );

			return [
				gmdate( 'Y-m-d', strtotime( $from ) - ( $length * DAY_IN_SECONDS ) ),
				gmdate( 'Y-m-d', strtotime( $from ) - DAY_IN_SECONDS ),
			];
		}

		/**
		 * Fetch every prescription order created inside a window.
		 *
		 * @param string $from Range start.
		 * @param string $to   Range end.
		 * @return array Order objects.
		 */
		protected function ddwcmpa_fetch_orders( $from, $to ) {
			return wc_get_orders(
				[
					'limit'        => -1,
					'type'         => 'shop_order',
					'status'       => array_keys( wc_get_order_statuses() ),
					'date_created' => $from . '...' . $to,
					'meta_query'   => [
						[
							'key'     => '_ddwcmpa_status',
							'compare' => 'EXISTS',
						],
					],
				]
			);
		}

		/**
		 * Reduce a set of orders to the numbers the dashboard needs.
		 *
		 * @param array $orders Order objects.
		 * @return array
		 */
		protected function ddwcmpa_summarise( $orders ) {
			$stats = [
				'total'         => 0,
				'approved'      => 0,
				'rejected'      => 0,
				'info_required' => 0,
				'by_status'     => [],
				'series'        => [
					'orders'   => [],
					'approved' => [],
				],
			];

			foreach ( $this->prescription_helper->ddwcmpa_get_statuses() as $status_key => $label ) {
				$stats['by_status'][ $status_key ] = 0;
			}

			foreach ( $orders as $order ) {
				$status  = $order->get_meta( '_ddwcmpa_status', true );
				$created = $order->get_date_created();
				$date    = $created ? $created->date( 'Y-m-d' ) : gmdate( 'Y-m-d' );

				++$stats['total'];

				if ( isset( $stats['by_status'][ $status ] ) ) {
					++$stats['by_status'][ $status ];
				}

				$stats['series']['orders'][ $date ] = ( $stats['series']['orders'][ $date ] ?? 0 ) + 1;

				if ( 'approved' === $status ) {
					++$stats['approved'];

					$stats['series']['approved'][ $date ] = ( $stats['series']['approved'][ $date ] ?? 0 ) + 1;
				}

				if ( 'rejected' === $status ) {
					++$stats['rejected'];
				}

				if ( 'info_required' === $status ) {
					++$stats['info_required'];
				}
			}

			return $stats;
		}

		/**
		 * Turn a status tally into doughnut chart rows.
		 *
		 * @param array $by_status Counts keyed by status.
		 * @return array
		 */
		protected function ddwcmpa_status_mix( $by_status ) {
			$labels = $this->prescription_helper->ddwcmpa_get_statuses();
			$rows   = [];

			foreach ( $by_status as $status_key => $count ) {
				if ( empty( $count ) ) {
					continue;
				}

				$rows[] = [
					'label' => isset( $labels[ $status_key ] ) ? $labels[ $status_key ] : $status_key,
					'value' => $count,
				];
			}

			return $rows;
		}

		/**
		 * Percentage change, plus whether the movement is good news.
		 *
		 * @param float $current  Current value.
		 * @param float $previous Previous value.
		 * @param bool  $more_is_better Whether growth is a positive signal.
		 * @return array
		 */
		protected function ddwcmpa_compare( $current, $previous, $more_is_better = true ) {
			if ( empty( $previous ) ) {
				$change = empty( $current ) ? 0 : 100;
			} else {
				$change = round( ( ( $current - $previous ) / $previous ) * 100, 1 );
			}

			$is_positive = $more_is_better ? ( $change >= 0 ) : ( $change <= 0 );

			return [
				'value'       => $current,
				'change'      => $change,
				'is_positive' => $is_positive,
			];
		}

		/**
		 * Prescriptions waiting on a pharmacist, oldest first.
		 *
		 * @param int $limit Maximum rows.
		 * @return array
		 */
		public function ddwcmpa_get_review_queue( $limit = 8 ) {
			$orders = wc_get_orders(
				[
					'limit'      => $limit,
					'type'       => 'shop_order',
					'status'     => array_keys( wc_get_order_statuses() ),
					'orderby'    => 'date',
					'order'      => 'ASC',
					'meta_query' => [
						[
							'key'     => '_ddwcmpa_status',
							'value'   => 'pending',
							'compare' => '=',
						],
					],
				]
			);

			$queue = [];

			foreach ( $orders as $order ) {
				$created = $order->get_date_created();

				$queue[] = [
					'id'       => $order->get_id(),
					'number'   => $order->get_order_number(),
					'customer' => trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ),
					'waiting'  => $created ? human_time_diff( $created->getTimestamp(), time() ) : '',
					'total'    => $order->get_formatted_order_total(),
					'url'      => $order->get_edit_order_url(),
					'files'    => count( array_filter( DDWCMPA_Prescription_Helper::ddwcmpa_get_array_meta( $order, '_ddwcmpa_attachments' ) ) ),
				];
			}

			return $queue;
		}
	}
}
