<?php
/**
 * Dashboard template.
 *
 * Supplies data and configuration to the shared framework dashboard builder.
 *
 * @package Prescription for WooCommerce
 * @version 1.0.0
 */

namespace DDWCMedicalPrescriptionAttachment\Templates\Admin\Dashboard;

use DDWCMedicalPrescriptionAttachment\Helper\DDWCMPA_Dashboard_Helper;
use DDWCMedicalPrescriptionAttachment\Helper\DDWCMPA_Icon_Helper;
use DevDiggers\Framework\Includes\DDFW_Dashboard;
use DevDiggers\Framework\Includes\DDFW_SVG;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCMPA_Dashboard_Template' ) ) {
	/**
	 * Dashboard template class.
	 */
	class DDWCMPA_Dashboard_Template {
		/**
		 * Dashboard helper.
		 *
		 * @var DDWCMPA_Dashboard_Helper
		 */
		protected $dashboard_helper;

		/**
		 * Dashboard data.
		 *
		 * @var array
		 */
		protected $dashboard_data;

		/**
		 * Construct.
		 *
		 * @param array $ddwcmpa_configuration Plugin configuration.
		 */
		public function __construct( $ddwcmpa_configuration ) {
			if ( ! class_exists( '\\DevDiggers\\Framework\\Includes\\DDFW_Dashboard' ) ) {
				return;
			}

			$this->dashboard_helper = new DDWCMPA_Dashboard_Helper( $ddwcmpa_configuration );
			$this->dashboard_data   = $this->dashboard_helper->get_dashboard_data();

			$this->render();
		}

		/**
		 * Render the dashboard.
		 *
		 * @return void
		 */
		protected function render() {
			$data       = $this->dashboard_data;
			$summary    = $data['summary'];
			$date_label = $data['date_range']['label'];

			new DDFW_Dashboard(
				[
					'columns'       => 5,
					'header'        => [
						/* translators: %s: admin display name. */
						'welcome'  => esc_html__( 'Welcome back, %s! 👋🏻', 'prescription-for-woocommerce' ),
						'subtitle' => esc_html__( 'Here is how prescription review is going across your store.', 'prescription-for-woocommerce' ),
					],
					'summary_cards' => [
						[
							'title'       => esc_html__( 'Awaiting Review', 'prescription-for-woocommerce' ),
							'value'       => $summary['awaiting']['value'],
							'change'      => $summary['awaiting']['change'],
							'is_positive' => $summary['awaiting']['is_positive'],
							'icon'        => $this->get_clock_icon(),
						],
						[
							'title'       => esc_html__( 'Approved', 'prescription-for-woocommerce' ),
							'value'       => $summary['approved']['value'],
							'change'      => $summary['approved']['change'],
							'is_positive' => $summary['approved']['is_positive'],
							'icon'        => DDFW_SVG::get_svg_icon( 'circle-check', true ),
						],
						[
							'title'       => esc_html__( 'Rejected', 'prescription-for-woocommerce' ),
							'value'       => $summary['rejected']['value'],
							'change'      => $summary['rejected']['change'],
							'is_positive' => $summary['rejected']['is_positive'],
							'icon'        => $this->get_cross_icon(),
						],
						[
							'title'       => esc_html__( 'Waiting on the Customer', 'prescription-for-woocommerce' ),
							'value'       => $summary['info_required']['value'],
							'change'      => $summary['info_required']['change'],
							'is_positive' => $summary['info_required']['is_positive'],
							'icon'        => DDWCMPA_Icon_Helper::get( 'reply', [ 'size' => 24 ] ),
						],
						[
							'title'       => esc_html__( 'Prescription Orders', 'prescription-for-woocommerce' ),
							'value'       => $summary['total']['value'],
							'change'      => $summary['total']['change'],
							'is_positive' => $summary['total']['is_positive'],
							'icon'        => $this->get_document_icon(),
						],
					],
					'charts'        => [
						[
							'id'         => 'ddwcmpa-trend',
							'title'      => esc_html__( 'Prescription Activity', 'prescription-for-woocommerce' ),
							'date_label' => $date_label,
							'full_width' => true,
							'type'       => 'line',
							'data'       => $data['charts']['trend'],
							'x_key'      => 'date',
							'series'     => [
								[
									'key'   => 'orders',
									'label' => esc_html__( 'Prescription Orders', 'prescription-for-woocommerce' ),
									'color' => '#0256ff',
								],
								[
									'key'   => 'approved',
									'label' => esc_html__( 'Approved', 'prescription-for-woocommerce' ),
									'color' => '#16a34a',
								],
							],
							'empty'      => [
								'title' => esc_html__( 'No prescription activity yet', 'prescription-for-woocommerce' ),
								'desc'  => esc_html__( 'Once customers start attaching prescriptions at checkout, their volume and approval trend appear here.', 'prescription-for-woocommerce' ),
							],
						],
					],
					'widgets'       => [
						[
							'title' => esc_html__( 'Review Status Mix', 'prescription-for-woocommerce' ),
							'width' => 'half',
							'chart' => [
								'id'        => 'ddwcmpa-status-mix',
								'type'      => 'doughnut',
								'data'      => $data['charts']['status_mix'],
								'label_key' => 'label',
								'value_key' => 'value',
								'empty'     => [
									'title' => esc_html__( 'No review data', 'prescription-for-woocommerce' ),
									'desc'  => esc_html__( 'The split between pending, approved and rejected prescriptions appears here.', 'prescription-for-woocommerce' ),
								],
							],
						],
						[
							'title'  => esc_html__( 'Review Queue', 'prescription-for-woocommerce' ),
							'width'  => 'half',
							'render' => [ $this, 'render_review_queue' ],
						],
					],
				]
			);
		}

		/**
		 * Render the oldest prescriptions still waiting on a decision.
		 *
		 * @return void
		 */
		public function render_review_queue() {
			$queue = $this->dashboard_data['tables']['queue'];

			if ( empty( $queue ) ) {
				?>
				<div class="ddfw-dash-no-data"><?php esc_html_e( 'Nothing is waiting for review. Nice work.', 'prescription-for-woocommerce' ); ?></div>
				<?php
				return;
			}
			?>
			<div class="ddwcmpa-dash-list">
				<?php foreach ( $queue as $item ) : ?>
					<a class="ddwcmpa-dash-item" href="<?php echo esc_url( $item['url'] ); ?>">
						<span class="ddwcmpa-dash-item-main">
							<strong><?php echo esc_html( '#' . $item['number'] . ' ' . $item['customer'] ); ?></strong>
							<span class="ddwcmpa-dash-item-sub">
								<?php
								printf(
									/* translators: 1: human readable duration, 2: number of files */
									esc_html__( 'Waiting %1$s &middot; %2$d file(s)', 'prescription-for-woocommerce' ),
									esc_html( $item['waiting'] ),
									absint( $item['files'] )
								);
								?>
							</span>
						</span>
						<span class="ddwcmpa-dash-item-value"><?php echo wp_kses_post( $item['total'] ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
			<a class="ddwcmpa-dash-more" href="<?php echo esc_url( admin_url( 'admin.php?page=ddwcmpa-dashboard&menu=orders&prescription_status=pending' ) ); ?>">
				<?php esc_html_e( 'Open the full queue', 'prescription-for-woocommerce' ); ?>
			</a>
			<?php
		}

		/**
		 * Clock icon.
		 *
		 * @return string
		 */
		protected function get_clock_icon() {
			return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/><path d="M12 7v5l3 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
		}

		/**
		 * Document icon.
		 *
		 * @return string
		 */
		protected function get_document_icon() {
			return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8l-5-5z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M14 3v5h5M9 13h6M9 17h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';
		}

		/**
		 * Cross icon.
		 *
		 * @return string
		 */
		protected function get_cross_icon() {
			return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/><path d="M9 9l6 6M15 9l-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';
		}
	}
}
