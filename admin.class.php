<?php
if ( !class_exists( 'SimpleAdsManagerAdmin' && class_exists('SimpleAdsManager') ) ) {
  class SimpleAdsManagerAdmin extends SimpleAdsManager {
    private $editPage;
    private $settingsPage;
    private $listPage;
    private $editZone;
    private $listZone;
    private $editBlock;
    private $listBlock;
    private $eLogPage;
    private $cmsVer;
    private $settingsTabs;
    private $samPointerOptions = array('places' => true, 'ads' => true, 'zones' => true, 'blocks' => true);
    
    public function __construct() {
      parent::__construct();

      global $wp_version, $sam_tables_defs, $wpdb;
      
			if ( function_exists( 'load_plugin_textdomain' ) )
				load_plugin_textdomain( SAM_DOMAIN, false, basename( SAM_PATH ) . '/langs/' );
      
      if(!is_dir(SAM_AD_IMG)) mkdir(SAM_AD_IMG);

      $this->settingsTabs = array();
      $sam_tables_defs = self::getTablesDefs($wpdb->prefix);
				
      register_activation_hook(SAM_MAIN_FILE, array(&$this, 'onActivate'));
      register_deactivation_hook(SAM_MAIN_FILE, array(&$this, 'onDeactivate'));
      register_uninstall_hook(SAM_MAIN_FILE, array(__CLASS__, 'onUninstall'));

      $options = parent::getSettings(false);
      if(!empty($options['access'])) $access = $options['access'];
      else $access = 'manage_options';
      //self::checkCachePlugins();

      define('SAM_ACCESS', $access);

      //add_action('wp_ajax_upload_ad_image', array(&$this, 'uploadHandler'));
      add_action('wp_ajax_close_pointer', array(&$this, 'closePointerHandler'));
			add_action('admin_menu', array(&$this, 'regAdminPage'));
      add_filter('tiny_mce_version', array(&$this, 'tinyMCEVersion'));
      add_action('init', array(&$this, 'addButtons'));
      add_action('admin_init', array(&$this, 'checkCachePlugins'));
      add_action('admin_init', array(&$this, 'checkBbpForum'));
      add_action('admin_init', array(&$this, 'initSettings'), 11);
      if(version_compare($wp_version, '3.3', '<'))
        add_filter('contextual_help', array(&$this, 'help'), 10, 3);

      $versions = parent::getVersions(true);
      if(empty($versions) ||
         version_compare($versions['sam'], SAM_VERSION, '<') ||
         version_compare($versions['db'], SAM_DB_VERSION, '<')) self::updateDB();

      $ver = $this->getWpVersion();
      if((int)$ver['major'] >= 3) {
        if((int)$ver['minor'] >= 3) $this->cmsVer = 'high';
        else $this->cmsVer = 'low';
      }
      else $this->cmsVer = 'not supported';
      self::getPointerOptions(true);
    }

    public function onActivate() {
      $settings = parent::getSettings(true);
			update_option( SAM_OPTIONS_NAME, $settings );
			self::updateDB();
    }

    public function onDeactivate() {
      global $wpdb;
			$zTable = $wpdb->prefix . "sam_zones";
      $pTable = $wpdb->prefix . "sam_places";
			$aTable = $wpdb->prefix . "sam_ads";
      $bTable = $wpdb->prefix . "sam_blocks";
      $eTable = $wpdb->prefix . "sam_errors";
      $sTable = $wpdb->prefix . "sam_stats";
			$settings = parent::getSettings();

			if($settings['deleteOptions'] == 1) {
				delete_option( SAM_OPTIONS_NAME );
				delete_option('sam_version');
				delete_option('sam_db_version');
			}
			if($settings['deleteDB'] == 1) {
				$sql = 'DROP TABLE IF EXISTS ';
        $wpdb->query($sql.$zTable);
				$wpdb->query($sql.$pTable);
				$wpdb->query($sql.$aTable);
        $wpdb->query($sql.$bTable);
        $wpdb->query($sql.$eTable);
        $wpdb->query($sql.$sTable);
				delete_option('sam_db_version');
			}
      if($settings['deleteFolder'] == 1) {
        if(is_dir(SAM_AD_IMG)) rmdir(SAM_AD_IMG);
      }
    }

    public static function onUninstall() {
      global $wpdb;
      $zTable = $wpdb->prefix . "sam_zones";
      $pTable = $wpdb->prefix . "sam_places";
      $aTable = $wpdb->prefix . "sam_ads";
      $bTable = $wpdb->prefix . "sam_blocks";
      $eTable = $wpdb->prefix . "sam_errors";
      $sTable = $wpdb->prefix . "sam_stats";

      delete_option( SAM_OPTIONS_NAME );
      delete_option('sam_version');
      delete_option('sam_db_version');

      $sql = 'DROP TABLE IF EXISTS ';
      $wpdb->query($sql.$zTable);
      $wpdb->query($sql.$pTable);
      $wpdb->query($sql.$aTable);
      $wpdb->query($sql.$bTable);
      $wpdb->query($sql.$eTable);
      $wpdb->query($sql.$sTable);

      if(is_dir(SAM_AD_IMG)) rmdir(SAM_AD_IMG);
    }

    public function checkCachePlugins() {
      $w3tc = 'w3-total-cache/w3-total-cache.php';
      $wpsc = 'wp-super-cache/wp-cache.php';
      define('SAM_WPSC', is_plugin_active($wpsc));
      define('SAM_W3TC', is_plugin_active($w3tc));
    }

    public function checkBbpForum() {
      $force = ( empty( $this->samOptions ) );
      $settings = parent::getSettings( $force );
      $bbp = 'bbpress/bbpress.php';
      define('SAM_BBP', is_plugin_active($bbp));
      $settings['bbpActive'] = ( SAM_BBP ) ? 1 : 0;
      if( ! SAM_BBP ) $settings['bbpEnabled'] = 0;
      update_option( SAM_OPTIONS_NAME, $settings );
    }

    public function hideBbpOptions() {
      $options = parent::getSettings();
      return !( SAM_BBP && $options['bbpEnabled']);
    }

    private function getWarningString( $mode = '' ) {
      if(empty($mode)) return '';

      global $wp_version;
      $options = parent::getSettings();
      $classDef = false;

      switch($mode) {
        case 'cache':
          if(SAM_W3TC) $text = __('Active W3 Total Cache plugin detected.', SAM_DOMAIN);
          elseif(SAM_WPSC) $text = __('Active WP Super Cache plugin detected.', SAM_DOMAIN);
          else $text = '';
          $classDef = ($options['adShow'] == 'php');
          break;
        case 'forum':
          if(SAM_BBP) $text = __('Active bbPress Forum plugin detected.', SAM_DOMAIN);
          else $text = '';
          $classDef = (!$options['bbpEnabled']);
      }

      if(version_compare($wp_version, '3.8-RC1', '<')) $class = ($classDef) ? 'sam-warning' : 'sam-info';
      else $class = ($classDef) ? 'sam2-warning' : 'sam2-info';

      return ((!empty($text)) ? "<div class='{$class}'><p>{$text}</p></div>" : '');
    }

    public function clearCache() {
      if( SAM_WPSC ) {
        global $blog_cache_dir, $wp_cache_object_cache;
        if($wp_cache_object_cache) reset_oc_version();
        else {
          prune_super_cache( $blog_cache_dir, true );
          prune_super_cache( get_supercache_dir(), true );
        }
        return __('Cache of WP Super Cache plugin is flushed.', SAM_DOMAIN);
      } elseif( SAM_W3TC ) {
        if(function_exists('w3tc_pgcache_flush')) w3tc_pgcache_flush();
        if(function_exists('w3tc_dbcache_flush')) w3tc_dbcache_flush();
        return __('Cache of W3 Total Cache plugin is flushed.', SAM_DOMAIN);
      } else return '';
    }

    public function  getPointerOptions($force = false) {
      if($force) {
        $pointers = get_option('sam_pointers', '');
        if($pointers == '') {
          $pointers = get_option('sam_pointers', $this->samPointerOptions);
          update_option('sam_pointers', $pointers);
        }
        $this->samPointerOptions = $pointers;
      }
      else $pointers = $this->samPointerOptions;

      return $pointers;
    }

    private function getVersionData($version) {
      $output = array();
      $vArray = explode('.', $version);

      $output['major'] = (integer)$vArray[0];
      $output['minor'] = (integer)$vArray[1];
      if(isset($vArray[2])) $output['revision'] = (integer)$vArray[2];
      else $output['revision'] = 0;

      return $output;
    }

    public function getWpVersion() {
      global $wp_version;
      $version = array();

      $ver = explode('.', $wp_version);
      $version['major'] = $ver[0];
      $vc = count($ver);
      if($vc == 2) {
        $subver = explode('-', $ver[1]);
        $version['minor'] = $subver[0];
        $version['spec'] = (count($subver) > 1) ? $subver[1] : '';
        $version['str'] = $version['major'].'.'.$version['minor'].((!empty($version['spec'])) ? ' ('.$version['spec'].')' : '');
      }
      else {
        $version['minor'] = $ver[1];
        $version['build'] = $ver[2];
        $version['str'] = $wp_version;
      }

      return $version;
    }

    private function updateDB() {
      $versions = $this->getVersions(true);
      $dbVersion = $versions['db'];
      $vData = $this->getVersionData($dbVersion);

      include_once('updater.class.php');

      $updater = new SamUpdater($dbVersion, $vData, parent::getSettings());
      $updater->update();

      $this->getVersions(true);
    }

    public function getTablesDefs( $prefix = 'wp_' ) {
      $pTableDef = array(
        'id' => array('Type' => "int(11)", 'Null' => 'NO', 'Key' => 'PRI', 'Default' => '', 'Extra' => 'auto_increment'),
        'name' => array('Type' => "varchar(255)", 'Null' => 'NO', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'description' => array('Type' => "varchar(255)", 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'code_before' => array('Type' => "varchar(255)", 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'code_after' => array('Type' => "varchar(255)", 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'place_size' => array('Type' => "varchar(25)", 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'place_custom_width' => array('Type' => "int(11)", 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'place_custom_height' => array('Type' => "int(11)", 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'patch_img' => array('Type' => "varchar(255)", 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'patch_link' => array('Type' => "varchar(255)", 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'patch_code' => array('Type' => "text", 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'patch_adserver' => array('Type' => "tinyint(1)", 'Null' => 'YES', 'Key' => '', 'Default' => '0', 'Extra' => ''),
        'patch_dfp' => array('Type' => "varchar(255)", 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'patch_source' => array('Type' => "tinyint(1)", 'Null' => 'YES', 'Key' => '', 'Default' => '0', 'Extra' => ''),
        'patch_hits' => array('Type' => "int(11)", 'Null' => 'YES', 'Key' => '', 'Default' => '0', 'Extra' => ''),
        'trash' => array('Type' => "tinyint(1)", 'Null' => 'YES', 'Key' => '', 'Default' => '0', 'Extra' => '')
      );

      $aTableDef = array(
        'id' => array('Type' => "int(11)", 'Null' => 'NO', 'Key' => 'PRI', 'Default' => '', 'Extra' => 'auto_increment'),
        'pid' => array('Type' => "int(11)", 'Null' => 'NO', 'Key' => 'PRI', 'Default' => '', 'Extra' => ''),
        'name' => array('Type' => "varchar(255)", 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'description' => array('Type' => "varchar(255)", 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'code_type' => array('Type' => "tinyint(1)", 'Null' => 'NO', 'Key' => '', 'Default' => '0', 'Extra' => ''),
        'code_mode' => array('Type' => "tinyint(1)", 'Null' => 'NO', 'Key' => '', 'Default' => '1', 'Extra' => ''),
        'ad_code' => array('Type' => "text", 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'ad_img' => array('Type' => "text", 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'ad_alt' => array('Type' => "text", 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'ad_title' => array('Type' => "varchar(255)", 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'ad_no' => array('Type' => "tinyint(1)", 'Null' => 'NO', 'Key' => '', 'Default' => '0', 'Extra' => ''),
        'ad_target' => array('Type' => "text", 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'ad_swf' => array('Type' => "tinyint(1)", 'Null' => 'YES', 'Key' => '', 'Default' => '0', 'Extra' => ''),
        'ad_swf_flashvars' => array('Type' => "text", 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'ad_swf_params' => array('Type' => "text", 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'ad_swf_attributes' => array('Type' => "text", 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'count_clicks' => array('Type' => "tinyint(1)", 'Null' => 'NO', 'Key' => '', 'Default' => '0', 'Extra' => ''),
        'view_type' => array('Type' => "int(11)", 'Null' => 'YES', 'Key' => '', 'Default' => '1', 'Extra' => ''),
        'view_pages' => array('Type' => "set('isHome','isSingular','isSingle','isPage','isAttachment','isSearch','is404','isArchive','isTax','isCategory','isTag','isAuthor','isDate','isPostType','isPostTypeArchive')", 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'view_id' => array('Type' => "text", 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'ad_users' => array('Type' => "tinyint(1)", 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'ad_users_unreg' => array('Type' => "tinyint(1)", 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'ad_users_reg' => array('Type' => "tinyint(1)", 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'x_ad_users' => array('Type' => "tinyint(1)", 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'x_view_users' => array('Type' => "text", 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'ad_users_adv' => array('Type' => "tinyint(1)", 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'ad_cats' => array('Type' => "tinyint(1)", 'Null' => 'YES', 'Key' => '', 'Default' => '0', 'Extra' => ''),
        'view_cats' => array('Type' => "text", 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'ad_authors' => array('Type' => "tinyint(1)", 'Null' => 'YES', 'Key' => '', 'Default' => '0', 'Extra' => ''),
        'view_authors' => array('Type' => "text", 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'ad_tags' => array('Type' => "tinyint(1)", 'Null' => 'YES', 'Key' => '', 'Default' => '0', 'Extra' => ''),
        'view_tags' => array('Type' => "text", 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'ad_custom' => array('Type' => "tinyint(1)", 'Null' => 'YES', 'Key' => '', 'Default' => '0', 'Extra' => ''),
        'view_custom' => array('Type' => "text", 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'x_id' => array('Type' => "tinyint(1)", 'Null' => 'YES', 'Key' => '', 'Default' => '0', 'Extra' => ''),
        'x_view_id' => array('Type' => "text", 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'x_cats' => array('Type' => "tinyint(1)", 'Null' => 'YES', 'Key' => '', 'Default' => '0', 'Extra' => ''),
        'x_view_cats' => array('Type' => "text", 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'x_authors' => array('Type' => "tinyint(1)", 'Null' => 'YES', 'Key' => '', 'Default' => '0', 'Extra' => ''),
        'x_view_authors' => array('Type' => "text", 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'x_tags' => array('Type' => "tinyint(1)", 'Null' => 'YES', 'Key' => '', 'Default' => '0', 'Extra' => ''),
        'x_view_tags' => array('Type' => "text", 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'x_custom' => array('Type' => "tinyint(1)", 'Null' => 'YES', 'Key' => '', 'Default' => '0', 'Extra' => ''),
        'x_view_custom' => array('Type' => "text", 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'ad_schedule' => array('Type' => "tinyint(1)", 'Null' => 'YES', 'Key' => '', 'Default' => '0', 'Extra' => ''),
        'ad_start_date' => array('Type' => "date", 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'ad_end_date' => array('Type' => "date", 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'limit_hits' => array('Type' => "tinyint(1)", 'Null' => 'YES', 'Key' => '', 'Default' => '0', 'Extra' => ''),
        'hits_limit' => array('Type' => "int(11)", 'Null' => 'YES', 'Key' => '', 'Default' => '0', 'Extra' => ''),
        'limit_clicks' => array('Type' => "tinyint(1)", 'Null' => 'YES', 'Key' => '', 'Default' => '0', 'Extra' => ''),
        'clicks_limit' => array('Type' => "int(11)", 'Null' => 'YES', 'Key' => '', 'Default' => '0', 'Extra' => ''),
        'ad_hits' => array('Type' => "int(11)", 'Null' => 'YES', 'Key' => '', 'Default' => '0', 'Extra' => ''),
        'ad_clicks' => array('Type' => "int(11)", 'Null' => 'YES', 'Key' => '', 'Default' => '0', 'Extra' => ''),
        'ad_weight' => array('Type' => "int(11)", 'Null' => 'YES', 'Key' => '', 'Default' => '10', 'Extra' => ''),
        'ad_weight_hits' => array('Type' => "int(11)", 'Null' => 'YES', 'Key' => '', 'Default' => '0', 'Extra' => ''),
        'adv_nick' => array('Type' => "varchar(50)", 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'adv_name' => array('Type' => "varchar(100)", 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'adv_mail' => array('Type' => "varchar(50)", 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'cpm' => array('Type' => "decimal(10,2) unsigned", 'Null' => 'YES', 'Key' => '', 'Default' => '0.00', 'Extra' => ''),
        'cpc' => array('Type' => "decimal(10,2) unsigned", 'Null' => 'YES', 'Key' => '', 'Default' => '0.00', 'Extra' => ''),
        'per_month' => array('Type' => "decimal(10,2) unsigned", 'Null' => 'YES', 'Key' => '', 'Default' => '0.00', 'Extra' => ''),
        'trash' => array('Type' => "tinyint(1)", 'Null' => 'NO', 'Key' => '', 'Default' => '0', 'Extra' => ''),
        'ad_custom_tax_terms' => array('Type' => "tinyint(1)", 'Null' => 'YES', 'Key' => '', 'Default' => '0', 'Extra' => ''),
        'view_custom_tax_terms' => array('Type' => "text", 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'x_ad_custom_tax_terms' => array('Type' => "tinyint(1)", 'Null' => 'YES', 'Key' => '', 'Default' => '0', 'Extra' => ''),
        'x_view_custom_tax_terms' => array('Type' => "text", 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => '')
      );

      $zTableDef = array(
        'id' => array('Type' => "int(11)", 'Null' => 'NO', 'Key' => 'PRI', 'Default' => '', 'Extra' => 'auto_increment'),
        'name' => array('Type' => "varchar(255)", 'Null' => 'NO', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'description' => array('Type' => "varchar(255)", 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'z_default' => array('Type' => "int(11)", 'Null' => 'YES', 'Key' => '', 'Default' => '0', 'Extra' => ''),
        'z_home' => array('Type' => "int(11)", 'Null' => 'YES', 'Key' => '', 'Default' => '0', 'Extra' => ''),
        'z_singular' => array('Type' => "int(11)", 'Null' => 'YES', 'Key' => '', 'Default' => '0', 'Extra' => ''),
        'z_single' => array('Type' => "int(11)", 'Null' => 'YES', 'Key' => '', 'Default' => '0', 'Extra' => ''),
        'z_ct' => array('Type' => "int(11)", 'Null' => 'YES', 'Key' => '', 'Default' => '0', 'Extra' => ''),
        'z_single_ct' => array('Type' => "longtext", 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'z_page' => array('Type' => "int(11)", 'Null' => 'YES', 'Key' => '', 'Default' => '0', 'Extra' => ''),
        'z_attachment' => array('Type' => "int(11)", 'Null' => 'YES', 'Key' => '', 'Default' => '0', 'Extra' => ''),
        'z_search' => array('Type' => "int(11)", 'Null' => 'YES', 'Key' => '', 'Default' => '0', 'Extra' => ''),
        'z_404' => array('Type' => "int(11)", 'Null' => 'YES', 'Key' => '', 'Default' => '0', 'Extra' => ''),
        'z_archive' => array('Type' => "int(11)", 'Null' => 'YES', 'Key' => '', 'Default' => '0', 'Extra' => ''),
        'z_tax' => array('Type' => "int(11)", 'Null' => 'YES', 'Key' => '', 'Default' => '0', 'Extra' => ''),
        'z_taxes' => array('Type' => "longtext", 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'z_category' => array('Type' => "int(11)", 'Null' => 'YES', 'Key' => '', 'Default' => '0', 'Extra' => ''),
        'z_cats' => array('Type' => "longtext", 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'z_tag' => array('Type' => "int(11)", 'Null' => 'YES', 'Key' => '', 'Default' => '0', 'Extra' => ''),
        'z_author' => array('Type' => "int(11)", 'Null' => 'YES', 'Key' => '', 'Default' => '0', 'Extra' => ''),
        'z_authors' => array('Type' => "longtext", 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'z_date' => array('Type' => "int(11)", 'Null' => 'YES', 'Key' => '', 'Default' => '0', 'Extra' => ''),
        'z_cts' => array('Type' => "int(11)", 'Null' => 'YES', 'Key' => '', 'Default' => '0', 'Extra' => ''),
        'z_archive_ct' => array('Type' => "longtext", 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'trash' => array('Type' => "tinyint(1)", 'Null' => 'YES', 'Key' => '', 'Default' => '0', 'Extra' => '')
      );

      $bTableDef = array(
        'id' => array('Type' => "int(11)", 'Null' => 'NO', 'Key' => 'PRI', 'Default' => '', 'Extra' => 'auto_increment'),
        'name' => array('Type' => "varchar(255)", 'Null' => 'NO', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'description' => array('Type' => "varchar(255)", 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'b_lines' => array('Type' => "int(11)", 'Null' => 'YES', 'Key' => '', 'Default' => '2', 'Extra' => ''),
        'b_cols' => array('Type' => "int(11)", 'Null' => 'YES', 'Key' => '', 'Default' => '2', 'Extra' => ''),
        'block_data' => array('Type' => "longtext", 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'b_margin' => array('Type' => "varchar(30)", 'Null' => 'YES', 'Key' => '', 'Default' => '5px 5px 5px 5px', 'Extra' => 'str'),
        'b_padding' => array('Type' => "varchar(30)", 'Null' => 'YES', 'Key' => '', 'Default' => '5px 5px 5px 5px', 'Extra' => 'str'),
        'b_background' => array('Type' => "varchar(30)", 'Null' => 'YES', 'Key' => '', 'Default' => '#FFFFFF', 'Extra' => 'str'),
        'b_border' => array('Type' => "varchar(30)", 'Null' => 'YES', 'Key' => '', 'Default' => '0px solid #333333', 'Extra' => 'str'),
        'i_margin' => array('Type' => "varchar(30)", 'Null' => 'YES', 'Key' => '', 'Default' => '5px 5px 5px 5px', 'Extra' => 'str'),
        'i_padding' => array('Type' => "varchar(30)", 'Null' => 'YES', 'Key' => '', 'Default' => '5px 5px 5px 5px', 'Extra' => 'str'),
        'i_background' => array('Type' => "varchar(30)", 'Null' => 'YES', 'Key' => '', 'Default' => '#FFFFFF', 'Extra' => 'str'),
        'i_border' => array('Type' => "varchar(30)", 'Null' => 'YES', 'Key' => '', 'Default' => '0px solid #333333', 'Extra' => 'str'),
        'trash' => array('Type' => "tinyint(1)", 'Null' => 'YES', 'Key' => '', 'Default' => '0', 'Extra' => '')
      );

      $sTableDef = array(
        'id' => array('Type' => 'int(10) unsigned', 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'pid' => array('Type' => 'int(10) unsigned', 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'event_time' => array('Type' => 'datetime', 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'event_type' => array('Type' => 'tinyint(1)', 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => ''),
        'remote_addr' => array('Type' => 'varchar(15)', 'Null' => 'YES', 'Key' => '', 'Default' => '', 'Extra' => '')
      );

	    $pIndexDef = array(
		    'UK_'.$prefix.'places' => array(
			    'id' => array('Non_unique' => 0, 'Seq_in_index' => 1, 'Column_name' => 'id')
		    )
	    );

	    $aIndexDef = array(
		    'UK_'.$prefix.'ads' => array(
		      'pid' => array('Non_unique' => 0, 'Seq_in_index' => 1, 'Column_name' => 'pid'),
		      'id' => array('Non_unique' => 0, 'Seq_in_index' => 2, 'Column_name' => 'id')
		    )
	    );

	    $zIndexDef = array(
		    'UK_'.$prefix.'zones' => array(
		      'id' => array('Non_unique' => 0, 'Seq_in_index' => 1, 'Column_name' => 'id')
		    )
	    );

	    $bIndexDef = array(
		    'UK_'.$prefix.'blocks' => array(
		      'id' => array('Non_unique' => 0, 'Seq_in_index' => 1, 'Column_name' => 'id')
		    )
	    );

	    $sIndexDef = array(
		    'IDX_'.$prefix.'stats' => array(
		      'id' => array('Non_unique' => 1, 'Seq_in_index' => 1, 'Column_name' => 'id'),
		      'pid' => array('Non_unique' => 1, 'Seq_in_index' => 2, 'Column_name' => 'pid'),
		      'event_time' => array('Non_unique' => 1, 'Seq_in_index' => 3, 'Column_name' => 'event_time')
		    )
	    );

      return array(
        'places' => $pTableDef,
        'ads' => $aTableDef,
        'zones' => $zTableDef,
        'blocks' => $bTableDef,
        'stats' => $sTableDef,
	      'idxPlaces' => $pIndexDef,
	      'idxAds' => $aIndexDef,
	      'idxZones' => $zIndexDef,
	      'idxBlocks' => $bIndexDef,
	      'idxStats' => $sIndexDef
      );
    }

	  private function getSearchesModel() {
		  return array(
			  'posts' => array(
				  array('field' => 'id', 'caption' => 'ID', 'type' => 'int'),
				  array('field' => 'title', 'caption' => __("Publication Title", SAM_DOMAIN), 'type' => 'text'),
				  array('field' => 'type', 'caption' => __("Publication Type", SAM_DOMAIN), 'type' => 'text')
			  )
		  );
	  }

    private function getColumnsModels() {
      return array(
        'comboGrid' => array(
          array('columnName' => 'id', 'width' => '15', 'hidden' => true, 'align' => 'right', 'label' => 'Id'),
          array('columnName' => 'title', 'width' => '190', 'align' => 'left', 'label' => __('Advertiser Name', SAM_DOMAIN)),
          array('columnName' => 'slug', 'width' => '190', 'align' => 'left', 'label' => __('Advertiser Nick', SAM_DOMAIN)),
          array('columnName' => 'email', 'width' => '190', 'align' => 'left', 'label' => __('Advertiser e-mail', SAM_DOMAIN))
        ),
        'cats' => array(
          array('field' => 'id', 'caption' => 'ID', 'size' => '40px'),
          array('field' => 'title', 'caption' => __("Category Title", SAM_DOMAIN), 'size' => '50%'),
          array('field' => 'slug', 'caption' => __("Category Slug", SAM_DOMAIN), 'size' => '40%')
        ),
        'authors' => array(
          array('field' => 'id', 'caption' => 'ID', 'size' => '40px'),
          array('field' => 'title', 'caption' => __("Display Name", SAM_DOMAIN), 'size' => '50%'),
          array('field' => 'slug', 'caption' => __("User Name", SAM_DOMAIN), 'size' => '40%')
        ),
        'tags' => array(
          array('field' => 'id', 'caption' => 'ID', 'size' => '40px'),
          array('field' => 'title', 'caption' => __("Tag Title", SAM_DOMAIN), 'size' => '50%'),
          array('field' => 'slug', 'caption' => __("Tag Slug", SAM_DOMAIN), 'size' => '40%')
        ),
        'customs' => array(
          array('field' => 'title', 'caption' => __("Custom Type Title", SAM_DOMAIN), 'size' => '50%'),
          array('field' => 'slug', 'caption' => __("Custom Type Slug", SAM_DOMAIN), 'size' => '50%')
        ),
        'posts' => array(
          array('field' => 'id', 'caption' => 'ID', 'size' => '40px'),
          array('field' => 'title', 'caption' => __("Publication Title", SAM_DOMAIN), 'size' => '50%'),
          array('field' => 'type', 'caption' => __("Publication Type", SAM_DOMAIN), 'size' => '40%')
        ),
        'users' => array(
          array('field' => 'id', 'caption' => 'ID', 'size' => '40px'),
          array('field' => 'title', 'caption' => __("Display Name", SAM_DOMAIN), 'size' => '40%'),
          array('field' => 'slug', 'caption' => __("User Name", SAM_DOMAIN), 'size' => '25%'),
          array('field' => 'role', 'caption' => __("Role", SAM_DOMAIN), 'size' => '25%')
        ),
        'customTaxes' => array(
          array('field' => 'term_id', 'caption' => __('ID', SAM_DOMAIN), 'size' => '30px'),
          array('field' => 'name', 'caption' => __('Term Name', SAM_DOMAIN), 'size' => '50%'),
          array('field' => 'ctax_name', 'caption' => __('Custom Taxonomy Name', SAM_DOMAIN), 'size' => '40%')
        )
      );
    }

    private function getGridsData() {
      global $wpdb, $wp_taxonomies;

      $tTable = $wpdb->prefix . "terms";
      $ttTable = $wpdb->prefix . "term_taxonomy";

      //Custom Post Types
      $args = array('public' => true, '_builtin' => false);
      $output = 'objects';
      $operator = 'and';
      $post_types = get_post_types($args, $output, $operator);
      $customs = array();
      $sCustoms = array();

      foreach($post_types as $post_type) {
        array_push($customs, array('title' => $post_type->labels->name, 'slug' => $post_type->name));
        array_push($sCustoms, $post_type->name);
      }
      $k = 0;
      foreach($customs as &$val) {
        $k++;
        $val['recid'] = $k;
      }
      if(!empty($sCustoms)) $custs = ',' . implode(',', $sCustoms);
      else $custs = '';

      // Custom Taxonomies Terms
      $sql = "SELECT wt.term_id, wt.name, wt.slug, wtt.taxonomy
              FROM $tTable wt
              INNER JOIN $ttTable wtt
              ON wt.term_id = wtt.term_id
              WHERE NOT FIND_IN_SET(wtt.taxonomy, 'category,post_tag,nav_menu,link_category,post_format');";

      $cTax = $wpdb->get_results($sql, ARRAY_A);
      $k = 0;
      foreach($cTax as &$val) {
        if(isset($wp_taxonomies[$val['taxonomy']])) $val['ctax_name'] = urldecode($wp_taxonomies[$val['taxonomy']]->labels->name);
        else $val['ctax_name'] = '';
        $k++;
        $val['recid'] = $k;
      }

      return array(
        'customs' => $customs,
        'custList' => $custs,
        'cTax' => $cTax
      );
    }

    public function getAdsDataX() {
      global $wpdb;

      $aTable = $wpdb->prefix . 'sam_ads';
      $pTable = $wpdb->prefix . 'sam_places';
      $zTable = $wpdb->prefix . 'sam_zones';
      $bTable = $wpdb->prefix . 'sam_blocks';

      $sql = "SELECT 0 AS parentId, wsa.id AS value, wsa.name AS text FROM $aTable wsa WHERE wsa.trash IS NOT TRUE
              UNION SELECT 1 AS parentId, wsp.id AS value, wsp.name AS text FROM $pTable wsp WHERE wsp.trash IS NOT TRUE
              UNION SELECT 2 AS parentId, wsz.id AS value, wsz.name AS text FROM $zTable wsz WHERE wsz.trash IS NOT TRUE
              UNION SELECT 3 AS parentId, wsb.id AS value, wsb.name AS text FROM $bTable wsb WHERE wsb.trash IS NOT TRUE;";
      $rows = $wpdb->get_results($sql, ARRAY_A);

      return $rows;
    }

    public function starSettingsTab( $section, $uri, $name ) {
      $this->settingsTabs[$section] = array('start_tab' => true, 'uri' => $uri, 'name' => $name);
    }

    public function finishSettingsTab( $section ) {
      $this->settingsTabs[$section]['finish_tab'] = true;
    }

		public function initSettings() {
			global $current_user;
      get_currentuserinfo();

      $scStr = __("Shortcode <code>[name]</code> will be replaced with advertiser's name. Shortcode <code>[site]</code> will be replaced with name of your site. Shotcode <code>[month]</code> will be replaced with name of month of reporting period.", SAM_DOMAIN);

      register_setting('samOptions', SAM_OPTIONS_NAME);

      self::starSettingsTab("sam_general_section", 'tabs-1', __('General', SAM_DOMAIN));
      add_settings_section("sam_general_section", __("General Settings", SAM_DOMAIN), array(&$this, "drawGeneralSection"), 'sam-settings');
      add_settings_section("sam_ext_section", __('Extended Options', SAM_DOMAIN), array(&$this, 'drawExtSection'), 'sam-settings');
      add_settings_section("sam_layout_section", __("Admin Layout", SAM_DOMAIN), array(&$this, "drawLayoutSection"), 'sam-settings');
      add_settings_section("sam_deactivate_section", __("Plugin Deactivating", SAM_DOMAIN), array(&$this, "drawDeactivateSection"), 'sam-settings');
      self::finishSettingsTab('sam_deactivate_section');
      self::starSettingsTab('sam_single_section', 'tabs-2', __('Auto Inserting', SAM_DOMAIN));
      add_settings_section("sam_single_section", __("Auto Inserting Settings", SAM_DOMAIN), array(&$this, "drawSingleSection"), 'sam-settings');
      self::finishSettingsTab('sam_single_section');
      self::starSettingsTab('sam_dfp_section', 'tabs-3', __('Google', SAM_DOMAIN));
      add_settings_section("sam_dfp_section", __("Google DFP Settings", SAM_DOMAIN), array(&$this, "drawDFPSection"), 'sam-settings');
      self::finishSettingsTab('sam_dfp_section');
      self::starSettingsTab('sam_mailer_section', 'tabs-4', __('Mailer', SAM_DOMAIN));
      add_settings_section('sam_mailer_section', __('Mailing System', SAM_DOMAIN), array(&$this, 'drawMailerSection'), 'sam-settings');
      add_settings_section('sam_mailer_data_section', __('Mail Data', SAM_DOMAIN), array(&$this, 'drawMailerDataSection'), 'sam-settings');
      add_settings_section('sam_mailer_content_section', __('Mail Content', SAM_DOMAIN), array(&$this, 'drawMailerContentSection'), 'sam-settings');
      add_settings_section('sam_mailer_preview_section', __('Preview', SAM_DOMAIN), array(&$this, 'drawPreviewSection'), 'sam-settings');
      self::finishSettingsTab('sam_mailer_preview_section');
      self::starSettingsTab('sam_statistic_section', 'tabs-5', __('Tools', SAM_DOMAIN));
      add_settings_section("sam_statistic_section", __("Statistics Settings", SAM_DOMAIN), array(&$this, "drawStatisticsSection"), 'sam-settings');
      self::finishSettingsTab('sam_statistic_section');

      add_settings_field('adCycle', __("Views per Cycle", SAM_DOMAIN), array(&$this, 'drawTextOption'), 'sam-settings', 'sam_general_section', array('description' => __('Number of hits of one ad for a full cycle of rotation (maximal activity).', SAM_DOMAIN)));
      add_settings_field('access', __('Minimum Level for access to menu', SAM_DOMAIN), array(&$this, 'drawJSliderOption'), 'sam-settings', 'sam_general_section', array('description' => __('Who can use menu of plugin - Minimum User Level needed for access to menu of plugin. In any case only Super Admin and Administrator can use Settings Menu of SAM Plugin.', SAM_DOMAIN), 'options' => array('manage_network' => __('Super Admin', SAM_DOMAIN), 'manage_options' => __('Administrator', SAM_DOMAIN), 'edit_others_posts' => __('Editor', SAM_DOMAIN), 'publish_posts' => __('Author', SAM_DOMAIN), 'edit_posts' => __('Contributor', SAM_DOMAIN)), 'values' => array('manage_network', 'manage_options', 'edit_others_posts', 'publish_posts', 'edit_posts')));
      add_settings_field('adShow', __("Ad Output Mode", SAM_DOMAIN), array(&$this, 'drawRadioOption'), 'sam-settings', 'sam_general_section', array('description' => __('Standard (PHP) mode is more faster but is not compatible with caching plugins. If your blog use caching plugin (i.e WP Super Cache or W3 Total Cache) select "Caching Compatible (Javascript)" mode. Due to the confusion around "mfunc" in caching plugins, I decided to refrain from development of special support of these plugins.', SAM_DOMAIN), 'options' => array('php' => __('Standard (PHP)', SAM_DOMAIN), 'js' => __('Caching Compatible (Javascript)', SAM_DOMAIN)), 'warning' => 'cache'));
      add_settings_field('adDisplay', __("Display Ad Source in", SAM_DOMAIN), array(&$this, 'drawRadioOption'), 'sam-settings', 'sam_general_section', array('description' => __('Target wintow (tab) for advetisement source.', SAM_DOMAIN), 'options' => array('blank' => __('New Window (Tab)', SAM_DOMAIN), 'self' => __('Current Window (Tab)', SAM_DOMAIN))));
      add_settings_field('bbpEnabled', __('Allow displaying ads on bbPress forum pages', SAM_DOMAIN), array(&$this, 'drawCheckboxOption'), 'sam-settings', 'sam_general_section', array('label_for' => 'bbpEnabled', 'checkbox' => true, 'warning' => 'forum', 'enabled' => ( (defined('SAM_BBP')) ? SAM_BBP  : false )));

      add_settings_field('bpAdsType', __('Ad Object before content', SAM_DOMAIN), array(&$this, 'drawCascadeSelectOption'), 'sam-settings', 'sam_single_section', array('group' => array('slave' => 'bpAdsId', 'master' => true, 'title' => __('Type of Ad Object', SAM_DOMAIN).':')));
      add_settings_field('bpAdsId', __("Ads Place before content", SAM_DOMAIN), array(&$this, 'drawCascadeSelectOption'), 'sam-settings', 'sam_single_section', array('description' => '', 'group' => array('slave' => null, 'master' => false, 'title' => __('Ad Object', SAM_DOMAIN).':')));
      add_settings_field('beforePost', __("Allow Ads Place auto inserting before post/page content", SAM_DOMAIN), array(&$this, 'drawCheckboxOption'), 'sam-settings', 'sam_single_section', array('label_for' => 'beforePost', 'checkbox' => true));
      add_settings_field('bpExcerpt', __('Allow Ads Place auto inserting before post/page or post/page excerpt in the loop', SAM_DOMAIN), array(&$this, 'drawCheckboxOption'), 'sam-settings', 'sam_single_section', array('label_for' => 'bpExcerpt', 'checkbox' => true));
      add_settings_field('bbpBeforePost', __("Allow Ads Place auto inserting before bbPress Forum topic content", SAM_DOMAIN), array(&$this, 'drawCheckboxOption'), 'sam-settings', 'sam_single_section', array('label_for' => 'bbpBeforePost', 'checkbox' => true, 'hide' => self::hideBbpOptions()));
      add_settings_field('bbpList', __("Allow Ads Place auto inserting into bbPress Forum forums/topics lists", SAM_DOMAIN), array(&$this, 'drawCheckboxOption'), 'sam-settings', 'sam_single_section', array('label_for' => 'bbpList', 'checkbox' => true, 'hide' => self::hideBbpOptions()));
      add_settings_field('bpUseCodes', __("Allow using predefined Ads Place HTML codes (before and after codes)", SAM_DOMAIN), array(&$this, 'drawCheckboxOption'), 'sam-settings', 'sam_single_section', array('label_for' => 'bpUseCodes', 'checkbox' => true));
      add_settings_field('mpAdsType', __('Ad Object in the middle of content', SAM_DOMAIN), array(&$this, 'drawCascadeSelectOption'), 'sam-settings', 'sam_single_section', array('group' => array('slave' => 'mpAdsId', 'master' => true, 'title' => __('Type of Ad Object', SAM_DOMAIN).':')));
      add_settings_field('mpAdsId', __("Ads Place in the middle of content", SAM_DOMAIN), array(&$this, 'drawCascadeSelectOption'), 'sam-settings', 'sam_single_section', array('description' => '', 'group' => array('slave' => null, 'master' => false, 'title' => __('Ad Object', SAM_DOMAIN).':')));
      add_settings_field('middlePost', __("Allow Ads Place auto inserting into the middle of post/page content", SAM_DOMAIN), array(&$this, 'drawCheckboxOption'), 'sam-settings', 'sam_single_section', array('label_for' => 'middlePost', 'checkbox' => true));
      add_settings_field('bbpMiddlePost', __("Allow Ads Place auto inserting into the middle of bbPress Forum topic content", SAM_DOMAIN), array(&$this, 'drawCheckboxOption'), 'sam-settings', 'sam_single_section', array('label_for' => 'bbpMiddlePost', 'checkbox' => true, 'hide' => self::hideBbpOptions()));
      add_settings_field('mpUseCodes', __("Allow using predefined Ads Place HTML codes (before and after codes)", SAM_DOMAIN), array(&$this, 'drawCheckboxOption'), 'sam-settings', 'sam_single_section', array('label_for' => 'mpUseCodes', 'checkbox' => true));
      add_settings_field('apAdsType', __('Ad Object after content', SAM_DOMAIN), array(&$this, 'drawCascadeSelectOption'), 'sam-settings', 'sam_single_section', array('group' => array('slave' => 'apAdsId', 'master' => true, 'title' => __('Type of Ad Object', SAM_DOMAIN).':')));
      add_settings_field('apAdsId', __("Ads Place after content", SAM_DOMAIN), array(&$this, 'drawCascadeSelectOption'), 'sam-settings', 'sam_single_section', array('description' => '', 'group' => array('slave' => null, 'master' => false, 'title' => __('Ad Object', SAM_DOMAIN).':')));
      add_settings_field('afterPost', __("Allow Ads Place auto inserting after post/page content", SAM_DOMAIN), array(&$this, 'drawCheckboxOption'), 'sam-settings', 'sam_single_section', array('label_for' => 'afterPost', 'checkbox' => true));
      add_settings_field('bbpAfterPost', __("Allow Ads Place auto inserting after bbPress Forum topic content", SAM_DOMAIN), array(&$this, 'drawCheckboxOption'), 'sam-settings', 'sam_single_section', array('label_for' => 'bbpAfterPost', 'checkbox' => true, 'hide' => self::hideBbpOptions()));
      add_settings_field('apUseCodes', __("Allow using predefined Ads Place HTML codes (before and after codes)", SAM_DOMAIN), array(&$this, 'drawCheckboxOption'), 'sam-settings', 'sam_single_section', array('label_for' => 'apUseCodes', 'checkbox' => true));

      add_settings_field('useSWF', __('I use (plan to use) my own flash (SWF) banners. In other words, allow loading the script "SWFObject" on the pages of the blog.', SAM_DOMAIN), array(&$this, 'drawCheckboxOption'), 'sam-settings', 'sam_ext_section', array('label_for' => 'useSWF', 'checkbox' => true));
      add_settings_field('errorlog', __('Turn on/off the error log.', SAM_DOMAIN), array(&$this, 'drawCheckboxOption'), 'sam-settings', 'sam_ext_section', array('label_for' => 'errorlog', 'checkbox' => true));
      add_settings_field('errorlogFS', __('Turn on/off the error log for Face Side.', SAM_DOMAIN), array(&$this, 'drawCheckboxOption'), 'sam-settings', 'sam_ext_section', array('label_for' => 'errorlogFS', 'checkbox' => true));

      add_settings_field('useDFP', __("Allow using Google DoubleClick for Publishers (DFP) rotator codes", SAM_DOMAIN), array(&$this, 'drawCheckboxOption'), 'sam-settings', 'sam_dfp_section', array('label_for' => 'useDFP', 'checkbox' => true));
      add_settings_field('dfpMode', __('Google DFP Mode', SAM_DOMAIN), array(&$this, 'drawRadioOption'), 'sam-settings', 'sam_dfp_section', array('options' => array('gam' => __('GAM (Google Ad Manager)', SAM_DOMAIN), 'gpt' => __('GPT (Google Publisher Tag)', SAM_DOMAIN)), 'description' => __('Select DFP Tags Mode.', SAM_DOMAIN)));
      add_settings_field('dfpPub', __("Google DFP Pub Code", SAM_DOMAIN), array(&$this, 'drawTextOption'), 'sam-settings', 'sam_dfp_section', array('description' => __('Your Google DFP Pub code. i.e:', SAM_DOMAIN).' ca-pub-0000000000000000.', 'width' => '200px'));
      add_settings_field('dfpNetworkCode', __('Google DFP Network Code', SAM_DOMAIN), array(&$this, 'drawTextOption'), 'sam-settings', 'sam_dfp_section', array('description' => __('Network Code of Your DFP Ad Network.', SAM_DOMAIN), 'width' => '200px'));

      add_settings_field('detectBots', __("Allow Bots and Crawlers detection", SAM_DOMAIN), array(&$this, 'drawCheckboxOption'), 'sam-settings', 'sam_statistic_section', array('label_for' => 'detectBots', 'checkbox' => true));
      add_settings_field('detectingMode', __("Accuracy of Bots and Crawlers Detection", SAM_DOMAIN), array(&$this, 'drawRadioOption'), 'sam-settings', 'sam_statistic_section', array('description' => __("If bot is detected hits of ads won't be counted. Use with caution! More exact detection requires more server resources.", SAM_DOMAIN), 'options' => array( 'inexact' => __('Inexact detection', SAM_DOMAIN), 'exact' => __('Exact detection', SAM_DOMAIN), 'more' => __('More exact detection', SAM_DOMAIN))));
      add_settings_field('stats', __('Allow to collect and to store statistical data', SAM_DOMAIN), array(&$this, 'drawCheckboxOption'), 'sam-settings', 'sam_statistic_section', array('label_for' => 'stats', 'checkbox' => true));
			add_settings_field('keepStats', __('Keep Statistical Data', SAM_DOMAIN), array(&$this, 'drawSelectOption'), 'sam-settings', 'sam_statistic_section', array('description' => __('Period of keeping statistical data (excluding current month).', SAM_DOMAIN), 'options' => array(0 => __('All Time', SAM_DOMAIN), 1 => __('One Month', SAM_DOMAIN), 3 => __('Three Months', SAM_DOMAIN), 6 => __('Six Months', SAM_DOMAIN), 12 => __('One Year'))));
      add_settings_field('currency', __("Display of Currency", SAM_DOMAIN), array(&$this, 'drawRadioOption'), 'sam-settings', 'sam_statistic_section', array('description' => __("Define display of currency. Auto - auto detection of currency from blog settings. USD, EUR - Forcing the display of currency to U.S. dollars or Euro.", SAM_DOMAIN), 'options' => array( 'auto' => __('Auto', SAM_DOMAIN), 'usd' => __('USD', SAM_DOMAIN), 'euro' => __('EUR', SAM_DOMAIN))));

      add_settings_field('editorButtonMode', __("TinyMCE Editor Button Mode", SAM_DOMAIN), array(&$this, 'drawRadioOption'), 'sam-settings', 'sam_layout_section', array('description' => __('If you do not want to use the modern dropdown button in your TinyMCE editor, or use of this button causes a problem, you can use classic TinyMCE buttons. In this case select "Classic TinyMCE Buttons".', SAM_DOMAIN), 'options' => array('modern' => __('Modern TinyMCE Button', SAM_DOMAIN), 'classic' => __('Classic TinyMCE Buttons', SAM_DOMAIN))));
      add_settings_field('placesPerPage', __("Ads Places per Page", SAM_DOMAIN), array(&$this, 'drawTextOption'), 'sam-settings', 'sam_layout_section', array('description' => __('Ads Places Management grid pagination. How many Ads Places will be shown on one page of grid.', SAM_DOMAIN)));
			add_settings_field('itemsPerPage', __("Ads per Page", SAM_DOMAIN), array(&$this, 'drawTextOption'), 'sam-settings', 'sam_layout_section', array('description' => __('Ads of Ads Place Management grid pagination. How many Ads will be shown on one page of grid.', SAM_DOMAIN)));

      add_settings_field('deleteOptions', __("Delete plugin options during deactivating plugin", SAM_DOMAIN), array(&$this, 'drawCheckboxOption'), 'sam-settings', 'sam_deactivate_section', array('label_for' => 'deleteOptions', 'checkbox' => true));
			add_settings_field('deleteDB', __("Delete database tables of plugin during deactivating plugin", SAM_DOMAIN), array(&$this, 'drawCheckboxOption'), 'sam-settings', 'sam_deactivate_section', array('label_for' => 'deleteDB', 'checkbox' => true));
      add_settings_field('deleteFolder', __("Delete custom images folder of plugin during deactivating plugin", SAM_DOMAIN), array(&$this, 'drawCheckboxOption'), 'sam-settings', 'sam_deactivate_section', array('label_for' => 'deleteFolder', 'checkbox' => true));

      add_settings_field('mailer', __('Allow SAM Mailing System to send statistical data to advertisers', SAM_DOMAIN), array(&$this, 'drawCheckboxOption'), 'sam-settings', 'sam_mailer_section', array('label_for' => 'mailer', 'checkbox' => true));
      add_settings_field('mail_period', __('Periodicity of sending reports', SAM_DOMAIN), array(&$this, 'drawRadioOption'), 'sam-settings', 'sam_mailer_section', array('options' => array('monthly' => __('Monthly', SAM_DOMAIN), 'weekly' => __('Weekly', SAM_DOMAIN))));

      add_settings_field('mail_hits', __('Ad Hits (Number of shows of the advertisement)', SAM_DOMAIN), array(&$this, 'drawCheckboxOption'), 'sam-settings', 'sam_mailer_data_section', array('label_for' => 'mail_hits', 'checkbox' => true));
      add_settings_field('mail_clicks', __('Ad Clicks (Number of clicks on the advertisement)', SAM_DOMAIN), array(&$this, 'drawCheckboxOption'), 'sam-settings', 'sam_mailer_data_section', array('label_for' => 'mail_clicks', 'checkbox' => true));
      add_settings_field('mail_cpm', __('CPM (Cost per thousand hits, calculated value)', SAM_DOMAIN), array(&$this, 'drawCheckboxOption'), 'sam-settings', 'sam_mailer_data_section', array('label_for' => 'mail_cpm', 'checkbox' => true));
      add_settings_field('mail_cpc', __('CPC (Cost per click)', SAM_DOMAIN), array(&$this, 'drawCheckboxOption'), 'sam-settings', 'sam_mailer_data_section', array('label_for' => 'mail_cpc', 'checkbox' => true));
      add_settings_field('mail_ctr', __('CTR (Click through rate, calculated value)', SAM_DOMAIN), array(&$this, 'drawCheckboxOption'), 'sam-settings', 'sam_mailer_data_section', array('label_for' => 'mail_ctr', 'checkbox' => true));

      add_settings_field('mail_subject', __('Mail Subject', SAM_DOMAIN), array(&$this, 'drawTextOption'), 'sam-settings', 'sam_mailer_content_section', array('description' => __('Mail subject of sending email.', SAM_DOMAIN), 'width' => '70%'));
      add_settings_field('mail_greeting', __('Mail Greeting String', SAM_DOMAIN), array(&$this, 'drawTextOption'), 'sam-settings', 'sam_mailer_content_section', array('description' => __('Greeting string of sending email.', SAM_DOMAIN).' '.$scStr, 'width' => '70%'));
      add_settings_field('mail_text_before', __('Mail Text before statistical data table', SAM_DOMAIN), array(&$this, 'drawTextareaOption'), 'sam-settings', 'sam_mailer_content_section', array('description' => __('Some text before statistical data table of sending email.', SAM_DOMAIN).' '.$scStr, 'height' => '75px'));
      add_settings_field('mail_text_after', __('Mail Text after statistical data table', SAM_DOMAIN), array(&$this, 'drawTextareaOption'), 'sam-settings', 'sam_mailer_content_section', array('description' => __('Some text after statistical data table of sending email.', SAM_DOMAIN).' '.$scStr, 'height' => '75px'));
      add_settings_field('mail_warning', __('Mail Warning 1', SAM_DOMAIN), array(&$this, 'drawTextareaOption'), 'sam-settings', 'sam_mailer_content_section', array('description' => __('This text will be placed at the end of sending email.', SAM_DOMAIN).' '.$scStr, 'height' => '50px'));
      add_settings_field('mail_message', __('Mail Warning 2', SAM_DOMAIN), array(&$this, 'drawTextareaOption'), 'sam-settings', 'sam_mailer_content_section', array('description' => __('This text will be placed at the very end of sending email.', SAM_DOMAIN).' '.$scStr, 'height' => '50px'));

      add_settings_field('mail_preview', __('Mail Preview', SAM_DOMAIN).':', array(&$this, 'drawPreviewMail'), 'sam-settings', 'sam_mailer_preview_section', array('user' => $current_user->display_name));

      register_setting('sam-settings', SAM_OPTIONS_NAME, array(&$this, 'sanitizeSettings'));
		}

    public function regAdminPage() {
			global $wp_version;

      $menuPage = add_object_page(__('Ads', SAM_DOMAIN), __('Ads', SAM_DOMAIN), SAM_ACCESS, 'sam-list', array(&$this, 'samTablePage'), WP_PLUGIN_URL.'/simple-ads-manager/images/sam-icon.png');
			$this->listPage = add_submenu_page('sam-list', __('Ads List', SAM_DOMAIN), __('Ads Places', SAM_DOMAIN), SAM_ACCESS, 'sam-list', array(&$this, 'samTablePage'));
			$this->editPage = add_submenu_page('sam-list', __('Ad Editor', SAM_DOMAIN), __('New Place', SAM_DOMAIN), SAM_ACCESS, 'sam-edit', array(&$this, 'samEditPage'));
      $this->listZone = add_submenu_page('sam-list', __('Ads Zones List', SAM_DOMAIN), __('Ads Zones', SAM_DOMAIN), SAM_ACCESS, 'sam-zone-list', array(&$this, 'samZoneListPage'));
      $this->editZone = add_submenu_page('sam-list', __('Ads Zone Editor', SAM_DOMAIN), __('New Zone', SAM_DOMAIN), SAM_ACCESS, 'sam-zone-edit', array(&$this, 'samZoneEditPage'));
      $this->listBlock = add_submenu_page('sam-list', __('Ads Blocks List', SAM_DOMAIN), __('Ads Blocks', SAM_DOMAIN), SAM_ACCESS, 'sam-block-list', array(&$this, 'samBlockListPage'));
      $this->editBlock = add_submenu_page('sam-list', __('Ads Block Editor', SAM_DOMAIN), __('New Block', SAM_DOMAIN), SAM_ACCESS, 'sam-block-edit', array(&$this, 'samBlockEditPage'));
      $this->settingsPage = add_submenu_page('sam-list', __('Simple Ads Manager Settings', SAM_DOMAIN), __('Settings', SAM_DOMAIN), 'manage_options', 'sam-settings', array(&$this, 'samAdminPage'));
      $this->eLogPage = add_submenu_page('sam-list', __('Simple Ads Manager Error Log', SAM_DOMAIN), __('Error Log', SAM_DOMAIN), SAM_ACCESS, 'sam-errors', array(&$this, 'samErrorLog'));

      add_action('admin_enqueue_scripts', array(&$this, 'loadScripts'));

      if(version_compare($wp_version, '3.3', '>=')) {
        add_action('load-'.$this->listPage, array(&$this, 'samHelp'));
        add_action('load-'.$this->editPage, array(&$this, 'samHelp'));
        add_action('load-'.$this->settingsPage, array(&$this, 'samHelp'));
      }
		}

    public function samHelp() {
      $samScreens = array($this->listPage, $this->editPage, $this->listZone, $this->editZone, $this->listBlock, $this->editBlock, $this->settingsPage);
      $samPages = array(
        'listPage' => $this->listPage,
        'editPage' => $this->editPage,
        'listZone' => $this->listZone,
        'editZone' => $this->editZone,
        'listBlock' => $this->listBlock,
        'editBlock' => $this->editBlock,
        'settingsPage' => $this->settingsPage
      );

      include_once('help.class.php');
      $help = new SAMHelp33(array('screens' => $samScreens, 'pages' => $samPages));
      $help->help();
    }

    public function help($contextualHelp, $screenId, $screen) {
      include_once('help.class.php');

      $help = new SAMHelp(array(
        'editPage' => $this->editPage,
        'listPage' => $this->listPage,
        'settingsPage' => $this->settingsPage
      ));

      return $help->help($contextualHelp, $screenId, $screen);
    }

    public function loadScripts($hook) {
      global $wp_version;
      $jqCSS = (version_compare($wp_version, '3.8-RC1', '<')) ? SAM_URL.'css/jquery-ui-sam.css' : SAM_URL.'css/jquery-ui-wp38.css';

      $sambImage = SAM_URL . 'js/img/sam-icon.png';
      echo
      "<style>\n.mce-ico.mce-i-samb {\n  background-image: url($sambImage);\n}\n</style>\n";

      if($hook == $this->settingsPage) {
        wp_enqueue_style('adminSettingsLayout', SAM_URL.'css/sam-settings.css', false, SAM_VERSION);
        wp_enqueue_style('jSlider', SAM_URL.'css/jslider.css', false, '1.1.0');
        wp_enqueue_style('jSlider-plastic', SAM_URL.'css/jslider.round.plastic.css', false, '1.1.0');
        wp_enqueue_style('colorButtons', SAM_URL.'css/color-buttons.css', false, SAM_VERSION);
        wp_enqueue_style('jquery-ui-css', $jqCSS, false, '1.10.3');
        wp_enqueue_style('ej-all', SAM_URL.'css/ej/ej.web.all.min.css');
        wp_enqueue_style('ej-theme', SAM_URL.'css/ej/ej.theme.min.css');
        wp_enqueue_style('ej-widgets-core', SAM_URL.'css/ej.widgets.core.min.css');
        wp_enqueue_style('ej-widgets', SAM_URL.'css/ej/ej.widgets.all.min.css');

        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-effects-core');
        wp_enqueue_script('jquery-effects-blind');
        wp_enqueue_script('jquery-ui-widget');
        wp_enqueue_script('jquery-ui-tabs');
        wp_enqueue_script('hash-table', SAM_URL.'js/slider/jshashtable-2.1_src.js', array('jquery'), '2.1');
        wp_enqueue_script('number-formatter', SAM_URL.'js/slider/jquery.numberformatter-1.2.3.js', array('jquery'), '1.2.3');
        wp_enqueue_script('templates', SAM_URL.'js/slider/tmpl.js', array('jquery'));
        wp_enqueue_script('depend-class', SAM_URL.'js/slider/jquery.dependClass-0.1.js', array('jquery'), '0.1');
        wp_enqueue_script('draggable', SAM_URL.'js/slider/draggable-0.1.js', array('jquery'), '0.1');
        wp_enqueue_script('jSlider', SAM_URL.'js/slider/jquery.slider.js', array('jquery', 'draggable'), '1.1.0');

        wp_enqueue_script('jq-easing', SAM_URL.'js/jquery.easing.1.3.js', array('jquery'), '1.3');
        wp_enqueue_script('ej-core', SAM_URL.'js/ej/ej.core.min.js', array('jquery'), '12.2.0.36');
        wp_enqueue_script('ej-data', SAM_URL.'js/ej/ej.data.min.js', array('jquery', 'ej-core'), '12.2.0.36');
        wp_enqueue_script('ej-dropdown', SAM_URL.'js/ej/ej.dropdownlist.min.js', array('jquery', 'ej-core', 'ej-data'), '12.2.0.36');
        wp_enqueue_script('ej-checkbox', SAM_URL.'js/ej/ej.checkbox.min.js', array('jquery', 'ej-core', 'ej-data'), '12.2.0.36');
        wp_enqueue_script('ej-scroller', SAM_URL.'js/ej/ej.scroller.min.js', array('jquery', 'ej-core', 'ej-data'), '12.2.0.36');

        wp_enqueue_script('sam-settings', SAM_URL.'js/sam-settings.min.js', array('jquery', 'draggable'), SAM_VERSION);
        wp_localize_script('sam-settings', 'options', array(
          'roles' => array(
            __('Super Admin', SAM_DOMAIN),
            __('Administrator', SAM_DOMAIN),
            __('Editor', SAM_DOMAIN),
            __('Author', SAM_DOMAIN),
            __('Contributor', SAM_DOMAIN)
          ),
          'values' => array('manage_network', 'manage_options', 'edit_others_posts', 'publish_posts', 'edit_posts'),
          'adTypes' => array(
            array('parentId' => 0, 'value' => 0, 'text' => __('Single Ad', SAM_DOMAIN)),
            array('parentId' => 1, 'value' => 1, 'text' => __('Ads Place', SAM_DOMAIN)),
            array('parentId' => 2, 'value' => 2, 'text' => __('Ads Zone', SAM_DOMAIN)),
            array('parentId' => 3, 'value' => 3, 'text' => __('Ads Block', SAM_DOMAIN))
          ),
          'adObjects' => self::getAdsDataX()
        ));
      }
      elseif($hook == $this->listPage || $hook == $this->listZone || $hook == $this->listBlock) {
        wp_enqueue_style('adminListLayout', SAM_URL.'css/sam-admin-list.css', false, SAM_VERSION);
        wp_enqueue_style('jquery-ui-css', $jqCSS, false, '1.10.3');
      }
      elseif($hook == $this->editPage) {
        $mode = (isset($_GET['mode'])) ? $_GET['mode'] : 'place';
        $pointers = self::getPointerOptions();
        if($mode == 'place') {
          wp_enqueue_style('adminEditLayout', SAM_URL.'css/sam-admin-edit.css', false, SAM_VERSION);
          wp_enqueue_style('jquery-ui-css', $jqCSS, false, '1.10.3');
          wp_enqueue_style('wp-pointer');
          wp_enqueue_style('colorButtons', SAM_URL.'css/color-buttons.css', false, SAM_VERSION);
          wp_enqueue_style('W2UI', SAM_URL . 'css/w2ui.min.css', false, '1.3');
          wp_enqueue_style('jqPlot', SAM_URL . 'css/jquery.jqplot.min.css', false, '1.0.2');

          if($this->cmsVer === 'low') {
            wp_register_script('jquery-effects-core', SAM_URL.'js/jquery.effects.core.min.js', array('jquery'), '1.8.16');
            wp_register_script('jquery-effects-blind', SAM_URL.'js/jquery.effects.blind.min.js', array('jquery', 'jquery-effects-core'), '1.8.16');
          }

          wp_enqueue_script('jquery');
          wp_enqueue_media( array('post' => null) );
          wp_enqueue_script('jquery-ui-core');
          wp_enqueue_script('jquery-effects-core');
          wp_enqueue_script('jquery-ui-widget');
          wp_enqueue_script('jquery-ui-sortable');
          wp_enqueue_script('jquery-ui-position');
          wp_enqueue_script('jquery-ui-tabs');
          wp_enqueue_script('jquery-effects-blind');
          wp_enqueue_script('jquery-ui-tooltip');
          wp_enqueue_script('plupload-all');
          wp_enqueue_script('W2UI', SAM_URL . 'js/w2ui.min.js', array('jquery'), '1.3');

          wp_enqueue_script('jqPlot', SAM_URL . 'js/jquery.jqplot.min.js', array('jquery'), '1.0.2');
          wp_enqueue_script('barRenderer', SAM_URL . 'js/jqplot.barRenderer.min.js', array('jquery', 'jqPlot'), '1.0.2');
          wp_enqueue_script('highlighter', SAM_URL . 'js/jqplot.highlighter.min.js', array('jquery', 'jqPlot'), '1.0.2');
          wp_enqueue_script('cursor', SAM_URL . 'js/jqplot.cursor.min.js', array('jquery', 'jqPlot'), '1.0.2');
          wp_enqueue_script('pointLabels', SAM_URL . 'js/jqplot.pointLabels.min.js', array('jquery', 'jqPlot'), '1.0.2');

          wp_enqueue_script('wp-pointer');
          wp_enqueue_script('adminEditScript', SAM_URL.'js/sam-admin-edit-place.min.js', array('jquery', 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-position'), SAM_VERSION);
          wp_localize_script('adminEditScript', 'samEditorOptions', array(
            'places' => array('enabled' => $pointers['places'], 'title' => __('Name of Ads Place', SAM_DOMAIN), 'content' => __('This is not required parameter. But it is strongly recommended to define it if you plan to use Ads Blocks, plugin\'s widgets or autoinserting of ads.', SAM_DOMAIN)),
            'ads' => array('enabled' => $pointers['ads'], 'title' => __('Name of Ad', SAM_DOMAIN), 'content' => __('This is not required parameter. But it is strongly recommended to define it if you plan to use Ads Blocks or plugin\'s widgets.', SAM_DOMAIN)),
            'media' => array('title' => __('Select Banner Image', SAM_DOMAIN), 'button' => __('Select', SAM_DOMAIN)),

            'samStatsUrl' => SAM_URL . 'sam-ajax-admin-stats.php',
            'options' => array(
              'uploading' => __('Uploading', SAM_DOMAIN).' ...',
              'uploaded' => __('Uploaded.', SAM_DOMAIN),
              'status' => __('Only JPG, PNG or GIF files are allowed', SAM_DOMAIN),
              'file' => __('File', SAM_DOMAIN),
              'path' => SAM_AD_IMG,
              'url' => SAM_AD_URL,
              'ajaxurl' => SAM_URL . 'sam-ajax-admin.php'
            ),
            'labels' => array('hits' => __('Hits', SAM_DOMAIN), 'clicks' => __('Clicks', SAM_DOMAIN)),
            'columns' => array(
              array('field' => 'id', 'caption' => 'ID', 'size' => '40px', 'render' => 'int'),
              array('field' => 'name', 'caption' => __("Name", SAM_DOMAIN), 'size' => '40%'),
              array('field' => 'ad_hits', 'caption' => __("Hits", SAM_DOMAIN), 'size' => '10%', 'render' => 'int'),
              array('field' => 'ad_clicks', 'caption' => __('Clicks', SAM_DOMAIN), 'size' => '10%', 'render' => 'int'),
              array('field' => 'e_cpm', 'caption' => 'CPM', 'size' => '10%', 'render' => 'float:2'),
              array('field' => 'e_cpc', 'caption' => 'CPC', 'size' => '10%', 'render' => 'float:2'),
              array('field' => 'e_ctr', 'caption' => 'CTR', 'size' => '10%', 'render' => 'percent')
            ),
          ));
        }
        if($mode == 'item') {
          wp_enqueue_style('adminEditLayout', SAM_URL.'css/sam-admin-edit.css', false, SAM_VERSION);
          wp_enqueue_style('jquery-ui-css', $jqCSS, false, '1.10.3');
          wp_enqueue_style('ComboGrid', SAM_URL.'css/jquery.ui.combogrid.css', false, '1.6.3');
          wp_enqueue_style('wp-pointer');
          wp_enqueue_style('colorButtons', SAM_URL.'css/color-buttons.css', false, SAM_VERSION);
          wp_enqueue_style('W2UI', SAM_URL . 'css/w2ui.min.css', false, '1.4.1');
          wp_enqueue_style('jqPlot', SAM_URL . 'css/jquery.jqplot.min.css', false, '1.0.2');

          $options = parent::getSettings();
          $loc = get_locale();
          if(in_array($loc, array('en_GB', 'fr_CH', 'pt_BR', 'sr_SR', 'zh_CN', 'zh_HK', 'zh_TW')))
            $lc = str_replace('_', '-', $loc);
          else $lc = substr($loc, 0, 2);

          if($this->cmsVer === 'low') {
            wp_register_script('jquery-effects-core', SAM_URL.'js/jquery.effects.core.min.js', array('jquery'), '1.8.16');
            wp_register_script('jquery-effects-blind', SAM_URL.'js/jquery.effects.blind.min.js', array('jquery', 'jquery-effects-core'), '1.8.16');
          }

          if($options['useSWF']) wp_enqueue_script('swfobject');
          wp_enqueue_script('jquery');
          wp_enqueue_media(array('post' => null));
          wp_enqueue_script('W2UI', SAM_URL . 'js/w2ui.min.js', array('jquery'), '1.4.1');
          wp_enqueue_script('jquery-ui-core');
          wp_enqueue_script('jquery-effects-core');
          //wp_enqueue_script('jquery-ui-mouse');
          wp_enqueue_script('jquery-ui-widget');
          wp_enqueue_script('jquery-ui-sortable');
          wp_enqueue_script('jquery-ui-position');
          //wp_enqueue_script('jquery-ui-autocomplete');
          wp_enqueue_script('jquery-ui-tabs');
          wp_enqueue_script('jquery-effects-blind');
          wp_enqueue_script('jquery-ui-datepicker');
          wp_enqueue_script('jquery-ui-tooltip');
          if(file_exists(SAM_PATH.'/js/i18n/jquery.ui.datepicker-'.$lc.'.js'))
            wp_enqueue_script('jquery-ui-locale', SAM_URL.'js/i18n/jquery.ui.datepicker-'.$lc.'.js', array('jquery'), '1.8.9');
          wp_enqueue_script('plupload-all');

          //wp_enqueue_script('cg-props', SAM_URL.'js/jquery.i18n.properties-1.0.9.js', array('jquery', 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-position'), '1.0.9');
          wp_enqueue_script('ComboGrid', SAM_URL.'js/jquery.ui.combogrid-1.6.3.js', array('jquery', 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-position'/*, 'cg-props'*/), '1.6.3');

          wp_enqueue_script('jqPlot', SAM_URL . 'js/jquery.jqplot.min.js', array('jquery'), '1.0.8');
          wp_enqueue_script('barRenderer', SAM_URL . 'js/jqplot.barRenderer.min.js', array('jquery', 'jqPlot'), '1.0.8');
          wp_enqueue_script('highlighter', SAM_URL . 'js/jqplot.highlighter.min.js', array('jquery', 'jqPlot'), '1.0.8');
          wp_enqueue_script('cursor', SAM_URL . 'js/jqplot.cursor.min.js', array('jquery', 'jqPlot'), '1.0.8');
          wp_enqueue_script('pointLabels', SAM_URL . 'js/jqplot.pointLabels.min.js', array('jquery', 'jqPlot'), '1.0.8');

          wp_enqueue_script('wp-pointer');
          wp_enqueue_script('adminEditScript', SAM_URL.'js/sam-admin-edit-item.min.js', array('jquery', 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-position'), SAM_VERSION);
          wp_localize_script('adminEditScript', 'samEditorOptions', array(
            'places' => array('enabled' => $pointers['places'], 'title' => __('Name of Ads Place', SAM_DOMAIN), 'content' => __('This is not required parameter. But it is strongly recommended to define it if you plan to use Ads Blocks, plugin\'s widgets or autoinserting of ads.', SAM_DOMAIN)),
            'ads' => array('enabled' => $pointers['ads'], 'title' => __('Name of Ad', SAM_DOMAIN), 'content' => __('This is not required parameter. But it is strongly recommended to define it if you plan to use Ads Blocks or plugin\'s widgets.', SAM_DOMAIN)),
            'media' => array('title' => __('Select Banner Image', SAM_DOMAIN), 'button' => __('Select', SAM_DOMAIN)),
            'samAjaxUrl' => SAM_URL . 'sam-ajax-admin.php',
            'samStatsUrl' => SAM_URL . 'sam-ajax-admin-stats.php',
            'models' => self::getColumnsModels(),
	          'searches' => self::getSearchesModel(),
            'data' => self::getGridsData(),
            'strings' => array(
              'uploading' => __('Uploading', SAM_DOMAIN).' ...',
              'uploaded' => __('Uploaded.', SAM_DOMAIN),
              'status' => __('Only JPG, PNG or GIF files are allowed', SAM_DOMAIN),
              'file' => __('File', SAM_DOMAIN),
              'path' => SAM_AD_IMG,
              'url' => SAM_AD_URL,
              'posts' => __('Post', SAM_DOMAIN),
              'page' => __('Page', SAM_DOMAIN),
              'subscriber' => __('Subscriber', SAM_DOMAIN),
              'contributor' => __('Contributor', SAM_DOMAIN),
              'author' => __('Author', SAM_DOMAIN),
              'editor' => __('Editor', SAM_DOMAIN),
              'admin' => __('Administrator', SAM_DOMAIN),
              'superAdmin' => __('Super Admin', SAM_DOMAIN),
              'labels' => array('hits' => __('Hits', SAM_DOMAIN), 'clicks' => __('Clicks', SAM_DOMAIN))
            ),
          ));
        }
      }
      elseif($hook == $this->editZone || $hook == $this->editBlock) {
        $pointers = self::getPointerOptions();

        wp_enqueue_style('adminEditLayout', SAM_URL.'css/sam-admin-edit.css', false, SAM_VERSION);
        wp_enqueue_style('jquery-ui-css', $jqCSS, false, '1.10.3');
        //wp_enqueue_style('ComboGrid', SAM_URL.'css/jquery.ui.combogrid.css', false, '1.6.2');
        wp_enqueue_style('wp-pointer');
        wp_enqueue_style('colorButtons', SAM_URL.'css/color-buttons.css', false, SAM_VERSION);
        //wp_enqueue_style('W2UI', SAM_URL . 'css/w2ui.min.css', false, '1.3');

        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-effects-core');
        wp_enqueue_script('jquery-ui-widget');
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('jquery-ui-position');
        wp_enqueue_script('jquery-effects-blind');
        wp_enqueue_script('jquery-ui-tooltip');

        wp_enqueue_script('wp-pointer');
        wp_localize_script('wp-pointer', 'samPointer', array(
          'zones' => array('enabled' => $pointers['zones'], 'title' => __('Name of Ads Zone', SAM_DOMAIN), 'content' => __('This is not required parameter. But it is strongly recommended to define it if you plan to use Ads Blocks or plugin\'s widgets.', SAM_DOMAIN)),
          'blocks' => array('enabled' => $pointers['blocks'], 'title' => __('Name of Ads Block', SAM_DOMAIN), 'content' => __('This is not required parameter. But it is strongly recommended to define it if you plan to use plugin\'s widgets.', SAM_DOMAIN))
        ));
        wp_enqueue_script('adminEditScript', SAM_URL.'js/sam-admin-edit-zb.min.js', array('jquery', 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-position'), SAM_VERSION);
      }
      elseif($hook == $this->eLogPage) {
        wp_enqueue_style('adminListLayout', SAM_URL.'css/sam-admin-list.css', false, SAM_VERSION);
        wp_enqueue_style('jquery-ui-css', $jqCSS, false, '1.10.3');
        wp_enqueue_style('W2UI', SAM_URL . 'css/w2ui.min.css', false, '1.3');

        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-widget');
        wp_enqueue_script('jquery-ui-button');
        wp_enqueue_script('jquery-ui-draggable');
        wp_enqueue_script('jquery-ui-mouse');
        wp_enqueue_script('jquery-ui-position');
        wp_enqueue_script('jquery-ui-resizable');
        wp_enqueue_script('jquery-ui-dialog');

        wp_enqueue_script('W2UI', SAM_URL . 'js/w2ui.min.js', array('jquery'), '1.3');
        wp_enqueue_script('errorsListScript', SAM_URL.'js/sam-errors-list.min.js', array('jquery', 'jquery-ui-core'), SAM_VERSION);
        wp_localize_script('errorsListScript', 'options', array(
          'id' => __('Error ID', SAM_DOMAIN),
          'date' => __('Error Date', SAM_DOMAIN),
          'table' => __('Table', SAM_DOMAIN),
          'msg' => __('Error Message', SAM_DOMAIN),
          'sql' => __('Error SQL', SAM_DOMAIN),
          'etype' => __('Type', SAM_DOMAIN),
          'close' => __('Close', SAM_DOMAIN),
          'title' => __('Error Info', SAM_DOMAIN),
          'imgURL' => SAM_IMG_URL,
          'alts' => array(__('Warning', SAM_DOMAIN), __('Ok', SAM_DOMAIN)),
          'warning' => __('Warning', SAM_DOMAIN),
          'update' => __('Update Error', SAM_DOMAIN),
          'output' => __('Output Error', SAM_DOMAIN),
          'ajaxurl' => SAM_URL . 'sam-ajax-admin.php'
        ));
      }
    }

    public function getCategories($valueType = 'array') {
      global $wpdb;
      $tTable = $wpdb->prefix . "terms";
      $ttTable = $wpdb->prefix . "term_taxonomy";

      $sql = "SELECT
                wt.term_id,
                wt.name,
                wtt.taxonomy
              FROM
                $tTable wt
              INNER JOIN $ttTable wtt
                ON wt.term_id = wtt.term_id
              WHERE
                wtt.taxonomy = 'category'";

      $cats = $wpdb->get_results($sql, ARRAY_A);
      if($valueType == 'array') $output = $cats;
      else {
        $output = '';
        foreach($cats as $cat) {
          if(!empty($output)) $output .= ',';
          $output .= "'".$cat['name']."'";
        }
      }
      return $output;
    }

    /*public function uploadHandler() {
      $uploaddir = SAM_AD_IMG;
      $file = $uploaddir . basename($_FILES['uploadfile']['name']);

      if ( move_uploaded_file( $_FILES['uploadfile']['tmp_name'], $file )) {
        exit("success");
      } else {
        exit("error");
      }
    }*/

    public function closePointerHandler() {
      $options = self::getPointerOptions();
      $charset = get_bloginfo('charset');
      @header("Content-Type: application/json; charset=$charset");
      if(isset($_REQUEST['pointer'])) {
        $pointer =  $_REQUEST['pointer'];
        $options[$pointer] = false;
        update_option('sam_pointers', $options);
        wp_send_json_success(array('pointer' => $pointer, 'options' => $options));
      }
      else wp_send_json_error();
    }

    public function settingsTabsHeader( $tabs ) {
      $out = "<ul>";

      foreach($tabs as $tab) {
        if(isset($tab['uri']) && isset($tab['name'])) {
          $tabUri = $tab['uri'];
          $tabName = $tab['name'];
          $out .= "<li><a href='#{$tabUri}'>{$tabName}</a></li>";
        }
      }

      $out .= "</ul>";

      return $out;
    }

		public function doSettingsSections($page, $tabs) {
      global $wp_settings_sections, $wp_settings_fields;

      if ( !isset($wp_settings_sections) || !isset($wp_settings_sections[$page]) )
        return;

      echo "<div id='tabs'>\n";
      echo self::settingsTabsHeader($tabs);

      foreach ( (array) $wp_settings_sections[$page] as $section ) {
        if(isset($this->settingsTabs[ $section['id'] ]['start_tab']) && $this->settingsTabs[ $section['id'] ]['start_tab'])
          echo "<div id='{$this->settingsTabs[ $section['id'] ]['uri']}'>";

        echo "<div class='ui-sortable sam-section'>\n";
        echo "<div class='postbox opened'>\n";
        echo "<h3>{$section['title']}</h3>\n";
        echo '<div class="inside">';
        call_user_func($section['callback'], $section);
        if ( !isset($wp_settings_fields) || !isset($wp_settings_fields[$page]) || !isset($wp_settings_fields[$page][$section['id']]) )
          continue;
        $this->doSettingsFields($page, $section['id']);
        echo '</div>';
        echo '</div>';
        echo '</div>';
        if(isset($this->settingsTabs[ $section['id'] ]['finish_tab']) && $this->settingsTabs[ $section['id'] ]['finish_tab']) echo "</div>";
      }
      echo "</div>";
    }

    public function doSettingsFields($page, $section) {
			global $wp_settings_fields;

			if ( !isset($wp_settings_fields) || !isset($wp_settings_fields[$page]) || !isset($wp_settings_fields[$page][$section]) )
				return;

			foreach ( (array) $wp_settings_fields[$page][$section] as $field ) {
				if ( !empty($field['args']['checkbox']) ) {
          echo '<p>';
				  call_user_func($field['callback'], $field['id'], $field['args']);
				  echo '<label for="' . $field['args']['label_for'] . '"' . ((isset($field['args']['hide']) && $field['args']['hide']) ? 'style="display: none;"' : '' ) . '>' . $field['title'] . '</label>';
          echo '</p>';
				}
				else {
          if(isset($field['args']['group'])) {
            if($field['args']['group']['master']) {
              echo "<p><strong>{$field['title']}</strong></p>";
              echo "<div class='group-frame'><div class='cascade-item'>";
              call_user_func($field['callback'], $field['id'], $field['args']);
              echo "</div>";
            }
            else {
              echo "<div class='cascade-item'>";
              call_user_func($field['callback'], $field['id'], $field['args']);
              echo "</div><div class='cascade-body'>&nbsp;</div></div>";
            }
          }
          else {
            echo '<p>';
            if ( !empty($field['args']['label_for']) )
					    echo '<label for="' . $field['args']['label_for'] . '">' . $field['title'] . '</label>';
				    else echo '<strong>' . $field['title'] . '</strong><br>';
            echo '</p>';
            echo '<p>';
				    call_user_func($field['callback'], $field['id'], $field['args']);
            echo '</p>';
          }
				}
        if(!empty($field['args']['description'])) echo '<p>' . $field['args']['description'] . '</p>';
        if(!empty($field['args']['warning'])) echo self::getWarningString($field['args']['warning']);
			}
		}

    public function setTransient( $options ) {
      if(false === ($mDate = get_transient( 'sam_maintenance_date' ))) {
        $date = new DateTime('now');
        if($options['mail_period'] == 'monthly') {
          $date->modify('+1 month');
          $nextDate = new DateTime($date->format('Y-m-01 02:00'));
          $diff = $nextDate->format('U') - $_SERVER['REQUEST_TIME'];
        }
        else {
          $dd = 8 - ((integer) $date->format('N'));
          $date->modify("+{$dd} day");
          $nextDate = new DateTime($date->format('Y-m-d 02:00'));
          $diff = (8 - ((integer) $date->format('N'))) * DAY_IN_SECONDS;
        }

        $format = get_option('date_format').' '.get_option('time_format');
        set_transient( 'sam_maintenance_date', $nextDate->format($format), $diff );
      }
    }

    public function sanitizeSettings($input) {
      global $wpdb;

      $pTable = $wpdb->prefix . "sam_places";
      $sql = "SELECT sp.patch_dfp, sp.place_size, sp.place_custom_width, sp.place_custom_height FROM $pTable sp WHERE sp.patch_source = 2";
      $rows = $wpdb->get_results($sql, ARRAY_A);
      $blocks = array();
      $blocks2 = array();
      $pub = explode('-', $input['dfpPub']);
      $divStr = (is_array($pub)) ? $pub[count($pub) - 1] : rand(1111111, 9999999);
      $div = "sam-dfp-{$divStr}";
      $k = 0;
      foreach($rows as $value) {
        array_push($blocks, $value['patch_dfp']);

        if($value['place_custom_width'] == 0) $sizes = explode('x', $value['place_size']);
        else $sizes = array($value['place_custom_width'], $value['place_custom_height']);
        array_push($blocks2, array('name' => $value['patch_dfp'], 'size' => $sizes, 'div' => $div.'-'.$k));

        $k++;
      }

      $intNames = array(
        'keepStats',
        'bpAdsType',
        'mpAdsType',
        'apAdsType'
      );

      foreach($intNames as $name)
        $output[$name] = (isset($input[$name])) ? (integer)$input[$name] : 0;

      $output = $input;
      $boolNames = array(
        'mailer',
        'detectBots',
        'deleteOptions',
        'deleteDB',
        'deleteFolder',
        'beforePost',
        'bpUseCodes',
        'bpExcerpt',
        'bbpBeforePost',
        'bbpList',
        'middlePost',
        'mpUseCodes',
        'bbpMiddlePost',
        'afterPost',
        'apUseCodes',
        'bbpAfterPost',
        'useDFP',
        'useSWF',
        'errorlog',
        'errorlogFS',
        'bbpActive',
        'bbpEnabled',
        'mail_hits',
        'mail_clicks',
        'mail_cpm',
        'mail_cpc',
        'mail_ctr',
        'mail_preview',
	      'stats'
      );
      foreach($boolNames as $name) {
        $output[$name] = ((isset($input[$name])) ? $input[$name] : 0);
      }
      //$output['keepStats'] = (integer)$input['keepStats'];
      $output['dfpBlocks'] = array_unique($blocks);
      $output['dfpBlocks2'] = array_unique($blocks2);
      return $output;
    }
    
    public function drawGeneralSection() {
      echo '<p>'.__('There are general options.', SAM_DOMAIN).'</p>';
    }
    
    public function drawSingleSection() {
      echo '<p>'.__('Single post/page auto inserting options. Use these parameters for allowing/defining Ads Places which will be automatically inserted before/after post/page content.', SAM_DOMAIN).'</p>';
    }

    public function drawExtSection() {
      echo '';
    }
    
    public function drawDFPSection() {
      echo '<p>'.__('Adjust parameters of your Google DFP account.', SAM_DOMAIN).'</p>';
    }
    
    public function drawStatisticsSection() {
      echo '<p>'.__('Adjust parameters of plugin statistics.', SAM_DOMAIN).'</p>';
    }
		
		public function drawLayoutSection() {
			echo '<p>'.__('This options define layout for Ads Managin Pages.', SAM_DOMAIN).'</p>';
		}
    
    public function drawDeactivateSection() {
			echo '<p>'.__('Are you allow to perform these actions during deactivating plugin?', SAM_DOMAIN).'</p>';
		}

    public function drawMailerSection() {
      $options = parent::getSettings();
      /*include_once('sam.tools.php');
      $test = new SamMailer($options);
      $mails = $test->sendMails();
      echo "Sending: {$mails} mails";*/

      if($options['mailer']) {
        self::setTransient($options);

        $time = get_transient( 'sam_maintenance_date' );
        echo "<p>".__("Next mailing is scheduled on", SAM_DOMAIN)." <code>{$time}</code>... "."</p>";
      }
      else echo "<p>".__("Adjust parameters of Mailing System.", SAM_DOMAIN)."</p>";
    }

    public function drawMailerDataSection() {
      $str = __('Adjust Reporting Data. Name and Description of the ad will be included to the reporting data in any case.', SAM_DOMAIN);
      echo "<p>{$str}</p>";
    }

    public function drawMailerContentSection() {
      $str = __('Adjust Mail Content.', SAM_DOMAIN);
      echo "<p>{$str}</p>";
    }

    public function drawPreviewSection() {
      return '';
    }
    
    public function drawTextOption( $id, $args ) {
      $settings = parent::getSettings();
      if(isset($args['width'])) $width = $args['width'];
      else $width = 55;
      ?>
        <input id="<?php echo $id; ?>"
					name="<?php echo SAM_OPTIONS_NAME.'['.$id.']'; ?>"
					type="text"
					value="<?php echo $settings[$id]; ?>"
          style="height: 22px; font-size: 11px; <?php echo "width: {$width};" ?>" />
      <?php
    }

    public function drawTextareaOption( $id, $args ) {
      $settings = parent::getSettings();
      if(isset($args['height'])) $height = $args['height'];
      else $height = '100px';
      ?>
      <textarea id="<?php echo $id; ?>"
        name="<?php echo SAM_OPTIONS_NAME.'['.$id.']'; ?>"
        style="width: 100%; height: <?php echo $height ?>;"><?php echo $settings[$id]; ?></textarea>
      <?php
    }

    public function drawCheckboxOption( $id, $args ) {
			$settings = parent::getSettings();
      $disabled = '';
      $hide = '';
      if(isset($args['enabled'])) $disabled = (($args['enabled']) ? '' : 'disabled');
      if(isset($args['hide'])) $hide = ( ($args['hide']) ? " style='display: none;'" : '');
			?>
				<input id="<?php echo $id; ?>"
					<?php checked('1', $settings[$id]); ?>
					name="<?php echo SAM_OPTIONS_NAME.'['.$id.']'; ?>"
					type="checkbox"
					value="1"
          <?php echo $disabled.$hide; ?>>
			<?php
		}

    public function drawSelectOption( $id, $args ) {
      $options = $args['options'];
      $settings = parent::getSettings();

      ?>
      <select id="<?php echo $id; ?>" name="<?php echo SAM_OPTIONS_NAME.'['.$id.']'; ?>">
        <?php
        foreach($options as $val=>$name)
          echo "<option value='$val' ".selected($val, $settings[$id], false).">$name</option>";
        ?>
      </select>
      <?php
    }
    
    public function drawSelectOptionX( $id, $args ) {
      global $wpdb;
      $pTable = $wpdb->prefix . "sam_places";
      
      $ids = $wpdb->get_results("SELECT $pTable.id, $pTable.name FROM $pTable WHERE $pTable.trash IS FALSE", ARRAY_A);
      $settings = parent::getSettings();
      ?>
        <select id="<?php echo $id; ?>" name="<?php echo SAM_OPTIONS_NAME.'['.$id.']'; ?>">
        <?php
          foreach($ids as $value) {
            echo "<option value='{$value['id']}' ".selected($value['id'], $settings[$id], false)." >{$value['name']}</option>";
          }
        ?>
        </select>
      <?php
    }

    public function drawCascadeSelectOption( $id, $args ) {
      $settings = parent::getSettings();
      ?>
      <input
        id="<?php echo $id; ?>"
        name="<?php echo SAM_OPTIONS_NAME.'['.$id.']'; ?>"
        type="text"
        value="<?php echo $settings[$id]; ?>">
      <?php
    }
    
    public function drawRadioOption( $id, $args ) {
      $options = $args['options'];
      $settings = parent::getSettings();
      
      foreach ($options as $key => $option) {
      ?>
        <input type="radio" 
          id="<?php echo $id.'_'.$key; ?>" 
          name="<?php echo SAM_OPTIONS_NAME.'['.$id.']'; ?>" 
          value="<?php echo $key; ?>" 
          <?php checked($key, $settings[$id]); ?> 
          <?php if($key == 'more') disabled('', ini_get("browscap")); ?> />
        <label for="<?php echo $id.'_'.$key; ?>"> 
          <?php echo $option;?>
        </label>&nbsp;&nbsp;&nbsp;&nbsp;        
      <?php
      }
    }

    public function drawJSliderOption( $id, $args ) {
      //$options = $args['options'];
      $values = $args['values'];
      $settings = parent::getSettings();
      $key = array_search($settings[$id], $values);
      if($key === false) $key = 1;

      ?>
      <input
        type="hidden"
        id="<?php echo $id; ?>"
        name="<?php echo SAM_OPTIONS_NAME.'['.$id.']'; ?>"
        value="<?php echo $settings[$id]; ?>" />
      <div class="layout">
        <div id="slider-div" class="layout-slider">
          <input id="role-slider" type="slider" name="area" value="0;<?php echo $key; ?>" style="display: none;" />
        </div>
      </div>
      <?php
    }

    public function drawPreviewMail( $id, $args ) {
      include_once('sam.tools.php');

      $mail = new SamMailer($this->samOptions);
      $prev = $mail->buildPreview($args['user']);

      echo "<div class='graph-container'>{$prev}</div>";
    }
		
		public function samAdminPage() {
      global $wpdb, $wp_version;
      
      $row = $wpdb->get_row('SELECT VERSION()AS ver', ARRAY_A);
      $sqlVersion = $row['ver'];
      $mem = ini_get('memory_limit');
      
      if(!is_dir(SAM_AD_IMG)) mkdir(SAM_AD_IMG);
      ?>
			<div class="wrap">
				<?php screen_icon("options-general"); ?>
				<h2><?php  _e('Simple Ads Manager Settings', SAM_DOMAIN); ?></h2>
				<?php
				/*$shell = $this->checkShell();
        if(!empty($shell)) echo $shell;*/
        include_once('errors.class.php');
        $errors = new samErrors();
        if(!empty($errors->errorString)) echo $errors->errorString;
        if(isset($_GET['settings-updated'])) $updated = $_GET['settings-updated'];
        elseif(isset($_GET['updated'])) $updated = $_GET['updated'];
        else $updated = false;
				if($updated === 'true') {
          parent::getSettings(true);
          $ccw = self::clearCache();
				  ?>
				  <div class="updated"><p><strong><?php echo __("Simple Ads Manager Settings Updated.", SAM_DOMAIN) .' '. $ccw ; ?></strong></p></div>
				<?php } else { ?>
				  <div class="clear"></div>
				<?php } ?>
				<form action="options.php" method="post">
          <div id='poststuff' class='metabox-holder has-right-sidebar'>
            <div id="side-info-column" class="inner-sidebar">
              <div class='postbox opened'>
                <h3><?php _e('System Info', SAM_DOMAIN) ?></h3>
                <div class="inside">
                  <p>
                    <?php 
                      //$versions = $this->getVersions(false);
                      //$dbVersion = $versions['db'];
                      //$vData = $this->getVersionData($dbVersion);
                      echo __('Wordpress Version', SAM_DOMAIN).': <strong>'.$wp_version.'</strong><br/>';
                      echo __('SAM Version', SAM_DOMAIN).': <strong>'.SAM_VERSION.'</strong><br/>';
                      echo __('SAM DB Version', SAM_DOMAIN).': <strong>'.SAM_DB_VERSION.'</strong><br/>';
                      echo __('PHP Version', SAM_DOMAIN).': <strong>'.PHP_VERSION.'</strong><br/>';
                      echo __('MySQL Version', SAM_DOMAIN).': <strong>'.$sqlVersion.'</strong><br/>';
                      echo __('Memory Limit', SAM_DOMAIN).': <strong>'.$mem.'</strong>'; 
                    ?>
                  </p>
                  <p>
                    <?php _e('Note! If you have detected a bug, include this data to bug report.', SAM_DOMAIN); ?>
                  </p>
                </div>
              </div>
              <div class='postbox opened'>
                <h3><?php _e('Resources', SAM_DOMAIN) ?></h3>
                <div class="inside">
                  <ul>
                    <li><a target='_blank' href='http://wordpress.org/extend/plugins/simple-ads-manager/'><?php _e("Wordpress Plugin Page", SAM_DOMAIN); ?></a></li>
                    <li><a target='_blank' href='http://www.simplelib.com/?p=480'><?php _e("Author Plugin Page", SAM_DOMAIN); ?></a></li>
                    <li><a target='_blank' href='http://forum.simplelib.com/index.php?forums/simple-ads-manager.13/'><?php _e("Support Forum", SAM_DOMAIN); ?></a></li>
                    <li><a target='_blank' href='http://www.simplelib.com/'><?php _e("Author's Blog", SAM_DOMAIN); ?></a></li>
                  </ul>                    
                </div>
              </div>  
              <div class='postbox opened'>
                <h3><?php _e('Donations', SAM_DOMAIN) ?></h3>
                <div class="inside">
                  <div style="text-align: center; margin-top: 10px;">
                    <script type="text/javascript">
                      /* <![CDATA[ */
                      function affiliateLink(str){ str = unescape(str); var r = ''; for(var i = 0; i < str.length; i++) r += String.fromCharCode(8^str.charCodeAt(i)); document.write(r); }
                      affiliateLink('4i%28%60zmn5*%60%7C%7Cx2%27%27%7F%7F%7F%26%7Cmp%7C%25dafc%25il%7B%26kge%277zmn5%3B%3A9%3E%3F0*64aeo%28%7Bzk5*%60%7C%7Cx2%27%27%7F%7F%7F%26%7Cmp%7C%25dafc%25il%7B%26kge%27aeiom%7B%27jiffmz%7B%27%7Bfgzm%25908p%3E8%26oan*%28jgzlmz5*8*%28id%7C5*%5Cmp%7C%28Dafc%28Il%7B*%2764%27i6');
                      /* ]]> */
                    </script>
                  </div>
                  <p>
                    <?php 
                      $format = __('If you have found this plugin useful, please consider making a %s to help support future development. Your support will be much appreciated. Thank you!', SAM_DOMAIN);
                      $str = '<a title="'.__('Donate Now!', SAM_DOMAIN).'" href="https://load.payoneer.com/LoadToPage.aspx?email=minimus@simplelib.com" target="_blank">'.__('donation', SAM_DOMAIN).'</a>';
                      printf($format, $str); 
                    ?>
                  </p>
                  <div style="text-align: center;">
                    <a href='https://pledgie.com/campaigns/23196'><img alt='Click here to lend your support to: Funds to complete the development of plugin Simple Ads Manager 2 and make a donation at pledgie.com !' src='https://pledgie.com/campaigns/23196.png?skin_name=chrome' border='0' ></a>
                  </div>
                  <div style="text-align: center; margin: 10px;">
                    <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
                      <input type="hidden" name="cmd" value="_s-xclick">
                      <input type="hidden" name="hosted_button_id" value="FNPBPFSWX4TVC">
                      <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
                      <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
                    </form>
                  </div>
                </div>
              </div>
              <div class='postbox opened'>
                <h3><?php _e('Another Plugins', SAM_DOMAIN) ?></h3>
                <div class="inside">
                  <p>
                    <?php
                    $format = __('Another plugins from %s', SAM_DOMAIN).':';
                    $str = '<strong><a target="_blank" href="http://wordpress.org/extend/plugins/profile/minimus">minimus</a></strong>';
                    printf($format, $str);
                    ?>
                  </p>
                    <ul>
                      <li><a target='_blank' href='http://wordpress.org/extend/plugins/wp-special-textboxes/'><strong>Special Text Boxes</strong></a> - <?php _e("Highlights any portion of text as text in the colored boxes.", SAM_DOMAIN); ?></li>
                      <li><a target='_blank' href='http://wordpress.org/extend/plugins/simple-counters/'><strong>Simple Counters</strong></a> - <?php _e("Adds simple counters badge (FeedBurner subscribers and Twitter followers) to your blog.", SAM_DOMAIN); ?></li>
                      <li><a target='_blank' href='http://wordpress.org/extend/plugins/simple-view/'><strong>Simple View</strong></a> - <?php _e("This plugin is WordPress shell for FloatBox library by Byron McGregor.", SAM_DOMAIN); ?></li>
                      <li><a target='_blank' href='http://wordpress.org/extend/plugins/wp-copyrighted-post/'><strong>Copyrighted Post</strong></a> - <?php _e("Adds copyright notice to the end of each post of your blog. ", SAM_DOMAIN); ?></li>
                    </ul>
                </div>
              </div>
            </div>
            <div id="post-body">
              <div id="post-body-content">
                <?php
                settings_fields('samOptions');
                $this->doSettingsSections('sam-settings', $this->settingsTabs);
                ?>
                <p class="submit">
                  <button id="submit-button" class="color-btn color-btn-left" name="Submit" type="submit">
                    <b style="background-color: #21759b"></b>
                    <?php esc_attr_e('Save Changes'); ?>
                  </button>
                </p>
                <p style='color: #777777; font-size: 13px; font-style: italic;'><?php _ex('Simple Ads Manager plugin for Wordpress.', 'Copyright String', SAM_DOMAIN); ?> Copyright &copy; 2010 - 2014, <a href='http://www.simplelib.com/'>minimus</a>. <?php _ex('All rights reserved.', 'Copyright String', SAM_DOMAIN); ?></p>
              </div>
            </div>
          </div>
				</form>
			</div>
			<?php
		}
    
    public function addButtons() {
      if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') && ! current_user_can(SAM_ACCESS) )
        return;
      
      if ( get_user_option('rich_editing') == 'true') {
        add_filter("mce_external_plugins", array(&$this, "addTinyMCEPlugin"));
        add_filter('mce_buttons', array(&$this, 'registerButton'));
      }
    }
    
    public function registerButton( $buttons ) {
      $options = $this->getSettings();
      if($options['editorButtonMode'] === 'modern') array_push($buttons, "separator", "samb");
      else array_push($buttons, 'separator', 'sama', 'samp', 'samz', 'samb');
      return $buttons;
    }
    
    public function addTinyMCEPlugin( $plugin_array ) {
      $options = parent::getSettings();
      if($options['editorButtonMode'] === 'modern') $plugin_array['samb'] = SAM_URL.'js/editor_plugin.js';
      else $plugin_array['samb'] = SAM_URL.'js/ep_classic.js';
      return $plugin_array;
    }
    
    public function tinyMCEVersion( $version ) {
      return ++$version;
    }
		
	  public function samTablePage() {
	    include_once('list.admin.class.php');
        $settings = parent::getSettings();
        $list = new SamPlaceList($settings);
        $list->page();
	  }
    
    public function samZoneListPage() {
      include_once('zone.list.admin.class.php');
      $settings = parent::getSettings();
      $list = new SamZoneList($settings);
      $list->page();
    }
    
    public function samBlockListPage() {
      include_once('block.list.admin.class.php');
      $settings = parent::getSettings();
      $list = new SamBlockList($settings);
      $list->page();
    }
		
	  public function samEditPage() {
	    include_once('editor.admin.class.php');
        $settings = parent::getSettings();
        $editor = new SamPlaceEdit($settings);
        $editor->page();
	  }
      
    public function samZoneEditPage() {
      include_once('zone.editor.admin.class.php');
      $settings = parent::getSettings();
      $editor = new SamZoneEditor($settings);
      $editor->page();
    }
    
    public function samBlockEditPage() {
      include_once('block.editor.admin.class.php');
      $settings = parent::getSettings();
      $editor = new SamBlockEditor($settings);
      $editor->page();
    }

    public function samErrorLog() {
      include_once('errorlog.admin.class.php');
      $settings = parent::getSettings();
      $list = new SamErrorLog($settings);
      $list->page();
    }
  } // end of class definition
} // end of if not class SimpleAdsManager exists
?>