=== MP Core ===
Contributors: mintplugins, johnstonphilip
Donate link: http://mintplugins.com/
Tags: Core, Functions, Classes, Utility
Requires at least: 3.5
Tested up to: 4.1
Stable tag: 1.0.2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A plugin for developers which contains a group of classes and functions used to power plugins and themes. The idea here is keeping all the code that we re-use over and over, and keeping it in one place so it never needs to be re-written - saving time and being efficient.

== Description ==

A plugin for developers which contains a group of classes and functions used to power plugins and themes. The idea here is keeping all the code that we re-use over and over, and keeping it in one place so it never needs to be re-written - saving time and being efficient.

On its own this plugin doesn’t actually do anything. Rather, it’s functions and classes can be utilized by other plugins and themes.

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload the 'mp-core’ folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Build Bricks under the “Stacks and Bricks” menu. 
4. Visit the Documentation/API page to learn how to build on MP Core

== Frequently Asked Questions ==

See full instructions at https://mintplugins.com/doc/mp-core-api/

== Screenshots ==


== Changelog ==

= 1.0.2.0 = May 13, 2015
* Revert Change in 1.0.1.9 of mp_core_get_post_meta back to the way it was in 1.0.1.8.

= 1.0.1.9 = May 13, 2015
* Use esc_url_raw instead of mp_core_add_query_arg for plugin checker/installer
* Better message if "allow_url_fopen" isn't "on" in class-plugin-installer.php. Lets user know what action to take.
* Hover animations are now disabled for touch devices because hovering isn't a thing on touch devices. Previously, if the user clicked once, the animation fired. This proved to be frustrating for mobile users so the action is now disabled entirely to reduce clicks.
* Change: mp_core_get_post_meta now only returns the "default" setting if its value has never been saved before. This way, if the user desires to have a "blank" value saved, they can. Previously, if the value was blank, it would return the default value no matter what.
* Fix: Preserve keys in mp_core_insert_meta. When inserting a meta option using mp_core_insert_meta, the meta key was removed. If you wanted to then insert more meta options later in the page, you couldn't because the keys were removed and replaced with array numbers eg array[0] instead of array['mykey'].

= 1.0.1.8 = April 30, 2015
* Changed 16x9 image from gif to transparent png
* Added html5 video tag support in mp_core_oembed_get function with options for loop, autoplay, no controls.

= 1.0.1.7 = April 25, 2015
* Proper error for ajax plugin updates when failed
* esc_url for mp_core_add_query_arg now uses esc_url_raw
* Better error for bulk installs with incorrect license.

= 1.0.1.6 = April 24, 2015
* Removed previously jquery.velocity.min.js (deprecated as of version 1.0.1.1) and was replaced with velocity.min.js.
* Make sure hidden meta fields are hidden always
* Added Isotope JS to utility js scripts folder.
* Added mp_core_get_post_meta_multiple_checkboxes function for retrieving multiple checkboxes easily.
* Addon-Directory pages have nav at bottom and top now
* Security Fix: All "add_query_arg" function changed to "mp_core_add_query_arg" to properly sanitize the URLs
* Security Fix: All "remove_query_arg" function changed to "mp_core_remove_query_arg" to properly sanitize the URLs

= 1.0.1.5 = April 2, 2015
* Added admin notice for if Theme is installed but not active to the Plugin Checker class.

= 1.0.1.4 = April 2, 2015
* Convert meta field "labels" to "divs" so that links in field descriptions are clickable.
* Add jquery to simple action page function
* Plugin Installer: Fixed issue where "Successfully Installed" was shown even if it wasn't. 
* In the simple action page misc function, a html_head output was added for plugins to use javascript and custom css on those pages.

= 1.0.1.3 = March 26, 2015
* Make sure stroke default opacities are 100%

= 1.0.1.2 = March 24, 2015
* TinyMCE Fix: Only re-initialize TinyMCE after Repeaters duplicated or re-ordered if it was previously set to be in “Visual” mode. Otherwise don’t reinitialize. This fixes the issue of having multiple text areas in one area upon repeater changes.
* Removed "Please Wait" from "Installing Items" in plugin installer.
* Customizer: fontsize, borderwidth, borderradius control options added.
* Dashicon Support added to shortcode class
* Animation Function Updates
* Drop Shadow Function webkit/firefox options added.
* waypoints.js updated to 3.1.1
* Metabox css upgrades for repeaters on right side
* Add Waypoints function to animate its own element

