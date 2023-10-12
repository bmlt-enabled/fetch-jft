=== Fetch JFT ===

Contributors: pjaudiomv, klgrimley, bmltenabled
Tags: jft, just for today, narcotics anonymous, na
Tested up to: 6.3.1
Stable tag: 1.8.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

Fetch JFT is a plugin that pulls the Just For Today from jftna.org and puts it on your page or post. Use the widget Fetch JFT to add to your sidebar or footer.

SHORTCODE
Basic: [jft]
Languages: Danish, English, Farsi, French, German, Italian, Japanese, Portuguese, Russian, Spanish, Swedish [jft language=""]
Layout: Table, Block [jft layout=""]
-- Shortcode parameters can be combined and accept either uppercase or lowercase

EXAMPLES

<a href="https://www.mvana.org/just-for-today/">https://www.mvana.org/just-for-today/</a>
<a href="https://newyorkna.org/information/just-for-today/">https://newyorkna.org/information/just-for-today/</a>
<a href="https://www.na-ireland.org/for-our-members/just-for-today/">https://www.na-ireland.org/for-our-members/just-for-today/</a>
<a href="https://hillcountryna.org/just-for-today/">https://hillcountryna.org/just-for-today/</a>
<a href="https://www.otwna.org/just-for-today/">https://www.otwna.org/just-for-today/</a>
<a href="http://emeraldcoastareana.org/">http://emeraldcoastareana.org/</a>
<a href="http://suncityna.org/members/just-for-today/">http://suncityna.org/members/just-for-today/</a>

As A Widget
<a href="http://crossroadsarea.org/events-activities/">http://crossroadsarea.org/events-activities/</a>

MORE INFORMATION

<a href="https://github.com/bmlt-enabled/fetch-jft" target="_blank">https://github.com/bmlt-enabled/fetch-jft</a>

== Installation ==

This section describes how to install the plugin and get it working.

1. Download and install the plugin from WordPress dashboard. You can also upload the entire Fetch JFT Plugin folder to the /wp-content/plugins/ directory
2. Activate the plugin through the Plugins menu in WordPress
3. Add [jft] shortcode to your Wordpress page/post or add widget Fetch JFT to your sidebar, Footer etc.

== Screenshots ==

1. screenshot-1.png

== Changelog ==

= 1.8.1 =

* Added a settings link to the plugins page.

= 1.8.0 =

* Fix for Portuguese JFT not displaying properly.
* Refactored codebase.

= 1.7.0 =

* Adds User-Agent headers.

= 1.6.9 =

* Various PHP warning fixes.

= 1.6.8 =

* Fixed Farsi from NA Iran.
* Removing Arabic as remote JFT is down.

= 1.6.7 =

* Version bump.

= 1.6.6 =

* Fix PHP Deprecation notice for Jalali date conversion.

= 1.6.5 =

* Fix for English JFT due to some days not using proper html entities.

= 1.6.4 =

* Fix for Portuguese JFT.

= 1.6.3 =

* Fix for Spanish JFT.
* Added better checks for character encoding.

= 1.6.2 =

* Fix for improperly UTF8 encoding for english JFT.

= 1.6.1 =

* Fix for Italian JFT.

= 1.6.0 =

* Fix for Portuguese JFT.

= 1.5.9 =

* Fix to better comply with WordPress best practices.
* Fix for missing CSS attributes.

= 1.5.8 =

* Fix for Danish.

= 1.5.7 =

* Added Farsi language from NA Iran.

= 1.5.6 =

* Added Arabic language from NA Kuwait.

= 1.5.5 =

* Added Japanese language from NA Japan.

= 1.5.4 =

* Added Russian language from NA Russia.

= 1.5.3 =

* Added Danish language from NA Denmark.

= 1.5.2 =

* Added Swedish language from NA Sweden.

= 1.5.1 =

* Added Italian language from NA Italy.

= 1.5.0 =

* Added German language from the German Speaking Region of NA.

= 1.4.4 =

* added screenshot.

= 1.4.3 =

* one more bump.

= 1.4.2 =

* Bumping version, forgot to tag.

= 1.4.1 =

* Added custom css text box for settings page.

= 1.4.0 =

* Tested for WP 5.0.
* Sanitize attributes.
* Add logo.

= 1.3.4 =

* Added Portuguese language from NA Brazil.

= 1.3.3 =

* Make use of Wordpress built in wp_remote_fopen function as some servers disable php’s file_get_contents. This will try fopen first then curl.

= 1.3.2 =

* Fetch French JFT over HTTPS.

= 1.3.1 =

* Suppress warnings and moved if tag for block layout up.

= 1.3.0 =

* Added support for up to php 7.2.5.
* Removed external lib simple_php_dom in favor of PHPs built in DOM.

= 1.2.2 =

* Fixed warnings and changed the way things get parsed.

= 1.2.1 =

* Wrap whole copyright in anchor tag to stay consistent.

= 1.2.0 =

* Added language options. Added Fetch JFT Settings page. Added ability to use either the standard html table or css block elements for English. Added shortcode attributes.

= 1.1.1 =

* Added Subscribe link by default, renamed widget to Fetch JFT to match plugin name.

= 1.1.0 =

* Added Widget to Plugin

= 1.0.2 =

* changed name

= 1.0.1 =

* Cleaned up for Wordpress

= 1.0.0 =

* Initial Release
