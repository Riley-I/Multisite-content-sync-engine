<?php
/**
 * Plugin Name:       Multisite Content Sync
 * Plugin URI:        https://github.com/your-name/multisite-content-sync
 * Description:       Sync posts, pages, CPTs, and options across WordPress multisite networks with queued jobs and fine-grained control.
 * Version:           0.1.0
 * Author:            Riley Inniss
 * Author URI:        https://rileyidesign.ca
 * Text Domain:       multisite-content-sync
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Network:           true
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// -----------------------------------------------------------------------------
// Constants
// -----------------------------------------------------------------------------

define( 'MCS_VERSION', '0.1.0' );
define( 'MCS_FILE', __FILE__ );
define( 'MCS_BASENAME', plugin_basename( __FILE__ ) );
define( 'MCS_PATH', plugin_dir_path( __FILE__ ) );
define( 'MCS_URL', plugin_dir_url( __FILE__ ) );

// -----------------------------------------------------------------------------
// Autoloading
// -----------------------------------------------------------------------------

// Prefer Composer if available.
if ( file_exists( MCS_PATH . 'vendor/autoload.php' ) ) {
	require_once MCS_PATH . 'vendor/autoload.php';
} else {
	// Fallback simple PSR-4 style autoloader for the plugin namespace.
	spl_autoload_register(
		function ( $class ) {
			$prefix   = 'RID\\MultisiteContentSync\\';
			$base_dir = MCS_PATH . 'src/';

			$len = strlen( $prefix );
			if ( strncmp( $prefix, $class, $len ) !== 0 ) {
				return;
			}

			$relative_class = substr( $class, $len );
			$file           = $base_dir . str_replace( '\\', DIRECTORY_SEPARATOR, $relative_class ) . '.php';

			if ( file_exists( $file ) ) {
				require $file;
			}
		}
	);
}

// -----------------------------------------------------------------------------
// Bootstrap
// -----------------------------------------------------------------------------

use RID\MultisiteContentSync\Plugin;

/**
 * Get the main plugin instance.
 *
 * @return \RID\MultisiteContentSync\Plugin
 */
function mcs() {
	static $instance = null;

	if ( null === $instance ) {
		$instance = new Plugin(
			[
				'version'  => MCS_VERSION,
				'basename' => MCS_BASENAME,
				'path'     => MCS_PATH,
				'url'      => MCS_URL,
			]
		);
	}

	return $instance;
}

// Kick things off after all plugins load so dependencies are available.
add_action(
	'plugins_loaded',
	static function () {
		// Only run in multisite contexts.
		if ( ! is_multisite() ) {
			// Graceful fail later: admin notice is registered inside Plugin.
			mcs()->mark_incompatible_environment();
			return;
		}

		mcs()->boot();
	}
);
