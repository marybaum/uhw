=== Formidable Registration ===
Contributors: sswells
Donate link: http://formidablepro.com/donate
Requires at least: 2.8
Tested up to: 3.6.1


== Installation ==
1. Upload `formidable-registration` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu
3. Go to edit your Formidable form
4. Update the registration options that have been added at the bottom of the page


== Changelog ==

TODO:
* Add user_url dropdown
* Add WPMU options: blog name (or default to username), blog title, search engines
* BuddyPress integration
* allow fields to be added to registration page and save as user meta/blog meta
* Add option to not send WP registration email to users
* Add option to tie an avatar into the get_avatar function
* Add option to wait to register user until payment is received

= 1.08 =
* Added redirect option to frm-login shortcode
* Removed globals and constants

= 1.07 =
* Added PO file for translations
* Fixed usage of inactive registration settings when editing an entry
* Changed automatic login to use wp_ajax

= 1.06 =
* Fixed validation for editing when user ID field is not placed before other fields
* Allow admins to create new entries from back end for existing users

= 1.05 =
* Updated validation to make sure usernames and emails are still unique when editing

= 1.04 =
* Also check existence of username when an admin is creating a new user
* Make sure extra profile values like "show toolbar" are not lost if not included in a form
* Update auto-updating to work with Formidable v1.07+

= 1.03 =
* Don't require a password field when editing
* Correctly update the Website field on the user profile when using user meta "user_url"
* Added frmreg_user_data hook

= 1.02 =
* Added filter on the user role new users will be created with
* Only show user meta one time on the profile page if multiple forms are being used for the same user meta
* Fixed bug with first and last name display names

= 1.01 =
* Added option to customize welcome email
* Fixed bug causing a blank email field on edit
* Automatically add a user ID field to a registration form is there isn't one yet. This is necessary in order to update the correct user account.
* Added display name option to registration settings
* Added rich text fields to the allowed list of fields to use in registration settings

= 1.0 =
* Added login form [frm-login]
* Added login widget
* Fixed automatic login for newly created users

= 1.0rc4 =
* Moved settings for Formidable 1.6 compatibility
* Added password field support. Passwords are automatically removed from Formidable so they won't be saved in plain text.
* Check for any updated user info before showing editable profile

= 1.0rc3 =
* Fixed bug preventing the "Use Full Email Address" option from staying selected

= 1.0rc2 =
* Show the name instead of the ID for data from entries fields on the WP profile page
* Added option to use full email address as username
* Added option to not automatically log in
* Allow admins to edit and create other users from the front-end

= 1.0rc1 =
* Fixed user creation with screenname field
* Automatically log user in after submitting new form

= 1.0b3 =
* Added check boxes to user meta options
* Display user meta on profile page

= 1.0b2 =
* Updated registration to allow the entries to be edited from the backend by users other than the owner of the entry