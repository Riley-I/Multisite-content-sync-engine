<?php

namespace RID\MultisiteContentSync;

defined( 'ABSPATH' ) || exit;

/**
 * Core plugin orchestrator.
 *
 * Responsible for:
 * - Wiring up service providers (Admin, API, Cron, etc.).
 * - Managing lifecycle (boot, activation, deactivation).
 * - Providing shared plugin context (paths, version, etc.).
 */
class Plugin {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	protected string $version;

	/**
	 * Plugin basename.
	 *
	 * @var string
	 */
	protected string $basename;

	/**
	 * Absolute plugin path (with trailing slash).
	 *
	 * @var string
	 */
	protected string $path;

	/**
	 * Plugin URL (with trailing slash).
	 *
	 * @var string
	 */
	protected string $url;

	/**
	 * Whether the environment is incompatible (e.g. not multisite).
	 *
	 * @var bool
	 */
	protected bool $incompatible_environment = false;

	/**
	 * Construct the plugin instance.
	 *
	 * @param array<string,string> $args {
	 *     @type string $version
	 *     @type string $basename
	 *     @type string $path
	 *     @type string $url
	 * }
	 */
	public function __construct( array $args ) {
		$this->version  = $args['version'] ?? '0.0.0';
		$this->basename = $args['basename'] ?? '';
		$this->path     = rtrim( $args['path'] ?? '', '/\\' ) . '/';
		$this->url      = rtrim( $args['url'] ?? '', '/\\' ) . '/';

		$this->register_activation_hooks();
	}

	/**
	 * Mark environment as incompatible so we can show notices, etc.
	 *
	 * Called from the main plugin file when not in multisite.
	 *
	 * @return void
	 */
	public function mark_incompatible_environment(): void {
		$this->incompatible_environment = true;

		// We still hook admin_notices so we can show useful feedback.
		if ( is_admin() ) {
			add_action( 'admin_notices', [ $this, 'render_incompatible_environment_notice' ] );
		}
	}

	/**
	 * Boot the plugin.
	 *
	 * Called on `plugins_loaded` once WordPress is ready.
	 *
	 * @return void
	 */
	public function boot(): void {
		if ( $this->incompatible_environment ) {
			return;
		}

		$this->load_textdomain();
		$this->register_hooks();
		$this->register_service_providers();
	}

	/**
	 * Register plugin-level hooks.
	 *
	 * @return void
	 */
	protected function register_hooks(): void {
		// Admin-specific hooks.
		if ( is_admin() ) {
			add_action( 'network_admin_menu', [ $this, 'register_network_admin_menu' ] );
		}
	}

	/**
	 * Register service providers (Admin, API, Cron, etc.).
	 *
	 * This is where you'll wire up:
	 * - new Admin\AdminServiceProvider( $this );
	 * - new API\ApiServiceProvider( $this );
	 * - new Cron\CronServiceProvider( $this );
	 *
	 * @return void
	 */
	protected function register_service_providers(): void {
		// Example skeleton for later:
		// (Uncomment as you create these classes.)
		//
		// ( new Admin\AdminServiceProvider( $this ) )->register();
		// ( new API\ApiServiceProvider( $this ) )->register();
		// ( new Cron\CronServiceProvider( $this ) )->register();
	}

	/**
	 * Register the Network Admin menu entry.
	 *
	 * @return void
	 */
	public function register_network_admin_menu(): void {
		// This shows up in the Network Admin for multisite super admins.
		add_menu_page(
			__( 'Content Sync', 'multisite-content-sync' ),
			__( 'Content Sync', 'multisite-content-sync' ),
			'manage_network_options', // You can create a custom cap later.
			'mcs-dashboard',
			[ $this, 'render_dashboard_page' ],
			'dashicons-migrate',
			58
		);

		add_submenu_page(
			'mcs-dashboard',
			__( 'Sync Now', 'multisite-content-sync' ),
			__( 'Sync Now', 'multisite-content-sync' ),
			'manage_network_options',
			'mcs-sync-now',
			[ $this, 'render_sync_now_page' ]
		);

		add_submenu_page(
			'mcs-dashboard',
			__( 'Settings', 'multisite-content-sync' ),
			__( 'Settings', 'multisite-content-sync' ),
			'manage_network_options',
			'mcs-settings',
			[ $this, 'render_settings_page' ]
		);
	}

