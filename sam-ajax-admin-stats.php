<?php
/**
 * Created by PhpStorm.
 * Author: minimus
 * Date: 22.12.13
 * Time: 9:00
 */

define('DOING_AJAX', true);

if (!isset( $_REQUEST['action'])) die('-1');
if (isset( $_REQUEST['level'] )) {
  $rootLevel = intval($_REQUEST['level']);
  $root = dirname( __FILE__ );
  for( $i = 0; $i < $rootLevel; $i++ ) $root = dirname( $root );
}
else $root = dirname(dirname(dirname(dirname(__FILE__))));

ini_set('html_errors', 0);

define('SHORTINIT', true);

require_once( $root . '/wp-load.php' );

global $wpdb;

$sTable = $wpdb->prefix . 'sam_stats';
$oTable = $wpdb->prefix . 'options';
$aTable = $wpdb->prefix . 'sam_ads';

$oSql = "SELECT $oTable.option_value FROM $oTable WHERE $oTable.option_name = 'blog_charset'";
$charset = $wpdb->get_var($oSql);

function samDaysInMonth($month, $year) {
  return $month == 2 ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year % 400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31);
}

@header("Content-Type: application/json; charset=$charset");
@header( 'X-Robots-Tag: noindex' );

send_nosniff_header();
nocache_headers();

$action = !empty($_REQUEST['action']) ? 'sam_ajax_' . stripslashes($_REQUEST['action']) : false;

//A bit of security
$allowed_actions = array(
  'sam_ajax_load_item_stats',
  'sam_ajax_load_stats',
  'sam_ajax_load_ads'
);
$out = array();

