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
      if(isset($_POST['id']) && isset($_POST['wc'])) {
        $placeId = $_POST['id'];
        $clauses = unserialize(base64_decode($_POST['wc']));
        include_once('ad.class.php');
        $ad = new SamAdPlace($args, false, false, $clauses, true);
        wp_send_json_success(array('ad' => $ad->ad));
      }
      break;
  }
}
else wp_send_json_error(array('error' => 'Not allowed action'));