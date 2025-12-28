<?php

namespace RID\MultisiteContentSync\Admin;

use RID\MultisiteContentSync\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * Admin service provider.
 *
 * Responsible for:
 * - Enqueuing admin assets.
 * - Any other admin-only bootstrap logic.
 */
class AdminServiceProvider {

	/**
	 * Plugin instance.
	 *
	 * @var \RID\MultisiteContentSync\Plugin
	 */
	protected Plugin $plugin;

	/**
	 * Constructor.
	 *
	 * @param Plugin $plugin Plugin instance.
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Register admin hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

	/**
	 * Enqueue admin-specific CSS/JS for our plugin pages.
	 *
	 * @param string $hook_suffix Current admin page hook suffix.
	 *
	 * @return void
	 */
	public function enqueue_assets( string $hook_suffix ): void {
		// Only load on our pages: mcs-dashboard, mcs-sync-now, mcs-settings.
		$screen = get_current_screen();

		if ( ! $screen || 'toplevel_page_mcs-dashboard' !== $screen->id ) {
			if ( 'multisite-content-sync_page_mcs-sync-now' !== $screen->id &&
			     'multisite-content-sync_page_mcs-settings' !== $screen->id ) {
				return;
			}
		}

		$version = $this->plugin->version();

		wp_enqueue_style(
			'mcs-admin',
			$this->plugin->url( 'assets/css/admin.css' ),
			[],
			$version
		);

		wp_enqueue_script(
			'mcs-admin',
			$this->plugin->url( 'assets/js/admin.js' ),
			[ 'jquery' ],
			$version,
			true
		);

		wp_localize_script(
			'mcs-admin',
			'MCS_Admin',
			[
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'mcs_admin_nonce' ),
			]
		);
	}
}
