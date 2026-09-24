<?php
/**
 * Self-check for the Free/Pro boundary.
 *
 * Every Pro feature is meant to be physically absent from this plugin, not switched off.
 * The easy ways to break that are a rebuild from a stale source tree or a file copied
 * across from the Pro plugin. This asserts that neither has happened.
 *
 *   php bin/test-free-boundary.php
 *
 * @package Prescription for WooCommerce
 */

$root   = dirname( __DIR__ ) . '/';
$errors = [];

/**
 * Read a file, or return '' when it is absent.
 *
 * @param string $path Absolute path.
 * @return string
 */
function ddwcmpa_read( $path ) {
	return file_exists( $path ) ? (string) file_get_contents( $path ) : '';
}

/**
 * Every PHP file that belongs to this plugin, excluding the bundled framework and tooling.
 *
 * @param string $root Plugin root.
 * @return string[]
 */
function ddwcmpa_plugin_php( $root ) {
	$files    = [];
	$iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $root, FilesystemIterator::SKIP_DOTS ) );

	foreach ( $iterator as $file ) {
		$path = $file->getPathname();

		if ( 'php' !== strtolower( $file->getExtension() ) ) {
			continue;
		}

		foreach ( [ '/node_modules/', '/devdiggers-framework/', '/bin/' ] as $skip ) {
			if ( false !== strpos( $path, $skip ) ) {
				continue 2;
			}
		}

		$files[ str_replace( $root, '', $path ) ] = ddwcmpa_read( $path );
	}

	return $files;
}

$php = ddwcmpa_plugin_php( $root );

// 1. Pro-only files are gone, not merely unhooked.
foreach ( [
	'helper/prescriber-helper.php',
	'helper/verification-helper.php',
	'helper/intelligence-helper.php',
	'includes/admin/export-wizard.php',
	'templates/admin/review/review-template.php',
	'templates/front/verify.php',
	'templates/front/my-prescriptions.php',
	'helper/compliance-helper.php',
	'templates/admin/configuration/license-configuration-template.php',
] as $file ) {
	if ( file_exists( $root . $file ) ) {
		$errors[] = "Pro file {$file} is shipped.";
	}
}

// 2. No licensing, updater or remote notifications. WordPress.org rejects a free plugin
// that phones home for a key, reachable or not.
foreach ( $php as $relative => $contents ) {
	foreach ( [ 'ddfw_is_license_activated', 'ddfw_register_plugin_for_updates', '_ddwcmpa_purchase_code', 'DevDiggers_Notifications', 'DDFW_Plugin_Updater' ] as $needle ) {
		if ( false !== strpos( $contents, $needle ) ) {
			$errors[] = "Licensing reference '{$needle}' is present in {$relative}.";
		}
	}
}

foreach ( [ 'includes/class-ddfw-plugin-updater.php', 'includes/class-devdiggers-notifications.php', 'templates/layout/license.php' ] as $file ) {
	if ( file_exists( $root . 'devdiggers-framework/' . $file ) ) {
		$errors[] = "Bundled framework ships {$file}.";
	}
}

// 3. No handler, cron or route for a Pro feature. Locked UI describes these; nothing may
// run them, including a direct request from a logged-in user.
$registrations = [
	"'wp_ajax_ddwcmpa_export'",
	"'wp_ajax_ddwcmpa_review_nav'",
	"'admin_post_ddwcmpa_review'",
	"'admin_post_ddwcmpa_verify_issue'",
	'wp_unschedule_hook',
	'EXPIRY_CRON',
	'RETENTION_CRON',
	'REMINDER_CRON',
	'wp_schedule_event',
	"'woocommerce_add_to_cart_validation'",
	"case 'reuse'",
	'get_bulk_actions',
	'add_rewrite_endpoint',
	'woocommerce_account_menu_items',
	'add_role(',
	'process_bulk_action',
];

foreach ( $php as $relative => $contents ) {
	foreach ( $registrations as $needle ) {
		if ( false !== strpos( $contents, $needle ) ) {
			$errors[] = "Pro registration {$needle} is present in {$relative}.";
		}
	}
}

// 4. No Pro business logic left behind in shared files: validity, reuse, refills,
// fingerprinting, audit and access logging, retention, assignment and service level.
$pro_logic = [
	'_ddwcmpa_expiry_date',
	'_ddwcmpa_reused_order_id',
	'_ddwcmpa_refills_left',
	'_ddwcmpa_file_hash',
	'_ddwcmpa_audit_log',
	'_ddwcmpa_access_log',
	'_ddwcmpa_reviewer',
	'purge_expired',
	'record_access',
	'sla_hours',
	'canned_replies',
	'max_quantity',
	'_ddwcmpa_details',
	'_ddwcmpa_consent',
	'ddwcmpa_render_detail',
	'record_consent',
	'ddwcmpa_manage_prescriptions',
];

