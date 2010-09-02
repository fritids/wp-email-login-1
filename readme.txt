=== WP Email Login ===
Contributors: beaulebens, r-a-y
Tags: email, login, authentication, users, admin
Requires at least: 2.8
Tested up to: 3.1-alpha
Stable tag: trunk
Donate link: http://dentedreality.com.au

Lets you use your email address to log into your WordPress account instead of a username.

== Description ==
Lets you use your email address to log into your WordPress account instead of a username.

Since email addresses are required to be unique within WordPress anyway, they also make good identifiers for logging in.

== Installation ==
1. Unzip and upload `/wp-email-login/` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Log out, log in again using the email address associated with your WordPress account.

== Changelog ==
= 4.0 =
* Add prompt to login form that you can use Email as well
* Use get_user_by_email(), props Hendry (via email)
* Remove support for versions older than 2.8 -- UPGRADE!

= 3.0 =
* Cut down to use new filters

= 2.0 =
* Now supports XML-RPC authentication using email address thanks to r-a-y!

= 1.0 =
* Initial release