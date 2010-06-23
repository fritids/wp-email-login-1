<?php
/*
Plugin Name: Email Login
Plugin URI: http://dentedreality.com.au/projects/wp-plugin-email-login/
Description: Allows you to log into WordPress (directly or via XML-RPC) using your email address instead of a(nother) username.
Author: Beau Lebens, r-a-y
Version: 2.0
Author URI: http://dentedreality.com.au/
*/

global $wp_version;

if ( version_compare( $wp_version, '2.8', '>=' ) ) {
	remove_filter( 'authenticate', 'wp_authenticate_username_password', 20, 3 );
	add_filter( 'authenticate', 'dr_email_login_authenticate', 20, 3 );
}
else {
	add_action( 'wp_authenticate', 'dr_email_login', false, 2 );
}

/**
 * Allow the use of an email address for login.
 * 
 * Basically a copy of wp_authenticate_username_password(), but 
 * with a check to see if an email address is passed as a username. 
 * If an email address is passed, try to match email with a username.
 * If no matching username, then continue as normal.
 *
 * @param object $user The User Object
 * @param string $username User's username or email address.
 * @param string $password User's password.
 * @return object Either WP_Error on failure, or WP_User on success.
 */
function dr_email_login_authenticate( $user, $username, $password ) {
	global $wpdb;

	if ( is_a( $user, 'WP_User' ) ) { return $user; }

	if ( empty( $username ) || empty( $password ) ) {
		$error = new WP_Error();

		if ( empty( $username ) )
			$error->add( 'empty_username', __( '<strong>ERROR</strong>: The username field is empty.' ) );

		if ( empty( $password ) )
			$error->add( 'empty_password', __( '<strong>ERROR</strong>: The password field is empty.' ) );

		return $error;
	}

	if ( is_email( $username ) ) {
		$found = $wpdb->get_var( $wpdb->prepare( "SELECT user_login FROM $wpdb->users WHERE user_email = %s", $username ) );
		$username = $found ? $found : $username;
	}

	$userdata = get_user_by( 'login', $username );

	if ( !$userdata ) {
		return new WP_Error( 'invalid_username', sprintf( __( '<strong>ERROR</strong>: Invalid username. <a href="%s" title="Password Lost and Found">Lost your password</a>?' ), site_url( 'wp-login.php?action=lostpassword', 'login' ) ) );
	}
	
	if ( function_exists( 'is_multisite' ) ) :
		if ( is_multisite() ) {
		        // Is user marked as spam?
		        if ( 1 == $userdata->spam )
		                return new WP_Error( 'invalid_username', __( '<strong>ERROR</strong>: Your account has been marked as a spammer.' ) );
	
		        // Is a user's blog marked as spam?
		        if ( !is_super_admin( $userdata->ID ) && isset( $userdata->primary_blog ) ) {
		                $details = get_blog_details( $userdata->primary_blog );
		                if ( is_object( $details ) && $details->spam == 1 )
		                        return new WP_Error( 'blog_suspended', __( 'Site Suspended.' ) );
		        }
		}
	endif;

	$userdata = apply_filters( 'wp_authenticate_user', $userdata, $password );
	if ( is_wp_error( $userdata ) ) {
		return $userdata;
	}

	if ( !wp_check_password( $password, $userdata->user_pass, $userdata->ID ) ) {
		return new WP_Error( 'incorrect_password', sprintf( __( '<strong>ERROR</strong>: Incorrect password. <a href="%s" title="Password Lost and Found">Lost your password</a>?' ), site_url( 'wp-login.php?action=lostpassword', 'login' ) ) );
	}

	$user =  new WP_User( $userdata->ID );
	return $user;
}


/**
 * For versions of WP older than 2.8
 */
function dr_email_login( $user, $pass ) {
	global $wpdb;
	if ( is_email( $user ) ) {
		$found = $wpdb->get_var( $wpdb->prepare( "SELECT user_login FROM $wpdb->users WHERE user_email = %s", $user ) );
		$user = $found ? $found : $user;
	}
	return;
}


/**
 * Add compatibility for WPMU 2.9.1 and WPMU 2.9.2
 */
if ( !function_exists( 'is_super_admin' ) ) :
	function get_super_admins() {
	    global $super_admins;

	    if ( isset( $super_admins ) )
	        return $super_admins;
	    else
	        return get_site_option( 'site_admins', array( 'admin' ) );
	}

	function is_super_admin( $user_id = false ) {
	    if ( ! $user_id ) {
	        $current_user = wp_get_current_user();
	        $user_id = ! empty( $current_user ) ? $current_user->id : 0;
	    }

	    if ( ! $user_id )
	        return false;

	    $user = new WP_User( $user_id );

	    if ( is_multisite() ) {
	        $super_admins = get_super_admins();
	        if ( is_array( $super_admins ) && in_array( $user->user_login, $super_admins ) )
	            return true;
	    } else {
	        if ( $user->has_cap( 'delete_users' ) )
	            return true;
	    }

	    return false;
	}
endif;

?>