<?php

/**
 * @author minimus
 * @copyright 2010
 */

/*$wpconfig = realpath("../../../../wp-config.php");
if (!file_exists($wpconfig))  {
	echo "Could not found wp-config.php. Error in path :\n\n".$wpconfig ;	
	die;	
}
require_once($wpconfig);*/
require_once('../../../../wp-admin/admin.php');

if ( function_exists( 'load_plugin_textdomain' ) )
  load_plugin_textdomain( SAM_DOMAIN, false, basename( SAM_PATH ) );

global $wpdb;
$pTable = $wpdb->prefix . "sam_places";

$places = $wpdb->get_results("SELECT id, name FROM {$pTable} WHERE trash IS FALSE", ARRAY_A);
?>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title><?php _e('Insert Ads Place', SAM_DOMAIN); ?></title>
  <meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>">
	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/utils/form_utils.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/utils/mctabs.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/utils/editable_selects.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo SAM_URL ?>js/sam-dialog.js"></script>
	<base target="_self" />
  <style>
    table, input {
      font-size: 11px;
    }
  </style>
</head>

<body id="link" onload="tinyMCEPopup.executeOnLoad('init();');document.body.style.display='';" style="display: none">
  <form name="svb" onsubmit="insertSAMCode();return false;" action="#">
    <div class="tabs">
      <ul>
        <li id="basic_tab" class="current"><span><a href="javascript:mcTabs.displayTab('basic_tab','basic_panel');" onmousedown="return false;"><?php _e("Basic Settings", SAM_DOMAIN); ?></a></span></li>
      </ul>
    </div>
    <div class="panel_wrapper" style="height: 200px;">
      <div id="basic_panel" class="panel current">
		    <table border="0" cellpadding="4" cellspacing="0">
		      <tr>
			      <td nowrap="nowrap"><label for="sam_id"><?php echo __('Ads Place', SAM_DOMAIN).':'; ?></label></td>
			      <td>
              <select name='sam_id' id='sam_id'>
              <?php
              foreach($places as $place) {
              ?>
                <option value='<?php echo $place['id']; ?>'><?php echo $place['name'] ?></option>
              <?php
              }
              ?>
              </select>
            </td>
		      </tr>
				</table>
		    <table border="0" cellpadding="4" cellspacing="0">
 					<tr>						
						<td>
							<label for="sam_item_id"><input type="radio" id="sam_item_id" name="sam_item" class="radio" value="id" checked="checked" />
                <?php _e('Ads Place ID', SAM_DOMAIN); ?>
              </label>&nbsp;&nbsp;&nbsp;&nbsp;
							<label for="sam_item_name"><input type="radio" id="sam_item_name" name="sam_item" class="radio" value="name" />
                <?php _e('Ads Place Name', SAM_DOMAIN); ?>
              </label>
						</td>
					</tr>
				</table>
				<table border="0" cellpadding="4" cellspacing="0">
 					<tr>						
						<td>
							<input type='checkbox' name='sam_codes' id='sam_codes' checked='checked'>
              <label for="sam_codes">
                <?php _e('Allow Ads Places predefined codes', SAM_DOMAIN); ?>
              </label>
						</td>
					</tr>
				</table>
        <p><?php //echo 'Path Info: '.$_SERVER['DOCUMENT_ROOT'].' original path info: '.realpath("../../../../wp-config.php"); ?></p>
      </div>
		</div>
		<div class="mceActionPanel">
		  <div style="float: left">
        <input type="button" id="cancel" name="cancel" value="<?php _e("Cancel", SAM_DOMAIN); ?>" onclick="tinyMCEPopup.close();" />
      </div>
      <div style="float: right">
        <input type="submit" id="insert" name="insert" value="<?php _e("Insert", SAM_DOMAIN); ?>" onclick="insertSAMCode();" />
      </div>
    </div>
  </form>