if(in_array($action, $allowed_actions)) {
  switch($action) {
    case 'sam_ajax_load_stats':
      if(isset($_POST['id'])) {
        $pid = $_POST['id'];
        $sMonth = (isset($_POST['sm'])) ? $_POST['sm'] : 0;

        $date = new DateTime('now');
        $si = '-'.$sMonth.' month';
        $date->modify($si);
        $month = $date->format('Y-m-d');
        $days = $date->format('t');

        $sql = "SELECT
                  DATE_FORMAT(ss.event_time, %s) AS ed,
                  COUNT(*) AS hits
                FROM $sTable ss
                WHERE (EXTRACT(YEAR_MONTH FROM %s) = EXTRACT(YEAR_MONTH FROM ss.event_time)) AND ss.event_type = %d AND ss.pid = %d
                GROUP BY ed;";
        $hits = $wpdb->get_results($wpdb->prepare($sql, '%e', $month, 0, $pid), ARRAY_A);
        $clicks = $wpdb->get_results($wpdb->prepare($sql, '%e', $month, 1, $pid), ARRAY_A);
        for($i = 1; $i <= $days; $i++) {
          $hitsFull[$i - 1] = array( $i, 0);
          $clicksFull[$i - 1] = array($i, 0);
        }
        foreach($hits as $hit) $hitsFull[$hit['ed'] - 1][1] = (integer) $hit['hits'];
        foreach($clicks as $click) $clicksFull[$click['ed'] - 1][1] = (integer) $click['hits'];

        $sql = "SELECT
                  (SELECT
                    COUNT(*)
                    FROM $sTable ss
                    WHERE (EXTRACT(YEAR_MONTH FROM %s) = EXTRACT(YEAR_MONTH FROM ss.event_time))
                      AND ss.event_type = 0 AND ss.pid = %d) AS hits,
                  (SELECT
                    COUNT(*)
                    FROM $sTable ss
                    WHERE (EXTRACT(YEAR_MONTH FROM %s) = EXTRACT(YEAR_MONTH FROM ss.event_time))
                      AND ss.event_type = 1 AND ss.pid = %d) AS clicks;";
        $total = $wpdb->get_row($wpdb->prepare($sql, $month, $pid, $month, $pid));

        $out = array('hits' => $hitsFull, 'clicks' => $clicksFull, 'total' => $total);
      }
      else $out = array("status" => "error", "message" => "Error");
      break;

    case 'sam_ajax_load_item_stats':
      if(isset($_POST['id'])) {
        $id = $_POST['id'];
        $sMonth = (isset($_POST['sm'])) ? $_POST['sm'] : 0;

        $date = new DateTime('now');
        $si = '-'.$sMonth.' month';
        $date->modify($si);
        $month = $date->format('Y-m-d');
        $days = $date->format('t');

        $sql = "SELECT
                  DATE_FORMAT(ss.event_time, %s) AS ed,
                  COUNT(*) AS hits
                FROM $sTable ss
                WHERE (EXTRACT(YEAR_MONTH FROM %s) = EXTRACT(YEAR_MONTH FROM ss.event_time)) AND ss.event_type = %d AND ss.id = %d
                GROUP BY ed;";
        $hits = $wpdb->get_results($wpdb->prepare($sql, '%e', $month, 0, $id), ARRAY_A);
        $clicks = $wpdb->get_results($wpdb->prepare($sql, '%e', $month, 1, $id), ARRAY_A);
        for($i = 1; $i <= $days; $i++) {
          $hitsFull[$i - 1] = array( $i, 0);
          $clicksFull[$i - 1] = array($i, 0);
        }
        foreach($hits as $hit) $hitsFull[$hit['ed'] - 1][1] = (integer) $hit['hits'];
        foreach($clicks as $click) $clicksFull[$click['ed'] - 1][1] = (integer) $click['hits'];

        $sql = "SELECT
                  (SELECT
                    COUNT(*)
                    FROM $sTable ss
                    WHERE (EXTRACT(YEAR_MONTH FROM %s) = EXTRACT(YEAR_MONTH FROM ss.event_time))
                      AND ss.event_type = 0 AND ss.id = %d) AS hits,
                  (SELECT
                    COUNT(*)
                    FROM $sTable ss
                    WHERE (EXTRACT(YEAR_MONTH FROM %s) = EXTRACT(YEAR_MONTH FROM ss.event_time))
                      AND ss.event_type = 1 AND ss.id = %d) AS clicks;";
        $total = $wpdb->get_row($wpdb->prepare($sql, $month, $id, $month, $id));

        $out = array('hits' => $hitsFull, 'clicks' => $clicksFull, 'total' => $total);
      }
      else $out = array("status" => "error", "message" => "Error");
      break;

    case 'sam_ajax_load_ads':
      if(isset($_POST['id'])) {
        $id = $_POST['id'];
        $sMonth = (isset($_POST['sm'])) ? $_POST['sm'] : 0;

        $date = new DateTime('now');
        $si = '-'.$sMonth.' month';
        $date->modify($si);
        $month = $date->format('Y-m-d');
        //$days = $date->format('t');

        $sql = "SELECT
                  sa.id,
                  sa.pid,
                  sa.name,
                  sa.description,
                  @ad_hits := (SELECT COUNT(*) FROM $sTable ss WHERE (EXTRACT(YEAR_MONTH FROM %s) = EXTRACT(YEAR_MONTH FROM ss.event_time)) AND ss.id = sa.id AND ss.pid = sa.pid AND ss.event_type = 0) AS ad_hits,
                  @ad_clicks := (SELECT COUNT(*) FROM $sTable ss WHERE (EXTRACT(YEAR_MONTH FROM %s) = EXTRACT(YEAR_MONTH FROM ss.event_time)) AND ss.id = sa.id AND ss.pid = sa.pid AND ss.event_type = 1) AS ad_clicks,
                  sa.ad_weight,
                  (sa.cpm / @ad_hits * 1000) AS e_cpm,
                  sa.cpc AS e_cpc,
                  (@ad_clicks / @ad_hits * 100) AS e_ctr
                FROM $aTable sa
                WHERE (sa.pid = %d) AND sa.trash = FALSE AND NOT (sa.ad_schedule AND sa.ad_end_date < NOW());";
        $rows = $wpdb->get_results($wpdb->prepare($sql, $month, $month, $id), ARRAY_A);

        $k = 0;
        foreach($rows as &$row) {
          $k++;
          $row['recid'] = $k;
        }

        $out = array(
          'status' => 'success',
          'total' => count($rows),
          'records' => $rows,
          'sql' => $wpdb->prepare($sql, $sMonth, $sMonth, $id)
        );
      }
      else $out = array('status' => 'error');
      break;

    default:
      $out = array("status" => "error", "message" => "Error");
      break;
  }
  echo json_encode( $out ); wp_die();
}
else $out = array("status" => "error", "message" => "Error");

wp_send_json_error($out);