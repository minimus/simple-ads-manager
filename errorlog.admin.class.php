<?php
if(!class_exists('SamErrorLog')) {
  class SamErrorLog {
    private $settings = array();

    public function  __construct($settings) {
      $this->settings = $settings;
    }

    public function page() {
      global $wpdb;
      $eTable = $wpdb->prefix . 'sam_errors';

      if(isset($_GET['mode'])) $mode = $_GET['mode'];
      else $mode = 'active';
      if(isset($_GET["action"])) $action = $_GET['action'];
      else $action = 'errors';
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

      if(!is_null($item)) {
        if($iaction === 'delete') $wpdb->update( $eTable, array( 'resolved' => true ), array( 'id' => $item ), array( '%d' ), array( '%d' ) );
        elseif($iaction === 'untrash') $wpdb->update( $eTable, array( 'resolved' => false ), array( 'id' => $item ), array( '%d' ), array( '%d' ) );
        elseif($iaction === 'kill') $wpdb->query("DELETE FROM $eTable WHERE id=$item");
      }
      if($iaction === 'kill-em-all') $wpdb->query("DELETE FROM $eTable");
      if($iaction === 'kill-resolved') $wpdb->query("DELETE FROM $eTable WHERE resolved = TRUE;");
      $resolved_num = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $eTable WHERE resolved = %d", 1));
      $active_num = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $eTable WHERE resolved = %d", 0));
      if(is_null($active_num)) $active_num = 0;
      if(is_null($resolved_num)) $resolved_num = 0;
      $all_num = $resolved_num + $active_num;
      $total = (($mode !== 'all') ? (($mode === 'trash') ? $resolved_num : $active_num) : $all_num);
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
<div class="wrap">
  <div class="icon32" style="background: url('<?php echo SAM_IMG_URL.'sam-bug.png' ?>') no-repeat transparent; "><br/></div>
  <h2><?php _e('Error Log', SAM_DOMAIN); ?></h2>
  <ul class="subsubsub">
    <li><a <?php if($mode === 'all') echo 'class="current"';?> href="<?php echo admin_url('admin.php'); ?>?page=sam-errors&action=errors&mode=all"><?php _e('All', SAM_DOMAIN); ?></a> (<?php echo $all_num; ?>) | </li>
    <li><a <?php if($mode === 'active') echo 'class="current"';?> href="<?php echo admin_url('admin.php'); ?>?page=sam-errors&action=errors&mode=active"><?php _e('Active', SAM_DOMAIN); ?></a> (<?php echo $active_num; ?>) | </li>
    <li><a <?php if($mode === 'resolved') echo 'class="current"';?> href="<?php echo admin_url('admin.php'); ?>?page=sam-errors&action=errors&mode=resolved"><?php _e('Resolved', SAM_DOMAIN); ?></a> (<?php echo $resolved_num; ?>)</li>
  </ul>
  <div class="tablenav">
    <div class="alignleft">
      <a class="button-secondary" href="<?php echo admin_url('admin.php'); ?>?page=sam-errors&action=errors&mode=resolved&iaction=kill-em-all"><?php _e('Clear Error Log', SAM_DOMAIN); ?></a>
    </div>
    <div class="alignleft">
      <a class="button-secondary" href="<?php echo admin_url('admin.php'); ?>?page=sam-errors&action=errors&mode=resolved&iaction=kill-resolved"><?php _e('Clear Resolved', SAM_DOMAIN); ?></a>
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
        <th id="t-status" class="manage-column column-title" style="width:7%;" scope="col"><?php _e('Status', SAM_DOMAIN); ?></th>
        <th id="t-date" class="manage-column column-title" style="width:13%;" scope="col"><?php _e('Date', SAM_DOMAIN);?></th>
        <th id="t-table" class="manage-column column-title" style="width:15%;" scope="col"><?php _e('Table', SAM_DOMAIN);?></th>
        <th id="t-type" class="manage-column column-title" style="width: 10%" scope="col"><?php _e('Type', SAM_DOMAIN) ?></th>
        <th id="t-msg" class="manage-column column-title" style="width:50%;" scope="col"><?php _e('Error Massage', SAM_DOMAIN);?></th>
      </tr>
    </thead>
    <tfoot>
      <tr>
        <th id="b-idg" class="manage-column column-title" style="width:5%;" scope="col"><?php _e('ID', SAM_DOMAIN); ?></th>
        <th id="b-status" class="manage-column column-title" style="width:7%;" scope="col"><?php _e('Status', SAM_DOMAIN); ?></th>
        <th id="b-date" class="manage-column column-title" style="width:13%;" scope="col"><?php _e('Date', SAM_DOMAIN);?></th>
        <th id="b-table" class="manage-column column-title" style="width:15%;" scope="col"><?php _e('Table', SAM_DOMAIN);?></th>
        <th id="b-type" class="manage-column column-title" style="width: 10%" scope="col"><?php _e('Type', SAM_DOMAIN) ?></th>
        <th id="b-msg" class="manage-column column-title" style="width:50%;" scope="col"><?php _e('Error Massage', SAM_DOMAIN);?></th>
      </tr>
    </tfoot>
    <tbody>
      <?php
      $eSql = "SELECT
                  se.id,
                  se.error_date,
                  UNIX_TIMESTAMP(se.error_date) as unix_error_date,
                  se.table_name,
                  se.error_type,
                  se.error_msg,
                  se.error_sql,
                  se.resolved
                FROM $eTable se".
                (($mode !== 'all') ? " WHERE se.resolved = ".(($mode === 'resolved') ? 'TRUE' : 'FALSE') : '').
                " LIMIT $offset, $places_per_page";
      $eData = $wpdb->get_results($eSql, ARRAY_A);
      $eTypes = array(__('Warning', SAM_DOMAIN), __('Update Error', SAM_DOMAIN), __('Output Error', SAM_DOMAIN));
      $i = 0;
      if(!is_array($eData) || empty ($eData)) {
      ?>
      <tr class="no-items">
        <th class="colspanchange" colspan='6'><?php _e('There are no data ...', SAM_DOMAIN); ?></th>
      </tr>
        <?php } else {
          foreach($eData as $row) {
        ?>
      <tr id="<?php echo $row['id'];?>" class="<?php echo (($i & 1) ? 'alternate' : ''); ?> author-self status-publish iedit" valign="top">
        <th class="post-title column-title"><?php echo $row['id']; ?></th>
        <td class="column-icon media-icon">
        <?php
          if($row['resolved'] == false) {
            $img = SAM_IMG_URL.'warning-32.png';
            $alt = __('Warning', SAM_DOMAIN);
          }
          else {
            $img = SAM_IMG_URL.'ok-32.png';
            $alt = __('Ok', SAM_DOMAIN);
          }
        ?>
          <img src="<?php echo $img; ?>" alt="<?php echo $alt; ?>">
        </td>
        <td class="post-title column-title"><?php echo date_i18n(get_option('date_format').' '.get_option('time_format'), $row['unix_error_date']); ?></td>
        <td class="post-title column-title"><?php echo $row['table_name']; ?></td>
        <td class="post-title column-title"><?php echo $eTypes[$row['error_type']]; ?></td>
        <td class="post-title column-title">
          <strong style='display: inline;'><?php echo $row['error_msg'];?><?php echo ((($row['resolved'] == true) && ($mode === 'all')) ? '<span class="post-state"> - '.__('Resolved', SAM_DOMAIN).'</span>' : ''); ?></strong>
          <div class="row-actions">
            <span class="edit"><a id="e-<?php echo $row['id']; ?>" class="more-info" href="#" title="<?php _e('More Info', SAM_DOMAIN) ?>"><?php _e('More Info', SAM_DOMAIN); ?></a> | </span>
            <?php
            if($row['resolved'] == true) {
              ?>
              <span class="delete"><a href="<?php echo admin_url('admin.php'); ?>?page=sam-errors&action=errors&mode=<?php echo $mode ?>&iaction=untrash&item=<?php echo $row['id'] ?>" title="<?php _e('Restore this Error', SAM_DOMAIN) ?>"><?php _e('Not Resolved', SAM_DOMAIN); ?></a> | </span>
              <span class="delete"><a href="<?php echo admin_url('admin.php'); ?>?page=sam-errors&action=errors&mode=<?php echo $mode ?>&iaction=kill&item=<?php echo $row['id'] ?>" title="<?php _e('Remove permanently', SAM_DOMAIN) ?>"><?php _e('Remove permanently', SAM_DOMAIN); ?></a></span>
            <?php
            }
            else {
              ?>
              <span class="untrash"><a href="<?php echo admin_url('admin.php'); ?>?page=sam-errors&action=errors&mode=<?php echo $mode ?>&iaction=delete&item=<?php echo $row['id'] ?>" title="<?php _e('Move to Trash', SAM_DOMAIN) ?>"><?php _e('Resolved', SAM_DOMAIN); ?></a></span>
            <?php } ?>
          </div>
        </td>
      </tr>
        <?php $i++; }}?>
    </tbody>
  </table>
  <div class="tablenav">
    <div class="alignleft">
      <a class="button-secondary" href="<?php echo admin_url('admin.php'); ?>?page=sam-errors&action=errors&mode=trash&iaction=kill-em-all"><?php _e('Clear Error Log', SAM_DOMAIN); ?></a>
    </div>
    <div class="alignleft">
      <a class="button-secondary" href="<?php echo admin_url('admin.php'); ?>?page=sam-errors&action=errors&mode=resolved&iaction=kill-resolved"><?php _e('Clear Resolved', SAM_DOMAIN); ?></a>
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
      /*$struct = $wpdb->get_results('DESCRIBE wp_sam_blocks;', ARRAY_A);
      foreach($struct as $var) {
        $out = " '{$var['Field']}' => array('Type' => \"{$var['Type']}\", 'Null' => '{$var['Null']}', 'Key' => '{$var['Key']}', 'Default' => '{$var['Default']}', 'Extra' => '{$var['Extra']}'),";
        echo $out."<br>";
      }*/
    }
  }
}
?>