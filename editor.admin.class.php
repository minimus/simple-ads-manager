<?php
if(!class_exists('SamPlaceEdit')) {
  class SamPlaceEdit {
    private $settings = array();
    
    public function __construct($settings) {
      $this->settings = $settings;
    }
    
    private function sanitizeSettings($input) {
      global $wpdb;
      
      $pTable = $wpdb->prefix . "sam_places";
      $sql = "SELECT sp.patch_dfp FROM $pTable sp WHERE sp.patch_source = 2";
      $rows = $wpdb->get_results($sql, ARRAY_A);
      $blocks = array();      
      foreach($rows as $value) array_push($blocks, $value['patch_dfp']);
      
      $output = $input;
      $output['dfpBlocks'] = array_unique($blocks);
      return $output;
    }
    
    private function removeTrailingComma($value = null) {
      if(empty($value)) return '';
      
      return rtrim(trim($value), ',');
    }
    
    private function buildViewPages($args) {
      $output = 0;
      foreach($args as $value) {
        if(!empty($value)) $output += $value;
      }
      return $output;
    }
    
    private function getFilesList($dir, $exclude = null) {
      $i = 1;
      
      if( is_null($exclude) ) $exclude = array();
      
      if ($handle = opendir($dir)) {
        while (false !== ($file = readdir($handle))) {
          if( $file != '.' && $file != '..' && !in_array( $file, $exclude ) ) {
            echo '<option value="'.$file.'"'.(($i == 1) ? '" selected="selected"' : '').'>'.$file.'</option>'."\n";
            $i++;
          }
        }
        closedir($handle);
      }
    }
    
    private function checkViewPages( $value, $page ) {
      return ( ($value & $page) > 0 );
    }
    
    /**
    * Outputs the name of Ads Place Size.
    *
    * Returns full Ads Place Size name.
    *
    * @since 0.1.1
    *
    * @param string $size Short name of Ads Place size
    * @return string value of Ads Place Size Name
    */
    private function getAdSize($value = '', $width = null, $height = null) {
      if($value == '') return null;

      if($value == 'custom') return array('name' => __('Custom sizes', SAM_DOMAIN), 'width' => $width, 'height' => $height);

      $aSizes = array(
        '800x90' => sprintf('%1$s x %2$s %3$s', 800, 90, __('Large Leaderboard', SAM_DOMAIN)),
        '728x90' => sprintf('%1$s x %2$s %3$s', 728, 90, __('Leaderboard', SAM_DOMAIN)),
        '600x90' => sprintf('%1$s x %2$s %3$s', 600, 90, __('Small Leaderboard', SAM_DOMAIN)),
        '550x250' => sprintf('%1$s x %2$s %3$s', 550, 250, __('Mega Unit', SAM_DOMAIN)),
        '550x120' => sprintf('%1$s x %2$s %3$s', 550, 120, __('Small Leaderboard', SAM_DOMAIN)),
        '550x90' => sprintf('%1$s x %2$s %3$s', 550, 90, __('Small Leaderboard', SAM_DOMAIN)),
        '468x180' => sprintf('%1$s x %2$s %3$s', 468, 180, __('Tall Banner', SAM_DOMAIN)),
        '468x120' => sprintf('%1$s x %2$s %3$s', 468, 120, __('Tall Banner', SAM_DOMAIN)),
        '468x90' => sprintf('%1$s x %2$s %3$s', 468, 90, __('Tall Banner', SAM_DOMAIN)),
        '468x60' => sprintf('%1$s x %2$s %3$s', 468, 60, __('Banner', SAM_DOMAIN)),
        '450x90' => sprintf('%1$s x %2$s %3$s', 450, 90, __('Tall Banner', SAM_DOMAIN)),
        '430x90' => sprintf('%1$s x %2$s %3$s', 430, 90, __('Tall Banner', SAM_DOMAIN)),
        '400x90' => sprintf('%1$s x %2$s %3$s', 400, 90, __('Tall Banner', SAM_DOMAIN)),
        '234x60' => sprintf('%1$s x %2$s %3$s', 234, 60, __('Half Banner', SAM_DOMAIN)),
        '200x90' => sprintf('%1$s x %2$s %3$s', 200, 90, __('Tall Half Banner', SAM_DOMAIN)),
        '150x50' => sprintf('%1$s x %2$s %3$s', 150, 50, __('Half Banner', SAM_DOMAIN)),
        '120x90' => sprintf('%1$s x %2$s %3$s', 120, 90, __('Button', SAM_DOMAIN)),
        '120x60' => sprintf('%1$s x %2$s %3$s', 120, 60, __('Button', SAM_DOMAIN)),
        '83x31' => sprintf('%1$s x %2$s %3$s', 83, 31, __('Micro Bar', SAM_DOMAIN)),
        '728x15x4' => sprintf('%1$s x %2$s %3$s, %4$s', 728, 15, __('Thin Banner', SAM_DOMAIN), sprintf(_n('%d Link', '%d Links', 4, SAM_DOMAIN), 4)),
        '728x15x5' => sprintf('%1$s x %2$s %3$s, %4$s', 728, 15, __('Thin Banner', SAM_DOMAIN), sprintf(_n('%d Link', '%d Links', 5, SAM_DOMAIN), 5)),
        '468x15x4' => sprintf('%1$s x %2$s %3$s, %4$s', 468, 15, __('Thin Banner', SAM_DOMAIN), sprintf(_n('%d Link', '%d Links', 4, SAM_DOMAIN), 4)),
        '468x15x5' => sprintf('%1$s x %2$s %3$s, %4$s', 468, 15, __('Thin Banner', SAM_DOMAIN), sprintf(_n('%d Link', '%d Links', 5, SAM_DOMAIN), 5)),
        '160x600' => sprintf('%1$s x %2$s %3$s', 160, 600, __('Wide Skyscraper', SAM_DOMAIN)),
        '120x600' => sprintf('%1$s x %2$s %3$s', 120, 600, __('Skyscraper', SAM_DOMAIN)),
        '200x360' => sprintf('%1$s x %2$s %3$s', 200, 360, __('Wide Half Banner', SAM_DOMAIN)),
        '240x400' => sprintf('%1$s x %2$s %3$s', 240, 400, __('Vertical Rectangle', SAM_DOMAIN)),
        '180x300' => sprintf('%1$s x %2$s %3$s', 180, 300, __('Tall Rectangle', SAM_DOMAIN)),
        '200x270' => sprintf('%1$s x %2$s %3$s', 200, 270, __('Tall Rectangle', SAM_DOMAIN)),
        '120x240' => sprintf('%1$s x %2$s %3$s', 120, 240, __('Vertical Banner', SAM_DOMAIN)),
        '336x280' => sprintf('%1$s x %2$s %3$s', 336, 280, __('Large Rectangle', SAM_DOMAIN)),
        '336x160' => sprintf('%1$s x %2$s %3$s', 336, 160, __('Wide Rectangle', SAM_DOMAIN)),
        '334x100' => sprintf('%1$s x %2$s %3$s', 334, 100, __('Wide Rectangle', SAM_DOMAIN)),
        '300x250' => sprintf('%1$s x %2$s %3$s', 300, 250, __('Medium Rectangle', SAM_DOMAIN)),
        '300x150' => sprintf('%1$s x %2$s %3$s', 300, 150, __('Small Wide Rectangle', SAM_DOMAIN)),
        '300x125' => sprintf('%1$s x %2$s %3$s', 300, 125, __('Small Wide Rectangle', SAM_DOMAIN)),
        '300x70' => sprintf('%1$s x %2$s %3$s', 300, 70, __('Mini Wide Rectangle', SAM_DOMAIN)),
        '250x250' => sprintf('%1$s x %2$s %3$s', 250, 250, __('Square', SAM_DOMAIN)),
        '200x200' => sprintf('%1$s x %2$s %3$s', 200, 200, __('Small Square', SAM_DOMAIN)),
        '200x180' => sprintf('%1$s x %2$s %3$s', 200, 180, __('Small Rectangle', SAM_DOMAIN)),
        '180x150' => sprintf('%1$s x %2$s %3$s', 180, 150, __('Small Rectangle', SAM_DOMAIN)),
        '160x160' => sprintf('%1$s x %2$s %3$s', 160, 160, __('Small Square', SAM_DOMAIN)),
        '125x125' => sprintf('%1$s x %2$s %3$s', 125, 125, __('Button', SAM_DOMAIN)),
        '200x90x4' => sprintf('%1$s x %2$s %3$s, %4$s', 200, 90, __('Tall Half Banner', SAM_DOMAIN), sprintf(_n('%d Link', '%d Links', 4, SAM_DOMAIN), 4)),
        '200x90x5' => sprintf('%1$s x %2$s %3$s, %4$s', 200, 90, __('Tall Half Banner', SAM_DOMAIN), sprintf(_n('%d Link', '%d Links', 5, SAM_DOMAIN), 5)),
        '180x90x4' => sprintf('%1$s x %2$s %3$s, %4$s', 180, 90, __('Half Banner', SAM_DOMAIN), sprintf(_n('%d Link', '%d Links', 4, SAM_DOMAIN), 4)),
        '180x90x5' => sprintf('%1$s x %2$s %3$s, %4$s', 180, 90, __('Half Banner', SAM_DOMAIN), sprintf(_n('%d Link', '%d Links', 5, SAM_DOMAIN), 5)),
        '160x90x4' => sprintf('%1$s x %2$s %3$s, %4$s', 160, 90, __('Tall Button', SAM_DOMAIN), sprintf(_n('%d Link', '%d Links', 4, SAM_DOMAIN), 4)),
        '160x90x5' => sprintf('%1$s x %2$s %3$s, %4$s', 160, 90, __('Tall Button', SAM_DOMAIN), sprintf(_n('%d Link', '%d Links', 5, SAM_DOMAIN), 5)),
        '120x90x4' => sprintf('%1$s x %2$s %3$s, %4$s', 120, 90, __('Button', SAM_DOMAIN), sprintf(_n('%d Link', '%d Links', 4, SAM_DOMAIN), 4)),
        '120x90x5' => sprintf('%1$s x %2$s %3$s, %4$s', 120, 90, __('Button', SAM_DOMAIN), sprintf(_n('%d Link', '%d Links', 5, SAM_DOMAIN), 5))
      );

      $aSize = explode("x", $value);
      //$aSize = preg_split("[x]", $value, null, PREG_SPLIT_NO_EMPTY);
      return array('name' => $aSizes[$value], 'width' => $aSize[0], 'height' => $aSize[1]);
    }
    
    private function adSizes($size = '468x60') {
      $sizes = array(
        'horizontal' => array(
          '800x90' => sprintf('%1$s x %2$s %3$s', 800, 90, __('Large Leaderboard', SAM_DOMAIN)),
          '728x90' => sprintf('%1$s x %2$s %3$s', 728, 90, __('Leaderboard', SAM_DOMAIN)),
          '600x90' => sprintf('%1$s x %2$s %3$s', 600, 90, __('Small Leaderboard', SAM_DOMAIN)),
          '550x250' => sprintf('%1$s x %2$s %3$s', 550, 250, __('Mega Unit', SAM_DOMAIN)),
          '550x120' => sprintf('%1$s x %2$s %3$s', 550, 120, __('Small Leaderboard', SAM_DOMAIN)),
          '550x90' => sprintf('%1$s x %2$s %3$s', 550, 90, __('Small Leaderboard', SAM_DOMAIN)),
          '468x180' => sprintf('%1$s x %2$s %3$s', 468, 180, __('Tall Banner', SAM_DOMAIN)),
          '468x120' => sprintf('%1$s x %2$s %3$s', 468, 120, __('Tall Banner', SAM_DOMAIN)),
          '468x90' => sprintf('%1$s x %2$s %3$s', 468, 90, __('Tall Banner', SAM_DOMAIN)),
          '468x60' => sprintf('%1$s x %2$s %3$s', 468, 60, __('Banner', SAM_DOMAIN)),
          '450x90' => sprintf('%1$s x %2$s %3$s', 450, 90, __('Tall Banner', SAM_DOMAIN)),
          '430x90' => sprintf('%1$s x %2$s %3$s', 430, 90, __('Tall Banner', SAM_DOMAIN)),
          '400x90' => sprintf('%1$s x %2$s %3$s', 400, 90, __('Tall Banner', SAM_DOMAIN)),
          '234x60' => sprintf('%1$s x %2$s %3$s', 234, 60, __('Half Banner', SAM_DOMAIN)),
          '200x90' => sprintf('%1$s x %2$s %3$s', 200, 90, __('Tall Half Banner', SAM_DOMAIN)),
          '150x50' => sprintf('%1$s x %2$s %3$s', 150, 50, __('Half Banner', SAM_DOMAIN)),
          '120x90' => sprintf('%1$s x %2$s %3$s', 120, 90, __('Button', SAM_DOMAIN)),
          '120x60' => sprintf('%1$s x %2$s %3$s', 120, 60, __('Button', SAM_DOMAIN)),
          '83x31' => sprintf('%1$s x %2$s %3$s', 83, 31, __('Micro Bar', SAM_DOMAIN)),
          '728x15x4' => sprintf('%1$s x %2$s %3$s, %4$s', 728, 15, __('Thin Banner', SAM_DOMAIN), sprintf(_n('%d Link', '%d Links', 4, SAM_DOMAIN), 4)),
          '728x15x5' => sprintf('%1$s x %2$s %3$s, %4$s', 728, 15, __('Thin Banner', SAM_DOMAIN), sprintf(_n('%d Link', '%d Links', 5, SAM_DOMAIN), 5)),
          '468x15x4' => sprintf('%1$s x %2$s %3$s, %4$s', 468, 15, __('Thin Banner', SAM_DOMAIN), sprintf(_n('%d Link', '%d Links', 4, SAM_DOMAIN), 4)),
          '468x15x5' => sprintf('%1$s x %2$s %3$s, %4$s', 468, 15, __('Thin Banner', SAM_DOMAIN), sprintf(_n('%d Link', '%d Links', 5, SAM_DOMAIN), 5))
        ),
        'vertical' => array(
          '160x600' => sprintf('%1$s x %2$s %3$s', 160, 600, __('Wide Skyscraper', SAM_DOMAIN)),
          '120x600' => sprintf('%1$s x %2$s %3$s', 120, 600, __('Skyscraper', SAM_DOMAIN)),
          '200x360' => sprintf('%1$s x %2$s %3$s', 200, 360, __('Wide Half Banner', SAM_DOMAIN)),
          '240x400' => sprintf('%1$s x %2$s %3$s', 240, 400, __('Vertical Rectangle', SAM_DOMAIN)),
          '180x300' => sprintf('%1$s x %2$s %3$s', 180, 300, __('Tall Rectangle', SAM_DOMAIN)),
          '200x270' => sprintf('%1$s x %2$s %3$s', 200, 270, __('Tall Rectangle', SAM_DOMAIN)),
          '120x240' => sprintf('%1$s x %2$s %3$s', 120, 240, __('Vertical Banner', SAM_DOMAIN))
        ),
        'square' => array(
          '336x280' => sprintf('%1$s x %2$s %3$s', 336, 280, __('Large Rectangle', SAM_DOMAIN)),
          '336x160' => sprintf('%1$s x %2$s %3$s', 336, 160, __('Wide Rectangle', SAM_DOMAIN)),
          '334x100' => sprintf('%1$s x %2$s %3$s', 334, 100, __('Wide Rectangle', SAM_DOMAIN)),
          '300x250' => sprintf('%1$s x %2$s %3$s', 300, 250, __('Medium Rectangle', SAM_DOMAIN)),
          '300x150' => sprintf('%1$s x %2$s %3$s', 300, 150, __('Small Wide Rectangle', SAM_DOMAIN)),
          '300x125' => sprintf('%1$s x %2$s %3$s', 300, 125, __('Small Wide Rectangle', SAM_DOMAIN)),
          '300x70' => sprintf('%1$s x %2$s %3$s', 300, 70, __('Mini Wide Rectangle', SAM_DOMAIN)),
          '250x250' => sprintf('%1$s x %2$s %3$s', 250, 250, __('Square', SAM_DOMAIN)),
          '200x200' => sprintf('%1$s x %2$s %3$s', 200, 200, __('Small Square', SAM_DOMAIN)),
          '200x180' => sprintf('%1$s x %2$s %3$s', 200, 180, __('Small Rectangle', SAM_DOMAIN)),
          '180x150' => sprintf('%1$s x %2$s %3$s', 180, 150, __('Small Rectangle', SAM_DOMAIN)),
          '160x160' => sprintf('%1$s x %2$s %3$s', 160, 160, __('Small Square', SAM_DOMAIN)),
          '125x125' => sprintf('%1$s x %2$s %3$s', 125, 125, __('Button', SAM_DOMAIN)),
          '200x90x4' => sprintf('%1$s x %2$s %3$s, %4$s', 200, 90, __('Tall Half Banner', SAM_DOMAIN), sprintf(_n('%d Link', '%d Links', 4, SAM_DOMAIN), 4)),
          '200x90x5' => sprintf('%1$s x %2$s %3$s, %4$s', 200, 90, __('Tall Half Banner', SAM_DOMAIN), sprintf(_n('%d Link', '%d Links', 5, SAM_DOMAIN), 5)),
          '180x90x4' => sprintf('%1$s x %2$s %3$s, %4$s', 180, 90, __('Half Banner', SAM_DOMAIN), sprintf(_n('%d Link', '%d Links', 4, SAM_DOMAIN), 4)),
          '180x90x5' => sprintf('%1$s x %2$s %3$s, %4$s', 180, 90, __('Half Banner', SAM_DOMAIN), sprintf(_n('%d Link', '%d Links', 5, SAM_DOMAIN), 5)),
          '160x90x4' => sprintf('%1$s x %2$s %3$s, %4$s', 160, 90, __('Tall Button', SAM_DOMAIN), sprintf(_n('%d Link', '%d Links', 4, SAM_DOMAIN), 4)),
          '160x90x5' => sprintf('%1$s x %2$s %3$s, %4$s', 160, 90, __('Tall Button', SAM_DOMAIN), sprintf(_n('%d Link', '%d Links', 5, SAM_DOMAIN), 5)),
          '120x90x4' => sprintf('%1$s x %2$s %3$s, %4$s', 120, 90, __('Button', SAM_DOMAIN), sprintf(_n('%d Link', '%d Links', 4, SAM_DOMAIN), 4)),
          '120x90x5' => sprintf('%1$s x %2$s %3$s, %4$s', 120, 90, __('Button', SAM_DOMAIN), sprintf(_n('%d Link', '%d Links', 5, SAM_DOMAIN), 5))
        ),
        'custom' => array( 'custom' => __('Custom sizes', SAM_DOMAIN) )
      );
      $sections = array(
        'horizontal' => __('Horizontal', SAM_DOMAIN),
        'vertical' => __('Vertical', SAM_DOMAIN),
        'square' => __('Square', SAM_DOMAIN),
        'custom' => __('Custom width and height', SAM_DOMAIN),
      );

      ?>
      <select id="place_size" name="place_size">
      <?php
      foreach($sizes as $key => $value) {
        ?>
        <optgroup label="<?php echo $sections[$key]; ?>">
            <?php
          foreach($value as $skey => $svalue) {
            ?>
          <option value="<?php echo $skey; ?>" <?php selected($size, $skey); ?> ><?php echo $svalue; ?></option>
            <?php
          }
          ?>
        </optgroup>
        <?php
      }
      ?>
      </select>
      <?php

    }

    private function getMonthName( $month ) {
      $months = array(
        __('January', SAM_DOMAIN),
        __('February', SAM_DOMAIN),
        __('Mart', SAM_DOMAIN),
        __('April', SAM_DOMAIN),
        __('May', SAM_DOMAIN),
        __('June', SAM_DOMAIN),
        __('July', SAM_DOMAIN),
        __('August', SAM_DOMAIN),
        __('September', SAM_DOMAIN),
        __('October', SAM_DOMAIN),
        __('November', SAM_DOMAIN),
        __('December', SAM_DOMAIN),
      );
      return $months[$month - 1];
    }

    private function drawImageTools() {
      ?>
      <div id="source_tools" >
        <p><strong><?php _e('Image Tools', SAM_DOMAIN); ?></strong></p>
        <div id="image_tools">
          <ul>
            <li><a href="#tab1"><?php _e('Media Library', SAM_DOMAIN); ?></a></li>
            <li><a href="#tab2"><?php _e('Server', SAM_DOMAIN); ?></a></li>
            <!--<li><a href="#tab3"><?php _e('Local Computer', SAM_DOMAIN); ?></a></li>-->
          </ul>
          <div id="tab1">
            <p>
              <strong><?php _e('Select Image from Media Library', SAM_DOMAIN); ?></strong>
            </p>
            <button id="banner-media" class="color-btn color-btn-left"><b style="background-color: #a915d1;"></b><?php _e('Select or Upload', SAM_DOMAIN); ?></button>
            <p>
              <?php _e('You can upload your banners to Wordpress Media Library or select banner image from it.', SAM_DOMAIN); ?>
            </p>
          </div>
          <div id="tab2">
            <p>
              <label for="files_list"><strong><?php echo (__('Select File', SAM_DOMAIN).':'); ?></strong></label>
              <select id="files_list" name="files_list" size="1"  dir="ltr" style="width: auto;">
                <?php $this->getFilesList(SAM_AD_IMG); ?>
              </select>&nbsp;&nbsp;
            </p>
            <button id="add-file-button" class="color-btn color-btn-left"><b style="background-color: #0cc77a"></b><?php _e('Apply', SAM_DOMAIN);?></button>
            <p>
              <?php _e("Select file from your blog server.", SAM_DOMAIN); ?>
            </p>
          </div>
          <!--<div id="tab3">
            <p>
              <strong><?php _e('Upload File', SAM_DOMAIN); ?></strong>
            </p>
            <button id="upload-file-button" class="color-btn color-btn-left"><b style="background-color: #21759b"></b><?php _e('Upload', SAM_DOMAIN);?></button>
            <span id="upload-console"></span>
            <span id="upload-progress"></span>
            <p>
              <span id="uploading-help"><?php _e("Select and upload file from your local computer.", SAM_DOMAIN); ?></span>
            </p>
          </div>-->
        </div>
	      <div class="sam2-warning">
		      <p><?php _e("The uploading feature (user's banners without using Media Library) was removed by request of administration of wordpress.org plugins repository. Use \"Select or Upload\" for uploading your ad banners to the server using Media Library. \"Selecting Files from Server\" feature is left for backward compatibility.", SAM_DOMAIN); ?></p>
	      </div>
      </div>
      <?php
    }
    
    public function page() {
      global $wpdb;
      $pTable = $wpdb->prefix . "sam_places";          
      $aTable = $wpdb->prefix . "sam_ads";
      $sTable = $wpdb->prefix . "sam_stats";
      
      $options = $this->settings;
      
      if(isset($_GET['action'])) $action = $_GET['action'];
      else $action = 'new';
      if(isset($_GET['mode'])) $mode = $_GET['mode'];
      else $mode = 'place';
      if(isset($_GET['item'])) $item = $_GET['item'];
      else $item = null;
      if(isset($_GET['place'])) $place = $_GET['place'];
      else $place = null;
      
      switch($mode) {
        case 'place':
          $updated = false;
          
          if(isset($_POST['update_place'])) {
            $placeId = $_POST['place_id'];
            $updateRow = array(
              'name' => stripslashes($_POST['place_name']),
              'description' => stripslashes($_POST['description']),
              'code_before' => stripslashes($_POST['code_before']),
              'code_after' => stripslashes($_POST['code_after']),
              'place_size' => $_POST['place_size'],
              'place_custom_width' => (isset($_POST['place_custom_width']) ? $_POST['place_custom_width'] : 0),
              'place_custom_height' => (isset($_POST['place_custom_height']) ? $_POST['place_custom_height'] : 0),
              'patch_img' => $_POST['patch_img'],
              'patch_link' => stripslashes($_POST['patch_link']),
              'patch_code' => stripslashes($_POST['patch_code']),
              'patch_adserver' => (isset($_POST['patch_adserver']) ? 1 : 0),
              'patch_dfp' => $_POST['patch_dfp'],
              'patch_source' => $_POST['patch_source'],
              'trash' => ($_POST['trash'] === 'true' ? 1 : 0)
            );
            $formatRow = array( '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%s', '%d', '%s', '%d', '%d');
            if($placeId === __('Undefined', SAM_DOMAIN)) {
              $wpdb->insert($pTable, $updateRow);
              $updated = true;
              $item = $wpdb->insert_id;
            }
            else {
              if(is_null($item)) $item = $placeId;
              $wpdb->update($pTable, $updateRow, array( 'id' => $item ), $formatRow, array( '%d' ));
              $updated = true;
            }            
            $newOptions = $this->sanitizeSettings($options);
            update_option( SAM_OPTIONS_NAME, $newOptions );
            ?>
<div class="updated"><p><strong><?php _e("Ads Place Data Updated.", SAM_DOMAIN);?></strong></p></div>
            <?php
          }

          $aSize = array();
          
          if($action !== 'new') {
            $row = $wpdb->get_row("SELECT id, name, description, code_before, code_after, place_size, place_custom_width, place_custom_height, patch_img, patch_link, patch_code, patch_adserver, patch_dfp, patch_source, trash FROM ".$pTable." WHERE id = ".$item, ARRAY_A);
            if($row['place_size'] === 'custom') $aSize = $this->getAdSize($row['place_size'], $row['place_custom_width'], $row['place_custom_height']);
            else $aSize = $this->getAdSize ($row['place_size']);
          }
          else {
            if($updated) {
              $row = $wpdb->get_row("SELECT id, name, description, code_before, code_after, place_size, place_custom_width, place_custom_height, patch_img, patch_link, patch_code, patch_adserver, patch_dfp, patch_source, trash FROM ".$pTable." WHERE id = ".$item, ARRAY_A);
              if($row['place_size'] === 'custom') $aSize = $this->getAdSize($row['place_size'], $row['place_custom_width'], $row['place_custom_height']);
              else $aSize = $this->getAdSize($row['place_size']);
            }
            else {
              $row = array(
                'id' => __('Undefined', SAM_DOMAIN),
                'name' => '',
                'description' => '',
                'code_before' => '',
                'code_after' => '',
                'place_size' => '468x60',
                'place_custom_width' => '',
                'place_custom_height' => '',
                'patch_img' => '',
                'patch_link' => '',
                'patch_code' => '',
                'patch_adserver' => 0,
                'patch_dfp' => '',
                'patch_source' => 0,
                'trash' => false
              );
              $aSize = array(
                'name' => __('Undefined', SAM_DOMAIN),
                'width' => __('Undefined', SAM_DOMAIN),
                'height' => __('Undefined', SAM_DOMAIN)
              );
            }
          }
          ?>
<div class="wrap">
  <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
    <div class="icon32" style="background: url('<?php echo SAM_IMG_URL.'sam-editor.png'; ?>') no-repeat transparent; "><br></div>
    <h2><?php echo ( ( ($action === 'new') && ( $row['id'] === __('Undefined', SAM_DOMAIN) ) ) ? __('New Ads Place', SAM_DOMAIN) : __('Edit Ads Place', SAM_DOMAIN).' ('.$item.')' ); ?></h2>
    <?php
      include_once('errors.class.php');
      $errors = new samErrors();
      if(!empty($errors->errorString)) echo $errors->errorString;
    ?>
    <div class="metabox-holder has-right-sidebar" id="poststuff">
      <div id="side-info-column" class="inner-sidebar">
        <div class="meta-box-sortables ui-sortable">
          <div id="submitdiv" class="postbox ">
            <div class="handlediv" title="<?php _e('Click to toggle', SAM_DOMAIN); ?>"><br></div>
            <h3 class="hndle"><span><?php _e('Status', SAM_DOMAIN);?></span></h3>
            <div class="inside">
              <div id="submitpost" class="submitbox">
                <div id="minor-publishing">
                  <div id="minor-publishing-actions">
                    <div id="save-action"> </div>
                    <div id="preview-action">
                      <a id="back-button" class="color-btn color-btn-left" href='<?php echo admin_url('admin.php'); ?>?page=sam-list'>
                        <b style="background-color: #bcbcbc"></b>
                        <?php _e('Back to Places List', SAM_DOMAIN) ?>
                      </a>
                    </div>
                    <div class="clear"></div>
                  </div>
                  <div id="misc-publishing-actions">
                    <div class="misc-pub-section">
                      <label for="place_id_stat"><?php echo __('Ads Place ID', SAM_DOMAIN).':'; ?></label>
                      <span id="place_id_stat" class="post-status-display"><?php echo $row['id']; ?></span>
                      <input type="hidden" id="place_id" name="place_id" value="<?php echo $row['id']; ?>" >
                      <input type='hidden' name='editor_mode' id='editor_mode' value='place'>
                    </div>
                    <div class="misc-pub-section">
                      <label for="place_size_info"><?php echo __('Size', SAM_DOMAIN).':'; ?></label>
                      <span id="place_size_info" class="post-status-display"><?php echo $aSize['name']; ?></span><br>
                      <label for="place_width"><?php echo __('Width', SAM_DOMAIN).':'; ?></label>
                      <span id="place_width" class="post-status-display"><?php echo $aSize['width']; ?></span><br>
                      <label for="place_height"><?php echo __('Height', SAM_DOMAIN).':'; ?></label>
                      <span id="place_height" class="post-status-display"><?php echo $aSize['height']; ?></span>
                    </div>
                    <div class="misc-pub-section">
                      <label for="trash_no"><input type="radio" id="trash_no" value="false" name="trash" <?php if (!$row['trash']) { echo 'checked="checked"'; }?> >  <?php _e('Is Active', SAM_DOMAIN); ?></label><br>
                      <label for="trash_yes"><input type="radio" id="trash_yes" value="true" name="trash" <?php if ($row['trash']) { echo 'checked="checked"'; }?> >  <?php _e('Is In Trash', SAM_DOMAIN); ?></label>
                    </div>
                  </div>
                  <div class="clear"></div>
                </div>
                <div id="major-publishing-actions">
                  <div id="delete-action">
                    <!--<a class="submitdelete deletion" href='<?php echo admin_url('admin.php'); ?>?page=sam-list'><?php _e('Cancel', SAM_DOMAIN) ?></a>-->
                  </div>
                  <div id="publishing-action">
                    <a id="cancel-button" class="color-btn color-btn-left" href='<?php echo admin_url('admin.php'); ?>?page=sam-list'>
                      <b style="background-color: #E5584A"></b>
                      <?php _e('Cancel', SAM_DOMAIN) ?>
                    </a>
                    <button id="submit-button" class="color-btn color-btn-left" name="update_place" type="submit">
                      <b style="background-color: #21759b"></b>
                      <?php _e('Save', SAM_DOMAIN) ?>
                    </button>
                    <!--<input type="submit" class='button-primary' name="update_place" value="<?php _e('Save', SAM_DOMAIN) ?>" >-->
                  </div>
                  <div class="clear"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div id="post-body">
        <div id="post-body-content">
          <div id="titlediv">
            <div id="titlewrap">
              <label class="screen-reader-text" for="title"><?php _e('Name', SAM_DOMAIN); ?></label>
              <input id="title" type="text" autocomplete="off" tabindex="1" size="30" name="place_name" value="<?php echo $row['name']; ?>" title="<?php echo __('Name of Ads Place', SAM_DOMAIN).'. '.__('Required for SAM widgets and settings.', SAM_DOMAIN); ?>" >
            </div>
          </div>
          <div class="meta-box-sortables ui-sortable">
            <div id="p-descdiv" class="postbox ">
              <div class="handlediv" title="<?php _e('Click to toggle', SAM_DOMAIN); ?>"><br></div>
              <h3 class="hndle"><span><?php _e('Description', SAM_DOMAIN);?></span></h3>
              <div class="inside">
                <p><?php _e('Enter description of this Ads Place.', SAM_DOMAIN);?></p>
                <p>
                  <label for="description"><?php echo __('Description', SAM_DOMAIN).':'; ?></label>
                  <textarea id="description" class="code" tabindex="2" name="description" style="width:100%; height: 80px;" ><?php echo $row['description']; ?></textarea>
                </p>
                <p><?php _e('This description is not used anywhere and is added solely for the convenience of managing advertisements.', SAM_DOMAIN); ?></p>
              </div>
            </div>
          </div>
          <div id="tabs">
            <ul>
              <li><a href="#tabs-1"><?php _e('General', SAM_DOMAIN); ?></a></li>
              <li><a href="#tabs-2"><?php _e('Default Ad', SAM_DOMAIN); ?></a></li>
              <li><a href="#tabs-3"><?php _e('Statistic', SAM_DOMAIN); ?></a></li>
            </ul>
            <div id="tabs-1">
              <div class="meta-box-sortables ui-sortable">
                <div id="sizediv" class="postbox ">
                  <div class="handlediv" title="<?php _e('Click to toggle', SAM_DOMAIN); ?>"><br></div>
                  <h3 class="hndle"><span><?php _e('Ads Place Size', SAM_DOMAIN);?></span></h3>
                  <div class="inside">
                    <p><?php _e('Select size of this Ads Place.', SAM_DOMAIN);?></p>
                    <p>
                      <?php $this->adSizes($row['place_size']); ?>
                    </p>
                    <p>
                      <label for="place_custom_width"><?php echo __('Custom Width', SAM_DOMAIN).':'; ?></label>
                      <input id="place_custom_width" type="text" tabindex="3" name="place_custom_width" value="<?php echo $row['place_custom_width']; ?>" style="width:20%" >
                    </p>
                    <p>
                      <label for="place_custom_height"><?php echo __('Custom Height', SAM_DOMAIN).':'; ?></label>
                      <input id="place_custom_height" type="text" tabindex="3" name="place_custom_height" value="<?php echo $row['place_custom_height']; ?>" style="width:20%" >
                    </p>
                    <p><?php _e('These values are not used and are added solely for the convenience of advertising management. Will be used in the future...', SAM_DOMAIN); ?></p>
                  </div>
                </div>
              </div>
              <div class="meta-box-sortables ui-sortable">
                <div id="codediv" class="postbox ">
                  <div class="handlediv" title="<?php _e('Click to toggle', SAM_DOMAIN); ?>"><br></div>
                  <h3 class="hndle"><span><?php _e('Codes', SAM_DOMAIN);?></span></h3>
                  <div class="inside">
                    <p><?php _e('Enter the code to output before and after the codes of Ads Place.', SAM_DOMAIN);?></p>
                    <p>
                      <label for="code_before"><?php echo __('Code Before', SAM_DOMAIN).':'; ?></label>
                      <input id="code_before" class="code" type="text" tabindex="2" name="code_before" value="<?php echo htmlspecialchars(stripslashes($row['code_before'])); ?>" style="width:100%" >
                    </p>
                    <p>
                      <label for="code_after"><?php echo __('Code After', SAM_DOMAIN).':'; ?></label>
                      <input id="code_after" class="code" type="text" tabindex="3" name="code_after" value="<?php echo htmlspecialchars(stripslashes($row['code_after'])); ?>" style="width:100%" >
                    </p>
                    <p><?php _e('You can enter any HTML codes here for the further withdrawal of their before and after the code of Ads Place.', SAM_DOMAIN); ?></p>
                  </div>
                </div>
              </div>
            </div>
            <div id="tabs-2">
              <div class="meta-box-sortables ui-sortable">
                <div id="srcdiv" class="postbox ">
                  <div class="handlediv" title="<?php _e('Click to toggle', SAM_DOMAIN); ?>"><br></div>
                  <h3 class="hndle"><span><?php _e('Ads Place Patch', SAM_DOMAIN);?></span></h3>
                  <div class="inside">
                    <p><?php _e('Select type of the code of a patch and fill data entry fields with the appropriate data.', SAM_DOMAIN);?></p>
                    <p>
                      <label for="patch_source_image"><input type="radio" id="patch_source_image" name="patch_source" value="0" <?php if($row['patch_source'] == '0') { echo 'checked="checked"'; } ?> >&nbsp;<?php _e('Image', SAM_DOMAIN); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;
                    </p>
                    <div id="rc-psi" class='radio-content' style="<?php if((int)$row['patch_source'] != 0) echo 'display: none;'; ?>">
                      <p>
                        <label for="patch_img"><?php echo __('Image', SAM_DOMAIN).':'; ?></label>
                        <input id="patch_img" class="code" type="text" tabindex="3" name="patch_img" value="<?php echo htmlspecialchars(stripslashes($row['patch_img'])); ?>" style="width:100%" >
                        <input id="patch_img_id" name="patch_img_id" type="hidden" value="<?php echo $row['patch_img_id'] ?>">
                      </p>
                      <p>
                        <?php _e('This image is a patch for advertising space. This may be an image with the text "Place your ad here".', SAM_DOMAIN); ?>
                      </p>
                      <p>
                        <label for="patch_link"><?php echo __('Target', SAM_DOMAIN).':'; ?></label>
                        <input id="patch_link" class="code" type="text" tabindex="4" name="patch_link" value="<?php echo htmlspecialchars(stripslashes($row['patch_link'])); ?>" style="width:100%" >
                      </p>
                      <p>
                        <?php _e('This is a link to a page where are your suggestions for advertisers.', SAM_DOMAIN); ?>
                      </p>
                      <?php self::drawImageTools(); ?>
                    </div>
                    <div class='clear-line'></div>
                    <p>
                      <label for="patch_source_code"><input type="radio" id="patch_source_code" name="patch_source" value="1" <?php if($row['patch_source'] == '1') { echo 'checked="checked"'; } ?> >&nbsp;<?php _e('HTML or Javascript Code', SAM_DOMAIN); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;
                    </p>
                    <div id="rc-psc" class='radio-content' style="<?php if((int)$row['patch_source'] != 1) echo 'display: none;'; ?>">
                      <p>
                        <label for="patch_code"><?php echo __('Patch Code', SAM_DOMAIN).':'; ?></label>
                        <textarea id="patch_code" class="code" rows='10' name="patch_code" style="width:100%" ><?php echo $row['patch_code']; ?></textarea>
                      </p>
                      <p>
                        <input type='checkbox' name='patch_adserver' id='patch_adserver' value='1' <?php checked(1, $row['patch_adserver']); ?> >
                        <label for='patch_adserver'><?php _e('This is one-block code of third-party AdServer rotator. Selecting this checkbox prevents displaying contained ads.', SAM_DOMAIN); ?></label>
                      </p>
                      <p>
                        <?php _e('This is a HTML-code patch of advertising space. For example: use the code to display AdSense advertisement. ', SAM_DOMAIN); ?>
                      </p>
                    </div>
                    <div class='clear-line'></div>
                    <p>
                      <label for="patch_source_dfp"><input type="radio" id="patch_source_dfp" name="patch_source" value="2" <?php if($row['patch_source'] == '2') { echo 'checked="checked"'; } ?> >&nbsp;<?php _e('Google DFP', SAM_DOMAIN); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;
                    </p>
                    <div id="rc-psd" class='radio-content' style="<?php if((int)$row['patch_source'] != 2) echo 'display: none;'; ?>">
                      <p>
                        <label for="patch_dfp"><?php echo __('DFP Block Name', SAM_DOMAIN).':'; ?></label>
                        <input type='text' name='patch_dfp' id='patch_dfp' value='<?php echo $row['patch_dfp']; ?>'>
                      </p>
                      <p>
                        <?php _e('This is name of Google DFP block!', SAM_DOMAIN); ?>
                      </p>
                    </div>
                    <p><?php _e('The patch (default advertisement) will be shown that if the logic of the plugin can not show any contained advertisement on the current page of the document.', SAM_DOMAIN); ?></p>
                  </div>
                </div>
              </div>
            </div>
            <div id="tabs-3">
              <?php if($action != 'new') { ?>
              <p class="totals">
                <?php
                $now = getdate();
                $thisMonth = $now['mon'];
                $thisYear = $now['year'];
                $prevMonth = ($thisMonth == 1) ? 12 : $thisMonth - 1;
                $prevYear = ($thisMonth == 1) ? $thisYear - 1 : $thisYear;

                ?>
                <label for="stats_month"><?php echo __('Select Period', SAM_DOMAIN) . ': '; ?></label>
                <select id="stats_month">
                  <option value="0"><?php echo __('This Month', SAM_DOMAIN) . ' (' . self::getMonthName($thisMonth) . ", $thisYear" . ')'; ?></option>
                  <option value="1"><?php echo __('Previous Month', SAM_DOMAIN) . ' (' . self::getMonthName($prevMonth) . ", $prevYear" . ')'; ?></option>
                </select>
              </p>
              <div class="graph-container">
                <div id="graph" style="width: 100%; height: 300px;"></div>
              </div>
              <p class="totals">
                <strong><?php echo __('Total', SAM_DOMAIN) . ':'; ?></strong><br>
                <?php _e('Hits', SAM_DOMAIN); ?>: <span id="total_hits"></span><br>
                <?php _e('Clicks', SAM_DOMAIN); ?>: <span id="total_clicks"></span><br>
              </p>
              <?php } ?>
            </div>
          </div>
          <div class="meta-box-sortables ui-sortable">
            <div id="p-descdiv" class="postbox ">
              <div class="handlediv" title="<?php _e('Click to toggle', SAM_DOMAIN); ?>"><br></div>
              <h3 class="hndle"><span><?php _e('Contained Ads', SAM_DOMAIN);?></span></h3>
              <div class="inside">
                <div id="ads-grid" style="height: 250px; width: 100%;"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>
</div>
          <?php
          break;
          
        case 'item':
          $aSize = array();
          
          if(isset($_POST['update_item'])) {
            $itemId = $_POST['item_id'];
            $placeId = $_POST['place_id'];
            $viewPages = $this->buildViewPages(array(
              ((isset($_POST['is_home'])) ? $_POST['is_home'] : 0),
              ((isset($_POST['is_singular'])) ? $_POST['is_singular'] : 0),
              ((isset($_POST['is_single'])) ? $_POST['is_single'] : 0),
              ((isset($_POST['is_page'])) ? $_POST['is_page'] : 0),
              ((isset($_POST['is_attachment'])) ? $_POST['is_attachment'] : 0),
              ((isset($_POST['is_search'])) ? $_POST['is_search'] : 0),
              ((isset($_POST['is_404'])) ? $_POST['is_404'] : 0),
              ((isset($_POST['is_archive'])) ? $_POST['is_archive'] : 0),
              ((isset($_POST['is_tax'])) ? $_POST['is_tax'] : 0),
              ((isset($_POST['is_category'])) ? $_POST['is_category'] : 0),
              ((isset($_POST['is_tag'])) ? $_POST['is_tag'] : 0),
              ((isset($_POST['is_author'])) ? $_POST['is_author'] : 0),
              ((isset($_POST['is_date'])) ? $_POST['is_date'] : 0),
              ((isset($_POST['is_posttype'])) ? $_POST['is_posttype'] : 0),
              ((isset($_POST['is_posttype_archive'])) ? $_POST['is_posttype_archive'] : 0)
            ));
            $updateRow = array(
              'pid' => $_POST['place_id'],
              'name' => stripslashes($_POST['item_name']),
              'description' => stripslashes($_POST['item_description']),
              'code_type' => (isset($_POST['code_type']) ? $_POST['code_type'] : 0),
              'code_mode' => $_POST['code_mode'],
              'ad_code' => stripslashes($_POST['ad_code']),
              'ad_img' => $_POST['ad_img'],
              'ad_alt' => $_POST['ad_alt'],
              'ad_no' => (isset($_POST['ad_no']) ? $_POST['ad_no'] : 0),
              'ad_target' => stripslashes($_POST['ad_target']),
              'ad_swf' => (isset($_POST['ad_swf']) ? $_POST['ad_swf'] : 0),
              'ad_swf_flashvars' => (!empty($_POST['ad_swf_flashvars'])) ? stripslashes($_POST['ad_swf_flashvars']) : '{}',
              'ad_swf_params' => (!empty($_POST['ad_swf_params'])) ? stripslashes($_POST['ad_swf_params']) : '{}',
              'ad_swf_attributes' => (!empty($_POST['ad_swf_attributes'])) ? stripslashes($_POST['ad_swf_attributes']) : '{}',
              'count_clicks' => (isset($_POST['count_clicks']) ? $_POST['count_clicks'] : 0),
              'ad_users' => $_POST['ad_users'],
              'ad_users_unreg' => (isset($_POST['ad_users_unreg']) ? $_POST['ad_users_unreg'] : 0),
              'ad_users_reg' => (isset($_POST['ad_users_reg']) ? $_POST['ad_users_reg'] : 0),
              'x_ad_users' => (isset($_POST['x_ad_users']) ? $_POST['x_ad_users'] : 0),
              'x_view_users' => self::removeTrailingComma( stripcslashes($_POST['x_view_users'])),
              'ad_users_adv' => (isset($_POST['ad_users_adv']) ? $_POST['ad_users_adv'] : 0),
              'view_type' => $_POST['view_type'],
              'view_pages' => $viewPages,
              'view_id' => $_POST['view_id'],
              'ad_cats' => (isset($_POST['ad_cats']) ? $_POST['ad_cats'] : 0),
              'view_cats' => self::removeTrailingComma( stripcslashes( $_POST['view_cats'] )),
              'ad_authors' => (isset($_POST['ad_authors']) ? $_POST['ad_authors'] : 0),
              'view_authors' => $this->removeTrailingComma(stripcslashes( $_POST['view_authors'])),
              'ad_tags' => (isset($_POST['ad_tags']) ? $_POST['ad_tags'] : 0),
              'view_tags' => self::removeTrailingComma( stripcslashes($_POST['view_tags']) ),
              'ad_custom' => (isset($_POST['ad_custom']) ? $_POST['ad_custom'] : 0),
              'view_custom' => self::removeTrailingComma( stripcslashes( $_POST['view_custom'] ) ),
              'x_id' => (isset($_POST['x_id']) ? $_POST['x_id'] : 0),
              'x_view_id' => $_POST['x_view_id'],
              'x_cats' => (isset($_POST['x_cats']) ? $_POST['x_cats'] : 0),
              'x_view_cats' => self::removeTrailingComma(stripslashes($_POST['x_view_cats'])),
              'x_authors' => (isset($_POST['x_authors']) ? $_POST['x_authors'] : 0),
              'x_view_authors' => self::removeTrailingComma(stripcslashes($_POST['x_view_authors'])),
              'x_tags' => (isset($_POST['x_tags']) ? $_POST['x_tags'] : 0),
              'x_view_tags' => self::removeTrailingComma(stripcslashes($_POST['x_view_tags'])),
              'x_custom' => (isset($_POST['x_custom']) ? $_POST['x_custom'] : 0),
              'x_view_custom' => self::removeTrailingComma(stripcslashes($_POST['x_view_custom'])),
              'ad_start_date' => (empty($_POST['ad_start_date']) ? '0000-00-00' :$_POST['ad_start_date']),
              'ad_end_date' => (empty($_POST['ad_end_date']) ? '0000-00-00' : $_POST['ad_end_date']),
              'ad_schedule' => (isset($_POST['ad_schedule']) ? $_POST['ad_schedule'] : 0),
              'ad_weight' => $_POST['ad_weight'],
              'limit_hits' => (isset($_POST['limit_hits']) ? $_POST['limit_hits'] : 0),
              'hits_limit' => $_POST['hits_limit'],
              'limit_clicks' => (isset($_POST['limit_clicks']) ? $_POST['limit_clicks'] : 0),
              'clicks_limit' => $_POST['clicks_limit'],
              'adv_nick' => $_POST['adv_nick'],
              'adv_name' => $_POST['adv_name'],
              'adv_mail' => $_POST['adv_mail'],
              'cpm' => $_POST['cpm'],
              'cpc' => $_POST['cpc'],
              'per_month' => $_POST['per_month'],
              'trash' => ($_POST['trash'] === 'true' ? 1 : 0),
              'ad_custom_tax_terms' => ((isset($_POST['ad_custom_tax_terms'])) ? $_POST['ad_custom_tax_terms'] : 0),
              'view_custom_tax_terms' => self::removeTrailingComma(stripslashes($_POST['view_custom_tax_terms'])),
              'x_ad_custom_tax_terms' => ((isset($_POST['x_ad_custom_tax_terms'])) ? $_POST['x_ad_custom_tax_terms'] : 0),
              'x_view_custom_tax_terms' => self::removeTrailingComma(stripslashes($_POST['x_view_custom_tax_terms']))
            );
            $formatRow = array(
              '%d', '%s', '%s', '%d', '%d', '%s', '%s', '%s', '%d', '%s',
              '%d', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%s',
              '%d', '%d', '%s', '%s', '%d', '%s', '%d', '%s', '%d', '%s',
              '%d', '%s', '%d', '%s', '%d', '%s', '%d', '%s', '%d', '%s',
              '%d', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%d',
              '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%s', '%d',
              '%s'
            );
            if($itemId === __('Undefined', SAM_DOMAIN)) {
              $wpdb->insert($aTable, $updateRow);
              $item = $wpdb->insert_id;
            }
            else {
              if(is_null($item)) $item = $itemId;
              $wpdb->update($aTable, $updateRow, array( 'id' => $item ), $formatRow, array( '%d' ));
            }
            $wpdb->query("UPDATE $aTable sa SET sa.ad_weight_hits = 0 WHERE sa.pid = {$placeId};");
            $action = 'edit';
            ?>
<div class="updated"><p><strong><?php _e("Ad Data Updated.", SAM_DOMAIN);?></strong></p></div>
            <?php
          }
          
          if($action !== 'new') {
            $row = $wpdb->get_row(
              "SELECT sa.id,
                      sa.pid,
                      sa.name,
                      sa.description,
                      sa.code_type,
                      sa.code_mode,
                      sa.ad_code,
                      sa.ad_img,
                      sa.ad_alt,
                      sa.ad_no,
                      sa.ad_target,
                      sa.ad_swf,
                      sa.ad_swf_flashvars,
                      sa.ad_swf_params,
                      sa.ad_swf_attributes,
                      sa.count_clicks,
                      sa.ad_users,
                      sa.ad_users_unreg,
                      sa.ad_users_reg,
                      sa.x_ad_users,
                      sa.x_view_users,
                      sa.ad_users_adv,
                      sp.place_size AS ad_size,
                      sp.place_custom_width AS ad_custom_width,
                      sp.place_custom_height AS ad_custom_height,
                      sa.view_type,
                      (sa.view_pages+0) AS view_pages,
                      sa.view_id,
                      sa.ad_cats,
                      sa.view_cats,
                      sa.ad_authors,
                      sa.view_authors,
                      sa.ad_tags,
                      sa.view_tags,
                      sa.ad_custom,
                      sa.view_custom,
                      sa.x_id,
                      sa.x_view_id,
                      sa.x_cats,
                      sa.x_view_cats,
                      sa.x_authors,
                      sa.x_view_authors,
                      sa.x_tags,
                      sa.x_view_tags,
                      sa.x_custom,
                      sa.x_view_custom,
                      sa.ad_start_date,
                      sa.ad_end_date,
                      sa.ad_schedule,
                      sa.limit_hits,
                      sa.hits_limit,
                      sa.limit_clicks,
                      sa.clicks_limit,
                      @ad_hits := (SELECT COUNT(*) FROM $sTable ss WHERE (EXTRACT(YEAR_MONTH FROM NOW()) = EXTRACT(YEAR_MONTH FROM ss.event_time)) AND ss.id = sa.id AND ss.pid = sa.pid AND ss.event_type = 0) AS ad_hits,
                      @ad_clicks := (SELECT COUNT(*) FROM $sTable ss WHERE (EXTRACT(YEAR_MONTH FROM NOW()) = EXTRACT(YEAR_MONTH FROM ss.event_time)) AND ss.id = sa.id AND ss.pid = sa.pid AND ss.event_type = 1) AS ad_clicks,
                      sa.ad_weight,
                      sa.ad_weight_hits,
                      sa.adv_nick,
                      sa.adv_name,
                      sa.adv_mail,
                      sa.cpm,
                      sa.cpc,
                      sa.per_month,
                      sa.trash,
                      (IF(sa.ad_schedule, NOT (DATEDIFF(sa.ad_end_date, NOW()) IS NULL OR DATEDIFF(sa.ad_end_date, NOW()) > 0), FALSE) OR
                      IF(sa.limit_hits = 1 AND sa.hits_limit <= @ad_hits, TRUE, FALSE) OR
                      IF(sa.limit_clicks AND sa.clicks_limit <= @ad_clicks, TRUE, FALSE)) AS expired,
                      sp.code_before,
                      sp.code_after,
                      sa.ad_custom_tax_terms,
                      sa.view_custom_tax_terms,
                      sa.x_ad_custom_tax_terms,
                      sa.x_view_custom_tax_terms
                  FROM $aTable sa
                  INNER JOIN $pTable sp
                  ON sa.pid = sp.id
                  WHERE sa.id = $item;",
              ARRAY_A);
              
            if($row['ad_size'] === 'custom') $aSize = $this->getAdSize($row['ad_size'], $row['ad_custom_width'], $row['ad_custom_height']);
            else $aSize = $this->getAdSize($row['ad_size']);  
          }
          else {
            $row = array(
              'id' => __('Undefined', SAM_DOMAIN),
              'pid' => $place,
              'name' => '',
              'description' => '',
              'code_type' => 0,
              'code_mode' => 1,
              'ad_code' => '',
              'ad_img' => '',
              'ad_alt' => '',
              'ad_no' => 0,
              'ad_target' => '',
              'ad_swf' => 0,
              'ad_swf_flashvars' => '{}',
              'ad_swf_params' => '{}',
              'ad_swf_attributes' => '{}',
              'count_clicks' => 0,
              'ad_users' => 0,
              'ad_users_unreg' => 0,
              'ad_users_reg' => 0,
              'x_ad_users' => 0,
              'x_view_users' => '',
              'ad_users_adv' => 0,
              'view_type' => 1,
              'view_pages' => 0,
              'view_id' => '',
              'ad_cats' => 0,
              'view_cats' => '',
              'ad_authors' => 0,
              'view_authors' => '',
              'ad_tags' => 0,
              'view_tags' => '',
              'ad_custom' => 0,
              'view_custom' => '',
              'x_id' => 0,
              'x_view_id' => '',
              'x_cats' => 0,
              'x_view_cats' => '',
              'x_authors' => 0,
              'x_view_authors' => '',
              'x_tags' => 0,
              'x_view_tags' => '',
              'x_custom' => 0,
              'x_view_custom' => '',
              'ad_start_date' => '',
              'ad_end_date' => '',              
              'ad_schedule' => 0,
              'limit_hits' => 0,
              'hits_limit' => 0,
              'limit_clicks' => 0,
              'clicks_limit' => 0,
              'ad_hits' => 0,
              'ad_clicks' => 0,
              'ad_weight' => 10,
              'ad_weight_hits' => 0,
              'adv_nick' => '',
              'adv_name' => '',
              'adv_mail' => '',
              'cpm' => 0.0,
              'cpc' => 0.0,
              'per_month' => 0.0,
              'trash' => 0,
              'expired' => 0,
              'ad_custom_tax_terms' => 0,
              'view_custom_tax_terms' => '',
              'x_ad_custom_tax_terms' => 0,
              'x_view_custom_tax_terms' => ''
            );
            $aSize = array(
                'name' => __('Undefined', SAM_DOMAIN),
                'width' => __('Undefined', SAM_DOMAIN),
                'height' => __('Undefined', SAM_DOMAIN)
              );
          }
          ?>
<div class="wrap">
  <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
    <div class="icon32" style="background: url('<?php echo SAM_IMG_URL.'sam-editor.png'; ?>') no-repeat transparent; "><br></div>
    <h2><?php echo ( ( $action === 'new' ) ? __('New advertisement', SAM_DOMAIN) : __('Edit advertisement', SAM_DOMAIN).' ('.$item.')' ); ?></h2>
    <?php
      include_once('errors.class.php');
      $errors = new samErrors();
      if(!empty($errors->errorString)) echo $errors->errorString;
    ?>
    <div class="metabox-holder has-right-sidebar" id="poststuff">
      <div id="side-info-column" class="inner-sidebar">
        <div class="meta-box-sortables ui-sortable">
          <div id="submitdiv" class="postbox ">
            <div class="handlediv" title="<?php _e('Click to toggle', SAM_DOMAIN); ?>"><br></div>
            <h3 class="hndle"><span><?php _e('Status', SAM_DOMAIN);?></span></h3>
            <div class="inside">
              <div id="submitpost" class="submitbox">
                <div id="minor-publishing">
                  <div id="minor-publishing-actions">
                    <div id="save-action"> </div>
                    <div id="preview-action">
                      <a id="back-button" class="color-btn color-btn-left" href='<?php echo admin_url('admin.php'); ?>?page=sam-list&action=items&mode=active&item=<?php echo $row['pid'] ?>'>
                        <b style="background-color: #bcbcbc"></b>
                        <?php _e('Back to Ads List', SAM_DOMAIN) ?>
                      </a>
                    </div>
                    <div class="clear"></div>
                  </div>
                  <div id="misc-publishing-actions">
                    <div class="misc-pub-section">
                      <label for="item_id_info"><?php echo __('Advertisement ID', SAM_DOMAIN).':'; ?></label>
                      <span id="item_id_info" style="font-weight: bold;"><?php echo $row['id']; ?></span>
                      <input type="hidden" id="item_id" name="item_id" value="<?php echo $row['id']; ?>" >
                      <input type="hidden" id="place_id" name="place_id" value="<?php echo $row['pid']; ?>" >
                      <input type='hidden' name='editor_mode' id='editor_mode' value='item'>
                    </div>
                    <div class="misc-pub-section">
                      <label for="ad_weight_info"><?php echo __('Activity', SAM_DOMAIN).':'; ?></label>
                      <span id="ad_weight_info" style="font-weight: bold;"><?php echo (($row['ad_weight'] > 0) && !$row['trash'] && !$row['expired']) ? __('Ad is Active', SAM_DOMAIN) : __('Ad is Inactive', SAM_DOMAIN); ?></span><br>
                      <label for="ad_hits_info"><?php echo __('Hits', SAM_DOMAIN).':'; ?></label>
                      <span id="ad_hits_info" style="font-weight: bold;"><?php echo $row['ad_hits']; ?></span><br>
                      <label for="ad_clicks_info"><?php echo __('Clicks', SAM_DOMAIN).':'; ?></label>
                      <span id="ad_clicks_info" style="font-weight: bold;"><?php echo $row['ad_clicks']; ?></span>
                    </div>
                    <div class="misc-pub-section">
                      <label for="place_size_info"><?php echo __('Size', SAM_DOMAIN).':'; ?></label>
                      <span id="ad_size_info" class="post-status-display"><strong><?php echo $aSize['name']; ?></strong></span><br>
                      <label for="place_width"><?php echo __('Width', SAM_DOMAIN).':'; ?></label>
                      <span id="ad_width" class="post-status-display"><strong><?php echo $aSize['width']; ?></strong></span><br>
                      <label for="place_height"><?php echo __('Height', SAM_DOMAIN).':'; ?></label>
                      <span id="ad_height" class="post-status-display"><strong><?php echo $aSize['height']; ?></strong></span>
                    </div>
                    <div class="misc-pub-section">
                      <input type="radio" id="trash_no" value="false" name="trash" <?php checked(0, $row['trash'], true); ?> >
                      <label for="trash_no">  <?php _e('Is in Rotation', SAM_DOMAIN); ?></label><br>
                      <input type="radio" id="trash_yes" value="true" name="trash" <?php checked(1, $row['trash'], true); ?> >
                      <label for="trash_yes">  <?php _e('Is In Trash', SAM_DOMAIN); ?></label>
                    </div>
                  </div>
                  <div class="clear"></div>
                </div>
                <div id="major-publishing-actions">
                  <div id="delete-action">
                    <!--<a class="submitdelete deletion" href='<?php echo admin_url('admin.php'); ?>?page=sam-list&action=items&mode=active&item=<?php echo $row['pid'] ?>'><?php _e('Cancel', SAM_DOMAIN) ?></a>-->
                  </div>
                  <div id="publishing-action">
                    <a id="cancel-button" class="color-btn color-btn-left" href='<?php echo admin_url('admin.php'); ?>?page=sam-list&action=items&mode=active&item=<?php echo $row['pid'] ?>'>
                      <b style="background-color: #E5584A"></b>
                      <?php _e('Cancel', SAM_DOMAIN) ?>
                    </a>
                    <button id="submit-button" class="color-btn color-btn-left" name="update_item" type="submit">
                      <b style="background-color: #21759b"></b>
                      <?php _e('Save', SAM_DOMAIN) ?>
                    </button>
                    <!--<input type="submit" class='button-primary' name="update_item" value="<?php _e('Save', SAM_DOMAIN) ?>" >-->
                  </div>
                  <div class="clear"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div id="post-body">
        <div id="post-body-content">
          <div id="titlediv">
            <div id="titlewrap">
              <label class="screen-reader-text" for="title"><?php _e('Title', SAM_DOMAIN); ?></label>
              <input id="title" type="text" autocomplete="off" tabindex="1" size="30" name="item_name" value="<?php echo $row['name']; ?>" title="<?php echo __('Name of Ad', SAM_DOMAIN).'. '.__('Required for SAM widgets.', SAM_DOMAIN); ?>" >
            </div>
          </div>
          <div id="normal-sortables" class="meta-box-sortables ui-sortable">
            <div id="descdiv" class="postbox ">
              <div class="handlediv" title="<?php _e('Click to toggle', SAM_DOMAIN); ?>"><br></div>
              <h3 class="hndle"><span><?php _e('Advertisement Description', SAM_DOMAIN);?></span></h3>
              <div class="inside">
                <p>
                  <label for="item_description"><strong><?php echo __('Description', SAM_DOMAIN).':' ?></strong></label>
                  <textarea rows='3' id="item_description" class="code" tabindex="2" name="item_description" style="width:100%; height: 80px;" ><?php echo $row['description']; ?></textarea>
                </p>
                <p>
                  <?php _e('This description is not used anywhere and is added solely for the convenience of managing advertisements.', SAM_DOMAIN); ?>
                </p>
              </div>
            </div>
          </div>
          <div id="tabs">
            <ul>
              <li><a href="#tabs-1"><?php _e('General', SAM_DOMAIN); ?></a></li>
              <li><a href="#tabs-2"><?php _e('Extended Restrictions', SAM_DOMAIN); ?></a></li>
              <li><a href="#tabs-3"><?php _e('Targeting', SAM_DOMAIN); ?></a></li>
              <li><a href="#tabs-4"><?php _e('Earnings settings', SAM_DOMAIN); ?></a></li>
              <li><a href="#tabs-5"><?php _e('Statistic', SAM_DOMAIN); ?></a></li>
            </ul>
            <div id="tabs-1">
              <div id="sources" class="meta-box-sortables ui-sortable">
                <div id="codediv" class="postbox ">
                  <div class="handlediv" title="<?php _e('Click to toggle', SAM_DOMAIN); ?>"><br></div>
                  <h3 class="hndle"><span><?php _e('Ad Code', SAM_DOMAIN);?></span></h3>
                  <div class="inside">
                    <p>
                      <input type='radio' name='code_mode' id='code_mode_false' value='0' <?php checked(0, $row['code_mode']) ?>>
                      <label for='code_mode_false'><strong><?php _e('Image Mode', SAM_DOMAIN); ?></strong></label>
                    </p>
                    <div id="rc-cmf" class='radio-content' style="<?php if((int)$row['code_mode'] != 0) echo 'display: none;'; ?>">
                      <p>
                        <label for="ad_img"><strong><?php echo __('Ad Image', SAM_DOMAIN).':' ?></strong></label>
                        <input id="ad_img" class="code" type="text" tabindex="3" name="ad_img" value="<?php echo $row['ad_img']; ?>" style="width:100%" >
                        <input id="ad_img_id" name="ad_img_id" type="hidden" value="<?php echo 438/*$row['ad_img_id']*/; ?>">
                      </p>
                      <p>
                        <label for="ad_target"><strong><?php echo __('Ad Target', SAM_DOMAIN).':' ?></strong></label>
                        <input id="ad_target" class="code" type="text" tabindex="3" name="ad_target" value="<?php echo $row['ad_target']; ?>" style="width:100%" >
                      </p>
                      <p>
                        <label for="ad_alt"><strong><?php echo __('Ad Alternative Text', SAM_DOMAIN).':' ?></strong></label>
                        <input id="ad_alt" class="code" type="text" tabindex="3" name="ad_alt" value="<?php echo $row['ad_alt']; ?>" style="width:100%" >
                      </p>
                      <p>
                        <input type='checkbox' name='count_clicks' id='count_clicks' value='1' <?php checked(1, $row['count_clicks']); ?> >
                        <label for='count_clicks'><?php _e('Count clicks for this advertisement', SAM_DOMAIN); ?></label>
                      </p>
                      <!--<p><strong><?php _e('Use carefully!', SAM_DOMAIN) ?></strong> <?php _e("Do not use if the wp-admin folder is password protected. In this case the viewer will be prompted to enter a username and password during ajax request. It's not good.", SAM_DOMAIN) ?></p>-->
                      <p>
                        <input type="checkbox" name="ad_swf" id="ad_swf" value="1" <?php checked(1, $row['ad_swf']); ?> >
                        <label for="ad_swf"><?php _e('This is flash (SWF) banner', SAM_DOMAIN); ?></label>
                      </p>
                      <div id="swf-params" class="radio-content" style="<?php if((int)$row['ad_swf'] != 1) echo 'display: none;'; ?>">
                        <label for="ad_swf_flashvars"><strong><?php _e('Flash banner "flashvars"', SAM_DOMAIN) ?>:</strong></label>
                        <textarea type="text" name="ad_swf_flashvars" id="ad_swf_flashvars" rows="3" style="width:100%;"><?php echo $row['ad_swf_flashvars']; ?></textarea>
                        <p><?php _e('Insert "flashvars" parameters between braces...', SAM_DOMAIN); ?></p>
                        <label for="ad_swf_params"><strong><?php _e('Flash banner "params"', SAM_DOMAIN) ?>:</strong></label>
                        <textarea type="text" name="ad_swf_params" id="ad_swf_params" rows="3" style="width:100%;"><?php echo $row['ad_swf_params']; ?></textarea>
                        <p><?php _e('Insert "params" parameters between braces...', SAM_DOMAIN); ?></p>
                        <label for="ad_swf_attributes"><strong><?php _e('Flash banner "attributes"', SAM_DOMAIN) ?>:</strong></label>
                        <textarea type="text" name="ad_swf_attributes" id="ad_swf_attributes" rows="3" style="width:100%;"><?php echo $row['ad_swf_attributes']; ?></textarea>
                        <p><?php _e('Insert "attributes" parameters between braces...', SAM_DOMAIN); ?></p>
                      </div>
                      <p>
                        <label for='ad_no'><strong><?php echo __('Add to ad', SAM_DOMAIN).':'; ?></strong></label>
                        <select name='ad_no' id='ad_no' disabled='disabled'>
                          <option value='0' <?php selected(0, $row['ad_no']); ?>><?php _e('Non Selected', SAM_DOMAIN) ?></option>
                          <option value='1' <?php selected(1, $row['ad_no']); ?>><?php _e('nofollow', SAM_DOMAIN) ?></option>
                          <option value='2' <?php selected(2, $row['ad_no']); ?>><?php _e('noindex', SAM_DOMAIN) ?></option>
                          <option value='3' <?php selected(3, $row['ad_no']); ?>><?php _e('nofollow and noindex', SAM_DOMAIN) ?></option>
                        </select>
                      </p>
                      <div class="clear"></div>
                      <?php self::drawImageTools(); ?>
                    </div>
                    <div class='clear-line' ></div>
                    <p>
                      <input type='radio' name='code_mode' id='code_mode_true' value='1' <?php checked(1, $row['code_mode']) ?>>
                      <label for='code_mode_true'><strong><?php _e('Code Mode', SAM_DOMAIN); ?></strong></label>
                    </p>
                    <div id="rc-cmt" class='radio-content' style="<?php if((int)$row['code_mode'] != 1) echo 'display: none;'; ?>">
                      <p>
                        <label for="ad_code"><strong><?php echo __('Ad Code', SAM_DOMAIN).':'; ?></strong></label>
                        <textarea name='ad_code' id='ad_code' rows='10' title='Ad Code' style='width: 100%;'><?php echo $row['ad_code'] ?></textarea>
                        <input type='checkbox' name='code_type' id='code_type' value='1' <?php checked(1, $row['code_type']); ?>><label for='code_type' style='vertical-align: middle;'> <?php _e('This code of ad contains PHP script', SAM_DOMAIN); ?></label>
                      </p>
                    </div>
                  </div>
                </div>
              </div>
              <div id="codes" class="meta-box-sortables ui-sortable">
                <div id="codediv" class="postbox ">
                  <div class="handlediv" title="<?php _e('Click to toggle', SAM_DOMAIN); ?>"><br></div>
                  <h3 class="hndle"><span><?php _e('Restrictions of advertisements showing', SAM_DOMAIN);?></span></h3>
                  <div class="inside">
                    <p>
                      <label for='ad_weight'><strong><?php echo __('Ad Weight', SAM_DOMAIN).':' ?></strong></label>
                      <select name='ad_weight' id='ad_weight'>
                        <?php
                        for($i=0; $i <= 10; $i++) {
                        ?>
                          <option value='<?php echo $i; ?>' <?php selected($i, $row['ad_weight']); ?>>
                            <?php
                              if($i == 0) echo $i.' - '.__('Inactive', SAM_DOMAIN);
                              elseif($i == 1) echo $i.' - '.__('Minimal Activity', SAM_DOMAIN);
                              elseif($i == 10) echo $i.' - '.__('Maximal Activity', SAM_DOMAIN);
                              else echo $i;
                            ?>
                          </option>
                          <?php
                        }
                        ?>
                      </select>
                    </p>
                    <p>
                      <?php _e('Ad weight - coefficient of frequency of show of the advertisement for one cycle of advertisements rotation.', SAM_DOMAIN); ?>
                      <ul>
                        <li><?php _e('0 - ad is inactive', SAM_DOMAIN); ?></li>
                        <li><?php _e('1 - minimal activity of this advertisement', SAM_DOMAIN); ?></li>
                        <li>...</li>
                        <li><?php _e('10 - maximal activity of this ad.', SAM_DOMAIN); ?></li>
                      </ul>
                    </p>
                    <div class='clear-line'></div>
                    <p>
                      <input type='radio' name='view_type' id='view_type_1' value='1' <?php checked(1, $row['view_type']); ?>>
                      <label for='view_type_1'><strong><?php _e('Show ad on all pages of blog', SAM_DOMAIN); ?></strong></label>
                    </p>
                    <p>
                      <input type='radio' name='view_type' id='view_type_0' value='0' <?php checked(0, $row['view_type']); ?>>
                      <label for='view_type_0'><strong><?php echo __('Show ad only on pages of this type', SAM_DOMAIN).':'; ?></strong></label>
                    </p>
                    <div id="rc-vt0" class='radio-content' style="<?php if((int)$row['view_type'] != 0) echo 'display: none;'; ?>">
                      <input type='checkbox' name='is_home' id='is_home' value='<?php echo SAM_IS_HOME; ?>' <?php checked(1, $this->checkViewPages($row['view_pages'], SAM_IS_HOME)); ?>>
                      <label for='is_home'><?php _e('Home Page (Home or Front Page)', SAM_DOMAIN); ?></label><br>
                      <input type='checkbox' name='is_singular' id='is_singular' value='<?php echo SAM_IS_SINGULAR; ?>' <?php checked(1, $this->checkViewPages($row['view_pages'], SAM_IS_SINGULAR)); ?>>
                      <label for='is_singular'><?php _e('Singular Pages', SAM_DOMAIN); ?></label><br>
                      <div class='radio-content'>
                        <input type='checkbox' name='is_single' id='is_single' value='<?php echo SAM_IS_SINGLE; ?>' <?php checked(1, $this->checkViewPages($row['view_pages'], SAM_IS_SINGLE)); ?>>
                        <label for='is_single'><?php _e('Single Post', SAM_DOMAIN); ?></label><br>
                        <input type='checkbox' name='is_page' id='is_page' value='<?php echo SAM_IS_PAGE; ?>' <?php checked(1, $this->checkViewPages($row['view_pages'], SAM_IS_PAGE)); ?>>
                        <label for='is_page'><?php _e('Page', SAM_DOMAIN); ?></label><br>
                        <input type='checkbox' name='is_posttype' id='is_posttype' value='<?php echo SAM_IS_POST_TYPE; ?>' <?php checked(1, $this->checkViewPages($row['view_pages'], SAM_IS_POST_TYPE)); ?>>
                        <label for='is_posttype'><?php _e('Custom Post Type', SAM_DOMAIN); ?></label><br>
                        <input type='checkbox' name='is_attachment' id='is_attachment' value='<?php echo SAM_IS_ATTACHMENT; ?>' <?php checked(1, $this->checkViewPages($row['view_pages'], SAM_IS_ATTACHMENT)); ?>>
                        <label for='is_attachment'><?php _e('Attachment', SAM_DOMAIN); ?></label><br>
                      </div>
                      <input type='checkbox' name='is_search' id='is_search' value='<?php echo SAM_IS_SEARCH; ?>' <?php checked(1, $this->checkViewPages($row['view_pages'], SAM_IS_SEARCH)); ?>>
                      <label for='is_search'><?php _e('Search Page', SAM_DOMAIN); ?></label><br>
                      <input type='checkbox' name='is_404' id='is_404' value='<?php echo SAM_IS_404; ?>' <?php checked(1, $this->checkViewPages($row['view_pages'], SAM_IS_404)); ?>>
                      <label for='is_404'><?php _e('"Not found" Page (HTTP 404: Not Found)', SAM_DOMAIN); ?></label><br>
                      <input type='checkbox' name='is_archive' id='is_archive' value='<?php echo SAM_IS_ARCHIVE; ?>' <?php checked(1, $this->checkViewPages($row['view_pages'], SAM_IS_ARCHIVE)); ?>>
                      <label for='is_archive'><?php _e('Archive Pages', SAM_DOMAIN); ?></label><br>
                      <div class='radio-content'>
                        <input type='checkbox' name='is_tax' id='is_tax' value='<?php echo SAM_IS_TAX; ?>' <?php checked(1, $this->checkViewPages($row['view_pages'], SAM_IS_TAX)); ?>>
                        <label for='is_tax'><?php _e('Taxonomy Archive Pages', SAM_DOMAIN); ?></label><br>
                        <input type='checkbox' name='is_category' id='is_category' value='<?php echo SAM_IS_CATEGORY; ?>' <?php checked(1, $this->checkViewPages($row['view_pages'], SAM_IS_CATEGORY)); ?>>
                        <label for='is_category'><?php _e('Category Archive Pages', SAM_DOMAIN); ?></label><br>
                        <input type='checkbox' name='is_tag' id='is_tag' value='<?php echo SAM_IS_TAG; ?>' <?php checked(1, $this->checkViewPages($row['view_pages'], SAM_IS_TAG)); ?>>
                        <label for='is_tag'><?php _e('Tag Archive Pages', SAM_DOMAIN); ?></label><br>
                        <input type='checkbox' name='is_author' id='is_author' value='<?php echo SAM_IS_AUTHOR; ?>' <?php checked(1, $this->checkViewPages($row['view_pages'], SAM_IS_AUTHOR)); ?>>
                        <label for='is_author'><?php _e('Author Archive Pages', SAM_DOMAIN); ?></label><br>
                        <input type='checkbox' name='is_posttype_archive' id='is_posttype_archive' value='<?php echo SAM_IS_POST_TYPE_ARCHIVE; ?>' <?php checked(1, $this->checkViewPages($row['view_pages'], SAM_IS_POST_TYPE_ARCHIVE)); ?>>
                        <label for='is_posttype_archive'><?php _e('Custom Post Type Archive Pages', SAM_DOMAIN); ?></label><br>
                        <input type='checkbox' name='is_date' id='is_date' value='<?php echo SAM_IS_DATE; ?>' <?php checked(1, $this->checkViewPages($row['view_pages'], SAM_IS_DATE)); ?>>
                        <label for='is_date'><?php _e('Date Archive Pages (any date-based archive pages, i.e. a monthly, yearly, daily or time-based archive)', SAM_DOMAIN); ?></label><br>
                      </div>
                    </div>
                    <p>
                      <input type='radio' name='view_type' id='view_type_2' value='2' <?php checked(2, $row['view_type']); ?>>
                      <label for='view_type_2'><strong><?php echo __('Show ad only in certain posts/pages', SAM_DOMAIN).':'; ?></strong></label>
                    </p>
                    <div id="rc-vt2" class='radio-content' style="<?php if((int)$row['view_type'] != 2) echo 'display: none;'; ?>">
                      <p>
                        <strong><?php echo __('Posts/Pages', SAM_DOMAIN).':'; ?></strong>
                        <input type='hidden' name='view_id' id='view_id' value='<?php echo $row['view_id']; ?>'>
                      </p>
                      <div>
                        <div id="posts-grid"></div>
                      </div>
                      <p>
                        <?php _e('Use this setting to display an ad only in certain posts/pages. Select posts/pages.', SAM_DOMAIN); ?>
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div id="tabs-2">
              <div id="xlimits" class="meta-box-sortables ui-sortable">

                <div id="limitsdiv" class="postbox ">
                  <div class="handlediv" title="<?php _e('Click to toggle', SAM_DOMAIN); ?>"><br></div>
                  <h3 class="hndle"><span><?php _e('Extended restrictions of advertisements showing', SAM_DOMAIN);?></span></h3>
                  <div class="inside">
                    <p>
                      <input type='checkbox' name='x_id' id='x_id' value='1' <?php checked(1, $row['x_id']); ?>>
                      <label for='x_id'><strong><?php echo __('Do not show ad on certain posts/pages', SAM_DOMAIN).':'; ?></strong></label>
                    </p>
                    <div id="rc-xid" class='radio-content' style="<?php if((int)$row['x_id'] != 1) echo 'display: none;'; ?>">
                      <p>
                        <strong><?php echo __('Posts/Pages', SAM_DOMAIN).':'; ?></strong>
                        <input type='hidden' name='x_view_id' id='x_view_id' value="<?php echo $row['x_view_id']; ?>" >
                      </p>
                      <div>
                        <div id="x-posts-grid"></div>
                      </div>
                    </div>
                    <p>
                      <?php _e('Use this setting to not display an ad on certain posts/pages. Select posts/pages.', SAM_DOMAIN); ?>
                    </p>
                    <div class='clear-line'></div>
                    <p>
                      <input type='checkbox' name='ad_cats' id='ad_cats' value='1' <?php checked(1, $row['ad_cats']); ?>>
                      <label for='ad_cats'><strong><?php echo __('Show ad only in single posts or categories archives of certain categories', SAM_DOMAIN).':'; ?></strong></label>
                    </p>
                    <div id="rc-ac" class='radio-content' style="<?php if((int)$row['ad_cats'] != 1) echo 'display: none;'; ?>">
                      <p><strong><?php echo __('Categories', SAM_DOMAIN).':'; ?></strong></p>
                      <input type='hidden' name='view_cats' id='view_cats' value="<?php echo $row['view_cats']; ?>" >
                      <div>
                        <div id="cats-grid"></div>
                      </div>
                    </div>
                    <p>
                      <?php _e('Use this setting to display an ad only in single posts or categories archives of certain categories.', SAM_DOMAIN); ?>
                    </p>
                    <div id="acw" class='sam-warning' style="<?php if((int)$row['ad_cats'] != 1) echo 'display: none;'; ?>">
                      <p>
                        <?php _e('This display logic parameter will be applied only when you use the "Show ad on all pages of blog" and "Show your ad only on the pages of this type" modes. Otherwise, it will be ignored.', SAM_DOMAIN); ?>
                      </p>
                    </div>
                    <div class='clear-line'></div>
                    <p>
                      <input type='checkbox' name='x_cats' id='x_cats' value='1' <?php checked(1, $row['x_cats']); ?>>
                      <label for='x_cats'><strong><?php echo __('Do not show ad in single posts or categories archives of certain categories', SAM_DOMAIN).':'; ?></strong></label>
                    </p>
                    <div id="rc-xc" class='radio-content' style="<?php if((int)$row['x_cats'] != 1) echo 'display: none;'; ?>">
                      <p><strong><?php echo __('Categories', SAM_DOMAIN).':'; ?></strong></p>
                      <input type='hidden' name='x_view_cats' id='x_view_cats' value="<?php echo $row['x_view_cats']; ?>" >
                      <div>
                        <div id="x-cats-grid"></div>
                      </div>
                    </div>
                    <p>
                      <?php _e('Use this setting to not display an ad in single posts or categories archives of certain categories.', SAM_DOMAIN); ?>
                    </p>
                    <div class='clear-line'></div>
                    <p>
                      <input type="checkbox" name="ad_custom_tax_terms" id="ad_custom_tax_terms" value="1"  <?php checked(1, $row['ad_custom_tax_terms']); ?>>
                      <label for="ad_custom_tax_terms"><strong><?php echo __('Show ad only in single posts or archives of certain Custom Taxonomies Terms', SAM_DOMAIN).':'; ?></strong></label>
                    </p>
                    <div id="rc-ctt" class="radio-content" style="<?php if((int)$row['ad_custom_tax_terms'] != 1) echo 'display: none;'; ?>">
                      <input type="hidden" id="view_custom_tax_terms" name="view_custom_tax_terms" value="<?php echo $row['view_custom_tax_terms']; ?>">
                      <div>
                        <div id="ctt-grid"></div>
                      </div>
                    </div>
                    <p>
                      <?php _e('Use this setting to display an ad only in single posts or archives of certain Custom Taxonomies Terms.', SAM_DOMAIN); ?>
                    </p>
                    <div id="cttw" class='sam-warning' style="<?php if((int)$row['ad_custom_tax_terms'] != 1) echo 'display: none;'; ?>">
                      <p>
                        <?php _e('This display logic parameter will be applied only when you use the "Show ad on all pages of blog" and "Show your ad only on the pages of this type" modes. Otherwise, it will be ignored.', SAM_DOMAIN); ?>
                      </p>
                    </div>
                    <div class='clear-line'></div>
                    <p>
                      <input type="checkbox" name="x_ad_custom_tax_terms" id="x_ad_custom_tax_terms" value="1"  <?php checked(1, $row['x_ad_custom_tax_terms']); ?>>
                      <label for="x_ad_custom_tax_terms"><strong><?php echo __('Do not show ad in single posts or archives of certain Custom Taxonomies Terms', SAM_DOMAIN).':'; ?></strong></label>
                    </p>
                    <div id="rc-xct" class="radio-content" style="<?php if((int)$row['x_ad_custom_tax_terms'] != 1) echo 'display: none;'; ?>">
                      <input type="hidden" id="x_view_custom_tax_terms" name="x_view_custom_tax_terms" value="<?php echo $row['x_view_custom_tax_terms']; ?>">
                      <div>
                        <div id="x-ctt-grid"></div>
                      </div>
                    </div>
                    <p>
                      <?php _e('Use this setting to not display an ad only in single posts or archives of certain Custom Taxonomies Terms.', SAM_DOMAIN); ?>
                    </p>
                    <div class='clear-line'></div>
                    <p>
                      <input type='checkbox' name='ad_authors' id='ad_authors' value='1' <?php checked(1, $row['ad_authors']); ?>>
                      <label for='ad_authors'><strong><?php echo __('Show ad only in single posts or authors archives of certain authors', SAM_DOMAIN).':'; ?></strong></label>
                    </p>
                    <div id="rc-au" class='radio-content' style="<?php if((int)$row['ad_authors'] != 1) echo 'display: none;'; ?>">
                      <p><strong><?php echo __('Authors', SAM_DOMAIN).':'; ?></strong></p>
                      <input type='hidden' name='view_authors' id='view_authors' value="<?php echo $row['view_authors']; ?>" >
                      <div>
                        <div id="auth-grid"></div>
                      </div>
                    </div>
                    <p>
                      <?php _e('Use this setting to display an ad only in single posts or authors archives of certain authors.', SAM_DOMAIN); ?>
                    </p>
                    <div id="aaw" class='sam-warning' style="<?php if((int)$row['ad_authors'] != 1) echo 'display: none;'; ?>">
                      <p>
                        <?php _e('This display logic parameter will be applied only when you use the "Show ad on all pages of blog" and "Show your ad only on the pages of this type" modes. Otherwise, it will be ignored.', SAM_DOMAIN); ?>
                      </p>
                    </div>
                    <div class='clear-line'></div>
                    <p>
                      <input type='checkbox' name='x_authors' id='x_authors' value='1' <?php checked(1, $row['x_authors']); ?>>
                      <label for='x_authors'><strong><?php echo __('Do not show ad in single posts or authors archives of certain authors', SAM_DOMAIN).':'; ?></strong></label>
                    </p>
                    <div id="rc-xa" class='radio-content' style="<?php if((int)$row['x_authors'] != 1) echo 'display: none;'; ?>">
                      <p><strong><?php echo __('Authors', SAM_DOMAIN).':'; ?></strong></p>
                      <input type='hidden' name='x_view_authors' id='x_view_authors' value="<?php echo $row['x_view_authors']; ?>" >
                      <div>
                        <div id="x-auth-grid"></div>
                      </div>
                    </div>
                    <p>
                      <?php _e('Use this setting to not display an ad in single posts or authors archives of certain authors.', SAM_DOMAIN); ?>
                    </p>
                    <div class='clear-line'></div>
                    <p>
                      <input type='checkbox' name='ad_tags' id='ad_tags' value='1' <?php checked(1, $row['ad_tags']); ?>>
                      <label for='ad_tags'><strong><?php echo __('Show ad only in single posts or tags archives of certain tags', SAM_DOMAIN).':'; ?></strong></label>
                    </p>
                    <div id="rc-at" class='radio-content' style="<?php if((int)$row['ad_tags'] != 1) echo 'display: none;'; ?>">
                      <p><strong><?php echo __('Tags', SAM_DOMAIN).':'; ?></strong></p>
                      <input type='hidden' name='view_tags' id='view_tags' value="<?php echo $row['view_tags']; ?>" >
                      <div>
                        <div id="tags-grid"></div>
                      </div>
                    </div>
                    <p>
                      <?php _e('Use this setting to display an ad only in single posts or tags archives of certain tags.', SAM_DOMAIN); ?>
                    </p>
                    <div id="atw" class='sam-warning' style="<?php if((int)$row['ad_tags'] != 1) echo 'display: none;'; ?>">
                      <p>
                        <?php _e('This display logic parameter will be applied only when you use the "Show ad on all pages of blog" and "Show your ad only on the pages of this type" modes. Otherwise, it will be ignored.', SAM_DOMAIN); ?>
                      </p>
                    </div>
                    <div class='clear-line'></div>
                    <p>
                      <input type='checkbox' name='x_tags' id='x_tags' value='1' <?php checked(1, $row['x_tags']); ?>>
                      <label for='x_tags'><strong><?php echo __('Do not show ad in single posts or tags archives of certain tags', SAM_DOMAIN).':'; ?></strong></label>
                    </p>
                    <div id="rc-xt" class='radio-content' style="<?php if((int)$row['x_tags'] != 1) echo 'display: none;'; ?>">
                      <p><strong><?php echo __('Tags', SAM_DOMAIN).':'; ?></strong></p>
                      <input type='hidden' name='x_view_tags' id='x_view_tags' value="<?php echo $row['x_view_tags']; ?>" >
                      <div>
                        <div id="x-tags-grid"></div>
                      </div>
                    </div>
                    <p>
                      <?php _e('Use this setting to not display an ad in single posts or tags archives of certain tags.', SAM_DOMAIN); ?>
                    </p>
                    <div class='clear-line'></div>
                    <p>
                      <input type='checkbox' name='ad_custom' id='ad_custom' value='1' <?php checked(1, $row['ad_custom']); ?>>
                      <label for='ad_custom'><strong><?php echo __('Show ad only in custom type single posts or custom post type archives of certain custom post types', SAM_DOMAIN).':'; ?></strong></label>
                    </p>
                    <div id="rc-cu" class='radio-content' style="<?php if((int)$row['ad_custom'] != 1) echo 'display: none;'; ?>">
                      <p><strong><?php echo __('Custom post types', SAM_DOMAIN).':'; ?></strong></p>
                      <input type='hidden' name='view_custom' id='view_custom' value="<?php echo $row['view_custom']; ?>" >
                      <div>
                        <div id="cust-grid"></div>
                      </div>
                    </div>
                    <p>
                      <?php _e('Use this setting to display an ad only in custom type single posts or custom post type archives of certain custom post types.', SAM_DOMAIN); ?>
                    </p>
                    <div id="cuw" class='sam-warning' style="<?php if((int)$row['ad_custom'] != 1) echo 'display: none;'; ?>">
                      <p>
                        <?php _e('This display logic parameter will be applied only when you use the "Show ad on all pages of blog" and "Show your ad only on the pages of this type" modes. Otherwise, it will be ignored.', SAM_DOMAIN); ?>
                      </p>
                    </div>
                    <div class='clear-line'></div>
                    <p>
                      <input type='checkbox' name='x_custom' id='x_custom' value='1' <?php checked(1, $row['x_custom']); ?>>
                      <label for='x_custom'><strong><?php echo __('Do not show ad in custom type single posts or custom post type archives of certain custom post types', SAM_DOMAIN).':'; ?></strong></label>
                    </p>
                    <div id="rc-xu" class='radio-content' style="<?php if((int)$row['x_custom'] != 1) echo 'display: none;'; ?>">
                      <p><strong><?php echo __('Custom post types', SAM_DOMAIN).':'; ?></strong></p>
                      <input type='hidden' name='x_view_custom' id='x_view_custom' value="<?php echo $row['x_view_custom']; ?>" >
                      <div>
                        <div id="x-cust-grid"></div>
                      </div>
                    </div>
                    <p>
                      <?php _e('Use this setting to not display an ad in custom type single posts or custom post type archives of certain custom post types.', SAM_DOMAIN); ?>
                    </p>
                    <div class='clear-line'></div>
                    <p>
                      <input type='checkbox' name='ad_schedule' id='ad_schedule' value='1' <?php checked(1, $row['ad_schedule']); ?>>
                      <label for='ad_schedule'><strong><?php _e('Use the schedule for this ad', SAM_DOMAIN); ?></strong></label>
                    </p>
                    <div id="rc-sc" class="radio-content" style="<?php if((int)$row['ad_schedule'] != 1) echo 'display: none;'; ?>">
                      <p>
                        <label for='ad_start_date'><?php echo __('Campaign Start Date', SAM_DOMAIN).':' ?></label>
                        <input type='text' name='ad_start_date' id='ad_start_date' value='<?php echo $row['ad_start_date']; ?>'>
                      </p>
                      <p>
                        <label for='ad_end_date'><?php echo __('Campaign End Date', SAM_DOMAIN).':' ?></label>
                        <input type='text' name='ad_end_date' id='ad_end_date' value='<?php echo $row['ad_end_date']; ?>'>
                      </p>
                    </div>
                    <p>
                      <?php _e('Use these parameters for displaying ad during the certain period of time.', SAM_DOMAIN); ?>
                    </p>
                    <div class='clear-line'></div>
                    <p>
                      <input type='checkbox' name='limit_hits' id='limit_hits' value='1' <?php checked(1, $row['limit_hits']); ?>>
                      <label for='limit_hits'><strong><?php _e('Use limitation by hits', SAM_DOMAIN); ?></strong></label>
                    </p>
                    <div id="rc-hl" class="radio-content" style="<?php if((int)$row['limit_hits'] != 1) echo 'display: none;'; ?>">
                      <p>
                        <label for='hits_limit'><?php echo __('Hits Limit', SAM_DOMAIN).':' ?></label>
                        <input type='text' name='hits_limit' id='hits_limit' value='<?php echo $row['hits_limit']; ?>'>
                      </p>
                    </div>
                    <p>
                      <?php _e('Use this parameter for limiting displaying of ad by hits.', SAM_DOMAIN); ?>
                    </p>
                    <div class='clear-line'></div>
                    <p>
                      <input type='checkbox' name='limit_clicks' id='limit_clicks' value='1' <?php checked(1, $row['limit_clicks']); ?>>
                      <label for='limit_clicks'><strong><?php _e('Use limitation by clicks', SAM_DOMAIN); ?></strong></label>
                    </p>
                    <div id="rc-cl" class="radio-content" style="<?php if((int)$row['limit_clicks'] != 1) echo 'display: none;'; ?>">
                      <p>
                        <label for='clicks_limit'><?php echo __('Clicks Limit', SAM_DOMAIN).':' ?></label>
                        <input type='text' name='clicks_limit' id='clicks_limit' value='<?php echo $row['clicks_limit']; ?>'>
                      </p>
                    </div>
                    <p>
                      <?php _e('Use this parameter for limiting displaying of ad by clicks.', SAM_DOMAIN); ?>
                    </p>
                  </div>
                </div>
              </div>
            </div>
            <div id="tabs-3">
              <div id="targeting" class="meta-box-sortables ui-sortable">
                <div id="limitsusr" class="postbox">
                  <div class="handlediv" title="<?php _e('Click to toggle', SAM_DOMAIN); ?>"><br></div>
                  <h3 class="hndle"><span><?php _e('Users', SAM_DOMAIN);?></span></h3>
                  <div class="inside">
                    <p><strong><?php echo __('Show this ad for', SAM_DOMAIN).':'; ?></strong></p>
                    <p>
                      <input type="radio" name="ad_users" id="ad_users_0" value="0" <?php checked(0, $row['ad_users']); ?> >
                      <label for="ad_users_0"><strong><?php _e('all users', SAM_DOMAIN); ?></strong></label>
                    </p>
                    <div class="clear-line"></div>
                    <p>
                      <input type="radio" name="ad_users" id="ad_users_1" value="1" <?php checked(1, $row['ad_users']); ?> >
                      <label for="ad_users_1"><strong><?php _e('these users', SAM_DOMAIN); ?></strong></label>
                    </p>
                    <div id="custom-users" class="radio-content" style="<?php if((int)$row['ad_users'] != 1) echo 'display: none;'; ?>">
                      <p>
                        <input type="checkbox" name="ad_users_unreg" id="ad_users_unreg" value="1" <?php checked(1, $row['ad_users_unreg']); ?> >
                        <label for="ad_users_unreg"><strong><?php _e('Unregistered Users', SAM_DOMAIN); ?></strong></label>
                      </p>
                      <p>
                        <input type="checkbox" name="ad_users_reg" id="ad_users_reg" value="1" <?php checked(1, $row['ad_users_reg']); ?> >
                        <label for="ad_users_reg"><strong><?php _e('Registered Users', SAM_DOMAIN) ?></strong></label>
                      </p>
                      <div id="x-reg-users" class="radio-content" style="<?php if((int)$row['ad_users_reg'] != 1) echo 'display: none;'; ?>">
                        <p>
                          <input type="checkbox" name="x_ad_users" id="x_ad_users" value="1" <?php checked(1, $row['x_ad_users']) ?> >
                          <label for="x_ad_users"><strong><?php _e('Exclude these users', SAM_DOMAIN) ?></strong></label>
                        </p>
                        <div id="x-view-users" class="radio-content" style="<?php if((int)$row['x_ad_users'] != 1) echo 'display: none;'; ?>">
                          <strong><?php echo __('Registered Users', SAM_DOMAIN).':'; ?></strong>
                          <input type="hidden" name="x_view_users" id="x_view_users" value="<?php echo $row['x_view_users'] ?>" >
                          <div>
                            <div id="users-grid"></div>
                          </div>
                        </div>
                        <p>
                          <input type="checkbox" name="ad_users_adv" id="ad_users_adv" value="1" <?php checked(1, $row['ad_users_adv']); ?> >
                          <label for="ad_users_adv"><strong><?php _e('Do not show this ad for advertiser', SAM_DOMAIN) ?></strong></label>
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div id="tabs-4">
              <div id="advertiser" class="meta-box-sortables ui-sortable">
                <div id="advdiv" class="postbox">
                  <div class="handlediv" title="<?php _e('Click to toggle', SAM_DOMAIN); ?>"><br></div>
                  <h3 class="hndle"><span><?php _e('Advertiser', SAM_DOMAIN); ?></span></h3>
                  <div class="inside">
                    <p>
                      <label for="adv_nick"><strong><?php echo __('Advertiser Nick Name', SAM_DOMAIN).':' ?></strong></label>
                      <input type="text" name="adv_nick" id="adv_nick" value="<?php echo $row['adv_nick'] ?>" style="width: 250px;">
                    </p>
                    <p>
                      <label for="adv_name"><strong><?php echo __('Advertiser Name', SAM_DOMAIN).':' ?></strong></label>
                      <input type="text" name="adv_name" id="adv_name" value="<?php echo $row['adv_name'] ?>" style="width: 250px;">
                    </p>
                    <p>
                      <label for="adv_mail"><strong><?php echo __('Advertiser e-mail', SAM_DOMAIN).':' ?></strong></label>
                      <input type="text" name="adv_mail" id="adv_mail" value="<?php echo $row['adv_mail'] ?>" style="width: 250px;">
                    </p>
                  </div>
                </div>
              </div>
              <div id="prices" class="meta-box-sortables ui-sortable">
                <div id="pricesdiv" class="postbox ">
                  <div class="handlediv" title="<?php _e('Click to toggle', SAM_DOMAIN); ?>"><br></div>
                  <h3 class="hndle"><span><?php _e('Prices', SAM_DOMAIN);?></span></h3>
                  <div class="inside">
                    <p>
                      <label for='per_month'><strong><?php echo __('Price of ad placement per month', SAM_DOMAIN).':' ?></strong></label>
                      <input type='text' name='per_month' id='per_month' value='<?php echo $row['per_month']; ?>'>
                    </p>
                    <p>
                      <?php _e('Tthis parameter used only for scheduled ads.', SAM_DOMAIN) ?>
                    </p>
                    <p>
                      <label for='cpm'><strong><?php echo __('Price per Thousand Hits', SAM_DOMAIN).':' ?></strong></label>
                      <input type='text' name='cpm' id='cpm' value='<?php echo $row['cpm']; ?>'>
                    </p>
                    <p>
                      <?php _e('Not only humans visit your blog, bots and crawlers too. In order not to deceive an advertiser, you must enable the detection of bots and crawlers.', SAM_DOMAIN); ?>
                    </p>
                    <p>
                      <label for='cpc'><strong><?php echo __('Price per Click', SAM_DOMAIN).':' ?></strong></label>
                      <input type='text' name='cpc' id='cpc' value='<?php echo $row['cpc']; ?>'>
                    </p>
                    <p>
                      <?php _e('To calculate the earnings on clicks, you must enable counting of clicks for that ad.', SAM_DOMAIN); ?>
                    </p>
                  </div>
                </div>
              </div>
            </div>
            <div id="tabs-5">
              <div id="stats" class="meta-box-sortables ui-sortable">
                <?php if($action != 'new') { ?>
                <p class="totals">
                  <?php
                  $now = getdate();
                  $thisMonth = $now['mon'];
                  $thisYear = $now['year'];
                  $prevMonth = ($thisMonth == 1) ? 12 : $thisMonth - 1;
                  $prevYear = ($thisMonth == 1) ? $thisYear - 1 : $thisYear;

                  ?>
                  <label for="stats_month"><?php echo __('Select Period', SAM_DOMAIN) . ': '; ?></label>
                  <select id="stats_month">
                    <option value="0"><?php echo __('This Month', SAM_DOMAIN) . ' (' . self::getMonthName($thisMonth) . ", $thisYear" . ')'; ?></option>
                    <option value="1"><?php echo __('Previous Month', SAM_DOMAIN) . ' (' . self::getMonthName($prevMonth) . ", $prevYear" . ')'; ?></option>
                  </select>
                </p>
                <div class="graph-container">
                  <div id="graph" style="width: 100%; height: 300px;"></div>
                </div>
                <p class="totals">
                  <strong><?php echo __('Total', SAM_DOMAIN) . ':'; ?></strong><br>
                  <?php _e('Hits', SAM_DOMAIN); ?>: <span id="total_hits"></span><br>
                  <?php _e('Clicks', SAM_DOMAIN); ?>: <span id="total_clicks"></span><br>
                </p>
                <?php } ?>
              </div>
            </div>
          </div>
          <?php if($action !== 'new') { ?>
          <div id="sources" class="meta-box-sortables ui-sortable">
            <div id="previewdiv" class="postbox ">
              <div class="handlediv" title="<?php _e('Click to toggle', SAM_DOMAIN); ?>"><br></div>
              <h3 class="hndle"><span><?php _e('Ad Preview', SAM_DOMAIN);?></span></h3>
              <div class="inside">
                <div class='ad-example'>
                  <?php 
                    $sample = new SamAd(array('id' => (integer) $row['id']), true, true);
                    echo $sample->ad; 
                  ?>
                </div>
              </div>
            </div>
          </div>
          <?php } ?>
        </div>
      </div>
    </div>
  </form>
</div>          
          <?php
          break;
          
      }
    }
  }
}
?>
