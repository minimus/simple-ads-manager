<?php
// Ads Place Widget
if(!class_exists('simple_ads_manager_widget_admin') && class_exists('WP_Widget')) {
  class simple_ads_manager_widget extends WP_Widget {
    protected $crawler = false;
    protected $aTitle = '';
    protected $wTable = '';
    
    function __construct() {
      if(!defined('SAM_OPTIONS_NAME')) define('SAM_OPTIONS_NAME', 'samPluginOptions');
      $this->crawler = $this->isCrawler();
      $this->aTitle = __('Ads Place:', SAM_DOMAIN);
      $this->wTable = 'sam_places';
      
      $widget_ops = array( 'classname' => 'simple_ads_manager_widget', 'description' => __('Ads Place rotator serviced by Simple Ads Manager.', SAM_DOMAIN));
      $control_ops = array( 'id_base' => 'simple_ads_manager_widget' );
      parent::__construct( 'simple_ads_manager_widget', __('Ads Place', SAM_DOMAIN), $widget_ops, $control_ops );
    }
    
    function getSettings() {
      $options = get_option(SAM_OPTIONS_NAME, '');      
      return $options;
    }
    
    protected function isCrawler() {
      $options = $this->getSettings();
      $crawler = false;
      
      if($options['detectBots'] == 1) {
        switch($options['detectingMode']) {
          case 'inexact':
            if($_SERVER["HTTP_USER_AGENT"] == '' ||
               $_SERVER['HTTP_ACCEPT'] == '' ||
               $_SERVER['HTTP_ACCEPT_ENCODING'] == '' ||
               $_SERVER['HTTP_ACCEPT_LANGUAGE'] == '' ||
               $_SERVER['HTTP_CONNECTION']=='' || is_admin()) $crawler = true;
            break;
            
          case 'exact':
            include_once('sam-browser.php');
            $browser = new samBrowser();
            $crawler = $browser->isRobot() || is_admin();
            break;
            
          case 'more':
            if(ini_get("browscap")) {
              $browser = get_browser(null, true);
              $crawler = $browser['crawler'] || is_admin();
            }
            break;
        }
      }
      return $crawler;
    }
    
    function widget( $args, $instance ) {
      $title = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title']);
      $adp_id = $instance['adp_id'];
      $hide_style = $instance['hide_style'];
      $place_codes = $instance['place_codes'];

      if(!is_admin()) {
        $ad = new SamAdPlace(array('id' => $adp_id), $place_codes, $this->crawler);
        $content = $ad->ad;
      }
      else $content = '';
      if(!empty($content)) {
        if ( !$hide_style ) {
          echo $args['before_widget'];
          if ( !empty( $title ) ) echo $args['before_title'] . $title . $args['after_title'];
          echo $content;
          echo $args['after_widget'];
        }
        else echo $content;
      }
    }

    function update( $new_instance, $old_instance ) {
      $instance = $old_instance;
      $instance['title'] = strip_tags($new_instance['title']);
      $instance['adp_id'] = $new_instance['adp_id'];
      $instance['hide_style'] = isset($new_instance['hide_style']);
      $instance['place_codes'] = isset($new_instance['place_codes']);
      return $instance;
    }

    function form( $instance ) {
      global $wpdb;
      $pTable = $wpdb->prefix . $this->wTable;

      $ids = $wpdb->get_results("SELECT $pTable.id, $pTable.name FROM $pTable WHERE $pTable.trash IS FALSE", ARRAY_A);

      $instance = wp_parse_args((array) $instance,
        array(
          'title'       => '',
          'adp_id'      => '',
          'parse'       => false
        )
      );
      $title = strip_tags($instance['title']);
      ?>
      <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', SAM_DOMAIN); ?></label>
        <input class="widefat"
               id="<?php echo $this->get_field_id('title'); ?>"
               name="<?php echo $this->get_field_name('title'); ?>"
               type="text" value="<?php echo esc_attr($title); ?>" />
      </p>
      <p>
        <label for="<?php echo $this->get_field_id('adp_id'); ?>"><?php echo $this->aTitle; ?></label>
        <select class="widefat"
                id="<?php echo $this->get_field_id('adp_id'); ?>"
                name="<?php echo $this->get_field_name('adp_id'); ?>" >
          <?php
          foreach ($ids as $option)
            echo '<option value='.$option['id'].(($instance['adp_id'] === $option['id']) ? ' selected' : '' ).'>'.$option['name'].'</option>';
          ?>
        </select>
      </p>
      <p>
        <input
          id="<?php echo $this->get_field_id('hide_style'); ?>"
          name="<?php echo $this->get_field_name('hide_style'); ?>"
          type="checkbox" <?php checked($instance['hide_style']); ?> />&nbsp;
        <label for="<?php echo $this->get_field_id('hide_style'); ?>">
          <?php _e('Hide widget style.', SAM_DOMAIN); ?>
        </label>
      </p>
      <p>
        <input
          id="<?php echo $this->get_field_id('place_codes'); ?>"
          name="<?php echo $this->get_field_name('place_codes'); ?>"
          type="checkbox" <?php checked($instance['place_codes']); ?> />&nbsp;
        <label for="<?php echo $this->get_field_id('place_codes'); ?>">
          <?php _e('Allow using previously defined "before" and "after" codes of Ads Place..', SAM_DOMAIN); ?>
        </label>
      </p>
    <?php
    }
  }
}

