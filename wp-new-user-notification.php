<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @since             1.0.0
 * @package           custom wp new user notification
 *
 * @wordpress-plugin
 * Plugin Name:       WP new user notification
 * Plugin URI:        https://github.com/jmucak/wp-new-user-notification
 * Description:       custom wp new user notification
 * Version:           1.0.0
 * Author:            Josip Mucak
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BF_NEW_USER_NOTIFICATION_PATH', plugin_dir_path( __FILE__ ) );
define( 'BF_NEW_USER_NOTIFICATION_URL', plugin_dir_url( __FILE__ ) );
define( 'BF_NEW_USER_NOTIFICATION_BASENAME', plugin_basename( __FILE__ ) );
define( 'BF_NEW_USER_NOTIFICATION_SLUG', 'bf-new-user-notification' );

require_once BF_NEW_USER_NOTIFICATION_PATH . 'app/core/BFDashboardSetup.php';
$bf_dashboard_setup = new BFDashboardSetup();
$bf_dashboard_setup->init();

if ( ! function_exists( 'wp_new_user_notification' ) ) {
	/**
	 * Emails login credentials to a newly-registered user.
	 *
	 * A new user registration notification is also sent to admin email.
	 *
	 * @param int $user_id User ID.
	 * @param null $deprecated Not used (argument deprecated).
	 * @param string $notify Optional. Type of notification that should happen. Accepts 'admin' or an empty
	 *                           string (admin only), 'user', or 'both' (admin and user). Default empty.
	 *
	 * @since 4.6.0 The `$notify` parameter accepts 'user' for sending notification only to the user created.
	 *
	 * @since 2.0.0
	 * @since 4.3.0 The `$plaintext_pass` parameter was changed to `$notify`.
	 * @since 4.3.1 The `$plaintext_pass` parameter was deprecated. `$notify` added as a third parameter.
	 */
	function wp_new_user_notification( int $user_id, $deprecated = null, string $notify = '' ) {
		if ( null !== $deprecated ) {
			_deprecated_argument( __FUNCTION__, '4.3.1' );
		}

		// Accepts only 'user', 'admin' , 'both' or default '' as $notify.
		if ( ! in_array( $notify, array( 'user', 'admin', 'both', '' ), true ) ) {
			return;
		}

		$user = get_userdata( $user_id );

		/*
		 * The blogname option is escaped with esc_html() on the way into the database in sanitize_option().
		 * We want to reverse this for the plain text arena of emails.
		 */
		$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

		/**
		 * Filters whether the admin is notified of a new user registration.
		 *
		 * @param bool $send Whether to send the email. Default true.
		 * @param WP_User $user User object for new user.
		 *
		 * @since 6.1.0
		 *
		 */
		$send_notification_to_admin = apply_filters( 'wp_send_new_user_notification_to_admin', true, $user );

		if ( 'user' !== $notify && true === $send_notification_to_admin ) {
			$switched_locale = switch_to_locale( get_locale() );

			/* translators: %s: Site title. */
			$message = sprintf( __( 'New user registration on your site %s:' ), $blogname ) . "\r\n\r\n";
			/* translators: %s: User login. */
			$message .= sprintf( __( 'Username: %s' ), $user->user_login ) . "\r\n\r\n";
			/* translators: %s: User email address. */
			$message .= sprintf( __( 'Email: %s' ), $user->user_email ) . "\r\n";

			$wp_new_user_notification_email_admin = array(
				'to'      => get_option( 'admin_email' ),
				/* translators: New user registration notification email subject. %s: Site title. */
				'subject' => __( '[%s] New User Registration' ),
				'message' => $message,
				'headers' => '',
			);

			/**
			 * Filters the contents of the new user notification email sent to the site admin.
			 *
			 * @param array $wp_new_user_notification_email_admin {
			 *     Used to build wp_mail().
			 *
			 * @type string $to The intended recipient - site admin email address.
			 * @type string $subject The subject of the email.
			 * @type string $message The body of the email.
			 * @type string $headers The headers of the email.
			 * }
			 *
			 * @param WP_User $user User object for new user.
			 * @param string $blogname The site title.
			 *
			 * @since 4.9.0
			 *
			 */
			$wp_new_user_notification_email_admin = apply_filters( 'wp_new_user_notification_email_admin', $wp_new_user_notification_email_admin, $user, $blogname );

			wp_mail(
				$wp_new_user_notification_email_admin['to'],
				wp_specialchars_decode( sprintf( $wp_new_user_notification_email_admin['subject'], $blogname ) ),
				$wp_new_user_notification_email_admin['message'],
				$wp_new_user_notification_email_admin['headers']
			);

			if ( $switched_locale ) {
				restore_previous_locale();
			}
		}

		/**
		 * Filters whether the user is notified of their new user registration.
		 *
		 * @param bool $send Whether to send the email. Default true.
		 * @param WP_User $user User object for new user.
		 *
		 * @since 6.1.0
		 *
		 */
		$send_notification_to_user = apply_filters( 'wp_send_new_user_notification_to_user', true, $user );

		// `$deprecated` was pre-4.3 `$plaintext_pass`. An empty `$plaintext_pass` didn't sent a user notification.
		if ( 'admin' === $notify || true !== $send_notification_to_user || ( empty( $deprecated ) && empty( $notify ) ) ) {
			return;
		}

		if ( ! empty( get_option( 'bf_new_user_notification' ) ) ) {
			$message = get_option( 'bf_new_user_notification' );

			if ( str_contains( $message, '[username]' ) ) {
				$message = str_replace( '[username]', $user->user_login, $message );
			}

		}

		$key = get_password_reset_key( $user );
		if ( is_wp_error( $key ) ) {
			return;
		}

		$switched_locale = switch_to_user_locale( $user_id );

//		$message .= network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user->user_login ), 'login' ) . "\r\n\r\n";

		if ( str_contains( $message, '[password_url]' ) ) {
			$message = str_replace( '[password_url]', network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user->user_login ), 'login' ) . "\r\n\r\n", $message );
		}

		$wp_new_user_notification_email = array(
			'to'      => $user->user_email,
			/* translators: Login details notification email subject. %s: Site title. */
			'subject' => __( '[%s] Login Details' ),
			'message' => $message,
			'headers' => '',
		);

		/**
		 * Filters the contents of the new user notification email sent to the new user.
		 *
		 * @param array $wp_new_user_notification_email {
		 *     Used to build wp_mail().
		 *
		 * @type string $to The intended recipient - New user email address.
		 * @type string $subject The subject of the email.
		 * @type string $message The body of the email.
		 * @type string $headers The headers of the email.
		 * }
		 *
		 * @param WP_User $user User object for new user.
		 * @param string $blogname The site title.
		 *
		 * @since 4.9.0
		 *
		 */
		$wp_new_user_notification_email = apply_filters( 'wp_new_user_notification_email', $wp_new_user_notification_email, $user, $blogname );

		wp_mail(
			$wp_new_user_notification_email['to'],
			wp_specialchars_decode( sprintf( $wp_new_user_notification_email['subject'], $blogname ) ),
			$wp_new_user_notification_email['message'],
			$wp_new_user_notification_email['headers']
		);

		if ( $switched_locale ) {
			restore_previous_locale();
		}
	}
}