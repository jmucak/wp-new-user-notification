<?php
if ( ! empty( $_POST['bf_new_user_notification'] ) ) {
	update_option( 'bf_new_user_notification', sanitize_textarea_field( $_POST['bf_new_user_notification'] ) );
}
?>
<div class="wrap">
    <h1>WP New User Notification Options</h1>
    <form action="" method="POST">
        <h2>New User notification message</h2>
        <fieldset>
            <textarea name="bf_new_user_notification" id="bf_new_user_notification" cols="100"
                      rows="20"><?php if ( ! empty( get_option( 'bf_new_user_notification' ) ) ) {
		            echo wp_kses_post( get_option( 'bf_new_user_notification' ) );
	            } ?></textarea>
        </fieldset>

        <p>You can use shortcodes "[username], [password_url]"</p>

        <?php do_action('bf_new_user_notification_form'); ?>

        <fieldset>
            <input type="submit" value="Save message" name="bf_new_user_notification_submit"
                   id="bf_new_user_notification_submit" class="button button-primary">
        </fieldset>
    </form>
</div>
