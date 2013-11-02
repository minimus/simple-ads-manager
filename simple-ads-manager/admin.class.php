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
    
    public function __construct() {
      parent::__construct();

      global $wp_version;
      
			if ( function_exists( 'load_plugin_textdomain' ) )
				load_plugin_textdomain( SAM_DOMAIN, false, basename( SAM_PATH ) );
      
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
      $this->listBlock = add_submenu_page('sam-list', __('Ads Blocks List', SAM_DOMAIN), __('Ads Blocks', SAM_DOMAIN), SAM_ACCESS, 'sam-block-list', array(&$this, 'samBlockListPage'));
      add_action('admin_print_styles-'.$this->listBlock, array(&$this, 'adminListStyles'));
      $this->editBlock = add_submenu_page('sam-list', __('Ads Block Editor', SAM_DOMAIN), __('New Block', SAM_DOMAIN), SAM_ACCESS, 'sam-block-edit', array(&$this, 'samBlockEditPage'));
      add_action('admin_print_styles-'.$this->editBlock, array(&$this, 'adminEditStyles'));
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
      wp_enqueue_style('jquery-ui-css', SAM_URL.'css/jquery-ui-1.8.9.custom.css', false, '1.8.9');
      wp_enqueue_style('ColorPickerCSS', SAM_URL.'css/colorpicker.css');
      wp_enqueue_style('slick', SAM_URL.'css/slick.grid.css', false, '2.0');
      wp_enqueue_style('ComboGrid', SAM_URL.'css/jquery.ui.combogrid.css', false, '1.6.2');
    }
    
    public function adminSettingsStyles() {
      wp_enqueue_style('adminSettingsLayout', SAM_URL.'css/sam-admin-edit.css', false, SAM_VERSION);
      //wp_enqueue_style('jquery-ui-css', SAM_URL.'css/jquery-ui-1.8.9.custom.css', false, '1.8.9');
      wp_enqueue_style('jSlider', SAM_URL.'css/jslider.css', false, '1.1.0');
      wp_enqueue_style('jSlider-plastic', SAM_URL.'css/jslider.round.plastic.css', false, '1.1.0');
    }
    
    public function adminListStyles() {
      wp_enqueue_style('adminListLayout', SAM_URL.'css/sam-admin-list.css', false, SAM_VERSION);
      wp_enqueue_style('jquery-ui-css', SAM_URL.'css/jquery-ui-1.8.9.custom.css', false, '1.8.9');
    }
    
    public function adminEditScripts() {
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
      /*wp_enqueue_script('jquery-ui', SAM_URL.'js/jquery-ui-1.8.9.custom.min.js', array('jquery'), '1.8.9');*/
      if(file_exists(SAM_PATH.'/js/i18n/jquery.ui.datepicker-'.$lc.'.js'))
        wp_enqueue_script('jquery-ui-locale', SAM_URL.'js/i18n/jquery.ui.datepicker-'.$lc.'.js', array('jquery'), '1.8.9');
      wp_enqueue_script('ColorPicker', SAM_URL.'js/colorpicker.js', array('jquery'));
      wp_enqueue_script('AjaxUpload', SAM_URL.'js/ajaxupload.js', array('jquery'), '3.9');

      wp_enqueue_script('jquery-event-drag', SAM_URL.'js/slick/jquery.event.drag-2.0.min.js', array('jquery'), '2.0');
      wp_enqueue_script('slick-core', SAM_URL.'js/slick/slick.core.js', array('jquery', 'jquery-ui-core'), '2.0');
      wp_enqueue_script('slick-checkboxes', SAM_URL.'js/slick/slick.checkboxselectcolumn.js', array('jquery', 'jquery-ui-core'), '2.0');
      wp_enqueue_script('slick-tooltips', SAM_URL.'js/slick/slick.autotooltips.js', array('jquery', 'jquery-ui-core'), '2.0');
      wp_enqueue_script('slick-cell-rd', SAM_URL.'js/slick/slick.cellrangedecorator.js', array('jquery', 'jquery-ui-core'), '2.0');
      wp_enqueue_script('slick-cell-rs', SAM_URL.'js/slick/slick.cellrangeselector.js', array('jquery', 'jquery-ui-core'), '2.0');
      wp_enqueue_script('slick-cell-cm', SAM_URL.'js/slick/slick.cellcopymanager.js', array('jquery', 'jquery-ui-core'), '2.0');
      wp_enqueue_script('slick-cell-sm', SAM_URL.'js/slick/slick.cellselectionmodel.js', array('jquery', 'jquery-ui-core'), '2.0');
      wp_enqueue_script('slick-row-sm', SAM_URL.'js/slick/slick.rowselectionmodel.js', array('jquery', 'jquery-ui-core'), '2.0');
      wp_enqueue_script('slick-formatters', SAM_URL.'js/slick/slick.formatters.js', array('jquery', 'jquery-ui-core'), '2.0');
      wp_enqueue_script('slick-editors', SAM_URL.'js/slick/slick.editors.js', array('jquery', 'jquery-ui-core'), '2.0');
      wp_enqueue_script('slick-grid', SAM_URL.'js/slick/slick.grid.js', array('jquery', 'jquery-ui-core'), '2.0');

      //wp_enqueue_script('cg-props', SAM_URL.'js/jquery.i18n.properties-1.0.9.js', array('jquery', 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-position'), '1.0.9');
      wp_enqueue_script('ComboGrid', SAM_URL.'js/jquery.ui.combogrid-1.6.2.js', array('jquery', 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-position'/*, 'cg-props'*/), '1.6.2');

      wp_enqueue_script('adminEditScript', SAM_URL.'js/sam-admin-edit.min.js', array('jquery', 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-position'), SAM_VERSION);
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
      global $wpdb;
      $tTable = $wpdb->prefix . "terms";
      $ttTable = $wpdb->prefix . "term_taxonomy";
      $uTable = $wpdb->prefix . "users";
      $umTable = $wpdb->prefix . "usermeta";
      $postTable = $wpdb->prefix . "posts";
      
      $sql = "SELECT $tTable.term_id AS id, $tTable.name AS title, $tTable.slug
              FROM $tTable
              INNER JOIN $ttTable
                ON $tTable.term_id = $ttTable.term_id
              WHERE $ttTable.taxonomy = 'category'
              ORDER BY $tTable.name;";
                
      $cats = $wpdb->get_results($sql, ARRAY_A);
      
      $sql = "SELECT $tTable.term_id AS id, $tTable.name AS title, $tTable.slug
              FROM $tTable
              INNER JOIN $ttTable
                ON $tTable.term_id = $ttTable.term_id
              WHERE $ttTable.taxonomy = 'post_tag'
              ORDER BY $tTable.name;";
                
      $tags = $wpdb->get_results($sql, ARRAY_A);
      
      $sql = "SELECT
                $uTable.id,
                $uTable.display_name AS title,
                $uTable.user_nicename AS slug
              FROM
                $uTable
              INNER JOIN $umTable
                ON $uTable.id = $umTable.user_id
              WHERE
                $umTable.meta_key = 'wp_user_level' AND
                $umTable.meta_value > 1
              ORDER BY $uTable.id;";
                
      $auth = $wpdb->get_results($sql, ARRAY_A);

      $sql = "SELECT
                $uTable.id,
                $uTable.display_name AS title,
                $uTable.user_nicename AS slug,
                (CASE $umTable.meta_value
                  WHEN 0 THEN '".__('Subscriber', SAM_DOMAIN)."'
                  WHEN 1 THEN '".__('Contributor', SAM_DOMAIN)."'
                  WHEN 2 THEN '".__('Author', SAM_DOMAIN)."'
                  ELSE
                    IF($umTable.meta_value > 2 AND $umTable.meta_value <= 7, '".__('Editor', SAM_DOMAIN)."',
                      IF($umTable.meta_value > 7 AND $umTable.meta_value <= 10, '".__('Administrator', SAM_DOMAIN)."',
                        IF($umTable.meta_value > 10, '".__('Super Admin', SAM_DOMAIN)."', NULL)
                      )
                    )
                END) AS role
              FROM $uTable
              INNER JOIN $umTable
                ON $uTable.id = $umTable.user_id AND $umTable.meta_key = 'wp_user_level'
              ORDER BY $uTable.id;";
      $users = $wpdb->get_results($sql, ARRAY_A);
      
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

      if(!empty($sCustoms)) $custs = ',' . implode(',', $sCustoms);
      else $custs = '';
      
      $sql = "SELECT
                $postTable.id,
                $postTable.post_title AS title,
                $postTable.post_type AS type
              FROM
                $postTable
              WHERE
                $postTable.post_status = 'publish' AND
                FIND_IN_SET($postTable.post_type, 'post,page".$custs."')
              ORDER BY $postTable.id;";

      $posts = $wpdb->get_results($sql, ARRAY_A);
      for($i = 0; $i < sizeof($posts); $i++) {
        switch($posts[$i]['type']) {
          case 'post':
            $posts[$i]['type'] = __('Post', SAM_DOMAIN);
            break;
          case 'page':
            $posts[$i]['type'] = __('Page', SAM_DOMAIN);
            break;
          default:
            $posts[$i]['type'] = __('Post:', SAM_DOMAIN).' '.$posts[$i]['type'];
            break;
        }
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
            array('id' => "id", 'name' => "ID", 'field' => "id", 'width' => 50),
            array('id' => "title", 'name' => __("Category Title", SAM_DOMAIN), 'field' => "title", 'width' => 500),
            array('id' => "slug", 'name' => __("Category Slug", SAM_DOMAIN), 'field' => "slug", 'width' => 200)
          ),
          'cats' => $cats
        ),
        'authors' => array(
          'columns' => array(
            array('id' => "id", 'name' => "ID", 'field' => "id", 'width' => 50),
            array('id' => "title", 'name' => __("Display Name", SAM_DOMAIN), 'field' => "title", 'width' => 500),
            array('id' => "slug", 'name' => __("User Name", SAM_DOMAIN), 'field' => "slug", 'width' => 200)
          ),
          'authors' => $auth
        ),
        'tags' => array(
          'columns' => array(
            array('id' => "id", 'name' => "ID", 'field' => "id", 'width' => 50),
            array('id' => "title", 'name' => __("Tag Title", SAM_DOMAIN), 'field' => "title", 'width' => 500),
            array('id' => "slug", 'name' => __("Tag Slug", SAM_DOMAIN), 'field' => "slug", 'width' => 200)
          ),
          'tags' => $tags
        ),
        'customs' => array(
          'columns' => array(
            array('id' => "title", 'name' => __("Custom Type Title", SAM_DOMAIN), 'field' => "title", 'width' => 550),
            array('id' => "slug", 'name' => __("Custom Type Slug", SAM_DOMAIN), 'field' => "slug", 'width' => 200)
          ),
          'customs' => $customs
        ),
        'posts' => array(
          'columns' => array(
            array('id' => "id", 'name' => "ID", 'field' => "id", 'width' => 50),
            array('id' => "title", 'name' => __("Publication Title", SAM_DOMAIN), 'field' => "title", 'width' => 500),
            array('id' => "type", 'name' => __("Publication Type", SAM_DOMAIN), 'field' => "type", 'width' => 200)
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
            array('id' => 'id', 'name' => 'ID', 'field' => 'id', 'width' => 50),
            array('id' => "title", 'name' => __("Display Name", SAM_DOMAIN), 'field' => "title", 'width' => 250),
            array('id' => "slug", 'name' => __("User Name", SAM_DOMAIN), 'field' => "slug", 'width' => 200),
            array('id' => 'role', 'name' => __("Role", SAM_DOMAIN), 'field' => 'role', 'width' => 200)
          ),
          'users' => $users
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
                  <p>
                    <?php 
                      $format = __('If you have found this plugin useful, please consider making a %s to help support future development. Your support will be much appreciated. Thank you!', SAM_DOMAIN);
                      $str = '<a title="'.__('Donate Now!', SAM_DOMAIN).'" href="https://load.payoneer.com/LoadToPage.aspx?email=minimus@simplelib.com" target="_blank">'.__('donation', SAM_DOMAIN).'</a>';
                      printf($format, $str); 
                    ?>
                  </p>
                  <p style="color: #777777"><strong><?php _e('Donate via', SAM_DOMAIN); ?> Payoneer:</strong></p>
                  <div style="text-align: center; margin: 10px;">
                    <a title="Donate Now!" href="https://load.payoneer.com/LoadToPage.aspx?email=minimus@simplelib.com" target="_blank">
                      <img  title="<?php _e('Donate Now!', SAM_DOMAIN); ?>" src="<?php echo SAM_IMG_URL.'donate-now.png' ?>" alt="" width="100" height="34" style='margin-right: 5px;' />
                    </a>
                  </div>
                  <p style='margin: 3px; font-size: 0.7em'>
                    <?php 
                      $format = __("Warning! The default value of donation is %s. Don't worry! This is not my appetite, this is default value defined by Payoneer service.", SAM_DOMAIN).'<strong>'.__(' You can change it to any value you want!', SAM_DOMAIN).'</strong>';
                      $str = '<strong>$200</strong>';
                      printf($format, $str);
                    ?>
                  </p>
                  <p style="color: #777777"><strong><?php _e('Donate via', SAM_DOMAIN); ?> PayPal:</strong></p>
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
                  <input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
                </p>
                <p style='color: #777777; font-size: 12px; font-style: italic;'>Simple Ads Manager plugin for Wordpress. Copyright &copy; 2010 - 2011, <a href='http://www.simplelib.com/'>minimus</a>. All rights reserved.</p>
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