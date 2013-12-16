<?php
if(!class_exists('SamBlockEditor')) {
  class SamBlockEditor {
    private $settings = array();
    private $freeItems = array();
    
    public function __construct($settings) {
      $this->settings = $settings;
      $this->freeItems = $this->getItems();
    }
    
    private function getData($data) {
      return unserialize($data);
    }
    
    private function setData($lines, $columns, $data) {
      $result = array();
      
      for($line = 1; $line <= $lines; $line++) {
        for($column = 1; $column <= $columns; $column++) {
          if(!is_null($data['item-'. $line.'-'.$column])) {
            switch($data['item-'. $line.'-'.$column]) {
              case 0:
                $result[$line][$column] = array('type' => 'place', 'id' => $data['place_id_'. $line.'_'.$column]);
                break;
              
              case 1:
                $result[$line][$column] = array('type' => 'ad', 'id' => $data['ad_id_'. $line.'_'.$column]);
                break;
              
              case 2:
                $result[$line][$column] = array('type' => 'zone', 'id' => $data['zone_id_'. $line.'_'.$column]);
                break;
                
              default:
                $result[$line][$column] = array('type' => 'place', 'id' => 0);
                break; 
            }
          }
          else {
            $result[$line][$column] = array('type' => 'place', 'id' => 0);
          }
        }
      }
      
      return serialize($result);
    }
    
    private function getItems($size = null) {
      global $wpdb;
      $pTable = $wpdb->prefix . "sam_places";          
      $aTable = $wpdb->prefix . "sam_ads";
      $zTable = $wpdb->prefix . "sam_zones";
      
      $items = array();
      
      $aSql = "SELECT $aTable.id, $aTable.name FROM $aTable";
      $pSql = "SELECT $pTable.id, $pTable.name FROM $pTable";
      $zSql = "SELECT $zTable.id, $zTable.name FROM $zTable";
      
      $items['ads'] = $wpdb->get_results($aSql, ARRAY_A);
      $items['places'] = $wpdb->get_results($pSql, ARRAY_A);
      $items['zones'] = $wpdb->get_results($zSql, ARRAY_A);
      
      return $items;
    }
    
    private function drawItem($line, $column, $data = null) {
      ?>
        <div class='block-editor-item'>
          <div class="meta-box-sortables ui-sortable">
            <div id="headdiv-<?php echo $line.'-'.$column ?>" class="postbox ">
              <div class="handlediv" title="<?php _e('Click to toggle', SAM_DOMAIN); ?>"><br/></div>
              <h3 class="hndle"><span><?php echo __('Item', SAM_DOMAIN)." $line-$column";?></span></h3>
              <div class="inside">
                <input type='radio' name='item-<?php echo $line.'-'.$column; ?>' id='item-<?php echo $line.'-'.$column; ?>-place' value='0' <?php checked('place', $data[$line][$column]['type']); ?>>
                <label for='item-<?php echo $line.'-'.$column; ?>-place'><?php _e('Ads Place', SAM_DOMAIN); ?>:</label>
                <select name='place_id_<?php echo $line.'_'.$column; ?>' id='place_id_<?php echo $line.'_'.$column; ?>' style="width: 100%;">
                  <option value='0' <?php selected(0, ($data[$line][$column]['type'] == 'place') ? $data[$line][$column]['id'] : 0); ?>><?php _e('Non selected', SAM_DOMAIN); ?></option>
      <?php
        foreach($this->freeItems['places'] as $value) {
          ?>
          <option value='<?php echo $value['id']; ?>' <?php selected($value['id'], ($data[$line][$column]['type'] == 'place') ? $data[$line][$column]['id'] : 0); ?>><?php echo $value['name']; ?></option>
          <?php
        }
      ?>
                </select>
                <br><br>
                <input type='radio' name='item-<?php echo $line.'-'.$column; ?>' id='item-<?php echo $line.'-'.$column; ?>-ad' value='1' <?php checked('ad', $data[$line][$column]['type']); ?>>
                <label for='item-<?php echo $line.'-'.$column; ?>-ad'><?php _e('Single Ad', SAM_DOMAIN); ?>:</label>
                <select name='ad_id_<?php echo $line.'_'.$column; ?>' id='ad_id_<?php echo $line.'_'.$column; ?>' style="width: 100%;">
                  <option value='0' <?php selected(0, ($data[$line][$column]['type'] == 'ad') ? $data[$line][$column]['id'] : 0); ?>><?php _e('Non selected', SAM_DOMAIN); ?></option>
      <?php
        foreach($this->freeItems['ads'] as $value) {
          ?>
          <option value='<?php echo $value['id']; ?>' <?php selected($value['id'], ($data[$line][$column]['type'] == 'ad') ? $data[$line][$column]['id'] : 0); ?>><?php echo $value['name']; ?></option>
          <?php
        }
      ?>
                </select>
                <br><br>
                <input type='radio' name='item-<?php echo $line.'-'.$column; ?>' id='item-<?php echo $line.'-'.$column; ?>-zone' value='2' <?php checked('zone', $data[$line][$column]['type']); ?>>
                <label for='item-<?php echo $line.'-'.$column; ?>-zone'><?php _e('Ads Zone', SAM_DOMAIN); ?>:</label>
                <select name='zone_id_<?php echo $line.'_'.$column; ?>' id='zone_id_<?php echo $line.'_'.$column; ?>' style="width: 100%">
                  <option value='0' <?php selected(0, ($data[$line][$column]['type'] == 'zone') ? $data[$line][$column]['id'] : 0); ?>><?php _e('Non selected', SAM_DOMAIN); ?></option>
      <?php
        foreach($this->freeItems['zones'] as $value) {
          ?>
          <option value='<?php echo $value['id']; ?>' <?php selected($value['id'], ($data[$line][$column]['type'] == 'zone') ? $data[$line][$column]['id'] : 0); ?>><?php echo $value['name']; ?></option>
          <?php
        }
      ?>
                </select>
              </div>
            </div>
          </div>
        </div>
      <?php
    }
    
    private function buildEditorItems($lines, $columns, $data = null) {
      $percent = (int)(100/$columns);
      for($i = 1; $i <= $lines; $i++) {
        echo "<div id='line-$i' class='block-editor-line'>";
        for($j = 1; $j <= $columns; $j++) {
          $this->drawItem($i, $j, $data);
        }
        echo "</div>";
      }
    }
    
    public function page() {
      global $wpdb;
      $pTable = $wpdb->prefix . "sam_places";          
      $aTable = $wpdb->prefix . "sam_ads";
      $zTable = $wpdb->prefix . "sam_zones";
      $bTable = $wpdb->prefix . "sam_blocks";
      
      $options = $this->settings;
      
      if(isset($_GET['action'])) $action = $_GET['action'];
      else $action = 'new';
      if(isset($_GET['mode'])) $mode = $_GET['mode'];
      else $mode = 'block';
      if(isset($_GET['item'])) $item = $_GET['item'];
      else $item = null;
      if(isset($_GET['block'])) $zone = $_GET['block'];
      else $block = null;
      
      $updated = false;
      
      if(isset($_POST['update_block'])) {
        $blockId = $_POST['block_id'];
        $updateRow = array(
          'name' => $_POST['block_name'],
          'description' => $_POST['description'],
          'b_lines' => $_POST['b_lines'],
          'b_cols' => $_POST['b_cols'],
          'block_data' => $this->setData((int)$_POST['b_lines'], (int)$_POST['b_cols'], $_POST),
          'b_margin' => $_POST['b_margin'],
          'b_padding' => $_POST['b_padding'],
          'b_background' => stripcslashes( $_POST['b_background'] ),
          'b_border' => $_POST['b_border'],
          'i_margin' => $_POST['i_margin'],
          'i_padding' => $_POST['i_padding'],
          'i_background'  => stripcslashes( $_POST['i_background'] ),
          'i_border' => $_POST['i_border'],
          //FIXED 'trash' => ($_POST['trash'] === 'true')
          'trash' => ($_POST['trash'] === 'true' ? 1 : 0)
        );
        //FIXED $formatRow = array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d');
        $formatRow = array( '%s', '%s', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d');
        if($blockId === __('Undefined', SAM_DOMAIN)) {
          $wpdb->insert($bTable, $updateRow);
          $updated = true;
          $item = $wpdb->insert_id;
        }
        else {
          if(is_null($item)) $item = $blockId;
          $wpdb->update($bTable, $updateRow, array( 'id' => $item ), $formatRow, array( '%d' ));
          $updated = true;
        }
        ?>
<div class="updated"><p><strong><?php _e("Ads Block Data Updated.", SAM_DOMAIN);?></strong></p></div>
        <?php
      }
      
      $bSql = "SELECT
                 id, 
                 name, 
                 description,
                 b_lines,
                 b_cols,
                 block_data,
                 b_margin,
                 b_padding,
                 b_background,
                 b_border,
                 i_margin,
                 i_padding,
                 i_background,
                 i_border,
                 trash
               FROM $bTable
               WHERE $bTable.id = $item;";
      if($action !== 'new') {
        $row = $wpdb->get_row($bSql, ARRAY_A);
        $data = $this->getData($row['block_data']);
      }
      else {
        if($updated) {
          $row = $wpdb->get_row($bSql, ARRAY_A);
          $data = $this->getData($row['block_data']);
        }
        else {
          $row = array(
            'id' => __('Undefined', SAM_DOMAIN),
            'name' => '',
            'description' => '',
            'b_lines' => 2,
            'b_cols' => 2,
            'block_data' => array(),
            'b_margin' => '5px 5px 5px 5px',
            'b_padding' => '5px 5px 5px 5px',
            'b_background' => '#FFFFFF',
            'b_border' => '0px solid #333333',
            'i_margin' => '5px 5px 5px 5px',
            'i_padding' => '5px 5px 5px 5px',
            'i_background' => '#FFFFFF',
            'i_border' => '0px solid #333333',
            'trash' => false
          );
          $data = array(
            1 => array(1 => array('type' => 'place', 'id' => 0), 2 => array('type' => 'place', 'id' => 0)),
            2 => array(1 => array('type' => 'place', 'id' => 0), 2 => array('type' => 'place', 'id' => 0))
          );
        }
      }
      ?>
<div class="wrap">
  <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
    <div class="icon32" style="background: url('<?php echo SAM_IMG_URL.'sam-editor.png'; ?>') no-repeat transparent; "><br/></div>
    <h2><?php echo ( ( ($action === 'new') && ( $row['id'] === __('Undefined', SAM_DOMAIN) ) ) ? __('New Ads Block', SAM_DOMAIN) : __('Edit Ads Block', SAM_DOMAIN).' ('.$item.')' ); ?></h2>
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
                      <a id="back-button" class="color-btn color-btn-left" href='<?php echo admin_url('admin.php'); ?>?page=sam-block-list'>
                        <b style="background-color: #bcbcbc"></b>
                        <?php _e('Back to Blocks List', SAM_DOMAIN) ?>
                      </a>
                    </div>
                    <div class="clear"></div>
                  </div>
                  <div id="misc-publishing-actions">
                    <div class="misc-pub-section">
                      <label for="place_id_stat"><?php echo __('Ads Block ID', SAM_DOMAIN).':'; ?></label>
                      <span id="place_id_stat" class="post-status-display"><?php echo $row['id']; ?></span>
                      <input type="hidden" id="block_id" name="block_id" value="<?php echo $row['id']; ?>">
                      <input type='hidden' name='editor_mode' id='editor_mode' value='block'>
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
                    <!--<a class="submitdelete deletion" href='<?php echo admin_url('admin.php'); ?>?page=sam-block-list'><?php _e('Cancel', SAM_DOMAIN) ?></a>-->
                  </div>
                  <div id="publishing-action">
                    <a id="cancel-button" class="color-btn color-btn-left" href='<?php echo admin_url('admin.php'); ?>?page=sam-block-list'>
                      <b style="background-color: #E5584A"></b>
                      <?php _e('Cancel', SAM_DOMAIN) ?>
                    </a>
                    <button id="submit-button" class="color-btn color-btn-left" name="update_block" type="submit">
                      <b style="background-color: #21759b"></b>
                      <?php _e('Save', SAM_DOMAIN) ?>
                    </button>
                    <!--<input type="submit" class='button-primary' name="update_block" value="<?php _e('Save', SAM_DOMAIN) ?>" />-->
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
              <input id="title" type="text" autocomplete="off" tabindex="1" size="30" name="block_name" value="<?php echo $row['name']; ?>" title="<?php echo __('Name of Ads Block', SAM_DOMAIN).'. '.__('Required for SAM widgets.', SAM_DOMAIN); ?>" />
            </div>
          </div>
          <div class="meta-box-sortables ui-sortable">
            <div id="descdiv" class="postbox ">
              <div class="handlediv" title="<?php _e('Click to toggle', SAM_DOMAIN); ?>"><br/></div>
              <h3 class="hndle"><span><?php _e('Description', SAM_DOMAIN);?></span></h3>
              <div class="inside">
                <p><?php _e('Enter description of this Ads Block.', SAM_DOMAIN);?></p>
                <p>
                  <label for="description"><?php echo __('Description', SAM_DOMAIN).':'; ?></label>
                  <textarea id="description" class="code" tabindex="2" name="description" style="width:100%" ><?php echo $row['description']; ?></textarea>
                </p>
                <p><?php _e('This description is not used anywhere and is added solely for the convenience of managing advertisements.', SAM_DOMAIN); ?></p>
              </div>
            </div>
          </div>
          <div class="meta-box-sortables ui-sortable">
            <div id="bstylediv" class="postbox ">
              <div class="handlediv" title="<?php _e('Click to toggle', SAM_DOMAIN); ?>"><br/></div>
              <h3 class="hndle"><span><?php _e('Block Structure', SAM_DOMAIN);?></span></h3>
              <div class="inside">
                <p><?php _e('Ads Block Structure Properties.', SAM_DOMAIN);?></p>
                <p>
                  <label for="b_lines"><?php echo __('Block Lines', SAM_DOMAIN).':'; ?></label>
                  <input type='text' name='b_lines' id='b_lines' value='<?php echo $row['b_lines']; ?>'>
                </p>
                <p>
                  <label for="b_cols"><?php echo __('Block Columns', SAM_DOMAIN).':'; ?></label>
                  <input type='text' name='b_cols' id='b_cols' value='<?php echo $row['b_cols']; ?>'>
                </p>
                <p><?php _e('After changing these properties you must save Ads Block settings before using Ads Block Editor.', SAM_DOMAIN); ?></p>
              </div>
            </div>
          </div>
          <div class="meta-box-sortables ui-sortable">
            <div id="bstylediv" class="postbox ">
              <div class="handlediv" title="<?php _e('Click to toggle', SAM_DOMAIN); ?>"><br/></div>
              <h3 class="hndle"><span><?php _e('Block Styles', SAM_DOMAIN);?></span></h3>
              <div class="inside">
                <p><?php _e('Configure Styles for this Ads Block.', SAM_DOMAIN);?></p>
                <p>
                  <label for="b_margin"><?php echo __('Margins', SAM_DOMAIN).':'; ?></label>
                  <input type='text' name='b_margin' id='b_margin' value='<?php echo $row['b_margin']; ?>'>
                </p>
                <p>
                  <label for="b_padding"><?php echo __('Padding', SAM_DOMAIN).':'; ?></label>
                  <input type='text' name='b_padding' id='b_padding' value='<?php echo $row['b_padding']; ?>'>
                </p>
                <p>
                  <label for="b_background"><?php echo __('Background', SAM_DOMAIN).':'; ?></label>
                  <input type='text' name='b_background' id='b_background' style='width: 100%' value='<?php echo $row['b_background']; ?>'>
                </p>
                <p>
                  <label for="b_border"><?php echo __('Borders', SAM_DOMAIN).':'; ?></label>
                  <input type='text' name='b_border' id='b_border' style='width: 100%' value='<?php echo $row['b_border']; ?>'>
                </p>
                <p><?php _e('Use <strong>Stylesheet rules</strong> for defining these properties.', SAM_DOMAIN); ?><br/><?php _e('For example:', SAM_DOMAIN) ?> <code>url(sheep.png) center bottom no-repeat</code> <?php _e('for background property or', SAM_DOMAIN); ?> <code>5px solid red</code> <?php _e('for border property', SAM_DOMAIN); ?>.</p>
              </div>
            </div>
          </div>
          <div class="meta-box-sortables ui-sortable">
            <div id="istylediv" class="postbox ">
              <div class="handlediv" title="<?php _e('Click to toggle', SAM_DOMAIN); ?>"><br/></div>
              <h3 class="hndle"><span><?php _e('Block Items Styles', SAM_DOMAIN);?></span></h3>
              <div class="inside">
                <p><?php _e("Configure Styles for this Ads Block Items.", SAM_DOMAIN);?></p>
                <p>
                  <label for="i_margin"><?php echo __('Margins', SAM_DOMAIN).':'; ?></label>
                  <input type='text' name='i_margin' id='i_margin' value='<?php echo $row['i_margin']; ?>'>
                </p>
                <p>
                  <label for="i_padding"><?php echo __('Padding', SAM_DOMAIN).':'; ?></label>
                  <input type='text' name='i_padding' id='i_padding' value='<?php echo $row['i_padding']; ?>'>
                </p>
                <p>
                  <label for="i_background"><?php echo __('Background', SAM_DOMAIN).':'; ?></label>
                  <input type='text' name='i_background' id='i_background' style='width: 100%' value='<?php echo $row['i_background']; ?>'>
                </p>
                <p>
                  <label for="i_border"><?php echo __('Borders', SAM_DOMAIN).':'; ?></label>
                  <input type='text' name='i_border' id='i_border' style='width: 100%' value='<?php echo $row['i_border']; ?>'>
                </p>
                <p><?php _e('Use <strong>Stylesheet rules</strong> for defining these properties.', SAM_DOMAIN); ?><br/><?php _e('For example:', SAM_DOMAIN) ?> <code>url(sheep.png) center bottom no-repeat</code> <?php _e('for background property or', SAM_DOMAIN); ?> <code>5px solid red</code> <?php _e('for border property', SAM_DOMAIN); ?>.</p>
                <p><strong><?php _e("Important Note", SAM_DOMAIN); ?></strong>: <?php _e("As the Ads Block is the regular structure, predefined styles of individual items for drawing Ads Block's elements aren't used. Define styles for Ads Block Items here!", SAM_DOMAIN);?></p>
              </div>
            </div>
          </div>
        </div>
        <div class='block-editor'>
          <div class="meta-box-sortables ui-sortable">
            <div id="descdiv" class="postbox ">
              <div class="handlediv" title="<?php _e('Click to toggle', SAM_DOMAIN); ?>"><br/></div>
              <h3 class="hndle"><span><?php _e('Ads Block Editor', SAM_DOMAIN);?></span></h3>
              <div class="inside">
                <p><?php _e('Adjust items settings of this Ads Block.', SAM_DOMAIN);?></p>
                <?php $this->buildEditorItems($row['b_lines'], $row['b_cols'], $data); ?>
                <p><?php _e('Block Editor.', SAM_DOMAIN); ?></p>
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
