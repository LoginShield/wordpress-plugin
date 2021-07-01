=== LoginShield for WordPress ===
Contributors: jbuhacoff
Donate link: https://loginshield.com/
Tags: authentication, login, 2-factor, 2fa, phishing, anti-phishing, password, password-less, security
Requires at least: 4.4
Tested up to: 5.7
Requires PHP: 5.2
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Stable tag: v1.0.12

LoginShield for WordPress is a more secure login for WordPress sites. It's easy to use and protects users against password and phishing attacks.

== Description ==

[LoginShield](https://loginshield.com) is an authentication system that features one-tap login, digital signatures, strong multi-factor authentication, and phishing protection.

LoginShield for WordPress replaces the login page with the following secure sequence:

1. Prompt for username
2. If user exists and has LoginShield enabled, use LoginShield; otherwise, prompt for password

The LoginShield app is available for Android and iOS. [Get the app](https://loginshield.com/software/).

== Installation ==

This section describes how to install the plugin and get it working.

1. Add the plugin to WordPress
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to the plugin settings in WordPress
4. Tap the 'Continue' button in the plugin settings to set up your LoginShield enterprise account and start your free trial

After the plugin is set up, individual users can enable or disable LoginShield in their 'Profile' settings.

== Frequently Asked Questions ==

= What is a monthly active user? =
A monthly active user (mau) is a WordPress user who has LoginShield enabled and logs in at least one time during the calendar month. For example, if you have 5000 registered users, and 500 of them enabled LoginShield, but only 50 of them log in at least once during the month, then you will be billed for 50 monthly active users for that month.

= What happens when the free trial expires? =

If you subscribe to LoginShield before the free trial expires, the plugin will continue to work.

If you don't subscribe to LoginShield before the free trial expires, any users who had LoginShield enabled will automatically revert to using their passwords to log in.

= What happens when I uninstall the plugin? =

When the plugin is uninstalled, any users who had LoginShield enabled will automatically revert to using their passwords to log in.

= Do users have to pay for LoginShield? =

No, the site owner pays for the LoginShield subscription, and users can get the LoginShield app for free.

= Where do users get the LoginShield app? =

The plugin directs users to download the app if they don't have it, or they can go to [LoginShield software downloads](https://loginshield.com/software/) directly to download the app.

= Where can I send questions or comments? =

Please visit [the LoginShield website](https://loginshield.com) for contact information.

== Screenshots ==

1. More secure login screen prompts for username first
2. When you see the LoginShield logo, look for a push notification
3. Convenient heads-up push notification on Android, one-tap login
4. Tap notification body to see login details
5. Phishing protection detects untrusted situations; continue with email or mobile browser
6. LoginShield app includes two strong multi-factor authentication options
7. Access recovery for lost, stolen, or damaged devices
8. Easy and free sign-up for users
9. Use camera button to snap QR code when needed

== Changelog ==

= 1.0.12 =
* Fix: removed example pricing from FAQ

= 1.0.11 =
* Fix: replace embedded pricing information with link to pricing page on loginshield.com

= 1.0.10 =
* Fix: incorrect minimum WordPress version in README.txt, should be 4.4
* Fix: incorrect minimum PHP version in README.txt, should be 5.2
* Fix: endpoint URL defined in multiple places, should be defined once
* Improve: move utility functions to new util.php

= 1.0.9 =
* Fix: missing banner and icon for WordPress plugin directory

= 1.0.8 =
* Fix: incorrect stable tag
* Fix: using curl instead of wp http api
* Fix: not validating or sanitizing some request parameters
* Fix: calling file locations poorly when loading template

= 1.0.7 =
* Add: link to plugin settings under the plugin name in all plugins list
* Fix: site logo missing from login page
* Fix: redirect from LoginShield safety notice results in 404 Not Found
* Fix: user login doesn't work after uninstall/reinstall plugin and connect to same authentication realm

= 1.0.6 =
* Fix: push notifications disabled
* Improve: always use verifyssl
* Improve: use json_encode instead of string concat

= 1.0.5 =
* Fix: showing obsolete authorization token field in plugin settings
* Fix: sending constant string instead of site name to LoginShield

= 1.0.0 =
* First draft

== Upgrade Notice ==

= 1.0.6 =
Important user experience and security improvements.

= 1.0.0 =
First draft of plugin for private testing.

== Pricing ==

For current pricing and free trial details, [visit our website](https://loginshield.com/pricing/wordpress/).

== Managing your LoginShield subscription ==

You can visit [https://loginshield.com](https://loginshield.com) to manage your LoginShield subscription.

== Privacy ==
The plugin shares the following information with [LoginShield](https://loginshield.com). For more information, see our [Privacy Policy](https://loginshield.com/notice/privacy/).

= Site Name, Site Icon, and Site URL =
When you activate and set up the plugin, it sends the site name, icon, and URL to LoginShield. This information is later displayed in the LoginShield app during login. If you deactivate or uninstall the plugin, and want to delete this information, you can visit [https://loginshield.com](https://loginshield.com) to delete your LoginShield account where this information is stored.

= User Name and Email =
When a user activates LoginShield in their profile settings, their name and email address are sent to LoginShield to register the user.
This information is later used by LoginShield for service-related communication with the user, such as our phishing protection feature. We DO NOT sell or share this information with anyone else, except as required by law. If the user deactivates LoginShield, and wants to delete this information, the user can visit [https://loginshield.com](https://loginshield.com) to delete their LoginShield account.

= Client ID =
When you activate the plugin, the plugin registers itself with LoginShield and receives a unique client ID. This client ID is then associated with the site name, icon, and URL, and is used to identify the WordPress site to LoginShield in all further backend communication, and is required so that users will be able to continue to log in even when you change the site name.

= Realm-Scoped User ID =
When a user activates LoginShield in their profile settings, a unique user id is generated and sent to LoginShield to register the user. This user id is NOT the same as the user's WordPress user id, and is required so that a LoginShield user will be able to continue to log in even when they change their email address. If the user deactivates LoginShield, and wants to delete this information, the user can visit [https://loginshield.com](https://loginshield.com) to delete their LoginShield account.
