=== Simple Ads Manager ===
Contributors: minimus
Donate link: http://www.simplelib.com/archives/wordpress-plugin-simple-ads-manager/
Tags: ad, ads, adbrite, adgridwork, adify, admin, adpinion, adroll, ads, adsense, adserver, advertisement, advertising, affiliate, banner, banners, chitika, cj, commercial, commission, crispads, dfp, google, income, junction, link, manager, media, money, plugin, random, referral, revenue, rotator, seo, server, shoppingads, widget, widgetbucks, wptouch
Requires at least: 3.9
Tested up to: 4.3
Stable tag: 2.9.6.121
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Advertisement rotation system with a flexible logic of displaying advertisements.

== Description ==

Simple Ads Manager is easy to use plugin providing a flexible logic of displaying advertisements.

[![endorse](https://api.coderwall.com/minimus/endorsecount.png)](https://coderwall.com/minimus)

= Features =
* Flexible logic of advertisements rotation based on defined weight of each advertisement in group (Ads Place)
* Custom default ad for each Ads Place
* Codes of Google *DoubleClick for Publishers* (DFP) supports
* More flexibility of displaying ads by using Ads Zone selector
* Allowed types of ad's codes are HTML, javascript, PHP
* Outputting ads as widget
* Outputting ads as shortcodes in any place of single post/page content
* Outputting ads in any place of theme template using functions
* Customizable outputting ads as block of ads
* Automatic outputting ads in single post/page if allowed
* Customizable limitation of displaying advertisements by types of page
* Customizable limitation of displaying advertisements on single post/page by post/page ID (IDs)
* Customizable limitation of displaying advertisements on single post page or category archive page by category (categories)
* Customizable limitation of displaying advertisements on single post page or author archive page by author (authors)
* Customizable limitation of displaying advertisements on single post page or tag archive page by tag (tags)
* Customizable limitation of displaying advertisements on custom type single post page or custom type archive page by Custom Type (Types)
* Customizable blocking of displaying advertisements on single post/page by post/page ID (IDs)
* Customizable blocking of displaying advertisements on single post page or category archive page by category (categories)
* Customizable blocking of displaying advertisements on single post page or author archive page by author (authors)
* Customizable blocking of displaying advertisements on single post page or tag archive page by tag (tags)
* Customizable blocking of displaying advertisements on custom type single post page or custom type archive page by Custom Type (Types)
* Schedule of displaying each advertisement if allowed
* Customizable limitation of displaying advertisements by hits
* Customizable limitation of displaying advertisements by clicks
* Statistics of hits
* Statistics of clicks (excluding iframes and swf-banners)
* Customizable accuracy of bots and crawlers detection
* Counting revenue from ads placement, display ads and clicks on advertisements
* Compatible with all caching plugins
* Compatible with the WPtouch plugin (Free Edition)

= Requirements =
* PHP 5.2.14+

= Available languages =
* English
* Russian
* German by [Fabian Krenzler](http://www.ktraces.de/) and **Ulrich Simon**
* Belarusian by Alexander Ovsov ([Web Geek Sciense](http://webhostinggeeks.com/science/))
* Spanish by [xiaobai_wp](http://wordpress.org/extend/plugins/profile/xiaobai_wp)
* Italian by [Alfredo](https://www.transifex.com/user/profile/alfredotranchedone/)

If you have created your own language pack, or have an update of an existing one, you can send **.po** and **.mo files** to me (minimus AT simplelib.com) so that I can bundle it into **Simple Ads Manager** plugin pack.  Also you can translate SAM on [Transifex](https://www.transifex.com/projects/p/simple-ads-manager/) site.  Just register on Transifex and make your SAM localization.

SAM Free also available at [GitHub](https://github.com/minimus/simple-ads-manager). You can fork it and make your own improvements.
  
Read more about **Simple Ads Manager** on the [plugin page](http://www.simplelib.com/archives/wordpress-plugin-simple-ads-manager/)...

== Installation ==

1. Upload plugin dir to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Change and save plugin settings if needed.
1. Create *Ads Places* and define *ads codes* for each rotating ad of *Ads Place*.

Do not upgrade this plugin from Update page! Upgrade it from Plugins page! 

== Frequently Asked Questions ==

No questions now...

== Screenshots ==

1. Ads Places List
1. Items of Ads Place List
1. Ad Editor
1. Ads Place Editor
1. Simple Ads Manager Settings
1. Ads Place Statistical Data

== Changelog ==

= 2.9.6.121 =
* The problem of arbitrary automatic insertion of ads on the main page is solved.
* According to numerous requests from users the modern control element "Slider" of the option "Access Level" was changed to the classical control element "Radio Button".
* Language pack is updated. Italian by Alfredo is added
= 2.9.5.118 =
* Possibility of SQL injection is fixed. Thanks for [Kacper Szurek](http://security.szurek.pl/).
* Minor bug resolved
= 2.9.4.116 =
* Possibility of DoS attacks is eliminated
* Flash overlay image URL is fixed ([**h8every1** aka **Anton Syuvaev**](http://h8every1.ru/))
= 2.9.3.114 =
* Overlay link for SWF banners is added. So it is possible to track views and clicks for flash banners. And also make flash banner clickable without having to edit banners in Adobe Flash. If the link for banner is not set, the swf banner is displayed as usual. Thanks for [**h8every1** aka **Anton Syuvaev**](http://h8every1.ru/).
* *noindex*, *nofollow*, *dofollow* are added.
* Some tools added
= 2.9.2.111 =
* Added manual sending reports to advertisers
* GPT tags of Google DFP now is supported
= 2.9.1.109 =
* Minor bug resolved
= 2.9.0.108 =
* The list of advertisers was improved
* Supporting for the plugin WPtouch (Free Edition) is provided
* Fallback code for Flash Banners is added
= 2.8.0.105 =
* The list of advertisers was added
* The Ads Zone Editor was improved
* The bug of pagination was resolved
= 2.7.102 =
* Potential vulnerability issue was resolved
= 2.7.101 =
* Potential vulnerability of the `add_query_arg` was fixed
* The bug known as "Disappearing Ads" was solved
= 2.7.99 =
* A bug of the statistics graph is fixed
* A bug of the using of deprecated functions is fixed
= 2.7.97 =
* SQL injection fix.
= 2.6.96 =
* Potential vulnerability issue was resolved.
* Error of detecting bots and crawlers was resolved.
= 2.5.94 =
* Indexes of plugin database tables were removed
* Custom naming of classes of the plugin tags is added
* Some minor bugs were resolved
= 2.4.91 =
* The uploading feature (user's banners without using Media Library) was removed by request of administration of wordpress.org plugins repository.
= 2.4.90 =
* The quantity of SQL of requests for each ad was reduced.
* Indices for plugin's database tables were added.
* Sequential loading of ads was changed to packet loading.
* The bug of data loading into the grid is fixed.
* Added ability to enable/disable the collection and storage of statistical data
= 2.3.85 =
* Scheduled Auto Cleaning of Statistical Data is added
* Auto inserting is changed (more objects for auto inserting of advertisements)
* Resolved for compatibility with TinyMCE 4
* Images (Ads) Loader changed to standard Wordpress Loader
* Some bugs are fixed
= 2.2.80 =
* Mailer improved
* Bugs fixed
= 2.1.77 =
* Some minor bugs of mailer are resolved
* The graphical representation of statistical data is improved
* Minor bug of banners uploading is fixed
= 2.0.74 =
* Minor bug is resolved
= 2.0.73 =
* Javascript output of ads (for caching compatibility) is added
* Custom Taxonomies restrictions are added
* Building query for SQL request is optimised
* Admin interface is improved
* Loading/Selecting banners from Wordpress Media Library is added
* Updater is fixed and improved
* Language pack folder is added
* bbPress support is added
* Some bugs are fixed
= 1.7.63 =
* Some bugs (Ads Block style, Click Tracker) are resolved.
= 1.7.61 =
* Some bugs are resolved.
= 1.7.60 =
* Minor bug is resolved (Ads Places List)
= 1.7.58 =
* Major bug is resolved (bug of database creating, not database updating)
= 1.7.57 =
* Data updating bugs of editors are fixed. Thanks to **Latibro**
* Control of Error Log is added
* Strange bug ("undefined post ID") is fixed
= 1.6.54 =
* Error Log is improved.
* Click Tracker is improved.
* Ads Block output bug is fixed.
* Ad Widget bug is fixed
= 1.5.50 =
* Error log is added
* User interface of *Ads Editor* is improved
* Categories and Tags identification is changed from *name* to *slug*
* Flash (SWF) banners support is added
* Limitations of displaying ads to users are added
* Accessing menu settings are added
= 1.4.44 =
* Minor bug (activity of ad in ads list) is fixed.
* Auto insertion of ad into the middle of post/page is added.
* Language pack is updated. Spanish language by **xiaobai_wp** is added.
= 1.3.41 =
* Minor bug fixed. Now you can define all possible limitations for Custom Types Posts too.
= 1.2.40 =
* MultiSite support is added. Thanks to *meermedia*.
* Major bug is fixed
= 1.1.38 =
* Language pack is updated. Belarusian by Alexander Ovsov is added
* TinyMCE plugin's button modes (modern, classic) are added
* Some fixes are made
= 1.0.35 =
* System of Checking Errors is added.
* Some minor improvements are made.
= 1.0.33 =
* Ads Blocks object is added.
* Ads Blocks widget is added.
* Custom Types supporting is added.
* Limitations by tags are added.
* Some improvements are added.
* Some fixes are made. 
= 0.6.25 =
* Language pack was updated by **Ulrich Simon** (German Language). 
* Some fixes are made.
= 0.6.23 =
* Click counting bug is fixed.
= 0.5.22 =
* Language pack is updated. German by [Fabian Krenzler](http://www.ktraces.de/) is added.
= 0.5.21 =
* Update bug fixed
= 0.5.20 =
* SQL queries are improved and optimised
* Ads Zone selector are added
* New widgets (Ads Zone and Single Ad) are added
* New limitations are added
= 0.4.16 =
* Ads management bug for Wordpress database with non "wp_" prefix are fixed
= 0.4.15 =
* Detection of Bots and Crawlers with customizable accuracy is added
* Limitations by hits and clicks are added
* Pricing by placement, hits and clicks are added
* Earnings counting is added
= 0.3.10 =
* Supporting of Google DFP codes is added
* Contextual Help is added
* Some codes are optimised
= 0.2.6 =
* Restriction of displaying ads by categories is changed
* Restriction of displaying ads by authors is added
* Some codes are changed
= 0.1.3 =
* Minor bug (using "drawAdsPlace()" with the attribute "name") are fixed. Thanks to [orangefinch](http://wordpress.org/support/profile/orangefinch).
= 0.1.2 =
* Ads management bug for Wordpress database with non "wp_" prefix are fixed
= 0.1.1 =
* Initial upload

== Upgrade Notice ==

= 2.9.6.121 =
Minor errors are eliminated. Italian language was added into the language pack.
= 2.9.5.118 =
Possibility of SQL injection is fixed. Minor bug resolved.
= 2.9.4.116 =
Possibility of DoS attacks is eliminated. Flash overlay image URL is fixed.
= 2.9.3.114 =
Overlay link for SWF banners is added. *noindex*, *nofollow*, *dofollow* are added.
= 2.9.2.111 =
Added manual sending reports to advertisers. GPT tags of Google DFP now is supported.
= 2.9.1.109 =
Minor bug resolved.
= 2.9.0.108 =
The list of advertisers was improved. Supporting for the plugin WPtouch is provided.
= 2.8.0.105 =
Some improvements...
= 2.7.102 =
Potential vulnerability issue was resolved.
= 2.7.101 =
The bug known as "Disappearing Ads" was solved.
= 2.7.99 =
Some bugs are fixed.
= 2.7.97 =
SQL injection fix.
= 2.6.96 =
Some issues with errors were resolved.
= 2.5.94 =
Some problems are solved.
= 2.4.91 =
Some functionality of the plugin were cut.
= 2.4.90 =
More faster, fewer bugs... See change log.
= 2.3.85 =
Some major changes of plugin (see change log).
= 2.0.74 =
Minor bug is resolved
= 1.8.72 =
Caching plugins compatibility, bbPress compatibility, Custom Taxonomies restrictions, etc.
= 1.7.63 =
Some bugs (Ads Block style, Click Tracker) are resolved.
= 1.7.61 =
Some bugs are resolved.
= 1.7.60 =
Minor bug is resolved (Ads Places List)
= 1.7.58 =
Major bug is resolved (bug of database creating, not database updating)
= 1.7.57 =
Data updating bugs of editors are fixed. Thanks to **Latibro**.
Control of Error Log is added.
Strange bug ("undefined post ID") is fixed.
= 1.6.54 =
Error Log is improved.
Click Tracker is improved.
Ads Block output bug is fixed.
Ad Widget bug is fixed.
= 1.5.50 =
Error log is added.
User interface of *Ads Editor* is improved.
Categories and Tags identification is changed from *name* to *slug*.
Flash (SWF) banners support is added.
Limitations of displaying ads to users are added.
Accessing menu settings are added.
= 1.4.44 =
Minor bug (activity of ad in ads list) is fixed.
Auto insertion of ad into the middle of post/page is added.
Language pack is updated. Spanish language by **xiaobai_wp** is added.
= 1.3.41 =
Minor bug (Custom Types Posts limitations) fixed.
= 1.2.40 =
MultiSite support is added.
Major bug is fixed.
= 1.1.38 =
Language pack is updated. Belarusian by Alexander Ovsov is added.
TinyMCE plugin's button modes (modern, classic) are added.
Some fixes are made.
= 1.0.35 =
System of Checking Errors is added.
Some minor improvements are made.
= 1.0.33 =
Quick patch (rollback of noindex|nofollow feature)! Bug of Automatic Insertion are fixed
= 1.0.32 =
Ads Blocks object is added. 
Ads Blocks widget is added. 
Custom Types supporting are added. 
Limitations by tags are added. 
Some improvements are added. 
Some fixes are made. 
= 0.6.25 =
Language pack was updated by Ulrich Simon (German Language). 
Some fixes are made.
= 0.6.23 =
Click counting bug is fixed.
= 0.5.22 =
Language pack is updated. German by [Fabian Krenzler](http://www.ktraces.de/) is added.
= 0.5.21 =
Update bug fixed
= 0.5.20 =
SQL queries are improved and optimised. 
Ads Zone selector are added. 
New widgets (Ads Zone and Single Ad) are added. 
New limitations are added. 
= 0.4.16 =
Ads management bug for Wordpress database with non "wp_" prefix are fixed
= 0.4.15 =
Detection of Bots and Crawlers with customizable accuracy is added. 
Limitations by hits and clicks are added. 
Pricing by placement, hits and clicks are added. 
Earnings counting is added. 
= 0.3.10 =
Supporting of Google DFP codes is added 
Contextual Help is added 
Some codes are optimised 
= 0.2.6 =
Restriction of displaying ads by categories is changed 
Restriction of displaying ads by authors is added 
Some codes are changed 
= 0.1.3 =
Minor bug (using "drawAdsPlace()" with the attribute "name") are fixed. Thanks to [orangefinch](http://wordpress.org/support/profile/orangefinch).
= 0.1.2 =
Ads management bug for Wordpress database with non "wp_" prefix are fixed
= 0.1.1 =
Initial upload

== Other Notes ==

This plugin is using jQuery **[jqPlot plugin](http://www.jqplot.com/)**.
This plugin is using jQuery **[W2UI - JavaScript UI Library](http://w2ui.com/web/)**.
This plugin is using jQuery **[ComboGrid plugin](http://combogrid.justmybit.com/)**.
