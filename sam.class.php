<?php
if ( !class_exists( 'SimpleAdsManager' ) ) {
  class SimpleAdsManager {
    private $samOptions = array();
    private $samVersions = array('sam' => null, 'db' => null);
    private $crawler = false;
    public $samNonce;
    private $whereClauses;
    
    private $defaultSettings = array(
      'adCycle' => 1000,
      'adDisplay' => 'blank',
      'placesPerPage' => 10,
      'itemsPerPage' => 10,
	    'deleteOptions' => 0,
      'deleteDB' => 0,
      'deleteFolder' => 0,
      'beforePost' => 0,
      'bpAdsId' => 0,
      'bpUseCodes' => 0,
      'middlePost' => 0,
      'mpAdsId' => 0,
      'mpUseCodes' => 0,
      'afterPost' => 0,
      'apAdsId' => 0,
      'apUseCodes' => 0,
      'useDFP' => 0,
      'detectBots' => 0,
      'detectingMode' => 'inexact',
      'currency' => 'auto',
      'dfpPub' => '',
      'dfpBlocks' => array(),
      'editorButtonMode' => 'modern', // modern|classic
      'useSWF' => 0,
      'access' => 'manage_options',
      'errorlog' => 1,
      'errorlogFS' => 1
	  );
		
	  public function __construct() {
      define('SAM_VERSION', '1.8.70');
      define('SAM_DB_VERSION', '2.2');
      define('SAM_PATH', dirname( __FILE__ ));
      define('SAM_URL', plugins_url('/' . str_replace( basename( __FILE__), "", plugin_basename( __FILE__ ) )) );
      define('SAM_IMG_URL', SAM_URL.'images/');
      define('SAM_DOMAIN', 'simple-ads-manager');
      define('SAM_OPTIONS_NAME', 'samPluginOptions');
      define('SAM_AD_IMG', WP_PLUGIN_DIR.'/sam-images/');
      define('SAM_AD_URL', plugins_url('/sam-images/'));
      
      define('SAM_IS_HOME', 1);
      define('SAM_IS_SINGULAR', 2);
      define('SAM_IS_SINGLE', 4);
      define('SAM_IS_PAGE', 8);
      define('SAM_IS_ATTACHMENT', 16);
      define('SAM_IS_SEARCH', 32);
      define('SAM_IS_404', 64);
      define('SAM_IS_ARCHIVE', 128);
      define('SAM_IS_TAX', 256);
      define('SAM_IS_CATEGORY', 512);
      define('SAM_IS_TAG', 1024);
      define('SAM_IS_AUTHOR', 2048);
      define('SAM_IS_DATE', 4096);
      define('SAM_IS_POST_TYPE', 8192);
      define('SAM_IS_POST_TYPE_ARCHIVE', 16384);

      $this->getSettings(true);
      $this->getVersions(true);
      $this->crawler = $this->isCrawler();
      
      if(!is_admin()) {
        add_action('wp_enqueue_scripts', array(&$this, 'headerScripts'));
        add_action('wp_head', array(&$this, 'headerCodes'));
        
        add_shortcode('sam', array(&$this, 'doShortcode'));
        add_shortcode('sam_ad', array(&$this, 'doAdShortcode'));
        add_shortcode('sam_zone', array(&$this, 'doZoneShortcode'));
        add_shortcode('sam_block', array(&$this, 'doBlockShortcode'));      
        add_filter('the_content', array(&$this, 'addContentAds'), 8);
        // For backward compatibility
        add_shortcode('sam-ad', array(&$this, 'doAdShortcode'));
        add_shortcode('sam-zone', array(&$this, 'doZoneShortcode'));
      }
      else $this->whereClauses = null;
    }
		
	  public function getSettings($force = false) {
	    if($force) {
        $pluginOptions = get_option(SAM_OPTIONS_NAME, '');
		    $options = $this->defaultSettings;
		    if ($pluginOptions !== '') {
		      foreach($pluginOptions as $key => $option) {
			      $options[$key] = $option;
		      }
		    }
		    $this->samOptions = $options;
      }
      else $options = $this->samOptions;
      return $options; 
	  }
    
    public function getVersions($force = false) {
      $versions = array('sam' => null, 'db' => null);
      if($force) {
        $versions['sam'] = get_option( 'sam_version', '' );
        $versions['db'] = get_option( 'sam_db_version', '' );
        $this->samVersions = $versions;
      }
      else $versions = $this->samVersions;
      
      return $versions;
    }

    private function getCustomPostTypes() {
      $args = array('public' => true, '_builtin' => false);
      $output = 'names';
      $operator = 'and';
      $post_types = get_post_types($args, $output, $operator);

      return $post_types;
    }

    private function isCustomPostType() {
      return (in_array(get_post_type(), $this->getCustomPostTypes()));
    }

    public function buildWhereClause() {
      $settings = $this->getSettings();
      if($settings['adCycle'] == 0) $cycle = 1000;
      else $cycle = $settings['adCycle'];
      $el = (integer)$settings['errorlogFS'];

      global $wpdb, $current_user;
      $aTable = $wpdb->prefix . "sam_ads";

      $viewPages = 0;
      $wcc = '';
      $wci = '';
      $wca = '';
      $wcx = '';
      $wct = '';
      $wcxc = '';
      $wcxa = '';
      $wcxt = '';

      if(is_user_logged_in()) {
        get_currentuserinfo();
        $uSlug = $current_user->user_login;
        $wcul = "IF($aTable.ad_users_reg = 1, IF($aTable.x_ad_users = 1, NOT FIND_IN_SET(\"$uSlug\", $aTable.x_view_users), TRUE) AND IF($aTable.ad_users_adv = 1, ($aTable.adv_nick <> \"$uSlug\"), TRUE), FALSE)";
      }
      else {
        $wcul = "($aTable.ad_users_unreg = 1)";
      }
      $wcu = "(IF($aTable.ad_users = 0, TRUE, $wcul)) AND";

      if(is_home() || is_front_page()) $viewPages += SAM_IS_HOME;
      if(is_singular()) {
        $viewPages |= SAM_IS_SINGULAR;
        if($this->isCustomPostType()) {
          $viewPages |= SAM_IS_SINGLE;
          $viewPages |= SAM_IS_POST_TYPE;

          $postType = get_post_type();
          $wct .= " AND IF($aTable.view_type < 2 AND $aTable.ad_custom AND IF($aTable.view_type = 0, $aTable.view_pages+0 & $viewPages, TRUE), FIND_IN_SET(\"$postType\", $aTable.view_custom), TRUE)";
          $wcxt .= " AND IF($aTable.view_type < 2 AND $aTable.x_custom AND IF($aTable.view_type = 0, $aTable.view_pages+0 & $viewPages, TRUE), NOT FIND_IN_SET(\"$postType\", $aTable.x_view_custom), TRUE)";
        }
        if(is_single()) {
          global $post;

          $viewPages |= SAM_IS_SINGLE;
          $categories = get_the_category($post->ID);
          $tags = get_the_tags();
          $postID = ((!empty($post->ID)) ? $post->ID : 0);

          if(!empty($categories)) {
            $wcc_0 = '';
            $wcxc_0 = '';
            $wcc = " AND IF($aTable.view_type < 2 AND $aTable.ad_cats AND IF($aTable.view_type = 0, $aTable.view_pages+0 & $viewPages, TRUE),";
            $wcxc = " AND IF($aTable.view_type < 2 AND $aTable.x_cats AND IF($aTable.view_type = 0, $aTable.view_pages+0 & $viewPages, TRUE),";
            foreach($categories as $category) {
              if(empty($wcc_0)) $wcc_0 = " FIND_IN_SET(\"{$category->category_nicename}\", $aTable.view_cats)";
              else $wcc_0 .= " OR FIND_IN_SET(\"{$category->category_nicename}\", $aTable.view_cats)";
              if(empty($wcxc_0)) $wcxc_0 = " (NOT FIND_IN_SET(\"{$category->category_nicename}\", $aTable.x_view_cats))";
              else $wcxc_0 .= " AND (NOT FIND_IN_SET(\"{$category->category_nicename}\", $aTable.x_view_cats))";
            }
            $wcc .= $wcc_0.", TRUE)";
            $wcxc .= $wcxc_0.", TRUE)";
          }

          if(!empty($tags)) {
            $wct_0 = '';
            $wcxt_0 = '';
            $wct .= " AND IF($aTable.view_type < 2 AND $aTable.ad_tags AND IF($aTable.view_type = 0, $aTable.view_pages+0 & $viewPages, TRUE),";
            $wcxt .= " AND IF($aTable.view_type < 2 AND $aTable.x_tags AND IF($aTable.view_type = 0, $aTable.view_pages+0 & $viewPages, TRUE),";
            foreach($tags as $tag) {
              if(empty($wct_0)) $wct_0 = " FIND_IN_SET(\"{$tag->slug}\", $aTable.view_tags)";
              else $wct_0 .= " OR FIND_IN_SET(\"{$tag->slug}\", $aTable.view_tags)";
              if(empty($wcxt_0)) $wcxt_0 = " (NOT FIND_IN_SET(\"{$tag->slug}\", $aTable.x_view_tags))";
              else $wcxt_0 .= " AND (NOT FIND_IN_SET(\"{$tag->slug}\", $aTable.x_view_tags))";
            }
            $wct .= $wct_0.", TRUE)";
            $wcxt .= $wcxt_0.", TRUE)";
          }

          $wci = " OR ($aTable.view_type = 2 AND FIND_IN_SET({$postID}, $aTable.view_id))";
          $wcx = " AND IF($aTable.x_id, NOT FIND_IN_SET({$postID}, $aTable.x_view_id), TRUE)";
          $author = get_userdata($post->post_author);
          $wca = " AND IF($aTable.view_type < 2 AND $aTable.ad_authors AND IF($aTable.view_type = 0, $aTable.view_pages+0 & $viewPages, TRUE), FIND_IN_SET(\"{$author->display_name}\", $aTable.view_authors), TRUE)";
          $wcxa = " AND IF($aTable.view_type < 2 AND $aTable.x_authors AND IF($aTable.view_type = 0, $aTable.view_pages+0 & $viewPages, TRUE), NOT FIND_IN_SET(\"{$author->display_name}\", $aTable.x_view_authors), TRUE)";
        }
        if(is_page()) {
          global $post;
          $postID = ((!empty($post->ID)) ? $post->ID : 0);

          $viewPages |= SAM_IS_PAGE;
          $wci = " OR ($aTable.view_type = 2 AND FIND_IN_SET({$postID}, $aTable.view_id))";
          $wcx = " AND IF($aTable.x_id, NOT FIND_IN_SET({$postID}, $aTable.x_view_id), TRUE)";
        }
        if(is_attachment()) $viewPages |= SAM_IS_ATTACHMENT;
      }
      if(is_search()) $viewPages |= SAM_IS_SEARCH;
      if(is_404()) $viewPages |= SAM_IS_404;
      if(is_archive()) {
        $viewPages |= SAM_IS_ARCHIVE;
        if(is_tax()) $viewPages |= SAM_IS_TAX;
        if(is_category()) {
          $viewPages |= SAM_IS_CATEGORY;
          $cat = get_category(get_query_var('cat'), false);
          $wcc = " AND IF($aTable.view_type < 2 AND $aTable.ad_cats AND IF($aTable.view_type = 0, $aTable.view_pages+0 & $viewPages, TRUE), FIND_IN_SET(\"{$cat->category_nicename}\", $aTable.view_cats), TRUE)";
          $wcxc = " AND IF($aTable.view_type < 2 AND $aTable.x_cats AND IF($aTable.view_type = 0, $aTable.view_pages+0 & $viewPages, TRUE), NOT FIND_IN_SET(\"{$cat->category_nicename}\", $aTable.x_view_cats), TRUE)";
        }
        if(is_tag()) {
          $viewPages |= SAM_IS_TAG;
          $tag = get_tag(get_query_var('tag_id'));
          $wct = " AND IF($aTable.view_type < 2 AND $aTable.ad_tags AND IF($aTable.view_type = 0, $aTable.view_pages+0 & $viewPages, TRUE), FIND_IN_SET('{$tag->slug}', $aTable.view_tags), TRUE)";
          $wcxt = " AND IF($aTable.view_type < 2 AND $aTable.x_tags AND IF($aTable.view_type = 0, $aTable.view_pages+0 & $viewPages, TRUE), NOT FIND_IN_SET('{$tag->slug}', $aTable.x_view_tags), TRUE)";
        }
        if(is_author()) {
          global $wp_query;

          $viewPages |= SAM_IS_AUTHOR;
          $author = $wp_query->get_queried_object();
          $wca = " AND IF($aTable.view_type < 2 AND $aTable.ad_authors = 1 AND IF($aTable.view_type = 0, $aTable.view_pages+0 & $viewPages, TRUE), FIND_IN_SET('{$author->display_name}', $aTable.view_authors), TRUE)";
          $wcxa = " AND IF($aTable.view_type < 2 AND $aTable.x_authors AND IF($aTable.view_type = 0, $aTable.view_pages+0 & $viewPages, TRUE), NOT FIND_IN_SET('{$author->display_name}', $aTable.x_view_authors), TRUE)";
        }
        if(is_post_type_archive()) {
          $viewPages |= SAM_IS_POST_TYPE_ARCHIVE;
          //$postType = post_type_archive_title( '', false );
          $postType = get_post_type();
          $wct = " AND IF($aTable.view_type < 2 AND $aTable.ad_custom AND IF($aTable.view_type = 0, $aTable.view_pages+0 & $viewPages, TRUE), FIND_IN_SET('{$postType}', $aTable.view_custom), TRUE)";
          $wcxt = " AND IF($aTable.view_type < 2 AND $aTable.x_custom AND IF($aTable.view_type = 0, $aTable.view_pages+0 & $viewPages, TRUE), NOT FIND_IN_SET('{$postType}', $aTable.x_view_custom), TRUE)";
        }
        if(is_date()) $viewPages |= SAM_IS_DATE;
      }

      if(empty($wcc)) $wcc = " AND ($aTable.ad_cats = 0)";
      if(empty($wca)) $wca = " AND ($aTable.ad_authors = 0)";

      $whereClause  = "$wcu (($aTable.view_type = 1)";
      $whereClause .= " OR ($aTable.view_type = 0 AND ($aTable.view_pages+0 & $viewPages))";
      $whereClause .= "$wci)";
      $whereClause .= "$wcc $wca $wct $wcx $wcxc $wcxa $wcxt";
      $whereClauseT = " AND IF($aTable.ad_schedule, CURDATE() BETWEEN $aTable.ad_start_date AND $aTable.ad_end_date, TRUE)";
      $whereClauseT .= " AND IF($aTable.limit_hits, $aTable.hits_limit > $aTable.ad_hits, TRUE)";
      $whereClauseT .= " AND IF($aTable.limit_clicks, $aTable.clicks_limit > $aTable.ad_clicks, TRUE)";

      $whereClauseW = " AND IF($aTable.ad_weight > 0, ($aTable.ad_weight_hits*10/($aTable.ad_weight*$cycle)) < 1, FALSE)";
      $whereClause2W = "AND ($aTable.ad_weight > 0)";

      return array('WC' => $whereClause, 'WCT' => $whereClauseT, 'WCW' => $whereClauseW, 'WC2W' => $whereClause2W);
    }
    
    public function headerScripts() {
      global $SAM_Query;

      $this->samNonce = wp_create_nonce('samNonce');
      $options = self::getSettings();
      $this->whereClauses = self::buildWhereClause();

      $SAM_Query = array('clauses' => $this->whereClauses);
      $clauses64 = base64_encode(serialize($SAM_Query['clauses']));
      //$dClauses64 = unserialize(base64_decode($clauses64));
      
      wp_enqueue_script('jquery');
      if($options['useSWF']) wp_enqueue_script('swfobject');
      wp_enqueue_script('samLayout', SAM_URL.'js/sam-layout.js', array('jquery'), SAM_VERSION);
      wp_localize_script('samLayout', 'samAjax', array(
          'ajaxurl' => SAM_URL . 'sam-ajax.php',
          'level' => count(explode('/', str_replace( ABSPATH, '', dirname( __FILE__ ) ))),
          //'queries' => $dClauses64,
          'clauses' => $clauses64 //$this->whereClauses
        )
      );
    }
    
    public function headerCodes() {
      $options = $this->getSettings();
      $pub = $options['dfpPub'];
      
      if(($options['useDFP'] == 1) && !empty($options['dfpPub'])) {
        $output = "<!-- Start of SAM ".SAM_VERSION." scripts -->"."\n";
        $output .= "<script type='text/javascript' src='http://partner.googleadservices.com/gampad/google_service.js'></script>"."\n";
        $output .= "<script type='text/javascript'>"."\n";
        $output .= "  GS_googleAddAdSenseService('$pub');"."\n";
        $output .= "  GS_googleEnableAllServices();"."\n";
        $output .= "</script>"."\n";
        $output .= "<script type='text/javascript'>"."\n";
        foreach($options['dfpBlocks'] as $value)
          $output .= "  GA_googleAddSlot('$pub', '$value');"."\n";
        $output .= "</script>"."\n";
        $output .= "<script type='text/javascript'>"."\n";
        $output .= "  GA_googleFetchAds();"."\n";
        $output .= "</script>"."\n";
        $output .= "<!-- End of SAM ".SAM_VERSION." scripts -->"."\n";
      }
      else $output = '';
      
      echo $output;
    }
    
    private function isCrawler() {
      $options = $this->getSettings();
      $crawler = false;
      
      if($options['detectBots'] == 1) {
        switch($options['detectingMode']) {
          case 'inexact':
            if($_SERVER["HTTP_USER_AGENT"] == '' ||
               $_SERVER['HTTP_ACCEPT'] == '' ||
               $_SERVER['HTTP_ACCEPT_ENCODING'] == '' ||
               $_SERVER['HTTP_ACCEPT_LANGUAGE'] == '' ||
               $_SERVER['HTTP_CONNECTION']=='') $crawler == true;
            break;
            
          case 'exact':
            if(!class_exists('Browser')) include_once('browser.php');
            $browser = new Browser();
            $crawler = $browser->isRobot();
            break;
            
          case 'more':
            if(ini_get("browscap")) {
              $browser = get_browser(null, true);
              $crawler = $browser['crawler']; 
            }
            break;
        }
      }
      return $crawler;
    }
		
		/**
    * Outputs the Single Ad.
    *
    * Returns Single Ad content.
    *
    * @since 0.5.20
    *
    * @param array $args 'id' array element: id of ad, 'name' array elemnt: name of ad
    * @param bool|array $useCodes If bool codes 'before' and 'after' from Ads Place record are used. If array codes 'before' and 'after' from array are used
    * @return string value of Ad content
    */
    public function buildSingleAd( $args = null, $useCodes = false ) {
      $ad = new SamAd($args, $useCodes, $this->crawler);
      $output = $ad->ad;
      return $output;
    }
    
    /**
    * Outputs Ads Place content.
    *
    * Returns Ads Place content.
    *
    * @since 0.1.1
    *
    * @param array $args 'id' array element: id of Ads Place, 'name' array elemnt: name of Ads Place
    * @param bool|array $useCodes If bool codes 'before' and 'after' from Ads Place record are used. If array codes 'before' and 'after' from array are used
    * @return string value of Ads Place content
    */
    public function buildAd( $args = null, $useCodes = false ) {
      $ad = new SamAdPlace($args, $useCodes, $this->crawler);
      $output = $ad->ad;
      return $output;
    }
    
    /**
    * Outputs Ads Zone content.
    *
    * Returns Ads Zone content.
    *
    * @since 0.5.20
    *
    * @param array $args 'id' array element: id of Ads Zone, 'name' array elemnt: name of Ads Zone
    * @param bool|array $useCodes If bool codes 'before' and 'after' from Ads Place record are used. If array codes 'before' and 'after' from array are used
    * @return string value of Ads Zone content
    */
    public function buildAdZone( $args = null, $useCodes = false ) {
      $ad = new SamAdPlaceZone($args, $useCodes, $this->crawler);
      $output = $ad->ad;
      return $output;
    }
    
    /**
    * Outputs Ads Block content.
    *
    * Returns Ads Block content.
    *
    * @since 1.0.25
    *
    * @param array $args 'id' array element: id of Ads Block, 'name' array elemnt: name of Ads Block
    * @return string value of Ads Zone content
    */
    public function buildAdBlock( $args = null ) {
      $block = new SamAdBlock($args, $this->crawler);
      $output = $block->ad;
      return $output;
    }
    
    public function doAdShortcode($atts) {
      extract(shortcode_atts( array( 'id' => '', 'name' => '', 'codes' => ''), $atts ));
      $ad = new SamAd(array('id' => $id, 'name' => $name), ($codes == 'true'), $this->crawler);
      return $ad->ad;
    }
    
    public function doShortcode( $atts ) {
      extract(shortcode_atts( array( 'id' => '', 'name' => '', 'codes' => ''), $atts ));      
      $ad = new SamAdPlace(array('id' => $id, 'name' => $name), ($codes == 'true'), $this->crawler);
      return $ad->ad;
    }
    
    public function doZoneShortcode($atts) {
      extract(shortcode_atts( array( 'id' => '', 'name' => '', 'codes' => ''), $atts ));
      $ad = new SamAdPlaceZone(array('id' => $id, 'name' => $name), ($codes == 'true'), $this->crawler);
      return $ad->ad;
    }
    
    public function doBlockShortcode($atts) {
      extract(shortcode_atts( array( 'id' => '', 'name' => ''), $atts ));
      $block = new SamAdBlock(array('id' => $id, 'name' => $name), $this->crawler);
      return $block->ad;
    }
    
    public function addContentAds( $content ) {
      $options = $this->getSettings();
      $bpAd = '';
      $apAd = '';
      $mpAd = '';
      
      if(is_single() || is_page()) {
        if(!empty($options['beforePost']) && !empty($options['bpAdsId'])) 
          $bpAd = $this->buildAd(array('id' => $options['bpAdsId']), $options['bpUseCodes']);
        if(!empty($options['middlePost']) && !empty($options['mpAdsId']))
          $mpAd = $this->buildAd(array('id' => $options['mpAdsId']), $options['mpUseCodes']);
        if(!empty($options['afterPost']) && !empty($options['apAdsId'])) 
          $apAd = $this->buildAd(array('id' => $options['apAdsId']), $options['apUseCodes']);
      }

      if(!empty($mpAd)) {
        $xc = explode("\r\n", $content);
        $hm = ceil(count($xc)/2);
        $cntFirst = implode("\r\n", array_slice($xc, 0, $hm));
        $cntLast = implode("\r\n", array_slice($xc, $hm));

        return $bpAd.$cntFirst.$mpAd.$cntLast.$apAd;
      }
      else return $bpAd.$content.$apAd;
    }
  } // end of class definition
} // end of if not class SimpleAdsManager exists
?>