<?php
/**
 * The plugin's own icon set.
 *
 * Dashicons are an admin font: on the front end the stylesheet is only enqueued
 * for logged in users, so a guest sees an empty box where the icon should be.
 * Every icon the plugin draws therefore comes from here as inline SVG instead.
 *
 * @author DevDiggers
 * @package Prescription for WooCommerce
 * @version 1.0.0
 */

namespace DDWCMedicalPrescriptionAttachment\Helper;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCMPA_Icon_Helper' ) ) {
	/**
	 * Icon helper class.
	 */
	class DDWCMPA_Icon_Helper {
		/**
		 * Icon bodies, drawn on a 24x24 grid.
		 *
		 * They share one stroke style so any two of them sit together without
		 * looking like they came from different sets.
		 *
		 * @var array
		 */
		const PATHS = [
			// Identity.
			'prescription' => '<path d="M6.5 2.5h7.2L19.5 8v13a1.5 1.5 0 0 1-1.5 1.5H6.5A1.5 1.5 0 0 1 5 21V4a1.5 1.5 0 0 1 1.5-1.5Z"/><path d="M13.5 2.5V8h6"/><path d="M8.5 18.5v-7h2.2a1.9 1.9 0 0 1 0 3.8H8.5"/><path d="m10.4 15.3 3.6 3.2M14.3 15.1l-3.4 3.4"/>',
			'pill'         => '<rect x="2.6" y="9" width="18.8" height="6" rx="3"/><path d="M12 9v6"/>',

			// Viewer controls.
			'zoom-in'      => '<circle cx="10.5" cy="10.5" r="6.5"/><path d="M15.4 15.4 21 21M10.5 7.8v5.4M7.8 10.5h5.4"/>',
			'zoom-out'     => '<circle cx="10.5" cy="10.5" r="6.5"/><path d="M15.4 15.4 21 21M7.8 10.5h5.4"/>',
			'rotate'       => '<path d="M20 11.5a8 8 0 1 1-2.6-5.9"/><path d="M20.5 3v4.5H16"/>',
			'reset'        => '<path d="M12 4.5a7.5 7.5 0 1 0 7.5 7.5"/><path d="M19.5 4.2v4h-4"/><path d="M12 8.4v3.9l2.7 1.6"/>',
			'expand'       => '<path d="M14.5 3.5h6v6M9.5 20.5h-6v-6M20.5 3.5 13.5 10.5M3.5 20.5l7-7"/>',
			'previous'     => '<path d="m14 5-7 7 7 7"/>',
			'next'         => '<path d="m10 5 7 7-7 7"/>',

			// Files.
			'document'     => '<path d="M6.5 2.5h7.2L19.5 8v13a1.5 1.5 0 0 1-1.5 1.5H6.5A1.5 1.5 0 0 1 5 21V4a1.5 1.5 0 0 1 1.5-1.5Z"/><path d="M13.5 2.5V8h6M8.5 13h7M8.5 17h4.5"/>',
			'paperclip'    => '<path d="M20 11.5 12.2 19.3a5 5 0 0 1-7.1-7.1l8.3-8.3a3.3 3.3 0 1 1 4.7 4.7l-8.2 8.3a1.7 1.7 0 0 1-2.4-2.4l7.6-7.6"/>',
			'upload'       => '<path d="M12 15.5V3.5m0 0L8 7.6M12 3.5l4 4.1"/><path d="M3.5 15v3.5a2 2 0 0 0 2 2h13a2 2 0 0 0 2-2V15"/>',
			'image'        => '<rect x="3.2" y="4.2" width="17.6" height="15.6" rx="2"/><circle cx="8.6" cy="9.4" r="1.7"/><path d="m3.6 17.4 4.9-4.6 4.2 3.8 3-2.7 4.7 4.2"/>',

			// State.
			'check'        => '<circle cx="12" cy="12" r="9"/><path d="m8 12.3 2.7 2.7L16 9.6"/>',
			// The bare tick, for somewhere that already draws its own ring.
			'tick'         => '<path d="m5 12.6 4.6 4.6L19 7.4"/>',
			'cross'        => '<path d="m6.5 6.5 11 11M17.5 6.5l-11 11"/>',
			'warning'      => '<path d="M12 3.6 21.4 20H2.6L12 3.6Z"/><path d="M12 9.6v4.2M12 17.1h.01"/>',
			'info'         => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/>',
			'clock'        => '<circle cx="12" cy="12" r="9"/><path d="M12 6.8V12l3.4 2.1"/>',
			'calendar'     => '<rect x="3.4" y="5" width="17.2" height="15.6" rx="2"/><path d="M3.4 9.8h17.2M8.2 3.4v3.2M15.8 3.4v3.2"/>',
			'repeat'       => '<path d="M3.5 8.5h13.2a3.8 3.8 0 0 1 0 7.6H12"/><path d="m7 4.5-3.5 4L7 12.5M17 19.5l3.5-4"/>',
			'shield'       => '<path d="M12 2.6 20 6v5.5c0 5-3.4 8.6-8 9.9-4.6-1.3-8-4.9-8-9.9V6l8-3.4Z"/><path d="m9 11.9 2 2 4-4"/>',
			'lock'         => '<rect x="4.5" y="10.2" width="15" height="10.3" rx="2"/><path d="M8.2 10.2V7.4a3.8 3.8 0 0 1 7.6 0v2.8"/>',
			'link'         => '<path d="M10 13.8a4 4 0 0 0 5.7 0l2.8-2.8a4 4 0 0 0-5.7-5.7l-1.6 1.6"/><path d="M14 10.2a4 4 0 0 0-5.7 0l-2.8 2.8a4 4 0 0 0 5.7 5.7l1.6-1.6"/>',

			// People and reporting.
			'user'         => '<circle cx="12" cy="8" r="3.6"/><path d="M4.6 20.4a7.4 7.4 0 0 1 14.8 0"/>',
			'stethoscope'  => '<path d="M5 3v5.5a4.5 4.5 0 0 0 9 0V3"/><path d="M4 3h2M13 3h2"/><path d="M9.5 13v2a5 5 0 0 0 10 0v-1.6"/><circle cx="19.5" cy="11.6" r="2.1"/>',
			'chart'        => '<path d="M4 20.2h16"/><path d="M7 20V11M12 20V4.8M17 20v-6"/>',
			'reply'        => '<path d="M9 5 3.5 10.4 9 15.8"/><path d="M3.5 10.4h9.8a6.7 6.7 0 0 1 6.7 6.7v1.4"/>',
			'plus'         => '<path d="M12 5v14M5 12h14"/>',
			// The classic filled bin: wide lid, tapered body. A destructive control
			// has to read at a glance, which a hairline outline does not.
			'trash'        => '<path d="M6 19a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7H6v12ZM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4Z" fill="currentColor" stroke="none"/>',
			'search'       => '<circle cx="10.5" cy="10.5" r="6.5"/><path d="M15.4 15.4 21 21"/>',
			'settings'     => '<path d="M4 6.5h4M12 6.5h8M4 12h10M18 12h2M4 17.5h6M14 17.5h6"/><circle cx="10" cy="6.5" r="2"/><circle cx="16" cy="12" r="2"/><circle cx="12" cy="17.5" r="2"/>',
			'email'        => '<rect x="2.8" y="4.8" width="18.4" height="14.4" rx="2.2"/><path d="m3.4 6.6 7.5 5.6a2 2 0 0 0 2.2 0l7.5-5.6"/>',
			'bell'         => '<path d="M18 9a6 6 0 1 0-12 0c0 5-2 6-2 6h16s-2-1-2-6"/><path d="M10.5 20a1.8 1.8 0 0 0 3 0"/>',
		];

		/**
		 * One icon as an inline SVG string.
		 *
		 * @param string $name Icon name.
		 * @param array  $args size, class and title overrides.
		 * @return string Empty string when the name is unknown.
		 */
		public static function get( $name, $args = [] ) {
			if ( empty( self::PATHS[ $name ] ) ) {
				return '';
			}

			$args = wp_parse_args(
				$args,
				[
					'size'  => 20,
					'class' => '',
					'title' => '',
				]
			);

			$size  = absint( $args['size'] );
			$class = trim( 'ddwcmpa-icon ddwcmpa-icon-' . $name . ' ' . $args['class'] );

			// A titled icon is the accessible name of whatever it sits in, an untitled
			// one is decoration beside text that already says the same thing.
			$label = '' !== $args['title']
				? ' role="img" aria-label="' . esc_attr( $args['title'] ) . '"'
				: ' aria-hidden="true" focusable="false"';

			return '<svg class="' . esc_attr( $class ) . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"' . $label . '>' . self::PATHS[ $name ] . '</svg>';
		}

		/**
		 * Echo an icon.
		 *
		 * @param string $name Icon name.
		 * @param array  $args Overrides.
		 * @return void
		 */
		public static function render( $name, $args = [] ) {
			echo self::get( $name, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Self contained markup built above.
		}

		/**
		 * The markup wp_kses needs to keep an icon intact.
		 *
		 * @return array
		 */
		public static function kses() {
			return [
				'svg'    => [
					'class'           => true,
					'width'           => true,
					'height'          => true,
					'viewbox'         => true,
					'fill'            => true,
					'stroke'          => true,
					'stroke-width'    => true,
					'stroke-linecap'  => true,
					'stroke-linejoin' => true,
					'aria-hidden'     => true,
					'aria-label'      => true,
					'focusable'       => true,
					'role'            => true,
				],
				'path'   => [
					'd'       => true,
					'opacity' => true,
					'fill'    => true,
					'stroke'  => true,
				],
				'circle' => [
					'cx'   => true,
					'cy'   => true,
					'r'    => true,
					'fill' => true,
				],
				'rect'   => [
					'x'      => true,
					'y'      => true,
					'width'  => true,
					'height' => true,
					'rx'     => true,
				],
			];
		}
	}
}
