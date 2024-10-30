=== Comment URL Filter ===
Contributors: janisi
Tags: comments, filter, url, spam, detecting
Requires at least: 4.0
Tested up to: 4.9.6
Stable tag: 1.5.2
Donate link: No donations.
License: GPL2
License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html

Simple comment SPAM detecting plugin based on URL field, [url] tags, your own spam keywords and URL count threshold.

== Description ==
Comment URL filter plugin automatically marks comment as spam, if comment URL field is not empty, or comment content has [url=][/url] tags in it.

If you need to filter comments against your own keywords, you have such option as well just add them to Comment URL filter settings page.
You might also like the feature to set allowed URL count per comment. If threshold is reached, comment is automatically marked as spam.

This plugin is a simple alternative for users who don't like Askimet plugin and dislike those heavy plugins with lot's of options.

= Features =
* Add your own spam keywords, phrases or even URL
* Set maximum URL count threshold per comment.
* Automatically filter comments with [url] tags or URL field filled with any content.

== Installation ==
Upload plugin to your plugins folder (unzip) and activate it in WP admin.

== Frequently Asked Questions ==
No questions yet.

== Screenshots ==
1. [WordPress Plugin](https://wordpress.org/plugins/comment-url-filter) - Comments
2. [WordPress Plugin](https://wordpress.org/plugins/comment-url-filter) - Options Page

== Changelog ==

= 1.5.2 =
* New: New URL count threshold values for no limit and block any count of URLs in comment.

= 1.5.1 =
* Fix: No keywords saved fix.

= 1.5 =
* New: Mark comment as SPAM if URL field starts with http, https or contains www., .com, .net, .org.

= 1.4 =
* New: Option to add spam phrases (multiple words). For example, keyword "shoes" might not be a spam keyword, but phrase "buy shoes" is spam.

= 1.3 =
* New: Option to add keywords.
* New: Option to set URL count threshold.

= 1.2 =
* Filter out comments text which contains URL tags [url=*][/url].

= 1.1 =
* Disabling notification e-mails for spam comments.

= 1.0 =
* Initial code.

== Upgrade Notice ==
* No action required.
