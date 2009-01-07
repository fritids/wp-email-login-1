<?php
/*
Plugin Name: Email Login
Plugin URI: http://dentedreality.com.au/projects/wp-plugin-email-login/
Description: Alllows you to log into WordPress using your email address instead of a(nother) username.
Author: Beau Lebens
Version: 1.0
Author URI: http://dentedreality.com.au/
*/

/**
 * Allow the use of an email address for login. This just looks up the
 * matching username for an email address, and then continues like normal.
 * Parameters are references, so we just update in place.
 * @param String $user The username they entered (in this case email)
 * @param String $pass The password they entered
**/
function dr_email_login($user, $pass) {
	global $wpdb;
	if (is_email($user)) {
		$found = $wpdb->get_var($wpdb->prepare("SELECT user_login FROM $wpdb->users WHERE user_email = '%s'", $user));
		$user = $found ? $found : $user;
	}
	return;
}
add_action('wp_authenticate', 'dr_email_login', false, 2);

?>
