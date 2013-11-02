<?php
if(!class_exists('SAMHelp')) {
  class SAMHelp {
    public $pages;

    public function __construct($pages) {
      $this->pages = $pages;
    }

    public function help($contextualHelp, $screenId, $screen) {
      if ($screenId == $this->pages['editPage']) {
        if($_GET['mode'] == 'item') {
          $contextualHelp = '<div class="sam-contextual-help">';
          $contextualHelp .= '<p>'.__('Enter a <strong>name</strong> and a <strong>description</strong> of the advertisement. These parameters are optional, because don’t influence anything, but help in the visual identification of the ad (do not forget which is which).', SAM_DOMAIN).'</p>';
          $contextualHelp .= '<p>'.__('<strong>Ad Code</strong> – code can be defined as a combination of the image URL and target page URL, or as HTML code, javascript code, or PHP code (for PHP-code don’t forget to set the checkbox labeled "This code of ad contains PHP script"). If you select the first option (image mode) you can keep statistics of clicks and also tools for uploading/selecting the downloaded image banner becomes available to you.', SAM_DOMAIN).'</p>';
          $contextualHelp .= '<p>'.__('<strong>Restrictions of advertisement Showing</strong>', SAM_DOMAIN).'</p>';
          $contextualHelp .= '<p>'.__('<em>Ad Weight</em> – coefficient of frequency of show of the advertisement for one cycle of advertisements rotation. 0 – ad is inactive, 1 – minimal activity of this advertisement, 10 – maximal activity of this ad.', SAM_DOMAIN).'</p>';
          $contextualHelp .= '<p>'.__('<em>Restrictions by the type of pages</em> – select restrictions:', SAM_DOMAIN);
          $contextualHelp .= '<ul>';
          $contextualHelp .= '<li>'.__('Show ad on all pages of blog', SAM_DOMAIN).'</li>';
          $contextualHelp .= '<li>'.__('Show ad only on pages of this type – ad will appear only on the pages of selected types', SAM_DOMAIN).'</li>';
          $contextualHelp .= '<li>'.__('Show ad only in certain posts – ad will be shown only on single posts pages with the given IDs (ID items separated by commas, no spaces)', SAM_DOMAIN).'</li>';
          $contextualHelp .= '</ul></p>';
          $contextualHelp .= '<p>'.__('<em>Additional restrictions</em>', SAM_DOMAIN);
          $contextualHelp .= '<ul>';
          $contextualHelp .= '<li>'.__('Show ad only in single posts or categories archives of certain categories – ad will be shown only on single posts pages or category archive pages of the specified categories', SAM_DOMAIN).'</li>';
          $contextualHelp .= '<li>'.__('Show ad only in single posts or authors archives of certain authors – ad will be shown only on single posts pages or author archive pages of the specified authors', SAM_DOMAIN).'</li>';
          $contextualHelp .= '</ul></p>';
          $contextualHelp .= '<p>'.__('<em>Use the schedule for this ad</em> – if necessary, select checkbox labeled “Use the schedule for this ad” and set start and finish dates of ad campaign.', SAM_DOMAIN).'</p>';
          $contextualHelp .= '<p>'.__('<em>Use limitation by hits</em> – if necessary, select checkbox labeled “Use limitation by hits” and set hits limit.', SAM_DOMAIN).'</p>';
          $contextualHelp .= '<p>'.__('<em>Use limitation by clicks</em> – if necessary, select checkbox labeled “Use limitation by clicks” and set clicks limit.', SAM_DOMAIN).'</p>';
          $contextualHelp .= '<p>'.'<strong>'.__('Prices', SAM_DOMAIN).'</strong>: '.__('Use these parameters to get the statistics of incomes from advertisements placed in your blog. "Price of ad placement per month" - parameter used only for calculating statistic of scheduled ads.', SAM_DOMAIN).'</p>';
          $contextualHelp .= '<p><a class="button-secondary" href="http://www.simplelib.com/?p=480" target="_blank">'.__('Manual', SAM_DOMAIN).'</a> ';
          $contextualHelp .= '<a class="button-secondary" href="http://forum.simplelib.com/forumdisplay.php?13-Simple-Ads-Manager" target="_blank">'.__('Support Forum', SAM_DOMAIN).'</a></p>';
          $contextualHelp .= '</div>';
        }
        elseif($_GET['mode'] == 'place') {
          $contextualHelp = '<div class="sam-contextual-help">';
          $contextualHelp .= '<p>'.__('Enter a <strong>name</strong> and a <strong>description</strong> of the Ads Place. In principle, it is not mandatory parameters, because these parameters don’t influence anything, but experience suggests that after a while all IDs usually will be forgotten  and such information may be useful.', SAM_DOMAIN).'</p>';
          $contextualHelp .= '<p>'.__('<strong>Ads Place Size</strong> – in this version is only for informational purposes only, but in future I plan to use this option. It is desirable to expose the real size.', SAM_DOMAIN).'</p>';
          $contextualHelp .= '<p>'.__('<strong>Ads Place Patch</strong> - it’s an ad that will appear in the event that the logic of basic ads outputing of this Ads Place on the current page will not be able to choose a single basic ad for displaying. For example, if all basic announcements are set to displaying only on archives pages or single pages, in this case the patch ad of Ads Place will be shown on the Home page. Conveniently to use the patch ad of Ads Place where you sell the advertising place for a limited time – after the time expiration of ordered ad will appear patch ad. It may be a banner leading to your page of advertisement publication costs or a banner from AdSense.', SAM_DOMAIN).'</p>';
          $contextualHelp .= '<p>'.__('Patch can be defined', SAM_DOMAIN);
          $contextualHelp .= '<ul>';
          $contextualHelp .= '<li>'.__('as combination of the image URL and target page URL', SAM_DOMAIN).'</li>';
          $contextualHelp .= '<li>'.__('as HTML code or javascript code', SAM_DOMAIN).'</li>';
          $contextualHelp .= '<li>'.__('as name of Google <a href="https://www.google.com/intl/en/dfp/info/welcome.html" target="_blank">DoubleClick for Publishers</a> (DFP) block', SAM_DOMAIN).'</li>';
          $contextualHelp .= '</ul></p>';
          $contextualHelp .= '<p>'.__('If you select the first option (image mode), tools to download/choosing of downloaded image banner become available for you.', SAM_DOMAIN).'</p>';
          $contextualHelp .= '<p>'.__('<strong>Codes</strong> – as Ads Place can be inserted into the page code not only as widget, but as a short code or by using function, you can use code “before” and “after” for centering or alignment of Ads Place on the place of inserting or for something else you need. Use HTML tags.', SAM_DOMAIN);
          $contextualHelp .= '<p><a class="button-secondary" href="http://www.simplelib.com/?p=480" target="_blank">'.__('Manual', SAM_DOMAIN).'</a> ';
          $contextualHelp .= '<a class="button-secondary" href="http://forum.simplelib.com/forumdisplay.php?13-Simple-Ads-Manager" target="_blank">'.__('Support Forum', SAM_DOMAIN).'</a></p>';
          $contextualHelp .= '</div>';
        }
      }
      elseif($screenId == $this->pages['listPage']) {
        $contextualHelp = '<div class="sam-contextual-help">';
        $contextualHelp .= '<p><a class="button-secondary" href="http://www.simplelib.com/?p=480" target="_blank">'.__('Manual', SAM_DOMAIN).'</a> ';
        $contextualHelp .= '<a class="button-secondary" href="http://forum.simplelib.com/forumdisplay.php?13-Simple-Ads-Manager" target="_blank">'.__('Support Forum', SAM_DOMAIN).'</a></p>';
        $contextualHelp .= '</div>';
      }
      elseif($screenId == $this->pages['settingsPage']) {
        $contextualHelp = '<div class="sam-contextual-help">';
        $contextualHelp .= '<p>'.__('<strong>Views per Cycle</strong> – the number of impressions an ad for one cycle of rotation, provided that this ad has maximum weight (the activity). In other words, if the number of hits in the series is 1000, an ad with a weight of 10 will be shown in 1000, and the ad with a weight of 3 will be shown 300 times.', SAM_DOMAIN).'</p>';
        $contextualHelp .= '<p>'.__('Do not set this parameter to a value less than the maximum number of visitors which may simultaneously be on your site – it may violate the logic of rotation.', SAM_DOMAIN).'</p>';
        $contextualHelp .= '<p>'.__('Not worth it, though it has no special meaning, set this parameter to a value greater than the number of hits your web pages during a month. Optimal, perhaps, is the value to the daily shows website pages.', SAM_DOMAIN).'</p>';
        $contextualHelp .= '<p>'.__('<strong>Auto Inserting Settings</strong> - here you can select the Ads Places and allow the display of their ads before and after the  content of single post.', SAM_DOMAIN).'</p>';
        $contextualHelp .= '<p>'.__("<strong>Google DFP Settings</strong> - if you want to use codes of Google DFP rotator, you must allow it's using and define your pub-code.", SAM_DOMAIN).'</p>';
        $contextualHelp .= '<p>'.'<strong>'.__('Statistics Settings', SAM_DOMAIN).'</strong>'.'</p>';
        $contextualHelp .= '<p>'.'<em>'.__('Bots and Crawlers detection', SAM_DOMAIN).'</em>: '.__("For obtaining of more exact indexes of statistics and incomes it is preferable to exclude data about visits of bots and crawlers from the data about all visits of your blog. If enabled and bot or crawler is detected, hits of ads won't be counted. Select accuracy of detection but use with caution - more exact detection requires more server resources.", SAM_DOMAIN).'</p>';
        $contextualHelp .= '<p>'.'<em>'.__('Display of Currency', SAM_DOMAIN).'</em>: '.__("Define display of currency. Auto - auto detection of currency from blog settings. USD, EUR - Forcing the display of currency to U.S. dollars or Euro.", SAM_DOMAIN).'</p>';
        $contextualHelp .= '<p><a class="button-secondary" href="http://www.simplelib.com/?p=480" target="_blank">'.__('Manual', SAM_DOMAIN).'</a> ';
        $contextualHelp .= '<a class="button-secondary" href="http://forum.simplelib.com/forumdisplay.php?13-Simple-Ads-Manager" target="_blank">'.__('Support Forum', SAM_DOMAIN).'</a></p>';
        $contextualHelp .= '</div>';
      }
      return $contextualHelp;
    }
  }
}

