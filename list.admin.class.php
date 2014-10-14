<?php
if(!class_exists('SamPlaceList')) {
  class SamPlaceList {
    private $settings = array();
    
    public function __construct($settings) {
      $this->settings = $settings;
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
    
    public function page() {
      global $wpdb;
      $pTable = $wpdb->prefix . "sam_places";
      $aTable = $wpdb->prefix . "sam_ads";
      $sTable = $wpdb->prefix . 'sam_stats';

      if(isset($_GET['mode'])) $mode = $_GET['mode'];
      else $mode = 'active';
      if(isset($_GET["action"])) $action = $_GET['action'];
      else $action = 'places';
      if(isset($_GET['item'])) $item = $_GET['item'];
      else $item = null;
      if(isset($_GET['iaction'])) $iaction = $_GET['iaction'];
      else $iaction = null;
      if(isset($_GET['iitem'])) $iitem = $_GET['iitem'];
      else $iitem = null;
      if(isset($_GET['apage'])) $apage = abs( (int) $_GET['apage'] );
      else $apage = 1;

      $options = $this->settings;
      $places_per_page = $options['placesPerPage'];
      $items_per_page = $options['itemsPerPage'];

      switch($action) {
        case 'places':
          if(!is_null($item)) {
            if($iaction === 'delete') $wpdb->update( $pTable, array( 'trash' => true ), array( 'id' => $item ), array( '%d' ), array( '%d' ) );
            elseif($iaction === 'untrash') $wpdb->update( $pTable, array( 'trash' => false ), array( 'id' => $item ), array( '%d' ), array( '%d' ) );
            elseif($iaction === 'kill') $wpdb->query("DELETE FROM {$pTable} WHERE id={$item}");
          }
          if($iaction === 'kill-em-all') $wpdb->query("DELETE FROM {$pTable} WHERE trash=true");
          if($iaction === 'clear-stats') {
            //$wpdb->query("UPDATE $pTable SET $pTable.patch_hits = 0;");
            //$wpdb->query("UPDATE $aTable SET $aTable.ad_hits = 0, $aTable.ad_clicks = 0;");
            include_once('sam.tools.php');
            $cleaner = new SamStatsCleaner($this->settings);
            $cleaner->clear();
          }
          $trash_num = $wpdb->get_var("SELECT COUNT(*) FROM $pTable WHERE trash = TRUE");
          $active_num = $wpdb->get_var("SELECT COUNT(*) FROM $pTable WHERE trash = FALSE");
          if(is_null($active_num)) $active_num = 0;
          if(is_null($trash_num)) $trash_num = 0;
          $all_num = $trash_num + $active_num;
          $total = (($mode !== 'all') ? (($mode === 'trash') ? $trash_num : $active_num) : $all_num);
          $start = $offset = ( $apage - 1 ) * $places_per_page;

          $page_links = paginate_links( array(
            'base' => add_query_arg( 'apage', '%#%' ),
            'format' => '',
            'prev_text' => __('&laquo;'),
            'next_text' => __('&raquo;'),
            'total' => ceil($total / $places_per_page),
            'current' => $apage
          ));
          ?>
<div class='wrap'>
  <div class="icon32" style="background: url('<?php echo SAM_IMG_URL.'sam-list.png' ?>') no-repeat transparent; "><br/></div>
  <h2><?php _e('Managing Ads Places', SAM_DOMAIN); ?></h2>
  <?php
    include_once('errors.class.php');
    $errors = new samErrors();
    if(!empty($errors->errorString)) echo $errors->errorString;
  ?>
  <ul class="subsubsub">
    <li><a <?php if($mode === 'all') echo 'class="current"';?> href="<?php echo admin_url('admin.php'); ?>?page=sam-list&action=places&mode=all"><?php _e('All', SAM_DOMAIN); ?></a> (<?php echo $all_num; ?>) | </li>
    <li><a <?php if($mode === 'active') echo 'class="current"';?> href="<?php echo admin_url('admin.php'); ?>?page=sam-list&action=places&mode=active"><?php _e('Active', SAM_DOMAIN); ?></a> (<?php echo $active_num; ?>) | </li>
    <li><a <?php if($mode === 'trash') echo 'class="current"';?> href="<?php echo admin_url('admin.php'); ?>?page=sam-list&action=places&mode=trash"><?php _e('Trash', SAM_DOMAIN); ?></a> (<?php echo $trash_num; ?>)</li>
  </ul>
  <div class="tablenav">
    <div class="alignleft">
      <?php if($mode === 'trash') {?>
      <a class="button-secondary" href="<?php echo admin_url('admin.php'); ?>?page=sam-list&action=places&mode=trash&iaction=kill-em-all"><?php _e('Clear Trash', SAM_DOMAIN); ?></a>
      <?php } else { ?>
      <a class="button-secondary" href="<?php echo admin_url('admin.php'); ?>?page=sam-edit&action=new&mode=place"><?php _e('Add New Place', SAM_DOMAIN); ?></a>
      <?php } ?>
    </div>
    <div class='alignleft'>
      <a class="button-secondary" href="<?php echo admin_url('admin.php'); ?>?page=sam-list&action=places&mode=<?php echo $mode; ?>&iaction=clear-stats"><?php _e('Reset Statistics', SAM_DOMAIN); ?></a>
    </div>
    <div class="tablenav-pages">
      <?php $page_links_text = sprintf( '<span class="displaying-num">' . __( 'Displaying %s&#8211;%s of %s', SAM_DOMAIN ) . '</span>%s',
        number_format_i18n( $start + 1 ),
        number_format_i18n( min( $apage * $places_per_page, $total ) ),
        '<span class="total-type-count">' . number_format_i18n( $total ) . '</span>',
        $page_links
      ); echo $page_links_text; ?>
    </div>
  </div>
  <div class="clear"></div>
  <table class="widefat fixed" cellpadding="0">
    <thead>
      <tr>
        <th id="t-idg" class="manage-column column-title" style="width:5%;" scope="col"><?php _e('ID', SAM_DOMAIN); ?></th>
        <th id="t-name" class="manage-column column-title" style="width:31%;" scope="col"><?php _e('Place Name', SAM_DOMAIN);?></th>
        <th id="t-size" class="manage-column column-title" style="width:15%;" scope="col"><?php _e('Size', SAM_DOMAIN); ?></th>
        <th id="t-size" class="manage-column column-title" style="width:7%;" scope="col"><?php _e('Hits', SAM_DOMAIN); ?></th>
        <th id="t-size" class="manage-column column-title" style="width:7%;" scope="col"><?php _e('Total Hits', SAM_DOMAIN); ?></th>
        <th id="tp-items" class="manage-column column-title" style="width:10%;" scope="col"><div class="vers"><?php _e('Total Ads', SAM_DOMAIN); ?></div></th>
        <th id="tp-earnings" class="manage-column column-title" style="width:15%;" scope="col"><?php _e('Earnings', SAM_DOMAIN); ?></th>        
      </tr>
    </thead>
    <tfoot>
      <tr>
        <th id="b-idg" class="manage-column column-title" style="width:5%;" scope="col"><?php _e('ID', SAM_DOMAIN); ?></th>
        <th id="b-name" class="manage-column column-title" style="width:31%;" scope="col"><?php _e('Place Name', SAM_DOMAIN);?></th>
        <th id="b-size" class="manage-column column-title" style="width:15%;" scope="col"><?php _e('Size', SAM_DOMAIN); ?></th>
        <th id="t-size" class="manage-column column-title" style="width:7%;" scope="col"><?php _e('Hits', SAM_DOMAIN); ?></th>
        <th id="t-size" class="manage-column column-title" style="width:7%;" scope="col"><?php _e('Total Hits', SAM_DOMAIN); ?></th>
        <th id="bp-items" class="manage-column column-title" style="width:10%;" scope="col"><div class="vers"><?php _e('Total Ads', SAM_DOMAIN); ?></div></th>
        <th id="bp-earnings" class="manage-column column-title" style="width:15%;" scope="col"><?php _e('Earnings', SAM_DOMAIN); ?></th>
      </tr>
    </tfoot>
    <tbody>
        <?php
          $pSql = "SELECT 
                      sp.id,
                      sp.name,
                      sp.description,
                      sp.place_size,
                      sp.place_custom_width,
                      sp.place_custom_height,
                      @patch_hits := (IFNULL((SELECT COUNT(*) FROM $sTable ss WHERE (EXTRACT(YEAR_MONTH FROM NOW()) = EXTRACT(YEAR_MONTH FROM ss.event_time)) AND ss.id = 0 AND ss.pid = sp.id AND ss.event_type = 0), 0)) AS patch_hits,
                      (IFNULL((SELECT COUNT(*) FROM $sTable ss WHERE (EXTRACT(YEAR_MONTH FROM NOW()) = EXTRACT(YEAR_MONTH FROM ss.event_time)) AND ss.id > 0 AND ss.pid = sp.id AND ss.event_type = 0), 0) + IFNULL(@patch_hits, 0)) as total_ad_hits,
                      (IFNULL((SELECT SUM(IF(sa.cpm > 0, IFNULL((SELECT COUNT(*) FROM $sTable ss WHERE (EXTRACT(YEAR_MONTH FROM NOW()) = EXTRACT(YEAR_MONTH FROM ss.event_time)) AND ss.id = sa.id AND ss.pid = sa.pid AND ss.event_type = 0), 0) * sa.cpm / 1000, 0)) FROM $aTable sa WHERE sa.pid = sp.id), 0)) AS e_cpm,
                      (IFNULL((SELECT SUM(IF(sa.cpc > 0, IFNULL((SELECT COUNT(*) FROM $sTable ss WHERE (EXTRACT(YEAR_MONTH FROM NOW()) = EXTRACT(YEAR_MONTH FROM ss.event_time)) AND ss.id = sa.id AND ss.pid = sa.pid AND ss.event_type = 1), 0) * sa.cpc, 0)) FROM $aTable sa WHERE sa.pid = sp.id), 0)) AS e_cpc,
                      (IFNULL((SELECT SUM(IF(sa.ad_schedule AND sa.per_month > 0, DATEDIFF(CURDATE(), sa.ad_start_date) * sa.per_month / 30, 0)) FROM $aTable sa WHERE sa.pid = sp.id), 0)) AS e_month,
                      sp.trash,
                      (SELECT COUNT(*) FROM $aTable sa WHERE sa.pid = sp.id) AS items
                    FROM {$pTable} sp".
                    (($mode !== 'all') ? " WHERE sp.trash = ".(($mode === 'trash') ? 'TRUE' : 'FALSE') : '').
                    " LIMIT $offset, $places_per_page;";
          $places = $wpdb->get_results($pSql, ARRAY_A);          
          $i = 0;
          if(!is_array($places) || empty($places)) {
        ?>
      <tr class="no-items">
        <th class="colspanchange" colspan='7'><?php _e('There are no data ...', SAM_DOMAIN).$pTable; ?></th>
      </tr>
        <?php } else {
          switch($options['currency']) {
            case 'auto': $lang = str_replace('-', '_', get_bloginfo('language')); break;
            case 'usd' : $lang = 'en_US'; break;
            case 'euro': $lang = 'de_DE'; break;
            default: $lang = str_replace('-', '_', get_bloginfo('language'));
          }          
          $codeset = get_bloginfo('charset');
          setlocale(LC_MONETARY, $lang.'.'.$codeset);
          foreach($places as $row) {
            $apSize = $this->getAdSize($row['place_size'], $row['place_custom_width'], $row['place_custom_height']);
            $eMonth = round(floatval($row['e_month']), 2);
            $eCPM = round(floatval($row['e_cpm']), 2);
            $eCPC = round(floatval($row['e_cpc']), 2);
            $eTotal = $eMonth + $eCPC + $eCPM;
            $earnings = $eMonth ? __('Placement', SAM_DOMAIN).": ".money_format('%.2n', $eMonth)." <br/>" : '';
            $earnings .= $eCPM ? __('Hits', SAM_DOMAIN).": ".money_format('%.2n', $eCPM)." <br/>" : '';
            $earnings .= $eCPC ? __('Clicks', SAM_DOMAIN).": ".money_format('%.2n', $eCPC)." <br/>" : '';
            $earnings .= $eTotal ? "<strong>".__('Total', SAM_DOMAIN).": ".money_format('%.2n', $eTotal)." </strong>" : __('N/A', SAM_DOMAIN);
        ?>
      <tr id="<?php echo $row['id'];?>" class="<?php echo (($i & 1) ? 'alternate' : ''); ?> author-self status-publish iedit" valign="top">
        <th class="post-title column-title"><?php echo $row['id']; ?></th>
        <td class="post-title column-title">
          <strong style='display: inline;'><a href="<?php echo admin_url('admin.php'); ?>?page=sam-list&action=items&mode=active&item=<?php echo $row['id']; ?>"><?php echo $row['name'];?></a><?php echo ((($row['trash'] == true) && ($mode === 'all')) ? '<span class="post-state"> - '.__('in Trash', SAM_DOMAIN).'</span>' : ''); ?></strong><br/><?php echo $row['description'];?>
          <div class="row-actions">
            <span class="edit"><a href="<?php echo admin_url('admin.php'); ?>?page=sam-edit&action=edit&mode=place&item=<?php echo $row['id'] ?>" title="<?php _e('Edit Place', SAM_DOMAIN) ?>"><?php _e('Edit', SAM_DOMAIN); ?></a> | </span>
            <?php 
            if($row['trash'] == true) { 
              ?>
              <span class="untrash"><a href="<?php echo admin_url('admin.php'); ?>?page=sam-list&action=places&mode=<?php echo $mode ?>&iaction=untrash&item=<?php echo $row['id'] ?>" title="<?php _e('Restore this Place from the Trash', SAM_DOMAIN) ?>"><?php _e('Restore', SAM_DOMAIN); ?></a> | </span>
              <span class="delete"><a href="<?php echo admin_url('admin.php'); ?>?page=sam-list&action=places&mode=<?php echo $mode ?>&iaction=kill&item=<?php echo $row['id'] ?>" title="<?php _e('Remove this Place permanently', SAM_DOMAIN) ?>"><?php _e('Remove permanently', SAM_DOMAIN); ?></a></span>
            <?php 
            } 
            else { 
              ?>
              <span class="delete"><a href="<?php echo admin_url('admin.php'); ?>?page=sam-list&action=places&mode=<?php echo $mode ?>&iaction=delete&item=<?php echo $row['id'] ?>" title="<?php _e('Move this Place to the Trash', SAM_DOMAIN) ?>"><?php _e('Delete', SAM_DOMAIN); ?></a> | </span>
              <span class="edit"><a href="<?php echo admin_url('admin.php'); ?>?page=sam-list&action=items&mode=active&item=<?php echo $row['id']; ?>" title="<?php _e('View List of Place Ads', SAM_DOMAIN) ?>"><?php _e('View Ads', SAM_DOMAIN); ?></a> | </span>
              <span class="edit"><a href="<?php echo admin_url('admin.php'); ?>?page=sam-edit&action=new&mode=item&place=<?php echo $row['id']; ?>" title="<?php _e('Create New Ad', SAM_DOMAIN) ?>"><?php _e('New Ad', SAM_DOMAIN); ?></a></span>
            <?php } ?>
          </div>
        </td>
        <td class="post-title column-title"><?php echo $apSize['name']; ?></td>
        <td class="post-title column-title"><div class="post-com-count-wrapper" style="text-align: center;"><?php echo $row['patch_hits'];?></div></td>
        <td class="post-title column-title"><div class="post-com-count-wrapper" style="text-align: center;"><?php echo $row['total_ad_hits'];?></div></td>
        <td class="post-title column-title"><div class="post-com-count-wrapper" style="text-align: center;"><?php echo $row['items'];?></div></td>
        <td class="post-title column-title"><div class='sam-earnings'><?php echo $earnings;?></div></td>
      </tr>
        <?php $i++; }}?>
    </tbody>
  </table>
  <div class="tablenav">
    <div class="alignleft">
      <?php if($mode === 'trash') {?>
      <a class="button-secondary" href="<?php echo admin_url('admin.php'); ?>?page=sam-list&action=places&mode=trash&iaction=kill-em-all"><?php _e('Clear Trash', SAM_DOMAIN); ?></a>
      <?php } else { ?>
      <a class="button-secondary" href="<?php echo admin_url('admin.php'); ?>?page=sam-edit&action=new&mode=place"><?php _e('Add New Place', SAM_DOMAIN); ?></a>      
      <?php } ?>
    </div>
    <div class='alignleft'>
      <a class="button-secondary" href="<?php echo admin_url('admin.php'); ?>?page=sam-list&action=places&mode=<?php echo $mode; ?>&iaction=clear-stats"><?php _e('Reset Statistics', SAM_DOMAIN); ?></a>
    </div>
    <div class="tablenav-pages">
      <?php $page_links_text = sprintf( '<span class="displaying-num">' . __( 'Displaying %s&#8211;%s of %s', SAM_DOMAIN ) . '</span>%s',
        number_format_i18n( $start + 1 ),
        number_format_i18n( min( $apage * $places_per_page, $total ) ),
        '<span class="total-type-count">' . number_format_i18n( $total ) . '</span>',
        $page_links
      ); echo $page_links_text; ?>
    </div>
  </div>
</div>
          <?php
          break;

        case 'items':
          if(!is_null($item)) {
            if($iaction === 'delete') $wpdb->update( $aTable, array( 'trash' => true ), array( 'id' => $iitem ), array( '%d' ), array( '%d' ) );
            elseif($iaction === 'untrash') $wpdb->update( $aTable, array( 'trash' => false ), array( 'id' => $iitem ), array( '%d' ), array( '%d' ) );
            elseif($iaction === 'kill') $wpdb->query("DELETE FROM $aTable WHERE id = $iitem");
          }
          if($iaction === 'kill-em-all') $wpdb->query("DELETE FROM $aTable WHERE trash=true");
          $trash_num = $wpdb->get_var("SELECT COUNT(*) FROM $aTable WHERE (trash = TRUE) AND (pid = $item)");
          $active_num = $wpdb->get_var("SELECT COUNT(*) FROM $aTable WHERE (trash = FALSE) AND (pid = $item)");
          if(is_null($active_num)) $active_num = 0;
          if(is_null($trash_num)) $trash_num = 0;
          $all_num = $trash_num + $active_num;
          $places = $wpdb->get_row("SELECT id, name, trash FROM $pTable WHERE id = $item", ARRAY_A);

          $total = (($mode !== 'all') ? (($mode === 'trash') ? $trash_num : $active_num) : $all_num);
          $start = $offset = ( $apage - 1 ) * $items_per_page;

          $page_links = paginate_links( array(
            'base' => add_query_arg( 'apage', '%#%' ),
            'format' => '',
            'prev_text' => __('&laquo;'),
            'next_text' => __('&raquo;'),
            'total' => ceil($total / $items_per_page),
            'current' => $apage
          ));
          ?>
<div class="wrap">
  <div class="icon32" style="background: url('<?php echo SAM_IMG_URL.'sam-list.png'; ?>') no-repeat transparent; "><br/></div>
  <h2><?php echo __('Managing Items of Ads Place', SAM_DOMAIN).' "'.$places['name'].'" ('.$item.') '; ?></h2>
  <?php
    include_once('errors.class.php');
    $errors = new samErrors();
    if(!empty($errors->errorString)) echo $errors->errorString;
  ?>
  <ul class="subsubsub">
    <li><a <?php if($mode === 'all') echo 'class="current"';?> href="<?php echo admin_url('admin.php'); ?>?page=sam-list&action=items&mode=all&item=<?php echo $item ?>"><?php _e('All', SAM_DOMAIN); ?></a> (<?php echo $all_num; ?>) | </li>
    <li><a <?php if($mode === 'active') echo 'class="current"';?> href="<?php echo admin_url('admin.php'); ?>?page=sam-list&action=items&mode=active&item=<?php echo $item ?>"><?php _e('Active', SAM_DOMAIN); ?></a> (<?php echo $active_num; ?>) | </li>
    <li><a <?php if($mode === 'trash') echo 'class="current"';?> href="<?php echo admin_url('admin.php'); ?>?page=sam-list&action=items&mode=trash&item=<?php echo $item ?>"><?php _e('Trash', SAM_DOMAIN); ?></a> (<?php echo $trash_num; ?>)</li>
  </ul>
  <div class="tablenav">
    <div class="alignleft">
      <?php 
      if($mode === 'trash') { ?>
      <a class="button-secondary" href="<?php echo admin_url('admin.php'); ?>?page=sam-list&action=items&mode=trash&iaction=kill-em-all&item=<?php echo $item ?>"><?php _e('Clear Trash', SAM_DOMAIN); ?></a>
      <?php } else { ?>
      <a class="button-secondary" href="<?php echo admin_url('admin.php'); ?>?page=sam-edit&action=new&mode=item&place=<?php echo $places['id']; ?>"><?php _e('Add New Ad', SAM_DOMAIN); ?></a>
      <?php } ?>
    </div>
    <div class="alignleft">
      <a class="button-secondary" href="<?php echo admin_url('admin.php'); ?>?page=sam-list"><?php _e('Back to Ads Places Management', SAM_DOMAIN); ?></a>
    </div>
    <div class="tablenav-pages">
      <?php 
      $page_links_text = sprintf( '<span class="displaying-num">' . __( 'Displaying %s&#8211;%s of %s', SAM_DOMAIN ) . '</span>%s',
        number_format_i18n( $start + 1 ),
        number_format_i18n( min( $apage * $items_per_page, $total ) ),
        '<span class="total-type-count">' . number_format_i18n( $total ) . '</span>',
        $page_links
      ); 
      echo $page_links_text; 
      ?>
    </div>
  </div>
  <div class="clear"></div>
  <table class="widefat fixed" cellpadding="0">
    <thead>
      <tr>
        <th id="t-id" class="manage-column column-title" style="width:5%;" scope="col"><?php _e('ID', SAM_DOMAIN); ?></th>
        <th id="t-ad" class='manage-column column-title' style="width:55%;" scope="col"><?php _e('Advertisement', SAM_DOMAIN); ?></th>
        <th id="t-act" class="manage-column column-title" style="width:10%;" scope="col"><?php _e('Activity', SAM_DOMAIN);?></th>
        <th id="t-hits" class="manage-column column-title" style="width:10%;" scope="col"><?php _e('Hits', SAM_DOMAIN);?></th>
        <th id="t-clicks" class="manage-column column-title" style="width:10%;" scope="col"><?php _e('Clicks', SAM_DOMAIN);?></th>
        <th id="t-earnings" class="manage-column column-title" style="width:10%;" scope="col"><?php _e('Earnings', SAM_DOMAIN);?></th>
      </tr>
    </thead>
    <tfoot>
      <tr>
        <th id="b-id" class="manage-column column-title" style="width:5%;" scope="col"><?php _e('ID', SAM_DOMAIN); ?></th>
        <th id="b-ad" class='manage-column column-title' style="width:55%;" scope="col"><?php _e('Advertisement', SAM_DOMAIN); ?></th>
        <th id="b-act" class="manage-column column-title" style="width:10%;" scope="col"><?php _e('Activity', SAM_DOMAIN);?></th>
        <th id="b-hits" class="manage-column column-title" style="width:10%;" scope="col"><?php _e('Hits', SAM_DOMAIN);?></th>
        <th id="b-clicks" class="manage-column column-title" style="width:10%;" scope="col"><?php _e('Clicks', SAM_DOMAIN);?></th>
        <th id="b-earnings" class="manage-column column-title" style="width:10%;" scope="col"><?php _e('Earnings', SAM_DOMAIN);?></th>
      </tr>
    </tfoot>
    <tbody>
        <?php
        if($mode == 'all') $trash = "";
        else $trash = " AND (trash = ".(($mode === 'trash') ? 'TRUE' : 'FALSE').")";
            $aSql = "SELECT 
                      sa.id,
                      sa.pid,
                      sa.name,
                      sa.description,
                      @ad_hits := (SELECT COUNT(*) FROM $sTable ss WHERE (EXTRACT(YEAR_MONTH FROM NOW()) = EXTRACT(YEAR_MONTH FROM ss.event_time)) AND ss.id = sa.id AND ss.pid = sa.pid AND ss.event_type = 0) AS ad_hits,
                      @ad_clicks := (SELECT COUNT(*) FROM $sTable ss WHERE (EXTRACT(YEAR_MONTH FROM NOW()) = EXTRACT(YEAR_MONTH FROM ss.event_time)) AND ss.id = sa.id AND ss.pid = sa.pid AND ss.event_type = 1) AS ad_clicks,
                      sa.ad_weight,
                      (IF(sa.ad_schedule AND sa.per_month > 0, DATEDIFF(CURDATE(), sa.ad_start_date)*sa.per_month/30, 0)) AS e_month,
                      (sa.cpm * @ad_hits / 1000) AS e_cpm,
                      (sa.cpc * @ad_clicks) AS e_cpc,
                      sa.trash,
                      (IF(sa.ad_schedule, NOT (DATEDIFF(sa.ad_end_date, NOW()) IS NULL OR DATEDIFF(sa.ad_end_date, NOW()) > 0), FALSE) OR
                      IF(sa.limit_hits = 1 AND sa.hits_limit <= @ad_hits, TRUE, FALSE) OR
                      IF(sa.limit_clicks AND sa.clicks_limit <= @ad_clicks, TRUE, FALSE)) AS expired
                     FROM $aTable sa
                     WHERE (pid = $item) $trash
                     LIMIT $offset, $items_per_page";

          $items = $wpdb->get_results($aSql, ARRAY_A);
          $i = 0;
          if(!is_array($items) || empty($items)) {
        ?>
      <tr class="no-items" valign="top">
        <th class="colspanchange" colspan='6'><?php _e('There are no data ...', SAM_DOMAIN); ?></th>
      </tr>
        <?php 
          } 
          else {
            switch($options['currency']) {
              case 'auto': $lang = str_replace('-', '_', get_bloginfo('language')); break;
              case 'usd' : $lang = 'en_US'; break;
              case 'euro': $lang = 'de_DE'; break;
              default: $lang = str_replace('-', '_', get_bloginfo('language'));
            }          
            $codeset = get_bloginfo('charset');
            setlocale(LC_MONETARY, $lang.'.'.$codeset);
            foreach($items as $row) {
              if($row['ad_weight'] > 0 && !$row['trash'] && !$row['expired']) $activity = __('Yes', SAM_DOMAIN);
              else $activity = __('No', SAM_DOMAIN);
              $eMonth = round(floatval($row['e_month']), 2);
              $eCPM = round(floatval($row['e_cpm']), 2);
              $eCPC = round(floatval($row['e_cpc']), 2);
              $eTotal = $eMonth + $eCPC + $eCPM;
              $earnings = $eMonth ? __('Placement', SAM_DOMAIN).": ".money_format('%.2n', $eMonth)." <br/>" : '';
              $earnings .= $eCPM ? __('Hits', SAM_DOMAIN).": ".money_format('%.2n', $eCPM)." <br/>" : '';
              $earnings .= $eCPC ? __('Clicks', SAM_DOMAIN).": ".money_format('%.2n', $eCPC)." <br/>" : '';
              $earnings .= $eTotal ? "<strong>".__('Total', SAM_DOMAIN).": ".money_format('%.2n', $eTotal)." </strong>" : __('N/A', SAM_DOMAIN);
        ?>
      <tr id="<?php echo $row['id'];?>" class="<?php echo (($i & 1) ? 'alternate' : ''); ?> author-self status-publish iedit" valign="top">
        <th class="post-title column-title"><?php echo $row['id']; ?></th>
        <td class="column-icon column-title">
          <strong><a href="<?php echo admin_url('admin.php'); ?>?page=sam-edit&action=edit&mode=item&item=<?php echo $row['id']; ?>"><?php echo $row['name'];?></a><?php echo ((($row['trash'] == true) && ($mode === 'all')) ? '<span class="post-state"> - '.__('in Trash', SAM_DOMAIN).'</span>' : ''); ?></strong><br/><?php echo $row['description'];?>
          <div class="row-actions">
            <span class="edit"><a href="<?php echo admin_url('admin.php'); ?>?page=sam-edit&action=edit&mode=item&item=<?php echo $row['id'] ?>" title="<?php _e('Edit this Item of Ads Place', SAM_DOMAIN) ?>"><?php _e('Edit', SAM_DOMAIN); ?></a> | </span>
            <?php 
            if($row['trash'] == true) { 
              ?>
              <span class="untrash"><a href="<?php echo admin_url('admin.php'); ?>?page=sam-list&action=items&mode=<?php echo $mode ?>&iaction=untrash&item=<?php echo $row['pid'] ?>&iitem=<?php echo $row['id'] ?>" title="<?php _e('Restore this Ad from the Trash', SAM_DOMAIN) ?>"><?php _e('Restore', SAM_DOMAIN); ?></a> | </span>
              <span class="delete"><a href="<?php echo admin_url('admin.php'); ?>?page=sam-list&action=items&mode=<?php echo $mode ?>&iaction=kill&item=<?php echo $row['pid'] ?>&iitem=<?php echo $row['id'] ?>" title="<?php _e('Remove this Ad permanently', SAM_DOMAIN) ?>"><?php _e('Remove permanently', SAM_DOMAIN); ?></a> </span>
            <?php } else { ?><span class="delete"><a href="<?php echo admin_url('admin.php'); ?>?page=sam-list&action=items&mode=<?php echo $mode ?>&iaction=delete&item=<?php echo $row['pid'] ?>&iitem=<?php echo $row['id'] ?>" title="<?php _e('Move this item to the Trash', SAM_DOMAIN) ?>"><?php _e('Delete', SAM_DOMAIN); ?></a> </span><?php } ?>
          </div>
        </td>
        <td class="post-title column-title"><?php echo $activity; ?></td>
        <td class="post-title column-title"><?php echo $row['ad_hits'];?></td>
        <td class="post-title column-title"><?php echo $row['ad_clicks'];?></td>
        <td class="post-title column-title"><div class='sam-earnings'><?php echo $earnings;?></div></td>
      </tr>
        <?php $i++; }}?>
    </tbody>
  </table>
  <div class="tablenav">
    <div class="alignleft">
      <?php 
      if($mode === 'trash') { ?>
      <a class="button-secondary" href="<?php echo admin_url('admin.php'); ?>?page=sam-list&action=items&mode=trash&iaction=kill-em-all&item=<?php echo $item ?>"><?php _e('Clear Trash', SAM_DOMAIN); ?></a>
      <?php } else { ?>
      <a class="button-secondary" href="<?php echo admin_url('admin.php'); ?>?page=sam-edit&action=new&mode=item&place=<?php echo $places['id']; ?>"><?php _e('Add New Ad', SAM_DOMAIN); ?></a>
      <?php } ?>
    </div>
    <div class="alignleft">
      <a class="button-secondary" href="<?php echo admin_url('admin.php'); ?>?page=sam-list"><?php _e('Back to Ads Places Management', SAM_DOMAIN); ?></a>
    </div>
    <div class="tablenav-pages">
      <?php $page_links_text = sprintf( '<span class="displaying-num">' . __( 'Displaying %s&#8211;%s of %s', SAM_DOMAIN ) . '</span>%s',
        number_format_i18n( $start + 1 ),
        number_format_i18n( min( $apage * $items_per_page, $total ) ),
        '<span class="total-type-count">' . number_format_i18n( $total ) . '</span>',
        $page_links
      ); echo $page_links_text; ?>
    </div>
  </div>
</div>
          <?php
          break;
      }
    }
  }
}
?>
