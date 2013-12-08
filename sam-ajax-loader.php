<?php
/**
 * Created by PhpStorm.
 * Author: minimus
 * Date: 12.11.13
 * Time: 11:47
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
  'sam_ajax_load_place',
  'sam_ajax_load_zone'
);

if(in_array($action, $allowed_actions)) {
  switch($action) {
    case 'sam_ajax_load_place':
      if(isset($_POST['id']) && isset($_POST['pid']) && isset($_POST['wc'])) {
        $placeId = $_POST['pid'];
        $adId = $_POST['id'];
        $clauses = unserialize(base64_decode($_POST['wc']));
        $args = array('id' => ($adId == 0) ? $placeId : $adId);
        if(isset($_POST['codes'])) $codes = (bool)($_POST['codes']);
        else $codes = false;
        include_once('ad.class.php');
        if($adId == 0) $ad = new SamAdPlace($args, $codes, false, $clauses, true);
        else $ad = new SamAd($args, $codes, false, true);
        echo json_encode(array(
          'success' => true,
          'ad' => $ad->ad,
          'id' => $ad->id,
          'pid' => $ad->pid,
          'cid' => $ad->cid,
          'clauses' => $clauses
        ));
      }
      break;
  }
}
else echo json_encode(array('success' => false, 'error' => 'Not allowed'));
wp_die();