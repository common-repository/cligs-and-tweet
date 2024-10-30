=== Cli.gs and Tweet ===
Contributors: Marcel Bokhorst
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=AJSBB7DGNA3MJ&lc=US&item_name=Cli%2egs%20and%20Tweet%20WordPress%20Plugin&item_number=Marcel%20Bokhorst&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_LG%2egif%3aNonHosted
Tags: post, cligs, shorturl, twitter, tweet
Requires at least: 2.7
Tested up to: 3.0.1
Stable tag: 0.6

This simple to use plugin automatically creates a Cli.gs short URL and sends a customizable Twitter message when saving a post.

== Description ==

*This plugin is no longer maintained!*

This simple to use plugin automatically creates a [Cli.gs](http://cli.gs/ "Cli.gs") short URL and sends a customizable [Twitter](http://twitter.com/ "Twitter") message when saving a post. Short URLs can be shown above and/or below posts and can be added to the page header for [auto-discovery](http://wiki.snaplog.com/short_url "Short URL Auto-Discovery").

See [Other Notes](http://wordpress.org/extend/plugins/cligs-and-tweet/other_notes/ "Other Notes") for usage instructions.

Please report any issue you have with this plugin on the [support page](http://blog.bokhorst.biz/2354/computers-en-internet/wordpress-plugin-cli-gs-and-tweet/ "Marcel's weblog"), so I can at least try to fix it. If you rate this plugin low, please [let me know why](http://blog.bokhorst.biz/2354/computers-en-internet/wordpress-plugin-cli-gs-and-tweet/#respond "Marcel's weblog").

See my [other plugins](http://wordpress.org/extend/plugins/profile/m66b "Marcel Bokhorst").

== Installation ==

*Using the WordPress dashboard*

1. Login to your weblog
1. Go to Plugins
1. Select Add New
1. Search for Cli.gs and Tweet
1. Select Install
1. Select Install Now
1. Select Activate Plugin

*Manual*

1. Download and unzip the plugin
1. Upload the entire cligs-and-tweet/ directory to the /wp-content/plugins/ directory
1. Activate the plugin through the Plugins menu in WordPress

== Frequently Asked Questions ==

= Why Cli.gs? =

Because it seems to be [actively supported](http://wewant.cli.gs/pages/3966-cligs-feedback "Cligs Feedback Feedback Forum") and because it is [Search Engine Friendly](http://cli.gs/#seo "Search Engine Friendly").

= Who can use the tools menu? =

Users with *publish\_posts* capability.

= Who can access the settings menu? =

Users with *manage\_options* capability.

= Which version PHP is required? =

In contrast to most other similar plugins Cli.gs and Tweet does only require PHP 4.3.0 or better.

= Is this plugin multi-user? =

Yes.

= How can I change the styling? =

1. Copy *wp-cligs-and-tweet.css* to your theme directory to prevent it from being overwritten by an update
2. Change the style sheet to your wishes; the style sheet contains documentation

= How can I create a new Cli.gs short URL? =

1. Go to the Customs Fields of the post
1. Delete the custom field with the name *cltw\_cligs\_url*
1. Update the post

= How can I send a Twitter message again? =

See previous question and replace *cltw\_cligs\_url* by *cltw\_tweet_id*.

= How are private and password protected posts/pages handled? =

In general URLs are created and Twitter messages are sent for published public posts/pages only, including password protected posts/pages.

= Are Twitter messages sent if I use the tool to create URLs for existing posts or pages? =

No, currently not.

= Where can I ask questions, report bugs and request features? =

You can write a comment on the [support page](http://blog.bokhorst.biz/2354/computers-en-internet/wordpress-plugin-cli-gs-and-tweet/ "Marcel's weblog").

== Screenshots ==

1. The Cli.gs URL above a post

== Changelog ==

= 0.6 =
* 'I have donated' removes donate button

= 0.5.2 =
* Added link to Privacy Policy of Sustainable Plugins Sponsorship Network
* Added option 'I have donated to this plugin'
* Moved Sustainable Plugins Sponsorship Network banner to top

= 0.5.1 =
* Participating in the [Sustainable Plugins Sponsorship Network](http://pluginsponsors.com/ "PluginSponsors.com")

= 0.5 =
* Conditional include of XML parser class

= 0.4.3 =
* Fix to make class PHP 4 compatible

= 0.4.2 =
* Added Chinese translation (zh\_CN) by *Ariagle*

= 0.4.1 =
* Added German translation (de\_DE) by *Heiko Bartsch \[mai 'kju:t̬i\]*

= 0.4 =
* Added *htmlspecialchars* to process text before/after
* Added *action* attribute to comply with html rules
* Added option to exclude individual posts/pages

= 0.3 =
* Added an option to disable showing URLs on the front page
* Hiding URLs when rendering and not after rendering
* Displaying post type (post/page) when creating URLs for existing posts/pages
* Updated documentation

= 0.2 =
* Using *$wpdb->users/usermeta/postmeta*
* Tool to create URLs for existing posts

= 0.1 =
* Initial version

= 0.0 =
* Development version

== Usage ==

1. Go to Tools, Cli.gs and Tweet
1. Enter your Twitter user name and password
1. Save the settings

These settings are user specific. Administrators can set site-wide options using the Cli.gs and Tweet settings menu.

== Acknowledgments ==

This plugin uses:

* [XML Parser Class](http://www.criticaldevelopment.net/xml/ "XML Parser Class")
by *Adam A. Flynn* and published under the GNU Lesser General Public License version 2

* [jQuery JavaScript Library](http://jquery.com/ "jQuery") published under both the GNU General Public License and MIT License
