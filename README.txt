=== LoginShield for WordPress ===
Contributors: jbuhacoff
Donate link: https://loginshield.com/
Tags: authentication, login, 2-factor, 2fa, phishing, anti-phishing, password, passwordless, password-less, security, mitm
Requires at least: 4.4
Tested up to: 5.7
Requires PHP: 5.2
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Stable tag: v1.0.14

LoginShield for WordPress is the secure and convenient way to login to your WordPress site. It's easy to use and protects users against password and phishing attacks.

== Description ==

[LoginShield](https://loginshield.com) is an authentication system that features one-tap login, digital signatures, strong multi-factor authentication, and phishing protection. This is a passwordless login solution. Login with one tap instead of a password!

LoginShield for WordPress replaces the login page with the following secure sequence:

1. Prompt for username
2. If user exists and has LoginShield enabled, use LoginShield; otherwise, prompt for password

The LoginShield app is available for Android and iOS. [Get the app](https://loginshield.com/software/).

== Benefits ==

* Eliminate password and phishing attacks on user accounts
* Quick and secure way to log in with one-tap, passwordless login
* Don't need to remember a password
* Don't need to rotate passwords for safety

== Features ==

= Self-service activation =
After you install and set up the LoginShield plugin, users can easily activate LoginShield for themselves in their profile settings page.

= One-tap login =
You and your users can log in to your WordPress site with just one tap.

For more information, read about [one-tap login](https://loginshield.com/article/one-tap-login/).

= Digital signatures =
Some of the most common ways that accounts are hacked are weak passwords and stolen passwords. This is why so many sites require users to come up with passwords that have special characters, and to change their passwords periodically (in case a current password was reused somewhere and cracked). But this is annoying to users and doesn't guarantee they will actually pick a secure password.

LoginShield uses digital signatures for authentication instead of passwords. This makes LoginShield a passwordless authentication system.

Digital signatures are far stronger protection for an account than passwords, and they don't require the user to come up with anything or remember anything. LoginShield automatically generates and uses a separate credential for each website, so you can use the same LoginShield app to login to multiple sites.

LoginShield uses strong, modern cryptographic algorithms and parameters to ensure your accounts get the best protection available.

= Strong multi-factor authentication =
The LoginShield app itself can be protected by a password (which never leaves the mobile device) or a fingerprint. This is far better protection than the standard two-factor authentication that many sites use.

For more information, read about [authentication factors](https://loginshield.com/article/authentication-factors/).

= Phishing protection =
LoginShield is the ONLY authentication solution to offer phishing protection.

Many data breaches start with a phishing email, tricking the user to log in to the attacker's website that is impersonating the real website. Any website that uses passwords to log in is vulnerable to this.

Websites that use standard two-factor authentication codes are also vulnerable -- whether they send the code via SMS or use an OTP app to display it, the fact that you enter that code into the website after the password prompt means a phishing attacker will also get the code.

Websites that use an authenticator app with push notifications are ALSO vulnerable to this, because they don't confirm that you're at the correct website when you tap the "login" button in the app.

Only LoginShield is able to detect that the user is not at a trusted website and route the user to the correct website, completely circumventing a credential-theft phishing attack.

For more information, read about [phishing protection](https://loginshield.com/article/phishing-protection/).

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

= 1.0.14 =
* Fix: realm not found error when connecting to LoginShield account

= 1.0.13 =
* Doc: edited plugin description

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