foreach ( $php as $relative => $contents ) {
	foreach ( $pro_logic as $needle ) {
		if ( false !== strpos( $contents, $needle ) ) {
			$errors[] = "Pro logic '{$needle}' is present in {$relative}.";
		}
	}
}

// 5. No Pro-only setting is registered, so a crafted options.php POST has nothing to write.
$admin_functions = ddwcmpa_read( $root . 'includes/admin/admin-functions.php' );

foreach ( [ '_ddwcmpa_expiry_enabled', '_ddwcmpa_refills_enabled', '_ddwcmpa_prescribers', '_ddwcmpa_assignment_enabled', '_ddwcmpa_duplicate_check_enabled', '_ddwcmpa_audit_log_enabled', '_ddwcmpa_reuse_enabled', '_ddwcmpa_reminder_enabled', '_ddwcmpa_retention_enabled', '_ddwcmpa_access_log_enabled', '_ddwcmpa_email_message_approved', '_ddwcmpa_verification_enabled', '_ddwcmpa_prescription_fields', '_ddwcmpa_required_fields', '_ddwcmpa_my_account_enabled', '_ddwcmpa_consent_enabled', '_ddwcmpa_consent_text' ] as $needle ) {
	if ( false !== strpos( $admin_functions, "'{$needle}'" ) ) {
		$errors[] = "Pro setting '{$needle}' is registered in includes/admin/admin-functions.php.";
	}
}

// 6. The built bundles carry no Pro flow, and still do the Free job.
$admin_bundle = ddwcmpa_read( $root . 'assets/js/admin.js' );
$front_bundle = ddwcmpa_read( $root . 'assets/js/front.js' );

if ( '' === $admin_bundle || '' === $front_bundle ) {
	$errors[] = 'Built bundles are missing. Run `npm run build`.';
}

foreach ( [ 'ddwcmpa_review_nav', 'ddwcmpa_export', 'ddwcmpa-registry', 'ddwcmpa-workspace', 'ddwcmpa-reply-chip', 'reuse_order_id', 'console.log', 'ddwcmpa-detail-input', 'ddwcmpa-consent-input' ] as $needle ) {
	foreach ( [ 'assets/js/admin.js' => $admin_bundle, 'assets/js/front.js' => $front_bundle, 'assets/js/order.js' => ddwcmpa_read( $root . 'assets/js/order.js' ) ] as $bundle => $contents ) {
		if ( false !== strpos( $contents, $needle ) ) {
			$errors[] = "Pro identifier '{$needle}' is present in {$bundle}.";
		}
	}
}

foreach ( [ 'ddwcmpa_handle_prescription_session', 'send_for_approval', 'attachment_when' ] as $needle ) {
	if ( '' !== $front_bundle && false === strpos( $front_bundle, $needle ) ) {
		$errors[] = "Free behaviour '{$needle}' is missing from assets/js/front.js.";
	}
}

// 7. Every built asset maps to a webpack entry.
$webpack = ddwcmpa_read( $root . 'webpack.config.js' );

foreach ( [ 'assets/js', 'assets/css' ] as $dir ) {
	foreach ( (array) glob( $root . $dir . '/*' ) as $path ) {
		$name = basename( $path );

		if ( 'index.php' === $name ) {
			continue;
		}

		if ( false === strpos( $webpack, '"' . pathinfo( $name, PATHINFO_FILENAME ) . '"' ) ) {
			$errors[] = "{$dir}/{$name} has no webpack entry.";
		}
	}
}

// 8. The Free bootstrap yields to Pro and loads the framework on its own pages.
$bootstrap = ddwcmpa_read( $root . 'functions.php' );

foreach ( [ "class_exists( 'DDWCMPA_Init' )", "'ddwcmpa' !== \$prefix", 'DDWCMPA_Free_Init' ] as $needle ) {
	if ( false === strpos( $bootstrap, $needle ) ) {
		$errors[] = "Bootstrap guard '{$needle}' is missing from functions.php.";
	}
}

// 9. No constants or upgrade routine a first release has no use for. Asset versions come
// from filemtime(), and the Pro link is written where it is used.
foreach ( $php as $relative => $contents ) {
	foreach ( [ 'DDWCMPA_VERSION', 'DDWCMPA_PRO_URL', 'check_version', 'migrate_legacy_files' ] as $needle ) {
		if ( false !== strpos( $contents, $needle ) ) {
			$errors[] = "Unneeded '{$needle}' is present in {$relative}.";
		}
	}
}

if ( $errors ) {
	echo "Free/Pro boundary check FAILED:\n";

	foreach ( $errors as $error ) {
		echo ' - ' . $error . "\n";
	}

	exit( 1 );
}

echo "Free/Pro boundary check passed.\n";
