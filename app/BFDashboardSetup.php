<?php

class BFDashboardSetup {
	public string $capability = 'manage_options';

	public function init(): void {
		add_action( 'admin_menu', array( $this, 'register_options_page' ) );
		add_filter( 'plugin_action_links_' . BF_NEW_USER_NOTIFICATION_BASENAME, array(
			$this,
			'add_settings_link'
		), 10, 1 );
	}

	public function register_options_page(): void {
		add_submenu_page(
			'tools.php',
			'BF New User Notification Options',
			'BF New User Notification Options',
			$this->capability,
			BF_NEW_USER_NOTIFICATION_SLUG,
			array(
				$this,
				'get_options_menu_page_html'
			)
		);
	}

	/**
	 * Main admin page html
	 */
	public function get_options_menu_page_html(): void {
		if ( ! current_user_can( $this->capability ) ) {
			exit;
		}

		load_template( BF_NEW_USER_NOTIFICATION_PATH . 'templates/main.php' );
	}

	public function add_settings_link( array $links ): array {
		$settings_link = 'tools.php?page=' . BF_NEW_USER_NOTIFICATION_SLUG;
		$links[]       = sprintf( '<a href="%s">Settings</a>', $settings_link );

		return $links;
	}
}