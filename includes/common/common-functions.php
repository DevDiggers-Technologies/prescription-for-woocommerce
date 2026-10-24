<?php
/**
 * This file contains shared callback functions for common actions and filters used throughout the plugin.
 *
 * @author DevDiggers
 * @version 1.0.0
 * @package DevDiggers Prescription for WooCommerce
 */

namespace DDWCMedicalPrescriptionAttachment\Includes\Common;

use DDWCMedicalPrescriptionAttachment\Includes\DDWCMPA_Email_Notification_Handler;
use DDWCMedicalPrescriptionAttachment\Helper\DDWCMPA_Security_Helper;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCMPA_Common_Functions' ) ) {
	/**
	 * Common functions class
	 */
	class DDWCMPA_Common_Functions {
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
		public function __construct( $ddwcmpa_configuration = [] ) {
			$this->ddwcmpa_configuration = $ddwcmpa_configuration;
		}

		/**
		 * Add Email Notification function
		 *
		 * @param array $email_classes Registered WooCommerce email classes.
		 * @return array
		 */
		public function ddwcmpa_add_new_email_notification( $email_classes ) {
			$email_classes['WC_Email_DDWCMPA_Notification'] = new DDWCMPA_Email_Notification_Handler();
			return $email_classes;
		}

		/**
		 * Add Email Notification Action function
		 *
		 * @param array $actions Registered WooCommerce email actions.
		 * @return array
		 */
		public function ddwcmpa_add_notification_actions( $actions ) {
			$actions[] = 'ddwcmpa_mail';
			return $actions;
		}

		/**
		 * Keep an order in the review holding status when payment tries to move it on.
		 *
		 * @param string              $status         Status the gateway wants to set.
		 * @param int|\WC_Order       $order_or_id    Order ID (payment complete) or order (offline gateways).
		 * @param \WC_Order|null      $order          Order object, when passed separately.
		 * @return string
		 */
		public function ddwcmpa_keep_order_on_hold( $status, $order_or_id = 0, $order = null ) {
			$order = is_a( $order, 'WC_Order' ) ? $order : ( is_a( $order_or_id, 'WC_Order' ) ? $order_or_id : wc_get_order( $order_or_id ) );

			if ( ! $order || empty( $this->ddwcmpa_configuration['enabled'] ) || empty( $this->ddwcmpa_configuration['hold_order_status'] ) ) {
				return $status;
			}

			// Only prescriptions still waiting on a decision, including one sent back to the
			// customer for more information. An approved or rejected one, or an order with no
			// prescription at all, carries on as the gateway intended.
			if ( ! in_array( $order->get_meta( '_ddwcmpa_status', true ), [ 'pending', 'attachments_pending', 'info_required' ], true ) ) {
				return $status;
			}

			return preg_replace( '/^wc-/', '', $this->ddwcmpa_configuration['hold_order_status'] );
		}

		/**
		 * Hide vaulted prescriptions from the list mode media library.
		 *
		 * @param \WP_Query $query Current query.
		 * @return void
		 */
		public function ddwcmpa_hide_protected_from_media_list( $query ) {
			if ( ! is_admin() || ! $query->is_main_query() || 'attachment' !== $query->get( 'post_type' ) ) {
				return;
			}

			if ( current_user_can( 'manage_options' ) ) {
				return;
			}

			$meta_query   = (array) $query->get( 'meta_query' );
			$meta_query[] = [
				'key'     => DDWCMPA_Security_Helper::PROTECTED_META,
				'compare' => 'NOT EXISTS',
			];

			$query->set( 'meta_query', $meta_query );
		}

		/**
		 * Carry a prescription meta query past the posts order store.
		 *
		 * WooCommerce's posts data store drops `meta_query` from wc_get_orders(), and
		 * since 9.2 logs a notice for it. So on a store still on the posts table, or on
		 * HPOS with the posts table authoritative, every prescription query matched
		 * every order: the queue tabs, the dashboard, the reuse list and the expiry,
		 * reminder and retention tasks. HPOS reads the argument natively, so it is
		 * only moved aside here when the posts store will answer, and put back by
		 * ddwcmpa_restore_order_meta_query() as a plain WP_Query argument.
		 *
		 * Scoped to queries on this plugin's own keys so no other plugin's query changes.
		 *
		 * @param array $args Arguments passed to wc_get_orders().
		 * @return array
		 */
		public static function ddwcmpa_park_order_meta_query( $args ) {
			if ( empty( $args['meta_query'] ) || ! self::ddwcmpa_is_own_meta_query( $args['meta_query'] ) || \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled() ) {
				return $args;
			}

			$args['ddwcmpa_meta_query'] = $args['meta_query'];
			unset( $args['meta_query'] );

			return $args;
		}

		/**
		 * Hand a parked prescription meta query to WP_Query.
		 *
		 * @param array $query      WP_Query arguments WooCommerce built.
		 * @param array $query_vars Arguments passed to wc_get_orders().
		 * @return array
		 */
		public static function ddwcmpa_restore_order_meta_query( $query, $query_vars ) {
			unset( $query['ddwcmpa_meta_query'] );

			if ( ! empty( $query_vars['ddwcmpa_meta_query'] ) ) {
				$ours = $query_vars['ddwcmpa_meta_query'];
			} elseif ( ! empty( $query_vars['meta_query'] ) && self::ddwcmpa_is_own_meta_query( $query_vars['meta_query'] ) ) {
				// The data store was queried directly, without wc_get_orders().
				$ours = $query_vars['meta_query'];
			} else {
				return $query;
			}

			$query['meta_query']   = ! empty( $query['meta_query'] ) ? (array) $query['meta_query'] : []; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Prescription queries filter on order meta by design.
			$query['meta_query'][] = $ours;

			return $query;
		}

		/**
		 * Whether a meta query filters on this plugin's own order meta.
		 *
		 * @param array $meta_query Meta query.
		 * @return bool
		 */
		protected static function ddwcmpa_is_own_meta_query( $meta_query ) {
			return false !== strpos( (string) wp_json_encode( $meta_query ), '"_ddwcmpa_' );
		}
	}
}
