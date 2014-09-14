<?php
if(!class_exists('SamZoneEditor')) {
  class SamZoneEditor {
    private $settings = array();
    
    public function __construct($settings) {
      $this->settings = $settings;
    }
    
    private function drawPlacesSelector($places = null, $current = -1, $default = false) {
      if(!is_null($places) && is_array($places)) {      
        if(is_null($current) && !$default) $current = -1;
        if(!$default) {
          ?>
            <option value="-1" <?php selected(-1, $current); ?> ><?php echo ' - '.__('Default', SAM_DOMAIN).' - '; ?></option>
            <option value="0" <?php selected(0, $current); ?> ><?php echo ' - '.__('None', SAM_DOMAIN).' - '; ?></option>
          <?php
        }
        foreach($places as $value) {
          ?>
            <option value="<?php echo $value['id']; ?>" <?php selected($value['id'], $current); ?> ><?php echo $value['name']; ?></option>
          <?php
        }
      }
    }
    
    private function getCustomPostTypes() {
      $args = array('public' => true, '_builtin' => false);
      $output = 'objects';
      $operator = 'and';
      $post_types = get_post_types($args, $output, $operator);
      
      return $post_types;
    }
    
    private function getTaxes($type = 'category') {
      if(empty($type)) return;

      if($type === 'custom_tax_terms') $wc = "NOT FIND_IN_SET(wtt.taxonomy, 'category,post_tag,nav_menu,link_category,post_format')";
      else $wc = "wtt.taxonomy = '$type'";
      
      global $wpdb;
      $tTable = $wpdb->prefix . "terms";
      $ttTable = $wpdb->prefix . "term_taxonomy";
      
      $sql = "SELECT
                wt.term_id,
                wt.name,
                wt.slug,
                wtt.taxonomy
              FROM
                $tTable wt
              INNER JOIN $ttTable wtt
                ON wt.term_id = wtt.term_id
              WHERE
                $wc AND wt.term_id <> 1;";
      
      $taxonomies = $wpdb->get_results($sql, ARRAY_A);
      
      $output = array();
      foreach($taxonomies as $tax) {
        array_push($output, array('name' => $tax['name'], 'slug' => $tax['slug'], 'tax' => $tax['taxonomy']));
      }
      return $output;
    }
    
    private function getAuthors() {
      global $wpdb;
      $uTable = $wpdb->base_prefix . "users";
      $umTable = $wpdb->base_prefix . "usermeta";
      $userLevel = $wpdb->base_prefix . 'user_level';
      
      $sql = "SELECT
                wu.id,
                wu.user_nicename,
                wu.display_name
              FROM
                $uTable wu
              INNER JOIN $umTable wum
                ON wu.ID = wum.user_id
              WHERE
                wum.meta_key = '$userLevel' AND
                wum.meta_value > 1;";
                
      $auth = $wpdb->get_results($sql, ARRAY_A);
      $authors = array();
      foreach($auth as $value) $authors[$value['display_name']] = $value['id'];
      
      return $authors;
    }
    
    public function page() {
      global $wpdb;
      $zTable = $wpdb->prefix . "sam_zones";
      $pTable = $wpdb->prefix . "sam_places";
      
      $options = $this->settings;
      $taxes = self::getTaxes('custom_tax_terms');
      $cats = self::getTaxes();
      $authors = self::getAuthors();
      $customs = self::getCustomPostTypes();

      $uTaxes = array();
      $uCats = array();
      $uAuthors = array();
      $uSingleCT = array();
      $uArchiveCT = array();
      
      if(isset($_GET['action'])) $action = $_GET['action'];
      else $action = 'new';
      if(isset($_GET['mode'])) $mode = $_GET['mode'];
      else $mode = 'zone';
      if(isset($_GET['item'])) $item = $_GET['item'];
      else $item = null;
      if(isset($_GET['zone'])) $zone = $_GET['zone'];
      else $zone = null;
      
      $updated = false;
          
      if(isset($_POST['update_zone'])) {
        $zoneId = $_POST['zone_id'];
        foreach($taxes as $tax) {
          if(isset($_POST['z_taxes_'.$tax['slug']])) {
            $value = (integer) $_POST['z_taxes_'.$tax['slug']];
            $uTaxes[$tax['slug']] = array('id' => $value, 'tax' => $tax['tax']);
          }
        }
        foreach($cats as $cat) {
          if(isset($_POST['z_cats_'.$cat['slug']])) {
            $value = (integer) $_POST['z_cats_'.$cat['slug']];
            $uCats[$cat['slug']] = $value;
          }          
        }
        foreach($authors as $key => $author) {
          if(isset($_POST['z_authors_'.$author])) $uAuthors[$author] = $_POST['z_authors_'.$author];
        }
        foreach($customs as $custom) {
          if(isset($_POST['z_single_ct_'.$custom->name])) $uSingleCT[$custom->name] = $_POST['z_single_ct_'.$custom->name];
          if(isset($_POST['z_archive_ct_'.$custom->name])) $uArchiveCT[$custom->name] = $_POST['z_archive_ct_'.$custom->name];
        }
        $updateRow = array(
          'name' => $_POST['zone_name'],
          'description' => $_POST['description'],
          'z_default' => $_POST['z_default'],
          'z_home' => $_POST['z_home'],
          'z_singular' => $_POST['z_singular'],
          'z_single' => $_POST['z_single'],
          'z_ct' => (isset($_POST['z_ct']) ? $_POST['z_ct'] : -1),
          'z_single_ct' => serialize($uSingleCT),
          'z_page' => $_POST['z_page'],
          'z_attachment' => $_POST['z_attachment'],
          'z_search' => $_POST['z_search'],
          'z_404' => $_POST['z_404'],
          'z_archive' => $_POST['z_archive'],
          'z_tax' => $_POST['z_tax'],
          'z_taxes' => serialize($uTaxes),
          'z_category' => $_POST['z_category'],
          'z_cats' => serialize($uCats),
          'z_tag' => $_POST['z_tag'],
          'z_author' => $_POST['z_author'],
          'z_authors' => serialize($uAuthors),
          'z_cts' => (isset($_POST['z_cts']) ? $_POST['z_cts'] : -1),
          'z_archive_ct' => serialize($uArchiveCT),
          'z_date' => $_POST['z_date'],
          'trash' => ($_POST['trash'] === 'true' ? 1 : 0)
        );
        $formatRow = array(
          '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%s', '%d', '%d',
          '%d', '%d', '%d', '%d', '%s', '%d', '%s', '%d', '%d', '%s',
          '%d', '%s', '%d', '%d'
        );
        if($zoneId === __('Undefined', SAM_DOMAIN)) {
          $wpdb->insert($zTable, $updateRow);
          $updated = true;
          $item = $wpdb->insert_id;
        }
        else {
          if(is_null($item)) $item = $zoneId;
          $wpdb->update($zTable, $updateRow, array( 'id' => $item ), $formatRow, array( '%d' ));
          $updated = true;
        }
        ?>
<div class="updated"><p><strong><?php _e("Ads Zone Data Updated.", SAM_DOMAIN);?></strong></p></div>
        <?php
      }
      
      $zSql = "SELECT 
                  id, 
                  name, 
                  description, 
                  z_default, 
                  z_home, 
                  z_singular, 
                  z_single,
                  z_ct,
                  z_single_ct, 
                  z_page, 
                  z_attachment, 
                  z_search, 
                  z_404, 
                  z_archive, 
                  z_tax,
                  z_taxes,
                  z_category,
                  z_cats,
                  z_tag,
                  z_author,
                  z_authors,
                  z_cts,
                  z_archive_ct,
                  z_date, 
                  trash 
                FROM $zTable 
                WHERE id = $item;";      
      
      $pSql = "SELECT id, name FROM $pTable WHERE $pTable.trash IS FALSE;";
      $places = $wpdb->get_results($pSql, ARRAY_A);

      $sCats = array();
      $sAuthors = array();
      $sSingleCT = array();
      $sArchiveCT = array();
      $sTaxes = array();
      
      if($action !== 'new') {
        $row = $wpdb->get_row($zSql, ARRAY_A);
        $zTaxes = unserialize($row['z_taxes']);
        $zCats = unserialize($row['z_cats']);
        $zAuthors = unserialize($row['z_authors']);
        $zSingleCT = unserialize($row['z_single_ct']);
        $zArchiveCT = unserialize($row['z_archive_ct']);

        if(is_array($taxes)) {
          foreach($taxes as $tax) {
            $val = (isset($zTaxes[$tax['slug']])) ? $zTaxes[$tax['slug']]['id'] : -1;
            array_push($sTaxes, array('name' => $tax['name'], 'slug' => $tax['slug'], 'tax' => $tax['tax'], 'val' => $val));
          }
        }
        foreach($cats as $cat) {
          $val = (!is_null($zCats[$cat['slug']])) ? $zCats[$cat['slug']] : -1;
          array_push($sCats, array('name' => $cat['name'], 'slug' => $cat['slug'], 'val' => $val));
        }
        foreach($authors as $key => $author) {
          $val = (!is_null($zAuthors[$author])) ? $zAuthors[$author] : -1;
          array_push($sAuthors, array('id' => $author, 'name' => $key, 'val' => $val));
        }
        if(is_array($customs)) {
          foreach($customs as $custom) {
            $val = (isset($zSingleCT[$custom->name])) ? $zSingleCT[$custom->name] : -1;
            array_push($sSingleCT, array('label' => $custom->label, 'name' => $custom->name, 'val' => $val));
            $val = (isset($zArchiveCT[$custom->name])) ? $zArchiveCT[$custom->name] : -1;
            array_push($sArchiveCT, array('label' => $custom->label, 'name' => $custom->name, 'val' => $val));
          }
        }
      }
      else {
        if($updated) {
          $row = $wpdb->get_row($zSql, ARRAY_A);
          $zTaxes = unserialize($row['z_taxes']);
          $zCats = unserialize($row['z_cats']);          
          $zAuthors = unserialize($row['z_authors']);
          $zSingleCT = unserialize($row['z_single_ct']);
          $zArchiveCT = unserialize($row['z_archive_ct']);

          if(is_array($taxes)) {
            foreach($taxes as $tax) {
              $val = (isset($zTaxes[$tax['slug']])) ? $zTaxes[$tax['slug']]['id'] : -1;
              array_push($sTaxes, array('name' => $tax['name'], 'slug' => $tax['slug'], 'tax' => $tax['tax'], 'val' => $val));
            }
          }
          foreach($cats as $cat) {
            $val = (!is_null($zCats[$cat['slug']])) ? $zCats[$cat['slug']] : -1;
            array_push($sCats, array('name' => $cat['name'], 'slug' => $cat['slug'], 'val' => $val));
          }
          foreach($authors as $key => $author) {
            $val = (!is_null($zAuthors[$author])) ? $zAuthors[$author] : -1;
            array_push($sAuthors, array('id' => $author, 'name' => $key, 'val' => $val));
          }
          if(is_array($customs)) {
            foreach($customs as $custom) {
              $val = (isset($zSingleCT[$custom->name])) ? $zSingleCT[$custom->name] : -1;
              array_push($sSingleCT, array('label' => $custom->label, 'name' => $custom->name, 'val' => $val));
              $val = (isset($zArchiveCT[$custom->name])) ? $zArchiveCT[$custom->name] : -1;
              array_push($sArchiveCT, array('label' => $custom->label, 'name' => $custom->name, 'val' => $val));
            }
          }
        }
        else {
          $row = array(
            'id' => __('Undefined', SAM_DOMAIN),
            'name' => '',
            'description' => '',
            'z_default' => 0,
            'z_home' => -1,
            'z_singular' => -1,
            'z_single' => -1,
            'z_ct' => -1,
            'z_page' => -1,
            'z_attachment' => -1,
            'z_search' => -1,
            'z_404' => -1,
            'z_archive' => -1,
            'z_tax' => -1,
            'z_category' => -1,
            'z_tag' => -1,
            'z_author' => -1,
            'z_cts' => -1,
            'z_date' => -1,
            'trash' => false
          );
          foreach($taxes as $tax) array_push($sTaxes, array('name' => $tax['name'], 'slug' => $tax['slug'], 'tax' => $tax['tax'], 'val' => -1));
          foreach($cats as $cat) array_push($sCats, array('name' => $cat['name'], 'slug' => $cat['slug'], 'val' => -1));
          foreach($authors as $key => $author) array_push($sAuthors, array('id' => $author, 'name' => $key, 'val' => -1));
          foreach($customs as $custom) {
            array_push($sSingleCT, array('label' => $custom->label, 'name' => $custom->name, 'val' => -1));
            array_push($sArchiveCT, array('label' => $custom->label, 'name' => $custom->name, 'val' => -1));
          }
        }
      }
      ?>
<div class="wrap">
  <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
    <div class="icon32" style="background: url('<?php echo SAM_IMG_URL.'sam-editor.png'; ?>') no-repeat transparent; "><br/></div>
    <h2><?php echo ( ( ($action === 'new') && ( $row['id'] === __('Undefined', SAM_DOMAIN) ) ) ? __('New Ads Zone', SAM_DOMAIN) : __('Edit Ads Zone', SAM_DOMAIN).' ('.$item.')' ); ?></h2>
    <?php
      include_once('errors.class.php');
      $errors = new samErrors();
      if(!empty($errors->errorString)) echo $errors->errorString;
    ?>
    <div class="metabox-holder has-right-sidebar" id="poststuff">
      <div id="side-info-column" class="inner-sidebar">
        <div class="meta-box-sortables ui-sortable">
          <div id="submitdiv" class="postbox ">
            <div class="handlediv" title="<?php _e('Click to toggle', SAM_DOMAIN); ?>"><br/></div>
            <h3 class="hndle"><span><?php _e('Status', SAM_DOMAIN);?></span></h3>
            <div class="inside">
              <div id="submitpost" class="submitbox">
                <div id="minor-publishing">
                  <div id="minor-publishing-actions">
                    <div id="save-action"> </div>
                    <div id="preview-action">
                      <a id="back-button" class="color-btn color-btn-left" href='<?php echo admin_url('admin.php'); ?>?page=sam-zone-list'>
                        <b style="background-color: #bcbcbc"></b>
                        <?php _e('Back to Zones List', SAM_DOMAIN) ?>
                      </a>
                    </div>
                    <div class="clear"></div>
                  </div>
                  <div id="misc-publishing-actions">
                    <div class="misc-pub-section">
                      <label for="place_id_stat"><?php echo __('Ads Zone ID', SAM_DOMAIN).':'; ?></label>
                      <span id="place_id_stat" class="post-status-display"><?php echo $row['id']; ?></span>
                      <input type="hidden" id="zone_id" name="zone_id" value="<?php echo $row['id']; ?>" />
                      <input type='hidden' name='editor_mode' id='editor_mode' value='zone'>
                    </div>
                    <div class="misc-pub-section">
                      <label for="trash_no"><input type="radio" id="trash_no" value="false" name="trash" <?php if (!$row['trash']) { echo 'checked="checked"'; }?> >  <?php _e('Is Active', SAM_DOMAIN); ?></label><br/>
                      <label for="trash_yes"><input type="radio" id="trash_yes" value="true" name="trash" <?php if ($row['trash']) { echo 'checked="checked"'; }?> >  <?php _e('Is In Trash', SAM_DOMAIN); ?></label>
                    </div>
                  </div>
                  <div class="clear"></div>
                </div>
                <div id="major-publishing-actions">
                  <div id="delete-action">
                    <!--<a class="submitdelete deletion" href='<?php echo admin_url('admin.php'); ?>?page=sam-zone-list'><?php _e('Cancel', SAM_DOMAIN) ?></a>-->
                  </div>
                  <div id="publishing-action">
                    <!--<input type="submit" class='button-primary' name="update_zone" value="<?php _e('Save', SAM_DOMAIN) ?>" />-->
                    <a id="cancel-button" class="color-btn color-btn-left" href='<?php echo admin_url('admin.php'); ?>?page=sam-zone-list'>
                      <b style="background-color: #E5584A"></b>
                      <?php _e('Cancel', SAM_DOMAIN) ?>
                    </a>
                    <button id="submit-button" class="color-btn color-btn-left" type="submit" name="update_zone">
                      <b style="background-color: #21759b"></b>
                      <?php _e('Save', SAM_DOMAIN) ?>
                    </button>
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
              <input id="title" type="text" autocomplete="off" tabindex="1" size="30" name="zone_name" value="<?php echo $row['name']; ?>" title="<?php echo __('Name of Ads Zone', SAM_DOMAIN).'. '.__('Required for SAM widgets.', SAM_DOMAIN); ?>" />
            </div>
          </div>
          <div class="meta-box-sortables ui-sortable">
            <div id="descdiv" class="postbox ">
              <div class="handlediv" title="<?php _e('Click to toggle', SAM_DOMAIN); ?>"><br/></div>
              <h3 class="hndle"><span><?php _e('Description', SAM_DOMAIN);?></span></h3>
              <div class="inside">
                <p><?php _e('Enter description of this Ads Zone.', SAM_DOMAIN);?></p>
                <p>
                  <label for="description"><?php echo __('Description', SAM_DOMAIN).':'; ?></label>
                  <textarea id="description" class="code" tabindex="2" name="description" style="width:100%" ><?php echo $row['description']; ?></textarea>
                </p>
                <p><?php _e('This description is not used anywhere and is added solely for the convenience of managing advertisements.', SAM_DOMAIN); ?></p>
              </div>
            </div>
          </div>
          <div class="meta-box-sortables ui-sortable">
            <div id="sizediv" class="postbox ">
              <div class="handlediv" title="<?php _e('Click to toggle', SAM_DOMAIN); ?>"><br/></div>
              <h3 class="hndle"><span><?php _e('Ads Zone Settings', SAM_DOMAIN);?></span></h3>
              <div class="inside">
                <p>
                  <label for='z_default'><?php echo __('Default Ads Place', SAM_DOMAIN).': '; ?></label>
                  <select id='z_default' name='z_default'>
                    <?php $this->drawPlacesSelector($places, $row['z_default'], true); ?>
                  </select>
                </p>
                <p>
                  <?php _e('Select the Ads Place by default. This Ads Place will be displayed in the event that for the page of a given type the Ads Place value is set to "Default".', SAM_DOMAIN); ?>
                </p>
                <div class='clear-line'></div>
                <p>
                  <label for='z_home'><?php echo __('Home Page Ads Place', SAM_DOMAIN).': '; ?></label>
                  <select id='z_home' name='z_home'>
                    <?php $this->drawPlacesSelector($places, $row['z_home'], false); ?>
                  </select>
                </p>
                <p>
                  <label for='z_singular'><?php echo __('Default Ads Place for Singular Pages', SAM_DOMAIN).': '; ?></label>
                  <select id='z_singular' name='z_singular'>
                    <?php $this->drawPlacesSelector($places, $row['z_singular'], false); ?>
                  </select>
                </p>
                <div class='sub-content'>
                  <p>
                    <label for='z_single'><?php echo __('Single Post Ads Place', SAM_DOMAIN).': '; ?></label>
                    <select id='z_single' name='z_single'>
                      <?php $this->drawPlacesSelector($places, $row['z_single'], false); ?>
                    </select>
                  </p>
                  <?php
                    if(!empty($sSingleCT)) {
                  ?>
                    <p>
                    <label for='z_ct'><?php echo __('Default Ads Place for Single Custom Type Post', SAM_DOMAIN).': '; ?></label>
                    <select id='z_ct' name='z_ct'>
                      <?php $this->drawPlacesSelector($places, $row['z_ct'], false); ?>
                    </select>
                  </p>
                    <div class='sub-content-level-2'>
                    
                    <?php
                        foreach($sSingleCT as $ctype) {
                    ?>
                      <p>
                        <label for='<?php echo 'z_single_ct_'.$ctype['name']; ?>'><?php echo __('Ads Place for Single Post of Custom Type', SAM_DOMAIN).' <strong>'.$ctype['label'].'</strong>: '; ?></label>
                        <select id='<?php echo 'z_single_ct_'.$ctype['name']; ?>' name='<?php echo 'z_single_ct_'.$ctype['name']; ?>'>
                          <?php $this->drawPlacesSelector($places, $ctype['val'], false); ?>
                        </select>
                      </p>
                    <?php } ?>
                    </div>
                  <?php } ?>
                  <p>
                    <label for='z_page'><?php echo __('Page Ads Place', SAM_DOMAIN).': '; ?></label>
                    <select id='z_page' name='z_page'>
                      <?php $this->drawPlacesSelector($places, $row['z_page'], false); ?>
                    </select>
                  </p>
                  <p>
                    <label for='z_attachment'><?php echo __('Attachment Ads Place', SAM_DOMAIN).': '; ?></label>
                    <select id='z_attachment' name='z_attachment'>
                      <?php $this->drawPlacesSelector($places, $row['z_attachment'], false); ?>
                    </select>
                  </p>
                </div>
                <p>
                  <label for='z_search'><?php echo __('Search Pages Ads Place', SAM_DOMAIN).': '; ?></label>
                  <select id='z_search' name='z_search'>
                    <?php $this->drawPlacesSelector($places, $row['z_search'], false); ?>
                  </select>
                </p>
                <p>
                  <label for='z_404'><?php echo __('404 Page Ads Place', SAM_DOMAIN).': '; ?></label>
                  <select id='z_404' name='z_404'>
                    <?php $this->drawPlacesSelector($places, $row['z_404'], false); ?>
                  </select>
                </p>
                <p>
                  <label for='z_archive'><?php echo __('Default Ads Place for Archive Pages', SAM_DOMAIN).': '; ?></label>
                  <select id='z_archive' name='z_archive'>
                    <?php $this->drawPlacesSelector($places, $row['z_archive'], false); ?>
                  </select>
                </p>
                <div class='sub-content'>
                  <p>
                    <label for='z_tax'><?php echo __('Default Ads Place for Taxonomies Pages', SAM_DOMAIN).': '; ?></label>
                    <select id='z_tax' name='z_tax'>
                      <?php $this->drawPlacesSelector($places, $row['z_tax'], false); ?>
                    </select>
                  </p>
                  <?php
                  if(count($sTaxes) > 1) {
                    ?>
                    <div class='sub-content-level-2'>
                    <?php
                    foreach($sTaxes as $tax) {
                      ?>
                      <p>
                        <label for='<?php echo 'z_taxes_'.$tax['slug']; ?>'><?php echo __('Ads Place for Custom Taxonomy Term', SAM_DOMAIN).' "<strong>'.$tax['name'].'</strong>": '; ?></label>
                        <select id='<?php echo 'z_taxes_'.$tax['slug']; ?>' name='<?php echo 'z_taxes_'.$tax['slug']; ?>'>
                          <?php $this->drawPlacesSelector($places, $tax['val'], false); ?>
                        </select>
                      </p>
                    <?php
                    }
                    ?>
                    </div>
                  <?php
                  }
                  ?>
                  <p>
                    <label for='z_category'><?php echo __('Default Ads Place for Category Archive Pages', SAM_DOMAIN).': '; ?></label>
                    <select id='z_category' name='z_category'>
                      <?php $this->drawPlacesSelector($places, $row['z_category'], false); ?>
                    </select>
                  </p>
                  <?php
                  if(count($sCats) > 1) {
                    ?>
                  <div class='sub-content-level-2'>
                    <?php
                    foreach($sCats as $cat) {
                      ?>
                    <p>
                      <label for='<?php echo 'z_cats_'.$cat['slug']; ?>'><?php echo __('Ads Place for Category', SAM_DOMAIN).' "<strong>'.$cat['name'].'</strong>": '; ?></label>
                      <select id='<?php echo 'z_cats_'.$cat['slug']; ?>' name='<?php echo 'z_cats_'.$cat['slug']; ?>'>
                        <?php $this->drawPlacesSelector($places, $cat['val'], false); ?>
                      </select>
                    </p>
                      <?php
                    }
                    ?>
                  </div>
                  <?php
                  }
                  ?>
                  <?php
                    if(!empty($sArchiveCT)) {
                  ?>
                    <p>
                      <label for='z_cts'><?php echo __('Default Ads Place for Archives of Custom Type Posts', SAM_DOMAIN).': '; ?></label>
                      <select id='z_cts' name='z_cts'>
                        <?php $this->drawPlacesSelector($places, $row['z_cts'], false); ?>
                      </select>
                    </p>
                    <div class='sub-content-level-2'>
                      <?php
                          foreach($sArchiveCT as $ctype) {
                      ?>
                      <p>
                        <label for='<?php echo 'z_archive_ct_'.$ctype[name]; ?>'><?php echo __('Ads Place for Custom Type Posts Archive', SAM_DOMAIN).' <strong>'.$ctype['label'].'</strong>: '; ?></label>
                        <select id='<?php echo 'z_archive_ct_'.$ctype[name]; ?>' name='<?php echo 'z_archive_ct_'.$ctype[name]; ?>'>
                          <?php $this->drawPlacesSelector($places, $ctype['val'], false); ?>
                        </select>
                      </p>
                      <?php } ?>
                    </div>
                  <?php } ?>
                  <p>
                    <label for='z_tag'><?php echo __('Tags Archive Pages Ads Place', SAM_DOMAIN).': '; ?></label>
                    <select id='z_tag' name='z_tag'>
                      <?php $this->drawPlacesSelector($places, $row['z_tag'], false); ?>
                    </select>
                  </p>
                  <p>
                    <label for='z_author'><?php echo __('Default Ads Place for Author Archive Pages', SAM_DOMAIN).': '; ?></label>
                    <select id='z_author' name='z_author'>
                      <?php $this->drawPlacesSelector($places, $row['z_author'], false); ?>
                    </select>
                  </p>
                  <?php if(count($sAuthors) > 1) { ?>
                  <div class='sub-content-level-2'>
                    <?php foreach($sAuthors as $author) { ?>
                    <p>
                      <label for='<?php echo 'z_authors_'.$author['id']; ?>'><?php echo __('Ads Place for author', SAM_DOMAIN).' <strong>'.$author['name'].'</strong>: '; ?></label>
                      <select id='<?php echo 'z_authors_'.$author['id']; ?>' name='<?php echo 'z_authors_'.$author['id']; ?>'>
                        <?php $this->drawPlacesSelector($places, $author['val'], false); ?>
                      </select>
                    </p>
                    <?php } ?>
                  </div>
                  <?php } ?>
                  <p>
                    <label for='z_date'><?php echo __('Date Archive Pages Ads Place', SAM_DOMAIN).': '; ?></label>
                    <select id='z_date' name='z_date'>
                      <?php $this->drawPlacesSelector($places, $row['z_date'], false); ?>
                    </select>
                  </p>
                </div>
                <p>
                  <?php _e('Ads Places for Singular pages, for Pages of Taxonomies and for Archive pages are Ads Places by default for the low level pages of relevant pages.', SAM_DOMAIN); ?>
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>
</div>      
      <?php
    }
  }
}
?>