= 1.0.1.1 = February 27, 2015
* Add 'site_activating' parameter to all update and licensing functions.
* Set the default for shadow blur to 50 to make default accurate.
* Fix for featured images on SSL using mp_core_the_featured_image
* Reduce unneeded nonces for Metabox saves.
* BIG METABOX CHANGE: Only save meta if field value is different. When retrieving meta fields, make sure defaults in code use mp_core_get_post_meta and have an accurate default set.
* Deprecated jquery.velocity.min.js and replaced it with velocity.min.js (version 1.2.1)
* Metabox Jquery: Remove the 'checked' attr when duplicating a repeat in a metabox.
* Added versioning to enqueues in the Metabox class.
* Various animation function upgrades.
* Change “Install Items” > “Installing Required Items” in the plugin checker.
* Make checkboxes in repeaters save an empty value (before they were removed from the repeater array if unchecked). This way we can check if the field key exists to know whether it has ever been saved before.
* Added function called “mp_core_get_post_meta_checkbox” which allows us to set a default for checkboxes when retrieving them.
* Animation upgrades for background color.
* Disabled 3 plugin loop check introduced in 1.0.1.0.

= 1.0.1.0 = February 4, 2015
* Plugin Checker Class: Only install 3 dependant plugins at a time.

= 1.0.0.9 = February 1, 2015
* You can now set a font size in pixels through the customizer class using the arg font-size(px)
* License Checking: Now we strip any slashes off the end of the api url so we don't have duplicates and also set sslverify to false because nginx's ssl wasn't verifying even though the SSL was valid.
* Enqueue admin css on every admin page using mp_core_enqueue_admin_scripts
* Shortcode Insert Class can now have conditional fields depending on parent fields.
* Post Exists function: now returns false if the post is in the trash.
* Directory: Images force load over ssl.
* Directory: Load MP Core Directory over non ssl because it doesn’t work with NGINX but does over non SSL.
* Added new functions for handling the output of css lines pertaining to box shadows and borders (strokes)
* Metabox Input Ranges now have number fields that users can type in.
* Showhiders can now repeat if they have a repeater AND field_showhider_repeats is true
* Drop Shadow Function Added

= 1.0.0.8 = January 18, 2015
* Improved animation for touch devices. Now the first click fires the animation and the second carries out the default action.
* Fixed TinyMCE Text Areas when repeated. Now they reset to the Visual Mode when moved in the DOM.
* Improved Metabox Sanitation prior to saves.
* Added mp_core_post_exists function to check if a post exists.
* Fixed gap below videos through mp_core_oembed_get by removing float:left and replacing with vertical-align:top;

= 1.0.0.7 = January 4, 2015
* Made field presets only show up if the field has never been saved (for metaboxes).
* Made checkboxes able to be checked by default in metaboxes.
* Removed gap below videos through mp_core_oembed_get by floating left.
* Removed global post from mp_core_the_featured_image because it was un-needed and caused a bug.
* Divide by zero fix for aq_resizer.

= 1.0.0.6 = December 23, 2014
* Fixed Directory layout css where boxes weren’t lining up
* Added Mint Plugins as a tab on the Plugins > Add New screen.
* Fixed the hiding of TinyMCE Controls (this broke in v1.0.0.5)

= 1.0.0.5 = December 20, 2014
* Changes the save routine for single meta fields to match the changes made for repeaters in 1.0.0.4 (quotes, esc_html)
* Make metaboxes save every field upon every save. This was supposed to save time but was failing.
* Made plugin updater only run if is_admin

= 1.0.0.4 = December 12, 2014
* Changed metabox save routine to save all fields if it is the first time it is ever being saved
* Took port number out of mp_core_get_current_url function. 
* Changed if theme update routine gets no response from server (if repo server down etc) , it now fails silently
* Changed html_entities on metabox saves to esc_html
* Added mp_core_fix_nbsp function to fix the black diamond question mark that shows up in tinymce fields

= 1.0.0.3 = December 1, 2014
* Added conditionally visible metafields - only shown if their parent has value X
* Added mp_core_object_to_array, mp_core_array_to_object, mp_core_time_ago functions
* Added activation tracking.

= 1.0.0.2 = November 19, 2014
* Added the mp_core_value_exists function for checking saved values
* Used new mp_core_value_exists to apply field defaults if the user has never saved it.
* Added Time Ago function for getting the date in a string like (1 week ago) etc
* Fixed issue with Installation of plugins from directory pages
* Added function which wraps media items in their html tag equivalent (mp_core_wrap_media_url_in_html_tag)

= 1.0.0.1 = October 9, 2014
* Made Theme and Plugin updates more efficient
* Fixed bug in plugin installs via directory tabs
* Included Scale in Animation Functions
* mp_core_the_featured_image now only crops the image if a height is provided

= 1.0.0.0 = September 29, 2014
* Original production release following 167 beta releases since Feb 13, 2013