// Ads Zone Widget
if(!class_exists('simple_ads_manager_zone_widget') && class_exists('WP_Widget')) {
  class simple_ads_manager_zone_widget extends WP_Widget {
    private $crawler = false;
    private $aTitle = '';
    private $wTable = '';
    
    function __construct() {
      if(!defined('SAM_OPTIONS_NAME')) define('SAM_OPTIONS_NAME', 'samPluginOptions');
      $this->crawler = $this->isCrawler();
      $this->aTitle = __('Ads Zone', SAM_DOMAIN).':';
      $this->wTable = 'sam_zones';
      
      $widget_ops = array( 'classname' => 'simple_ads_manager_zone_widget', 'description' => __('Ads Zone selector serviced by Simple Ads Manager.', SAM_DOMAIN));
      $control_ops = array( 'id_base' => 'simple_ads_manager_zone_widget' );
      parent::__construct( 'simple_ads_manager_zone_widget', __('Ads Zone', SAM_DOMAIN), $widget_ops, $control_ops );
    }
    
    function getSettings() {
      $options = get_option(SAM_OPTIONS_NAME, '');      
      return $options;
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
               $_SERVER['HTTP_CONNECTION']=='' || is_admin()) $crawler = true;
            break;
            
          case 'exact':
            include_once('sam-browser.php');
            $browser = new samBrowser();
            $crawler = $browser->isRobot() || is_admin();
            break;
            
          case 'more':
            if(ini_get("browscap")) {
              $browser = get_browser(null, true);
              $crawler = $browser['crawler'] || is_admin();
            }
            break;
        }
      }
      return $crawler;
    }
    
    function widget( $args, $instance ) {
      $title = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title']);
      $adp_id = $instance['adp_id'];
      $hide_style = $instance['hide_style'];
      $place_codes = $instance['place_codes'];
      
      $ad = new SamAdPlaceZone(array('id' => $adp_id), $place_codes, $this->crawler);
      $content = $ad->ad;
      if(!empty($content)) {
        if ( !$hide_style ) {
          echo $args['before_widget'];
          if ( !empty( $title ) ) echo $args['before_title'] . $title . $args['after_title'];
          echo $content;
          echo $args['after_widget'];
        }
        else echo $content;
      }
    }
    
    function update( $new_instance, $old_instance ) {
      $instance = $old_instance;
      $instance['title'] = strip_tags($new_instance['title']);
      $instance['adp_id'] = $new_instance['adp_id'];
      $instance['hide_style'] = isset($new_instance['hide_style']);
      $instance['place_codes'] = isset($new_instance['place_codes']);
      return $instance;
    }
    
    function form( $instance ) {
      global $wpdb;
      $pTable = $wpdb->prefix . $this->wTable;
      
      $ids = $wpdb->get_results("SELECT $pTable.id, $pTable.name FROM $pTable WHERE $pTable.trash IS FALSE", ARRAY_A);
      
      $instance = wp_parse_args((array) $instance, 
        array(
          'title'       => '', 
          'adp_id'      => '', 
          'parse'       => false
        )
      );
      $title = strip_tags($instance['title']);
      ?>
      <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', SAM_DOMAIN); ?></label>
        <input class="widefat" 
          id="<?php echo $this->get_field_id('title'); ?>" 
          name="<?php echo $this->get_field_name('title'); ?>" 
          type="text" value="<?php echo esc_attr($title); ?>" />
      </p>
      <p>
        <label for="<?php echo $this->get_field_id('adp_id'); ?>"><?php echo $this->aTitle; ?></label>
        <select class="widefat" 
          id="<?php echo $this->get_field_id('adp_id'); ?>" 
          name="<?php echo $this->get_field_name('adp_id'); ?>" >
        <?php 
          foreach ($ids as $option) 
            echo '<option value='.$option['id'].(($instance['adp_id'] === $option['id']) ? ' selected' : '' ).'>'.$option['name'].'</option>';
        ?> 
        </select>
      </p>    
      <p>
        <input 
          id="<?php echo $this->get_field_id('hide_style'); ?>" 
          name="<?php echo $this->get_field_name('hide_style'); ?>" 
          type="checkbox" <?php checked($instance['hide_style']); ?> />&nbsp;
        <label for="<?php echo $this->get_field_id('hide_style'); ?>">
          <?php _e('Hide widget style.', SAM_DOMAIN); ?>
        </label>
      </p>
      <p>
        <input 
          id="<?php echo $this->get_field_id('place_codes'); ?>" 
          name="<?php echo $this->get_field_name('place_codes'); ?>" 
          type="checkbox" <?php checked($instance['place_codes']); ?> />&nbsp;
        <label for="<?php echo $this->get_field_id('place_codes'); ?>">
          <?php _e('Allow using previously defined "before" and "after" codes of Ads Place..', SAM_DOMAIN); ?>
        </label>
      </p>
      <?php
    }
  }
}