	/**
	 * Render the main dashboard page.
	 *
	 * For now, just a placeholder view to get a clean first commit.
	 *
	 * @return void
	 */
	public function render_dashboard_page(): void {
		// Later you’ll include a view file:
		// include $this->path . 'views/admin/dashboard.php';
		echo '<div class="wrap"><h1>' . esc_html__( 'Multisite Content Sync', 'multisite-content-sync' ) . '</h1>';
		echo '<p>' . esc_html__( 'This is the main dashboard. Sync activity, stats, and logs will appear here.', 'multisite-content-sync' ) . '</p></div>';
	}

	/**
	 * Render the "Sync Now" page.
	 *
	 * @return void
	 */
	public function render_sync_now_page(): void {
		echo '<div class="wrap"><h1>' . esc_html__( 'Sync Now', 'multisite-content-sync' ) . '</h1>';
		echo '<p>' . esc_html__( 'From here you will be able to trigger manual sync jobs between selected sites.', 'multisite-content-sync' ) . '</p></div>';
	}

	/**
	 * Render the Settings page.
	 *
	 * @return void
	 */
	public function render_settings_page(): void {
		echo '<div class="wrap"><h1>' . esc_html__( 'Content Sync Settings', 'multisite-content-sync' ) . '</h1>';
		echo '<p>' . esc_html__( 'Global configuration, defaults, and advanced options will live here.', 'multisite-content-sync' ) . '</p></div>';
	}

	/**
	 * Show a notice when the environment is not compatible (e.g. not multisite).
	 *
	 * @return void
	 */
	public function render_incompatible_environment_notice(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		echo '<div class="notice notice-error"><p>';
		echo esc_html__(
			'Multisite Content Sync requires WordPress Multisite to be enabled. The plugin is currently inactive.',
			'multisite-content-sync'
		);
		echo '</p></div>';
	}

	/**
	 * Load plugin textdomain for translations.
	 *
	 * @return void
	 */
	protected function load_textdomain(): void {
		load_plugin_textdomain(
			'multisite-content-sync',
			false,
			dirname( $this->basename ) . '/languages'
		);
	}

	/**
	 * Register activation/deactivation hooks.
	 *
	 * Note: These must be registered in the main plugin file, but we centralize
	 * the callbacks here for clarity.
	 *
	 * @return void
	 */
	protected function register_activation_hooks(): void {
		// These calls must happen from the main plugin file, but centralizing
		// callbacks here keeps logic together.
		register_activation_hook( MCS_FILE, [ self::class, 'activate' ] );
		register_deactivation_hook( MCS_FILE, [ self::class, 'deactivate' ] );
	}

	/**
	 * Plugin activation callback.
	 *
	 * @return void
	 */
	public static function activate(): void {
		// Later: run DB migrations, set default options, etc.
		if ( ! is_multisite() ) {
			// If someone activates on non-multisite, just bail; notice will show.
			return;
		}

		// Example: run migrations via a SchemaManager later.
	}

	/**
	 * Plugin deactivation callback.
	 *
	 * @return void
	 */
	public static function deactivate(): void {
		// Later: clear scheduled events, temporary options, etc.
	}

	// ---------------------------------------------------------------------
	// Helper accessors (useful for service providers later)
	// ---------------------------------------------------------------------

	public function version(): string {
		return $this->version;
	}

	public function basename(): string {
		return $this->basename;
	}

	public function path( string $append = '' ): string {
		return $this->path . ltrim( $append, '/\\' );
	}

	public function url( string $append = '' ): string {
		return $this->url . ltrim( $append, '/\\' );
	}
}
