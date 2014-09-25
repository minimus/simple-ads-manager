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
$oSql = "SELECT wo.option_value FROM $oTable wo WHERE wo.option_name = %s  LIMIT 1;";
$charset = $wpdb->get_var($wpdb->prepare( $oSql, 'blog_charset' ));
$aTable = $wpdb->prefix . "sam_ads";
$pTable = $wpdb->prefix . 'sam_places';
$sTable = $wpdb->prefix . 'sam_stats';

$options = get_option('samPluginOptions');

function sanitize_option($option, $value) {
  return $value;
}

//Typical headers
@header("Content-Type: application/json; charset=$charset");
@header( 'X-Robots-Tag: noindex' );

send_nosniff_header();
nocache_headers();

$action = !empty($_POST['action']) ? 'sam_ajax_' . stripslashes($_POST['action']) : false;

//A bit of security
$allowed_actions = array(
  'sam_ajax_sam_click',
  'sam_ajax_sam_hit',
	'sam_ajax_sam_hits',
  'sam_ajax_sam_maintenance'
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
      elseif(isset($_POST['id'])) {
        $id = $_POST['id'];
        $pid = $_POST['pid'];
      }
      else $id = -100;

      if($id > 0) {
        //$aSql = "UPDATE $aTable sa SET sa.ad_clicks = sa.ad_clicks + 1 WHERE sa.id = %d;";
        $aSql = "INSERT HIGH_PRIORITY INTO $sTable (id, pid, event_time, event_type) VALUES (%d, %d, NOW(), 1);";
        $result = $wpdb->query($wpdb->prepare($aSql, $id, $pid));
        if($result === 1) {
          $out = array('success' => true, 'id' => $id, 'result' => $result, 'charset' => $charset);
          echo json_encode( $out );
        }
        else echo json_encode(array('success' => false, 'id' => $id, 'sql' => $wpdb->last_query, 'error' => $wpdb->last_error));
      }
      else echo json_encode(array('success' => false, 'id' => $id));
      break;

    case 'sam_ajax_sam_hit':
      if(isset($_POST['id']) && isset($_POST['pid'])) {
        $id = $_POST['id'];
        $pid = $_POST['pid'];
        $cid = ($id == 0) ? $pid : $id;
        $result = 0;
        //if($id > 0) $sql = "UPDATE $aTable sa SET sa.ad_hits = sa.ad_hits + 1, sa.ad_weight_hits = sa.ad_weight_hits + 1 WHERE sa.id = %d;";
        /*if($id > 0) $sql = "UPDATE $aTable sa SET sa.ad_hits = sa.ad_hits + 1 WHERE sa.id = %d;";
        elseif($id == 0) $sql = "UPDATE $pTable sp SET sp.patch_hits = sp.patch_hits + 1 WHERE sp.id = %d;";
        else $sql = '';*/
        $sql = "INSERT INTO $sTable (id, pid, event_time, event_type) VALUES (%d, %d, NOW(), 0);";
        if(!empty($sql)) $result = $wpdb->query($wpdb->prepare($sql, $id, $pid));
        if($result === 1) echo json_encode(array('success' => true, 'id' => $id, 'pid' => $pid));
        else echo json_encode(array(
          'success' => false,
          'id' => $id,
          'pid' => $pid,
          'cid' => $cid,
          'result' => $result,
          'sql' => $wpdb->prepare($sql, $cid)
        ));
      }
      else echo json_encode(array('success' => false));
      break;

	  case 'sam_ajax_sam_hits':
		  if(isset($_POST['hits']) && is_array($_POST['hits'])) {
			  $hits = $_POST['hits'];
			  $values = '';
			  $remoteAddr = $_SERVER['REMOTE_ADDR'];
			  foreach($hits as $hit) {
				  $values .= ((empty($values)) ? '' : ', ') . "({$hit[1]}, {$hit[0]}, NOW(), 0, \"{$remoteAddr}\")";
			  }
			  $sql = "INSERT INTO $sTable (id, pid, event_time, event_type, remote_addr) VALUES {$values};";
			  $result = $wpdb->query($sql);
			  if($result > 0) echo json_encode(array('success' => true, 'sql' => $sql, 'addr' => $_SERVER['REMOTE_ADDR']));
			  else echo json_encode(array(
				  'success' => false,
				  'result' => $result,
				  'sql' => $sql,
				  'hits' => $hits,
				  'values' => $values
			  ));
		  }
		  break;

    case 'sam_ajax_sam_maintenance':
      if(false === ($mDate = get_transient( 'sam_maintenance_date' )) && $options['mailer']) {
        include_once('sam.tools.php');
        $mailer = new SamMailer($options);
        $samSM = $mailer->sendMails();
        $date = new DateTime('now');
        if($options['mail_period'] == 'monthly') {
          $date->modify('+1 month');
          $nextDate = new DateTime($date->format('Y-m-01 02:00'));
          $diff = $nextDate->format('U') - $_SERVER['REQUEST_TIME'];
        }
        else {
          $dd = 8 - ((integer) $date->format('N'));
          $date->modify("+{$dd} day");
          $nextDate = new DateTime($date->format('Y-m-d 02:00'));
          $diff = (8 - ((integer) $date->format('N'))) * DAY_IN_SECONDS;
        }
        $format = get_option('date_format').' '.get_option('time_format');
        set_transient( 'sam_maintenance_date', $nextDate->format($format), $diff );
        echo json_encode(array('success' => true, 'send_mail' => $samSM));
      }
      else echo json_encode(array('success' => true, 'send_mail' => false));
      break;

    default:
      echo json_encode(array('success' => false, 'error' => 'Data error'));
      break;
  }
}
else echo json_encode(array('success' => false, 'error' => 'Not allowed'));
wp_die();