// Single Ad Widget
if(!class_exists('simple_ads_manager_ad_widget') && class_exists('WP_Widget')) {
  class simple_ads_manager_ad_widget extends WP_Widget {
    private $crawler = false;
    private $aTitle = '';
    private $wTable = '';
    
    function __construct() {
      if(!defined('SAM_OPTIONS_NAME')) define('SAM_OPTIONS_NAME', 'samPluginOptions');
      $this->crawler = $this->isCrawler();
      $this->aTitle = __('Ad', SAM_DOMAIN).':';
      $this->wTable = 'sam_ads';
      
      $widget_ops = array( 'classname' => 'simple_ads_manager_ad_widget', 'description' => __('Non-rotating single ad serviced by Simple Ads Manager.', SAM_DOMAIN));
      $control_ops = array( 'id_base' => 'simple_ads_manager_ad_widget' );
      parent::__construct( 'simple_ads_manager_ad_widget', __('Single Ad', SAM_DOMAIN), $widget_ops, $control_ops );
    }
    
    function getSettings() {
      $options = get_option(SAM_OPTIONS_NAME, '');      
      return $options;
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
               $_SERVER['HTTP_CONNECTION']=='' || is_admin()) $crawler = true;
            break;
            
          case 'exact':
            include_once('sam-browser.php');
            $browser = new samBrowser();
            $crawler = $browser->isRobot() || is_admin();
            break;
            
          case 'more':
            if(ini_get("browscap")) {
              $browser = get_browser(null, true);
              $crawler = $browser['crawler'] || is_admin();
            }
            break;
        }
      }
      return $crawler;
    }
    
    function widget( $args, $instance ) {
      extract($args);
      $title = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title']);
      $adp_id = $instance['adp_id'];
      $hide_style = $instance['hide_style'];
      $ad_codes = $instance['ad_codes'];
      
      $ad = new SamAd(array('id' => $adp_id), $ad_codes, $this->crawler);
      $content = $ad->ad;
      if(!empty($content)) {
        if ( !$hide_style ) {
          echo $args['before_widget'];
          if ( !empty( $title ) ) echo $args['before_title'] . $title . $args['after_title'];
          echo $content;
          echo $args['after_widget'];
        }
        else echo $content;
      }
    }
    
    function update( $new_instance, $old_instance ) {
      $instance = $old_instance;
      $instance['title'] = strip_tags($new_instance['title']);
      $instance['adp_id'] = $new_instance['adp_id'];
      $instance['hide_style'] = isset($new_instance['hide_style']);
      $instance['ad_codes'] = isset($new_instance['ad_codes']);
      return $instance;
    }
    
    function form( $instance ) {
      global $wpdb;
      $pTable = $wpdb->prefix . $this->wTable;
      
      $ids = $wpdb->get_results("SELECT $pTable.id, $pTable.name FROM $pTable WHERE $pTable.trash IS FALSE", ARRAY_A);
      
      $instance = wp_parse_args((array) $instance, 
        array(
          'title'       => '', 
          'adp_id'      => '', 
          'parse'       => false
        )
      );
      $title = strip_tags($instance['title']);
      ?>
      <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', SAM_DOMAIN); ?></label>
        <input class="widefat" 
          id="<?php echo $this->get_field_id('title'); ?>" 
          name="<?php echo $this->get_field_name('title'); ?>" 
          type="text" value="<?php echo esc_attr($title); ?>" />
      </p>
      <p>
        <label for="<?php echo $this->get_field_id('adp_id'); ?>"><?php echo $this->aTitle; ?></label>
        <select class="widefat" 
          id="<?php echo $this->get_field_id('adp_id'); ?>" 
          name="<?php echo $this->get_field_name('adp_id'); ?>" >
        <?php 
          foreach ($ids as $option) 
            echo '<option value='.$option['id'].(($instance['adp_id'] === $option['id']) ? ' selected' : '' ).'>'.$option['name'].'</option>';
        ?> 
        </select>
      </p>    
      <p>
        <input 
          id="<?php echo $this->get_field_id('hide_style'); ?>" 
          name="<?php echo $this->get_field_name('hide_style'); ?>" 
          type="checkbox" <?php checked($instance['hide_style']); ?> />&nbsp;
        <label for="<?php echo $this->get_field_id('hide_style'); ?>">
          <?php _e('Hide widget style.', SAM_DOMAIN); ?>
        </label>
      </p>
      <p>
        <input 
          id="<?php echo $this->get_field_id('ad_codes'); ?>" 
          name="<?php echo $this->get_field_name('ad_codes'); ?>" 
          type="checkbox" <?php checked($instance['ad_codes']); ?> />&nbsp;
        <label for="<?php echo $this->get_field_id('ad_codes'); ?>">
          <?php _e('Allow using previously defined "before" and "after" codes of Ads Place..', SAM_DOMAIN); ?>
        </label>
      </p>
      <?php
    }
  }
}

