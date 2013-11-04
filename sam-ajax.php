<?php
/**
 * Author: minimus
 * Date: 24.10.13
 * Time: 12:14
 */

define('DOING_AJAX', true);

if (!isset( $_POST['action'])) die('-1');
if (isset( $_POST['level'] )) {
  $rootLevel = intval($_POST['level']);
  $root = dirname( __FILE__ );
  for( $i = 0; $i < $rootLevel; $i++ ) $root = dirname( $root );
}
else $root = dirname(dirname(dirname(dirname(__FILE__))));

ini_set('html_errors', 0);

define('SHORTINIT', true);

require_once( $root . '/wp-load.php' );

global $wpdb;

$oTable = $wpdb->prefix . 'options';
$oSql = "SELECT $oTable.option_value FROM $oTable WHERE $oTable.option_name = 'blog_charset'";
$charset = $wpdb->get_var($oSql);

//Typical headers
@header("Content-Type: application/json; charset=$charset");
@header( 'X-Robots-Tag: noindex' );

send_nosniff_header();
nocache_headers();

$action = !empty($_POST['action']) ? 'sam_ajax_' . stripslashes($_POST['action']) : false;

//A bit of security
$allowed_actions = array(
  'sam_ajax_sam_click',
  'sam_ajax_sam_show'
);

if(in_array($action, $allowed_actions)){
  switch($action) {
    case 'sam_ajax_sam_click':
      $out = null;
      if(isset($_POST['sam_ad_id'])) {
        $adId = $_POST['sam_ad_id'];
        $aId = explode('_', $adId);
        $id = (integer) $aId[1];
      }
      else $id = -100;

      if($id > 0) {
        $aTable = $wpdb->prefix . "sam_ads";

        $aSql = "UPDATE $aTable SET $aTable.ad_clicks = $aTable.ad_clicks+1 WHERE $aTable.id = %d;";
        $result = $wpdb->query($wpdb->prepare($aSql, $id));
        if($result === 1) {
          $out = array('id' => $id, 'result' => $result, 'charset' => $charset);
          wp_send_json_success( $out );
        }
        else wp_send_json_error(array('id' => $id, 'sql' => $wpdb->last_query, 'error' => $wpdb->last_error));
      }
      else wp_send_json_error(array('id' => $id));
      break;

    case 'sam_ajax_sam_show':

      break;
  }
}
else wp_send_json_error(array('error' => 'Not allowed action'));