if(!class_exists('SAMHelp33')) {
  class SAMHelp33 {
    public $pages;

    public function __construct($pages) {
      $this->pages = $pages;
    }

    public function help() {
      //$samScreens = array($this->listPage, $this->editPage, $this->listZone, $this->editZone, $this->listBlock, $this->editBlock, $this->settingsPage);
      $screen = get_current_screen();
      $content = '';

      if(isset($_GET["action"])) $action = $_GET['action'];
      else $action = 'places';
      if(isset($_GET['mode'])) $mode = $_GET['mode'];
      else $mode = 'place';

      if(!in_array($screen->id, $this->pages['screens'])) return;

      if($screen->id == $this->pages['pages']['listPage']) {
        if($action == 'places') {
          //$content = '<div class="sam-contentual-help">';
          $content .= '<p>'.__('This is list of Ads Places', SAM_DOMAIN).'</p>';
          $content .= '<p><a class="button-secondary" href="http://www.simplelib.com/?p=480" target="_blank">'.__('Manual', SAM_DOMAIN).'</a> ';
          $content .= '<a class="button-secondary" href="http://forum.simplelib.com/forumdisplay.php?13-Simple-Ads-Manager" target="_blank">'.__('Support Forum', SAM_DOMAIN).'</a></p>';
          //$content .= '</div>';
          $title = __('Help', SAM_DOMAIN);
        }
        else {
          //$content = '<div class="sam-contentual-help">';
          $content .= '<p>'.__('This is list of Ads', SAM_DOMAIN).'</p>';
          $content .= '<p><a class="button-secondary" href="http://www.simplelib.com/?p=480" target="_blank">'.__('Manual', SAM_DOMAIN).'</a> ';
          $content .= '<a class="button-secondary" href="http://forum.simplelib.com/forumdisplay.php?13-Simple-Ads-Manager" target="_blank">'.__('Support Forum', SAM_DOMAIN).'</a></p>';
          //$content .= '</div>';
          $title = __('Help', SAM_DOMAIN);
        }
        $screen->add_help_tab(array('id' => 'sam-help', 'title' => $title, 'content' => $content));
      }

      if($screen->id == $this->pages['pages']['editPage']) {
        if($mode == 'place') {
          $content = '<p>'.__('The main object of the plugin is “Ads Place“. Each Ads Place is a container for the advertisements and provides the logic of the show and rotation. In addition, one of the parameters of advertising space is “patch ad code”, ie ad to be shown if and only if the logic of ads this Ads Place does not permit to show none of the advertisements contained in this Ads Place. One Ads Place can contain any number of objects “advertisement”.', SAM_DOMAIN).'</p>';
          $content .= '<p><a class="button-secondary" href="http://www.simplelib.com/?p=480" target="_blank">'.__('Manual', SAM_DOMAIN).'</a> ';
          $content .= '<a class="button-secondary" href="http://forum.simplelib.com/forumdisplay.php?13-Simple-Ads-Manager" target="_blank">'.__('Support Forum', SAM_DOMAIN).'</a></p>';
          $title = __('Ads Place', SAM_DOMAIN);

          $screen->add_help_tab(array('id' => 'sam-help', 'title' => $title, 'content' => $content));

          $content2 = '<p>'.__('Enter a <strong>name</strong> and a <strong>description</strong> of the Ads Place. In principle, it is not mandatory parameters, because these parameters don’t influence anything, but experience suggests that after a while all IDs usually will be forgotten  and such information may be useful.', SAM_DOMAIN).'</p>';
          $content2 .= '<p>'.__('<strong>Ads Place Size</strong> – in this version is only for informational purposes only, but in future I plan to use this option. It is desirable to expose the real size.', SAM_DOMAIN).'</p>';
          $content2 .= '<p>'.__('<strong>Ads Place Patch</strong> - it’s an ad that will appear in the event that the logic of basic ads outputing of this Ads Place on the current page will not be able to choose a single basic ad for displaying. For example, if all basic announcements are set to displaying only on archives pages or single pages, in this case the patch ad of Ads Place will be shown on the Home page. Conveniently to use the patch ad of Ads Place where you sell the advertising place for a limited time – after the time expiration of ordered ad will appear patch ad. It may be a banner leading to your page of advertisement publication costs or a banner from AdSense.', SAM_DOMAIN).'</p>';
          $content2 .= '<p>'.__('Patch can be defined', SAM_DOMAIN);
          $content2 .= '<ul>';
          $content2 .= '<li>'.__('as combination of the image URL and target page URL', SAM_DOMAIN).'</li>';
          $content2 .= '<li>'.__('as HTML code or javascript code', SAM_DOMAIN).'</li>';
          $content2 .= '<li>'.__('as name of Google <a href="https://www.google.com/intl/en/dfp/info/welcome.html" target="_blank">DoubleClick for Publishers</a> (DFP) block', SAM_DOMAIN).'</li>';
          $content2 .= '</ul></p>';
          $content2 .= '<p>'.__('If you select the first option (image mode), tools to download/choosing of downloaded image banner become available for you.', SAM_DOMAIN).'</p>';
          $content2 .= '<p>'.__('<strong>Codes</strong> – as Ads Place can be inserted into the page code not only as widget, but as a short code or by using function, you can use code “before” and “after” for centering or alignment of Ads Place on the place of inserting or for something else you need. Use HTML tags.', SAM_DOMAIN);
          $content2 .= '<p><a class="button-secondary" href="http://www.simplelib.com/?p=480" target="_blank">'.__('Manual', SAM_DOMAIN).'</a> ';
          $content2 .= '<a class="button-secondary" href="http://forum.simplelib.com/forumdisplay.php?13-Simple-Ads-Manager" target="_blank">'.__('Support Forum', SAM_DOMAIN).'</a></p>';
          $title2 = __('Parameters', SAM_DOMAIN);

          $screen->add_help_tab(array('id' => 'sam-help-place', 'title' => $title2, 'content' => $content2));

        }
        else {
          $content = '<p>'.__('Object “advertisement” rigidly attached to his container “Ads Place”. Its parameters determine frequency (weight) of displaying and limiting displaying from “show all pages” to “show the articles with ID … ” and show from date to date (the schedule).', SAM_DOMAIN).'</p>';
          $content .= '<p><a class="button-secondary" href="http://www.simplelib.com/?p=480" target="_blank">'.__('Manual', SAM_DOMAIN).'</a> ';
          $content .= '<a class="button-secondary" href="http://forum.simplelib.com/forumdisplay.php?13-Simple-Ads-Manager" target="_blank">'.__('Support Forum', SAM_DOMAIN).'</a></p>';
          $title = __('Advertisement', SAM_DOMAIN);

          $screen->add_help_tab(array('id' => 'sam-help', 'title' => $title, 'content' => $content));

          $content2 = '<p>'.__('Enter a <strong>name</strong> and a <strong>description</strong> of the advertisement. These parameters are optional, because don’t influence anything, but help in the visual identification of the ad (do not forget which is which).', SAM_DOMAIN).'</p>';
          $content2 .= '<p>'.__('<strong>Ad Code</strong> – code can be defined as a combination of the image URL and target page URL, or as HTML code, javascript code, or PHP code (for PHP-code don’t forget to set the checkbox labeled "This code of ad contains PHP script"). If you select the first option (image mode) you can keep statistics of clicks and also tools for uploading/selecting the downloaded image banner becomes available to you.', SAM_DOMAIN).'</p>';
          $content2 .= '<p>'.__('<strong>Restrictions of advertisement Showing</strong>', SAM_DOMAIN).'</p>';
          $content2 .= '<p>'.__('<em>Ad Weight</em> – coefficient of frequency of show of the advertisement for one cycle of advertisements rotation. 0 – ad is inactive, 1 – minimal activity of this advertisement, 10 – maximal activity of this ad.', SAM_DOMAIN).'</p>';
          $content2 .= '<p>'.__('<em>Restrictions by the type of pages</em> – select restrictions:', SAM_DOMAIN);
          $content2 .= '<ul>';
          $content2 .= '<li>'.__('Show ad on all pages of blog', SAM_DOMAIN).'</li>';
          $content2 .= '<li>'.__('Show ad only on pages of this type – ad will appear only on the pages of selected types', SAM_DOMAIN).'</li>';
          $content2 .= '<li>'.__('Show ad only in certain posts – ad will be shown only on single posts pages with the given IDs (ID items separated by commas, no spaces)', SAM_DOMAIN).'</li>';
          $content2 .= '</ul></p>';
          $content2 .= '<p><a class="button-secondary" href="http://www.simplelib.com/?p=480" target="_blank">'.__('Manual', SAM_DOMAIN).'</a> ';
          $content2 .= '<a class="button-secondary" href="http://forum.simplelib.com/forumdisplay.php?13-Simple-Ads-Manager" target="_blank">'.__('Support Forum', SAM_DOMAIN).'</a></p>';

          $title2 = __('Parameters', SAM_DOMAIN);

          $screen->add_help_tab(array('id' => 'sam-help-item', 'title' => $title2, 'content' => $content2));

          $content3 = '<p>'.__('<strong>Additional restrictions</strong>', SAM_DOMAIN);
          $content3 .= '<ul>';
          $content3 .= '<li>'.__('Show ad only in single posts or categories archives of certain categories – ad will be shown only on single posts pages or category archive pages of the specified categories', SAM_DOMAIN).'</li>';
          $content3 .= '<li>'.__('Show ad only in single posts or authors archives of certain authors – ad will be shown only on single posts pages or author archive pages of the specified authors', SAM_DOMAIN).'</li>';
          $content3 .= '</ul></p>';
          $content3 .= '<p>'.__('<em>Use the schedule for this ad</em> – if necessary, select checkbox labeled “Use the schedule for this ad” and set start and finish dates of ad campaign.', SAM_DOMAIN).'</p>';
          $content3 .= '<p>'.__('<em>Use limitation by hits</em> – if necessary, select checkbox labeled “Use limitation by hits” and set hits limit.', SAM_DOMAIN).'</p>';
          $content3 .= '<p>'.__('<em>Use limitation by clicks</em> – if necessary, select checkbox labeled “Use limitation by clicks” and set clicks limit.', SAM_DOMAIN).'</p>';
          $content3 .= '<p>'.'<strong>'.__('Prices', SAM_DOMAIN).'</strong>: '.__('Use these parameters to get the statistics of incomes from advertisements placed in your blog. "Price of ad placement per month" - parameter used only for calculating statistic of scheduled ads.', SAM_DOMAIN).'</p>';
          $content3 .= '<p><a class="button-secondary" href="http://www.simplelib.com/?p=480" target="_blank">'.__('Manual', SAM_DOMAIN).'</a> ';
          $content3 .= '<a class="button-secondary" href="http://forum.simplelib.com/forumdisplay.php?13-Simple-Ads-Manager" target="_blank">'.__('Support Forum', SAM_DOMAIN).'</a></p>';

          $title3 = __('Additional Parameters', SAM_DOMAIN);

          $screen->add_help_tab(array('id' => 'sam-help-item-lmt', 'title' => $title3, 'content' => $content3));
        }
      }
      if($screen->id == $this->pages['pages']['settingsPage']) {
        $content .= '<p>'.__('<strong>Views per Cycle</strong> – the number of impressions an ad for one cycle of rotation, provided that this ad has maximum weight (the activity). In other words, if the number of hits in the series is 1000, an ad with a weight of 10 will be shown in 1000, and the ad with a weight of 3 will be shown 300 times.', SAM_DOMAIN).'</p>';
        $content .= '<p>'.__('Do not set this parameter to a value less than the maximum number of visitors which may simultaneously be on your site – it may violate the logic of rotation.', SAM_DOMAIN).'</p>';
        $content .= '<p>'.__('Not worth it, though it has no special meaning, set this parameter to a value greater than the number of hits your web pages during a month. Optimal, perhaps, is the value to the daily shows website pages.', SAM_DOMAIN).'</p>';
        $content .= '<p>'.__('<strong>Auto Inserting Settings</strong> - here you can select the Ads Places and allow the display of their ads before and after the  content of single post.', SAM_DOMAIN).'</p>';
        $content .= '<p>'.__("<strong>Google DFP Settings</strong> - if you want to use codes of Google DFP rotator, you must allow it's using and define your pub-code.", SAM_DOMAIN).'</p>';
        $content .= '<p>'.'<strong>'.__('Statistics Settings', SAM_DOMAIN).'</strong>'.'</p>';
        $content .= '<p>'.'<em>'.__('Bots and Crawlers detection', SAM_DOMAIN).'</em>: '.__("For obtaining of more exact indexes of statistics and incomes it is preferable to exclude data about visits of bots and crawlers from the data about all visits of your blog. If enabled and bot or crawler is detected, hits of ads won't be counted. Select accuracy of detection but use with caution - more exact detection requires more server resources.", SAM_DOMAIN).'</p>';
        $content .= '<p>'.'<em>'.__('Display of Currency', SAM_DOMAIN).'</em>: '.__("Define display of currency. Auto - auto detection of currency from blog settings. USD, EUR - Forcing the display of currency to U.S. dollars or Euro.", SAM_DOMAIN).'</p>';
        $content .= '<p><a class="button-secondary" href="http://www.simplelib.com/?p=480" target="_blank">'.__('Manual', SAM_DOMAIN).'</a> ';
        $content .= '<a class="button-secondary" href="http://forum.simplelib.com/forumdisplay.php?13-Simple-Ads-Manager" target="_blank">'.__('Support Forum', SAM_DOMAIN).'</a></p>';

        $title = __('Help', SAM_DOMAIN);

        $screen->add_help_tab(array('id' => 'sam-help-settings', 'title' => $title, 'content' => $content));
      }
    }
  }
}
?>