// Ads Block Widget
if(!class_exists('simple_ads_manager_block_widget') && class_exists('WP_Widget')) {
  class simple_ads_manager_block_widget extends WP_Widget {
    private $crawler = false;
    private $aTitle = '';
    private $wTable = '';
    
    function __construct() {
      if(!defined('SAM_OPTIONS_NAME')) define('SAM_OPTIONS_NAME', 'samPluginOptions');
      $this->crawler = $this->isCrawler();
      $this->aTitle = __('Block', SAM_DOMAIN).':';
      $this->wTable = 'sam_blocks';
      
      $widget_ops = array( 'classname' => 'simple_ads_manager_block_widget', 'description' => __('Ads Block collector serviced by Simple Ads Manager.', SAM_DOMAIN));
      $control_ops = array( 'id_base' => 'simple_ads_manager_block_widget' );
      parent::__construct( 'simple_ads_manager_block_widget', __('Ads Block', SAM_DOMAIN), $widget_ops, $control_ops );
    }
    
    function getSettings() {
      $options = get_option(SAM_OPTIONS_NAME, '');      
      return $options;
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
               $_SERVER['HTTP_CONNECTION']=='' || is_admin()) $crawler = true;
            break;
            
          case 'exact':
            if(!class_exists('samBrowser')) include_once('sam-browser.php');
            $browser = new samBrowser();
            $crawler = $browser->isRobot() || is_admin();
            break;
            
          case 'more':
            if(ini_get("browscap")) {
              $browser = get_browser(null, true);
              $crawler = $browser['crawler'] || is_admin();
            }
            break;
        }
      }
      return $crawler;
    }
    
    function widget( $args, $instance ) {
      extract($args);
      $title = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title']);
      $adp_id = $instance['adp_id'];
      $hide_style = $instance['hide_style'];
      
      $block = new SamAdBlock(array('id' => $adp_id), $this->crawler);
      $content = $block->ad;
      if(!empty($content)) {
        if ( !$hide_style ) {
          echo $args['before_widget'];
          if ( !empty( $title ) ) echo $args['before_title'] . $title . $args['after_title'];
          echo $content;
          echo $args['after_widget'];
        }
        else echo $content;
      }
    }
    
    function update( $new_instance, $old_instance ) {
      $instance = $old_instance;
      $instance['title'] = strip_tags($new_instance['title']);
      $instance['adp_id'] = $new_instance['adp_id'];
      $instance['hide_style'] = isset($new_instance['hide_style']);
      return $instance;
    }
    
    function form( $instance ) {
      global $wpdb;
      $bTable = $wpdb->prefix . $this->wTable;
      
      $ids = $wpdb->get_results("SELECT $bTable.id, $bTable.name FROM $bTable WHERE $bTable.trash IS FALSE", ARRAY_A);
      
      $instance = wp_parse_args((array) $instance, 
        array(
          'title'       => '', 
          'adp_id'      => '', 
          'parse'       => false
        )
      );
      $title = strip_tags($instance['title']);
      ?>
      <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', SAM_DOMAIN); ?></label>
        <input class="widefat" 
          id="<?php echo $this->get_field_id('title'); ?>" 
          name="<?php echo $this->get_field_name('title'); ?>" 
          type="text" value="<?php echo esc_attr($title); ?>" />
      </p>
      <p>
        <label for="<?php echo $this->get_field_id('adp_id'); ?>"><?php echo $this->aTitle; ?></label>
        <select class="widefat" 
          id="<?php echo $this->get_field_id('adp_id'); ?>" 
          name="<?php echo $this->get_field_name('adp_id'); ?>" >
        <?php 
          foreach ($ids as $option) 
            echo '<option value='.$option['id'].(($instance['adp_id'] === $option['id']) ? ' selected' : '' ).'>'.$option['name'].'</option>';
        ?> 
        </select>
      </p>    
      <p>
        <input 
          id="<?php echo $this->get_field_id('hide_style'); ?>" 
          name="<?php echo $this->get_field_name('hide_style'); ?>" 
          type="checkbox" <?php checked($instance['hide_style']); ?> />&nbsp;
        <label for="<?php echo $this->get_field_id('hide_style'); ?>">
          <?php _e('Hide widget style.', SAM_DOMAIN); ?>
        </label>
      </p>
      <?php
    }
  }
}
