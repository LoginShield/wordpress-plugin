=== LoginShield for WordPress ===
Contributors: jbuhacoff
Donate link: https://loginshield.com/
Tags: authentication, login, 2-factor, 2fa, phishing, anti-phishing, password, password-less, security
Requires at least: 3.0.1
Tested up to: 5.7
Requires PHP: 5.6.20
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Stable tag: trunk

LoginShield for WordPress is a more secure login for WordPress sites. It's easy to use and protects users against password and phishing attacks.

== Description ==

[LoginShield](https://loginshield.com) is an authentication system that features one-tap login, digital signatures, strong multi-factor authentication, and phishing protection.

LoginShield for WordPres replaces the login page with the following secure sequence:

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

The free trial is for two weeks (14 days). After the free trial, to continue using LoginShield for WordPress you need to have a LoginShield subscription.

Subscription price: $10/month base fee + $0.05/month for each monthly active user. For current pricing, [visit our website](https://loginshield.com/pricing/wordpress/).

A monthly active user is a WordPress user who has LoginShield enabled and logs in at least one time during the calendar month. For example, if you have 5000 registered users, and 1000 of them enabled LoginShield, but only 500 of them log in at least once during the month, then you will be billed for 500 monthly active users for that month.

The monthly active user pricing makes it possible to provide you with a very predictable price. You pay the same rate, regardless of how many times a user logs in to the site.

== Managing your LoginShield subscription ==

You can visit [https://loginshield.com](https://loginshield.com) to manage your LoginShield subscription.
