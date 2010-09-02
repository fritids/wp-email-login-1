<?php
/*
Plugin Name: Email Login
Plugin URI: http://dentedreality.com.au/projects/wp-plugin-email-login/
Description: Allows you to log into WordPress (directly or via XML-RPC) using your email address instead of a(nother) username.
Author: Beau Lebens, r-a-y
Version: 4.0
Author URI: http://dentedreality.com.au/
*/

/**
 * Replace default filter for authentication with a new one that looks up a username first if an email is passed in
 */
remove_filter( 'authenticate', 'wp_authenticate_username_password', 20, 3 );
add_filter( 'authenticate', 'dr_email_login_authenticate', 20, 3 );

/**
 * If an email address is entered in the username box, then look up the matching username and authenticate as per normal, using that.
 *
 * @param string $user 
 * @param string $username 
 * @param string $password 
 * @return Results of autheticating via wp_authenticate_username_password(), using the username found when looking up via email.
 */
function dr_email_login_authenticate( $user, $username, $password ) {
	$user = get_user_by_email( $username );
	if ( $user )
		$username = $user->user_login;
	
	return wp_authenticate_username_password( null, $username, $password );
}

/**
 * Add compatibility for WPMU 2.9.1 and WPMU 2.9.2, props r-a-y
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

/**
 * Modify the string on the login page to prompt for username or email address
 */
function username_or_email_login() {
	?><script type="text/javascript">
	// Form Label
	document.getElementById('loginform').childNodes[1].childNodes[1].childNodes[0].nodeValue = 'Username or Email';
	
	// Error Messages
	if ( document.getElementById('login_error') )
		document.getElementById('login_error').innerHTML = document.getElementById('login_error').innerHTML.replace( 'username', 'username or email' );
	</script><?php
}
add_action( 'login_form', 'username_or_email_login' );