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
    private $samPointerOptions = array('places' => true, 'ads' => true, 'zones' => true, 'blocks' => true);
    
    public function __construct() {
      parent::__construct();

      global $wp_version;
      
			if ( function_exists( 'load_plugin_textdomain' ) )
				load_plugin_textdomain( SAM_DOMAIN, false, basename( SAM_PATH ) . '/lang/' );
      
      if(!is_dir(SAM_AD_IMG)) mkdir(SAM_AD_IMG);
				
      register_activation_hook(SAM_MAIN_FILE, array(&$this, 'onActivate'));
      register_deactivation_hook(SAM_MAIN_FILE, array(&$this, 'onDeactivate'));

      $options = parent::getSettings(false);
      if(!empty($options['access'])) $access = $options['access'];
      else $access = 'manage_options';
      /*switch($options) {
        case 'SuperAdmin':
          $access = 'manage_network';
          break;
        case 'Administrator':
          $access = 'Plugin menu access by user role';
          break;
        case 'Editor':
          $access = 'edit_others_posts';
          break;
        case 'Author':
          $access = 'publish_posts';
          break;
        case 'Contributor':
          $access = 'edit_posts';
          break;
        default:
          $access = 'manage_options';
          break;
      }*/
      define('SAM_ACCESS', $access);
      
      add_action('wp_ajax_upload_ad_image', array(&$this, 'uploadHandler'));
      add_action('wp_ajax_get_strings', array(&$this, 'getStringsHandler'));
      add_action('wp_ajax_get_combo_data', array(&$this, 'getComboDataHandler'));
      add_action('wp_ajax_close_pointer', array(&$this, 'closePointerHandler'));
			add_action('admin_init', array(&$this, 'initSettings'));
			add_action('admin_menu', array(&$this, 'regAdminPage'));
      add_filter('tiny_mce_version', array(&$this, 'tinyMCEVersion'));
      add_action('init', array(&$this, 'addButtons'));
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
				delete_option('sam_db_version');
			}
      if($settings['deleteFolder'] == 1) {
        if(is_dir(SAM_AD_IMG)) rmdir(SAM_AD_IMG);
      }
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
      if(!is_null((integer)$vArray[2])) $output['revision'] = (integer)$vArray[2];
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
        $version['spec'] = $subver[1];
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
		
		public function initSettings() {
			register_setting('samOptions', SAM_OPTIONS_NAME);
      add_settings_section("sam_general_section", __("General Settings", SAM_DOMAIN), array(&$this, "drawGeneralSection"), 'sam-settings');
      add_settings_section("sam_single_section", __("Auto Inserting Settings", SAM_DOMAIN), array(&$this, "drawSingleSection"), 'sam-settings');
      add_settings_section("sam_ext_section", __('Extended Options', SAM_DOMAIN), array(&$this, 'drawExtSection'), 'sam-settings');
      add_settings_section("sam_dfp_section", __("Google DFP Settings", SAM_DOMAIN), array(&$this, "drawDFPSection"), 'sam-settings');
      add_settings_section("sam_statistic_section", __("Statistics Settings", SAM_DOMAIN), array(&$this, "drawStatisticsSection"), 'sam-settings');
      add_settings_section("sam_layout_section", __("Admin Layout", SAM_DOMAIN), array(&$this, "drawLayoutSection"), 'sam-settings');
			add_settings_section("sam_deactivate_section", __("Plugin Deactivating", SAM_DOMAIN), array(&$this, "drawDeactivateSection"), 'sam-settings');
			
      add_settings_field('adCycle', __("Views per Cycle", SAM_DOMAIN), array(&$this, 'drawTextOption'), 'sam-settings', 'sam_general_section', array('description' => __('Number of hits of one ad for a full cycle of rotation (maximal activity).', SAM_DOMAIN)));
      add_settings_field('access', __('Minimum Level for access to menu', SAM_DOMAIN), array(&$this, 'drawJSliderOption'), 'sam-settings', 'sam_general_section', array('description' => __('Who can use menu of plugin - Minimum User Level needed for access to menu of plugin. In any case only Super Admin and Administrator can use Settings Menu of SAM Plugin.', SAM_DOMAIN), 'options' => array('manage_network' => __('Super Admin', SAM_DOMAIN), 'manage_options' => __('Administrator', SAM_DOMAIN), 'edit_others_posts' => __('Editor', SAM_DOMAIN), 'publish_posts' => __('Author', SAM_DOMAIN), 'edit_posts' => __('Contributor', SAM_DOMAIN)), 'values' => array('manage_network', 'manage_options', 'edit_others_posts', 'publish_posts', 'edit_posts')));
      add_settings_field('adShow', __("Ad Output Mode", SAM_DOMAIN), array(&$this, 'drawRadioOption'), 'sam-settings', 'sam_general_section', array('description' => __('Standard (PHP) mode is more faster but is not compatible with caching plugins. If your blog use caching plugin (i.e WP Super Cache or Hyper Cache) select "Caching Compatible (Javascript)" mode.', SAM_DOMAIN), 'options' => array('php' => __('Standard (PHP)', SAM_DOMAIN), 'js' => __('Caching Compatible (Javascript)', SAM_DOMAIN))));
      add_settings_field('adDisplay', __("Display Ad Source in", SAM_DOMAIN), array(&$this, 'drawRadioOption'), 'sam-settings', 'sam_general_section', array('description' => __('Target wintow (tab) for advetisement source.', SAM_DOMAIN), 'options' => array('blank' => __('New Window (Tab)', SAM_DOMAIN), 'self' => __('Current Window (Tab)', SAM_DOMAIN))));
      
      add_settings_field('bpAdsId', __("Ads Place before content", SAM_DOMAIN), array(&$this, 'drawSelectOptionX'), 'sam-settings', 'sam_single_section', array('description' => ''));
      add_settings_field('beforePost', __("Allow Ads Place auto inserting before post/page content", SAM_DOMAIN), array(&$this, 'drawCheckboxOption'), 'sam-settings', 'sam_single_section', array('label_for' => 'beforePost', 'checkbox' => true));
      add_settings_field('bpUseCodes', __("Allow using predefined Ads Place HTML codes (before and after codes)", SAM_DOMAIN), array(&$this, 'drawCheckboxOption'), 'sam-settings', 'sam_single_section', array('label_for' => 'bpUseCodes', 'checkbox' => true));
      add_settings_field('mpAdsId', __("Ads Place in the middle of content", SAM_DOMAIN), array(&$this, 'drawSelectOptionX'), 'sam-settings', 'sam_single_section', array('description' => ''));
      add_settings_field('middlePost', __("Allow Ads Place auto inserting into the middle of post/page content", SAM_DOMAIN), array(&$this, 'drawCheckboxOption'), 'sam-settings', 'sam_single_section', array('label_for' => 'middlePost', 'checkbox' => true));
      add_settings_field('mpUseCodes', __("Allow using predefined Ads Place HTML codes (before and after codes)", SAM_DOMAIN), array(&$this, 'drawCheckboxOption'), 'sam-settings', 'sam_single_section', array('label_for' => 'mpUseCodes', 'checkbox' => true));
      add_settings_field('apAdsId', __("Ads Place after content", SAM_DOMAIN), array(&$this, 'drawSelectOptionX'), 'sam-settings', 'sam_single_section', array('description' => ''));
      add_settings_field('afterPost', __("Allow Ads Place auto inserting after post/page content", SAM_DOMAIN), array(&$this, 'drawCheckboxOption'), 'sam-settings', 'sam_single_section', array('label_for' => 'afterPost', 'checkbox' => true));
      add_settings_field('apUseCodes', __("Allow using predefined Ads Place HTML codes (before and after codes)", SAM_DOMAIN), array(&$this, 'drawCheckboxOption'), 'sam-settings', 'sam_single_section', array('label_for' => 'apUseCodes', 'checkbox' => true));

      add_settings_field('useSWF', __('I use (plan to use) my own flash (SWF) banners. In other words, allow loading the script "SWFObject" on the pages of the blog.', SAM_DOMAIN), array(&$this, 'drawCheckboxOption'), 'sam-settings', 'sam_ext_section', array('label_for' => 'useSWF', 'checkbox' => true));
      add_settings_field('errorlog', __('Turn on/off the error log.', SAM_DOMAIN), array(&$this, 'drawCheckboxOption'), 'sam-settings', 'sam_ext_section', array('label_for' => 'errorlog', 'checkbox' => true));
      add_settings_field('errorlogFS', __('Turn on/off the error log for Face Side.', SAM_DOMAIN), array(&$this, 'drawCheckboxOption'), 'sam-settings', 'sam_ext_section', array('label_for' => 'errorlogFS', 'checkbox' => true));

      add_settings_field('useDFP', __("Allow using Google DoubleClick for Publishers (DFP) rotator codes", SAM_DOMAIN), array(&$this, 'drawCheckboxOption'), 'sam-settings', 'sam_dfp_section', array('label_for' => 'useDFP', 'checkbox' => true));
      add_settings_field('dfpPub', __("Google DFP Pub Code", SAM_DOMAIN), array(&$this, 'drawTextOption'), 'sam-settings', 'sam_dfp_section', array('description' => __('Your Google DFP Pub code. i.e:', SAM_DOMAIN).' ca-pub-0000000000000000.', 'width' => 200));
      
      add_settings_field('detectBots', __("Allow Bots and Crawlers detection", SAM_DOMAIN), array(&$this, 'drawCheckboxOption'), 'sam-settings', 'sam_statistic_section', array('label_for' => 'detectBots', 'checkbox' => true));
      add_settings_field('detectingMode', __("Accuracy of Bots and Crawlers Detection", SAM_DOMAIN), array(&$this, 'drawRadioOption'), 'sam-settings', 'sam_statistic_section', array('description' => __("If bot is detected hits of ads won't be counted. Use with caution! More exact detection requires more server resources.", SAM_DOMAIN), 'options' => array( 'inexact' => __('Inexact detection', SAM_DOMAIN), 'exact' => __('Exact detection', SAM_DOMAIN), 'more' => __('More exact detection', SAM_DOMAIN))));
      add_settings_field('currency', __("Display of Currency", SAM_DOMAIN), array(&$this, 'drawRadioOption'), 'sam-settings', 'sam_statistic_section', array('description' => __("Define display of currency. Auto - auto detection of currency from blog settings. USD, EUR - Forcing the display of currency to U.S. dollars or Euro.", SAM_DOMAIN), 'options' => array( 'auto' => __('Auto', SAM_DOMAIN), 'usd' => __('USD', SAM_DOMAIN), 'euro' => __('EUR', SAM_DOMAIN))));

      add_settings_field('editorButtonMode', __("TinyMCE Editor Button Mode", SAM_DOMAIN), array(&$this, 'drawRadioOption'), 'sam-settings', 'sam_layout_section', array('description' => __('If you do not want to use the modern dropdown button in your TinyMCE editor, or use of this button causes a problem, you can use classic TinyMCE buttons. In this case select "Classic TinyMCE Buttons".', SAM_DOMAIN), 'options' => array('modern' => __('Modern TinyMCE Button', SAM_DOMAIN), 'classic' => __('Classic TinyMCE Buttons', SAM_DOMAIN))));
      add_settings_field('placesPerPage', __("Ads Places per Page", SAM_DOMAIN), array(&$this, 'drawTextOption'), 'sam-settings', 'sam_layout_section', array('description' => __('Ads Places Management grid pagination. How many Ads Places will be shown on one page of grid.', SAM_DOMAIN)));
			add_settings_field('itemsPerPage', __("Ads per Page", SAM_DOMAIN), array(&$this, 'drawTextOption'), 'sam-settings', 'sam_layout_section', array('description' => __('Ads of Ads Place Management grid pagination. How many Ads will be shown on one page of grid.', SAM_DOMAIN)));
      
      add_settings_field('deleteOptions', __("Delete plugin options during deactivating plugin", SAM_DOMAIN), array(&$this, 'drawCheckboxOption'), 'sam-settings', 'sam_deactivate_section', array('label_for' => 'deleteOptions', 'checkbox' => true));
			add_settings_field('deleteDB', __("Delete database tables of plugin during deactivating plugin", SAM_DOMAIN), array(&$this, 'drawCheckboxOption'), 'sam-settings', 'sam_deactivate_section', array('label_for' => 'deleteDB', 'checkbox' => true));
      add_settings_field('deleteFolder', __("Delete custom images folder of plugin during deactivating plugin", SAM_DOMAIN), array(&$this, 'drawCheckboxOption'), 'sam-settings', 'sam_deactivate_section', array('label_for' => 'deleteFolder', 'checkbox' => true));
      
      register_setting('sam-settings', SAM_OPTIONS_NAME, array(&$this, 'sanitizeSettings'));
		}
    
    public function regAdminPage() {
			global $wp_version;

      $menuPage = add_object_page(__('Ads', SAM_DOMAIN), __('Ads', SAM_DOMAIN), SAM_ACCESS, 'sam-list', array(&$this, 'samTablePage'), WP_PLUGIN_URL.'/simple-ads-manager/images/sam-icon.png');
			$this->listPage = add_submenu_page('sam-list', __('Ads List', SAM_DOMAIN), __('Ads Places', SAM_DOMAIN), SAM_ACCESS, 'sam-list', array(&$this, 'samTablePage'));
			add_action('admin_print_styles-'.$this->listPage, array(&$this, 'adminListStyles'));
      $this->editPage = add_submenu_page('sam-list', __('Ad Editor', SAM_DOMAIN), __('New Place', SAM_DOMAIN), SAM_ACCESS, 'sam-edit', array(&$this, 'samEditPage'));
      add_action('admin_print_styles-'.$this->editPage, array(&$this, 'adminEditStyles'));
      add_action('admin_print_scripts-'.$this->editPage, array(&$this, 'adminEditScripts'));
      $this->listZone = add_submenu_page('sam-list', __('Ads Zones List', SAM_DOMAIN), __('Ads Zones', SAM_DOMAIN), SAM_ACCESS, 'sam-zone-list', array(&$this, 'samZoneListPage'));
      add_action('admin_print_styles-'.$this->listZone, array(&$this, 'adminListStyles'));
      $this->editZone = add_submenu_page('sam-list', __('Ads Zone Editor', SAM_DOMAIN), __('New Zone', SAM_DOMAIN), SAM_ACCESS, 'sam-zone-edit', array(&$this, 'samZoneEditPage'));
      add_action('admin_print_styles-'.$this->editZone, array(&$this, 'adminEditStyles'));
      add_action('admin_print_scripts-'.$this->editZone, array(&$this, 'adminEditZBScripts'));
      $this->listBlock = add_submenu_page('sam-list', __('Ads Blocks List', SAM_DOMAIN), __('Ads Blocks', SAM_DOMAIN), SAM_ACCESS, 'sam-block-list', array(&$this, 'samBlockListPage'));
      add_action('admin_print_styles-'.$this->listBlock, array(&$this, 'adminListStyles'));
      $this->editBlock = add_submenu_page('sam-list', __('Ads Block Editor', SAM_DOMAIN), __('New Block', SAM_DOMAIN), SAM_ACCESS, 'sam-block-edit', array(&$this, 'samBlockEditPage'));
      add_action('admin_print_styles-'.$this->editBlock, array(&$this, 'adminEditStyles'));
      add_action('admin_print_scripts-'.$this->editBlock, array(&$this, 'adminEditZBScripts'));
			$this->settingsPage = add_submenu_page('sam-list', __('Simple Ads Manager Settings', SAM_DOMAIN), __('Settings', SAM_DOMAIN), 'manage_options', 'sam-settings', array(&$this, 'samAdminPage'));
      add_action('admin_print_styles-'.$this->settingsPage, array(&$this, 'adminSettingsStyles'));
      add_action('admin_print_scripts-'.$this->settingsPage, array(&$this, 'adminSettingsScripts'));
      $this->eLogPage = add_submenu_page('sam-list', __('Simple Ads Manager Error Log', SAM_DOMAIN), __('Error Log', SAM_DOMAIN), SAM_ACCESS, 'sam-errors', array(&$this, 'samErrorLog'));
      add_action('admin_print_styles-'.$this->eLogPage, array(&$this, 'adminListStyles'));
      add_action('admin_print_scripts-'.$this->eLogPage, array(&$this, 'errorsListScripts'));
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
    
    public function adminEditStyles() {
      wp_enqueue_style('adminEditLayout', SAM_URL.'css/sam-admin-edit.css', false, SAM_VERSION);
      wp_enqueue_style('jquery-ui-css', SAM_URL.'css/jquery-ui.css', false, '1.10.3');
      wp_enqueue_style('ComboGrid', SAM_URL.'css/jquery.ui.combogrid.css', false, '1.6.2');
      wp_enqueue_style('wp-pointer');
      wp_enqueue_style('colorButtons', SAM_URL.'css/color-buttons.css', false, SAM_VERSION);
      wp_enqueue_style('W2UI', SAM_URL . 'css/w2ui.min.css', false, '1.3');
    }
    
    public function adminSettingsStyles() {
      wp_enqueue_style('adminSettingsLayout', SAM_URL.'css/sam-admin-edit.css', false, SAM_VERSION);
      //wp_enqueue_style('jquery-ui-css', SAM_URL.'css/jquery-ui-1.8.9.custom.css', false, '1.8.9');
      wp_enqueue_style('jSlider', SAM_URL.'css/jslider.css', false, '1.1.0');
      wp_enqueue_style('jSlider-plastic', SAM_URL.'css/jslider.round.plastic.css', false, '1.1.0');
      wp_enqueue_style('colorButtons', SAM_URL.'css/color-buttons.css', false, SAM_VERSION);
    }
    
    public function adminListStyles() {
      wp_enqueue_style('adminListLayout', SAM_URL.'css/sam-admin-list.css', false, SAM_VERSION);
      wp_enqueue_style('jquery-ui-css', SAM_URL.'css/jquery-ui.css', false, '1.10.3');
    }
    
    public function adminEditScripts() {
      $options = parent::getSettings();
      $pointers = self::getPointerOptions();
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
      wp_enqueue_script('W2UI', SAM_URL . 'js/w2ui.min.js', array('jquery'), '1.3');
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
      wp_enqueue_script('AjaxUpload', SAM_URL.'js/ajaxupload.js', array('jquery'), '3.9');

      //wp_enqueue_script('cg-props', SAM_URL.'js/jquery.i18n.properties-1.0.9.js', array('jquery', 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-position'), '1.0.9');
      wp_enqueue_script('ComboGrid', SAM_URL.'js/jquery.ui.combogrid-1.6.3.js', array('jquery', 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-position'/*, 'cg-props'*/), '1.6.2');

      wp_enqueue_script('wp-pointer');
      wp_localize_script('wp-pointer', 'samPointer', array(
        'places' => array('enabled' => $pointers['places'], 'title' => __('Name of Ads Place', SAM_DOMAIN), 'content' => __('This is not required parameter. But it is strongly recommended to define it if you plan to use Ads Blocks, plugin\'s widgets or autoinserting of ads.', SAM_DOMAIN)),
        'ads' => array('enabled' => $pointers['ads'], 'title' => __('Name of Ad', SAM_DOMAIN), 'content' => __('This is not required parameter. But it is strongly recommended to define it if you plan to use Ads Blocks or plugin\'s widgets.', SAM_DOMAIN))
      ));
      wp_enqueue_script('adminEditScript', SAM_URL.'js/sam-admin-edit.min.js', array('jquery', 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-position'), SAM_VERSION);
    }

    public function adminEditZBScripts() {
      $pointers = self::getPointerOptions();

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
      wp_enqueue_script('adminEditScript', SAM_URL.'js/sam-admin-edit-zb.js', array('jquery', 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-position'), SAM_VERSION);
    }

    public function errorsListScripts() {
      wp_enqueue_script('jquery');
      wp_enqueue_script('jquery-ui-core');
      wp_enqueue_script('jquery-ui-widget');
      wp_enqueue_script('jquery-ui-button');
      wp_enqueue_script('jquery-ui-draggable');
      wp_enqueue_script('jquery-ui-mouse');
      wp_enqueue_script('jquery-ui-position');
      wp_enqueue_script('jquery-ui-resizable');
      wp_enqueue_script('jquery-ui-dialog');
      wp_enqueue_script('errorsListScript', SAM_URL.'js/sam-errors-list.js', array('jquery', 'jquery-ui-core'), SAM_VERSION);
      wp_localize_script('errorsListScript', 'options', array(
        'id' => __('Error ID', SAM_DOMAIN),
        'date' => __('Error Date', SAM_DOMAIN),
        'table' => __('Table', SAM_DOMAIN),
        'msg' => __('Error Message', SAM_DOMAIN),
        'sql' => __('Error SQL', SAM_DOMAIN),
        'etype' => __('Type', SAM_DOMAIN),
        'close' => __('Close', SAM_DOMAIN),
        'imgURL' => SAM_IMG_URL,
        'alts' => array(__('Warning', SAM_DOMAIN), __('Ok', SAM_DOMAIN))
      ));
    }

    public function adminSettingsScripts() {
      wp_enqueue_script('jquery');
      //wp_enqueue_script('jquery-ui-core');
      //wp_enqueue_script('jquery-ui-widget');
      //wp_enqueue_script('jquery-ui-mouse');
      //wp_enqueue_script('jquery-ui-slider');
      wp_enqueue_script('hash-table', SAM_URL.'js/slider/jshashtable-2.1_src.js', array('jquery'), '2.1');
      wp_enqueue_script('number-formatter', SAM_URL.'js/slider/jquery.numberformatter-1.2.3.js', array('jquery'), '1.2.3');
      wp_enqueue_script('templates', SAM_URL.'js/slider/tmpl.js', array('jquery'));
      wp_enqueue_script('depend-class', SAM_URL.'js/slider/jquery.dependClass-0.1.js', array('jquery'), '0.1');
      wp_enqueue_script('draggable', SAM_URL.'js/slider/draggable-0.1.js', array('jquery'), '0.1');
      wp_enqueue_script('jSlider', SAM_URL.'js/slider/jquery.slider.js', array('jquery', 'draggable'), '1.1.0');

      wp_enqueue_script('sam-settings', SAM_URL.'js/sam-settings.js', array('jquery', 'draggable'), SAM_VERSION);
      wp_localize_script('sam-settings', 'options', array(
        'roles' => array(
          __('Super Admin', SAM_DOMAIN),
          __('Administrator', SAM_DOMAIN),
          __('Editor', SAM_DOMAIN),
          __('Author', SAM_DOMAIN),
          __('Contributor', SAM_DOMAIN)
        ),
        'values' => array('manage_network', 'manage_options', 'edit_others_posts', 'publish_posts', 'edit_posts')
      ));
    }

    public function getCategories($valueType = 'array') {
      global $wpdb;
      $tTable = $wpdb->prefix . "terms";
      $ttTable = $wpdb->prefix . "term_taxonomy";
      
      $sql = "SELECT
                $tTable.term_id,
                $tTable.name,
                $ttTable.taxonomy
              FROM
                $tTable
              INNER JOIN $ttTable
                ON $tTable.term_id = $ttTable.term_id
              WHERE
                $ttTable.taxonomy = 'category'";
                
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
    
    public function uploadHandler() {
      $uploaddir = SAM_AD_IMG;  
      $file = $uploaddir . basename($_FILES['uploadfile']['name']);   

      if ( move_uploaded_file( $_FILES['uploadfile']['tmp_name'], $file )) {
        exit("success");  
      } else {
        exit("error");  
      }
    }

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

    public function getComboDataHandler() {
      global $wpdb;
      $uTable = $wpdb->prefix . "users";
      $page = $_GET['page'];
      $rows = $_GET['rows'];
      $searchTerm = $_GET['searchTerm'];
      $offset = ((int)$page - 1) * (int)$rows;

      $sql = "SELECT
                $uTable.id,
                $uTable.display_name AS title,
                $uTable.user_nicename AS slug,
                $uTable.user_email AS email
              FROM
                $uTable
              WHERE $uTable.user_nicename LIKE '".$searchTerm."%'
              ORDER BY $uTable.id
              LIMIT $offset, $rows;";
      $users = $wpdb->get_results($sql, ARRAY_A);

      $sql = "SELECT
      	        COUNT(*)
              FROM $uTable
              WHERE $uTable.user_nicename LIKE '".$searchTerm."%';";
      $rTotal = $wpdb->get_var($wpdb->prepare($sql));
      $total = ceil((int)$rTotal/(int)$rows);

      $charset = get_bloginfo('charset');

      header("Content-type: application/json; charset=$charset");
      exit(json_encode(array(
        'page' => $page,
        'records' => count($users),
        'rows' => $users,
        'total' => $total,
        'offset' => $offset
      )));
    }
    
    public function getStringsHandler() {
      global $wpdb, $wp_taxonomies;
      $tTable = $wpdb->prefix . "terms";
      $ttTable = $wpdb->prefix . "term_taxonomy";
      $uTable = $wpdb->prefix . "users";
      $umTable = $wpdb->prefix . "usermeta";
      $postTable = $wpdb->prefix . "posts";
      
      $sql = "SELECT wt.term_id AS id, wt.name AS title, wt.slug
              FROM $tTable wt
              INNER JOIN $ttTable wtt
                ON wt.term_id = wtt.term_id
              WHERE wtt.taxonomy = 'category'
              ORDER BY wt.name;";
                
      $cats = $wpdb->get_results($sql, ARRAY_A);
      $k = 0;
      foreach($cats as &$val) {
        $k++;
        $val['recid'] = $k;
      }
      
      $sql = "SELECT wt.term_id AS id, wt.name AS title, wt.slug
              FROM $tTable wt
              INNER JOIN $ttTable wtt
                ON wt.term_id = wtt.term_id
              WHERE wtt.taxonomy = 'post_tag'
              ORDER BY wt.name;";
                
      $tags = $wpdb->get_results($sql, ARRAY_A);
      $k = 0;
      foreach($tags as &$val) {
        $k++;
        $val['recid'] = $k;
      }

      $sql = "SELECT wtt.taxonomy
              FROM wp_term_taxonomy wtt
              WHERE NOT FIND_IN_SET(wtt.taxonomy, 'category,post_tag,nav_menu,link_category,post_format')
              GROUP BY wtt.taxonomy;";

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
      
      $sql = "SELECT
                wu.id,
                wu.display_name AS title,
                wu.user_nicename AS slug
              FROM
                $uTable wu
              INNER JOIN $umTable wum
                ON wu.id = wum.user_id
              WHERE
                wum.meta_key = 'wp_user_level' AND
                wum.meta_value > 1
              ORDER BY wu.id;";
                
      $auth = $wpdb->get_results($sql, ARRAY_A);
      $k = 0;
      foreach($auth as &$val) {
        $k++;
        $val['recid'] = $k;
      }

      $roleSubscriber = __('Subscriber', SAM_DOMAIN);
      $roleContributor = __('Contributor', SAM_DOMAIN);
      $roleAuthor = __('Author', SAM_DOMAIN);
      $roleEditor = __('Editor', SAM_DOMAIN);
      $roleAdministrator = __('Administrator', SAM_DOMAIN);
      $roleSuperAdmin = __('Super Admin', SAM_DOMAIN);
      $sql = "SELECT
                wu.id,
                wu.display_name AS title,
                wu.user_nicename AS slug,
                (CASE wum.meta_value
                  WHEN 0 THEN '$roleSubscriber'
                  WHEN 1 THEN '$roleContributor'
                  WHEN 2 THEN '$roleAuthor'
                  ELSE
                    IF(wum.meta_value > 2 AND wum.meta_value <= 7, '$roleEditor',
                      IF(wum.meta_value > 7 AND wum.meta_value <= 10, '$roleAdministrator',
                        IF(wum.meta_value > 10, '$roleSuperAdmin', NULL)
                      )
                    )
                END) AS role
              FROM $uTable wu
              INNER JOIN $umTable wum
                ON wu.id = wum.user_id AND wum.meta_key = 'wp_user_level'
              ORDER BY wu.id;";
      $users = $wpdb->get_results($sql, ARRAY_A);

      $k = 0;
      foreach($users as &$val) {
        $k++;
        $val['recid'] = $k;
      }
      
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
      
      $sql = "SELECT
                wp.id,
                wp.post_title AS title,
                wp.post_type AS type
              FROM
                $postTable wp
              WHERE
                wp.post_status = 'publish' AND
                FIND_IN_SET(wp.post_type, 'post,page{$custs}')
              ORDER BY wp.id;";

      $posts = $wpdb->get_results($sql, ARRAY_A);

      $k = 0;
      foreach($posts as &$val) {
        switch($val['type']) {
          case 'post':
            $val['type'] = __('Post', SAM_DOMAIN);
            break;
          case 'page':
            $val['type'] = __('Page', SAM_DOMAIN);
            break;
          default:
            $val['type'] = __('Post:', SAM_DOMAIN).' '.$val['type'];
            break;
        }
        $k++;
        $val['recid'] = $k;
      }

      $output = array(
        'uploading' => __('Uploading', SAM_DOMAIN).' ...',
        'uploaded' => __('Uploaded.', SAM_DOMAIN),
        'status' => __('Only JPG, PNG or GIF files are allowed', SAM_DOMAIN),
        'file' => __('File', SAM_DOMAIN),
        'path' => SAM_AD_IMG,
        'url' => SAM_AD_URL,
        'cats' => array(
          'columns' => array(
            array('field' => 'id', 'caption' => 'ID', 'size' => '40px'),
            array('field' => 'title', 'caption' => __("Category Title", SAM_DOMAIN), 'size' => '50%'),
            array('field' => 'slug', 'caption' => __("Category Slug", SAM_DOMAIN), 'size' => '40%')
          ),
          'cats' => $cats
        ),
        'authors' => array(
          'columns' => array(
            array('field' => 'id', 'caption' => 'ID', 'size' => '40px'),
            array('field' => 'title', 'caption' => __("Display Name", SAM_DOMAIN), 'size' => '50%'),
            array('field' => 'slug', 'caption' => __("User Name", SAM_DOMAIN), 'size' => '40%')
          ),
          'authors' => $auth
        ),
        'tags' => array(
          'columns' => array(
            array('field' => 'id', 'caption' => 'ID', 'size' => '40px'),
            array('field' => 'title', 'caption' => __("Tag Title", SAM_DOMAIN), 'size' => '50%'),
            array('field' => 'slug', 'caption' => __("Tag Slug", SAM_DOMAIN), 'size' => '40%')
          ),
          'tags' => $tags
        ),
        'customs' => array(
          'columns' => array(
            array('field' => 'title', 'caption' => __("Custom Type Title", SAM_DOMAIN), 'size' => '50%'),
            array('field' => 'slug', 'caption' => __("Custom Type Slug", SAM_DOMAIN), 'size' => '50%')
          ),
          'customs' => $customs
        ),
        'posts' => array(
          'columns' => array(
            array('field' => 'id', 'caption' => 'ID', 'size' => '40px'),
            array('field' => 'title', 'caption' => __("Publication Title", SAM_DOMAIN), 'size' => '50%'),
            array('field' => 'type', 'caption' => __("Publication Type", SAM_DOMAIN), 'size' => '40%')
          ),
          'posts' => $posts
        ),
        'users' => array(
          'colModel' => array(
            array('columnName' => 'id', 'width' => '15', 'hidden' => true, 'align' => 'right', 'label' => 'Id'),
            array('columnName' => 'title', 'width' => '190', 'align' => 'left', 'label' => __('Advertiser Name', SAM_DOMAIN)),
            array('columnName' => 'slug', 'width' => '190', 'align' => 'left', 'label' => __('Advertiser Nick', SAM_DOMAIN)),
            array('columnName' => 'email', 'width' => '190', 'align' => 'left', 'label' => __('Advertiser e-mail', SAM_DOMAIN))
          ),
          'columns' => array(
            array('field' => 'id', 'caption' => 'ID', 'size' => '40px'),
            array('field' => 'title', 'caption' => __("Display Name", SAM_DOMAIN), 'size' => '40%'),
            array('field' => 'slug', 'caption' => __("User Name", SAM_DOMAIN), 'size' => '25%'),
            array('field' => 'role', 'caption' => __("Role", SAM_DOMAIN), 'size' => '25%')
          ),
          'users' => $users
        ),
        'custom_taxes' => array(
          'taxes' => $cTax,
          'columns' => array(
            array('field' => 'term_id', 'caption' => __('ID', SAM_DOMAIN), 'size' => '30px'),
            array('field' => 'name', 'caption' => __('Term Name', SAM_DOMAIN), 'size' => '50%'),
            array('field' => 'ctax_name', 'caption' => __('Custom Taxonomy Name', SAM_DOMAIN), 'size' => '40%')
          )
        )
      );
      $charset = get_bloginfo('charset');
      
      header("Content-type: application/json; charset=$charset"); 
      exit(json_encode($output));
    }
		
		public function doSettingsSections($page) {
      global $wp_settings_sections, $wp_settings_fields;

      if ( !isset($wp_settings_sections) || !isset($wp_settings_sections[$page]) )
        return;

      foreach ( (array) $wp_settings_sections[$page] as $section ) {
        echo "<div id='poststuff' class='ui-sortable'>\n";
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
      }
    }
    
    public function doSettingsFields($page, $section) {
			global $wp_settings_fields;

			if ( !isset($wp_settings_fields) || !isset($wp_settings_fields[$page]) || !isset($wp_settings_fields[$page][$section]) )
				return;

			foreach ( (array) $wp_settings_fields[$page][$section] as $field ) {
				echo '<p>';
				if ( !empty($field['args']['checkbox']) ) {
					call_user_func($field['callback'], $field['id'], $field['args']);
					echo '<label for="' . $field['args']['label_for'] . '">' . $field['title'] . '</label>';
          echo '</p>';
				}
				else {
					if ( !empty($field['args']['label_for']) )
						echo '<label for="' . $field['args']['label_for'] . '">' . $field['title'] . '</label>';
					else
						echo '<strong>' . $field['title'] . '</strong><br/>';
          echo '</p>';
          echo '<p>';
					call_user_func($field['callback'], $field['id'], $field['args']);
          echo '</p>';
				}
        if(!empty($field['args']['description'])) echo '<p>' . $field['args']['description'] . '</p>';
			}
		}
    
    public function sanitizeSettings($input) {
      global $wpdb;
      
      $pTable = $wpdb->prefix . "sam_places";
      $sql = "SELECT $pTable.patch_dfp FROM $pTable WHERE $pTable.patch_source = 2";
      $rows = $wpdb->get_results($sql, ARRAY_A);
      $blocks = array();      
      foreach($rows as $value) array_push($blocks, $value['patch_dfp']);
      
      $output = $input;
      $output['dfpBlocks'] = array_unique($blocks);
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
    
    public function drawTextOption( $id, $args ) {
      $settings = parent::getSettings();
      if(isset($args['width'])) $width = $args['width'];
      else $width = 55;
      ?>
        <input id="<?php echo $id; ?>"
					name="<?php echo SAM_OPTIONS_NAME.'['.$id.']'; ?>"
					type="text"
					value="<?php echo $settings[$id]; ?>"
          style="height: 22px; font-size: 11px; <?php echo "width: {$width}px;" ?>" />
      <?php
    }

    public function drawCheckboxOption( $id, $args ) {
			$settings = parent::getSettings();
			?>
				<input id="<?php echo $id; ?>"
					<?php checked('1', $settings[$id]); ?>
					name="<?php echo SAM_OPTIONS_NAME.'['.$id.']'; ?>"
					type="checkbox"
					value="1" />
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

    /*public function drawSliderOption( $id, $args ) {
      $options = $args['options'];
      $settings = parent::getSettings();

      ?>
      <input
        type="hidden"
        id="<?php echo $id; ?>"
        name="<?php echo SAM_OPTIONS_NAME.'['.$id.']'; ?>"
        value="<?php echo $settings[$id]; ?>" />
      <div id="slider"></div>
      <?php
    }*/

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
				  ?>
				  <div class="updated"><p><strong><?php _e("Simple Ads Manager Settings Updated.", SAM_DOMAIN); ?></strong></p></div>
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
                    <li><a target='_blank' href='http://forum.simplelib.com/forumdisplay.php?13-Simple-Ads-Manager/'><?php _e("Support Forum", SAM_DOMAIN); ?></a></li>
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
                      <li><a target='_blank' href='http://wordpress.org/extend/plugins/wp-copyrighted-post/'><strong>Copyrighted Post</strong></a> - <?php _e("Adds copyright notice in the end of each post of your blog. ", SAM_DOMAIN); ?></li>
                    </ul>
                </div>
              </div>
            </div>
            <div id="post-body">
              <div id="post-body-content">
                <?php settings_fields('samOptions'); ?>
                <?php $this->doSettingsSections('sam-settings'); ?>
                <p class="submit">
                  <!--<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />-->
                  <button id="submit-button" class="color-btn color-btn-left" name="Submit" type="submit">
                    <b style="background-color: #21759b"></b>
                    <?php esc_attr_e('Save Changes'); ?>
                  </button>
                </p>
                <p style='color: #777777; font-size: 12px; font-style: italic;'><?php _ex('Simple Ads Manager plugin for Wordpress.', 'Copyright String', SAM_DOMAIN); ?> Copyright &copy; 2010 - 2014, <a href='http://www.simplelib.com/'>minimus</a>. <?php _ex('All rights reserved.', 'Copyright String', SAM_DOMAIN); ?></p>
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