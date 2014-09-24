<?php
/**
 * Created by PhpStorm.
 * Author: minimus
 * Date: 29.11.13
 * Time: 20:48
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

function random_string($chars = 12) {
	$letters = 'abcefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890 ';
	return substr(str_shuffle($letters), 0, $chars);
}

global $wpdb;

$sTable = $wpdb->prefix . 'sam_stats';
$tTable = $wpdb->prefix . "terms";
$ttTable = $wpdb->prefix . "term_taxonomy";
$uTable = $wpdb->base_prefix . "users";
$umTable = $wpdb->base_prefix . "usermeta";
$postTable = $wpdb->prefix . "posts";
$eTable = $wpdb->prefix . 'sam_errors';
$userLevel = $wpdb->base_prefix . 'user_level';

$oTable = $wpdb->prefix . 'options';
$oSql = "SELECT $oTable.option_value FROM $oTable WHERE $oTable.option_name = 'blog_charset'";
$charset = $wpdb->get_var($oSql);

//Typical headers
@header("Content-Type: application/json; charset=$charset");
@header( 'X-Robots-Tag: noindex' );

send_nosniff_header();
nocache_headers();

$action = !empty($_REQUEST['action']) ? 'sam_ajax_' . stripslashes($_REQUEST['action']) : false;

//A bit of security
$allowed_actions = array(
  'sam_ajax_load_cats',
  'sam_ajax_load_tags',
  'sam_ajax_load_authors',
  'sam_ajax_load_posts',
	'sam_ajax_load_posts_debug',
  'sam_ajax_load_users',
  'sam_ajax_load_combo_data',
  'sam_ajax_get_error',
  'sam_ajax_upload_ad_image',
  'sam_ajax_load_stats'
);
$out = array();

if(in_array($action, $allowed_actions)) {
  switch($action) {
    case 'sam_ajax_load_cats':
      $sql = "SELECT wt.term_id AS id, wt.name AS title, wt.slug
              FROM $tTable wt
              INNER JOIN $ttTable wtt
                ON wt.term_id = wtt.term_id
              WHERE wtt.taxonomy = 'category'
              ORDER BY wt.name;";

      $cats = $wpdb->get_results($sql, ARRAY_A);
      $k = 0;
      foreach($cats as &$val) {
        $k++;
        $val['recid'] = $k;
      }
      $out = $cats;
      break;

    case 'sam_ajax_load_tags':
      $sql = "SELECT wt.term_id AS id, wt.name AS title, wt.slug
              FROM $tTable wt
              INNER JOIN $ttTable wtt
                ON wt.term_id = wtt.term_id
              WHERE wtt.taxonomy = 'post_tag'
              ORDER BY wt.name;";

      $tags = $wpdb->get_results($sql, ARRAY_A);
      $k = 0;
      foreach($tags as &$val) {
        $k++;
        $val['recid'] = $k;
      }
      $out = $tags;
      break;

    case 'sam_ajax_load_authors':
      $sql = "SELECT
                wu.id,
                wu.display_name AS title,
                wu.user_nicename AS slug
              FROM
                $uTable wu
              INNER JOIN $umTable wum
                ON wu.id = wum.user_id
              WHERE
                wum.meta_key = '$userLevel' AND
                wum.meta_value > 1
              ORDER BY wu.id;";

      $auth = $wpdb->get_results($sql, ARRAY_A);
      $k = 0;
      foreach($auth as &$val) {
        $k++;
        $val['recid'] = $k;
      }
      $out = $auth;
      break;

    case 'sam_ajax_load_posts':
      $custs = (isset($_REQUEST['cstr'])) ? $_REQUEST['cstr'] : '';
      $sPost = (isset($_REQUEST['sp'])) ? urldecode( $_REQUEST['sp'] ) : 'Post';
      $sPage = (isset($_REQUEST['spg'])) ? urldecode( $_REQUEST['spg'] ) : 'Page';

      //set @row_num = 0;
      //SELECT @row_num := @row_num + 1 AS recid
      $sql = "SELECT
                wp.id,
                wp.post_title AS title,
                wp.post_type AS type
              FROM
                $postTable wp
              WHERE
                wp.post_status = 'publish' AND
                FIND_IN_SET(wp.post_type, 'post,page{$custs}')
              ORDER BY wp.id;";

      $posts = $wpdb->get_results($sql, ARRAY_A);

      $k = 0;
      foreach($posts as &$val) {
        switch($val['type']) {
          case 'post':
            $val['type'] = $sPost;
            break;
          case 'page':
            $val['type'] = $sPage;
            break;
          default:
            $val['type'] = $sPost . ': '.$val['type'];
            break;
        }
        $k++;
        $val['recid'] = $k;
      }
      $out = array(
        'status' => 'success',
        'total' => count($posts),
        'records' => $posts
      );
      break;

	  case "sam_ajax_load_posts_debug":
		  $posts = array();
			$types = array('post', 'page');
		  for($itr = 0; $itr < 5000; ++$itr) {
			  array_push($posts, array(
				  'recid' => $itr,
				  'id' => rand(0, 10000),
				  'title' => random_string(rand(12, 24)),
				  'type' => $types[rand(0,1)]
			  ));
		  }
		  $out = array(
			  'status' => 'success',
			  'total' => count($posts),
			  'records' => $posts
		  );
		  break;

    case 'sam_ajax_load_users':
      $roleSubscriber = (isset($_REQUEST['subscriber'])) ? urldecode($_REQUEST['subscriber']) : 'Subscriber';
      $roleContributor = (isset($_REQUEST['contributor'])) ? urldecode($_REQUEST['contributor']) : 'Contributor';
      $roleAuthor = (isset($_REQUEST['author'])) ? urldecode($_REQUEST['author']) : 'Author';
      $roleEditor = (isset($_REQUEST['editor'])) ? urldecode($_REQUEST['editor']) : 'Editor';
      $roleAdministrator = (isset($_REQUEST["admin"])) ? urldecode($_REQUEST["admin"]) : 'Administrator';
      $roleSuperAdmin = (isset($_REQUEST['sadmin'])) ? urldecode($_REQUEST['sadmin']) : 'Super Admin';
      $sql = "SELECT
                wu.id,
                wu.display_name AS title,
                wu.user_nicename AS slug,
                (CASE wum.meta_value
                  WHEN 0 THEN '$roleSubscriber'
                  WHEN 1 THEN '$roleContributor'
                  WHEN 2 THEN '$roleAuthor'
                  ELSE
                    IF(wum.meta_value > 2 AND wum.meta_value <= 7, '$roleEditor',
                      IF(wum.meta_value > 7 AND wum.meta_value <= 10, '$roleAdministrator',
                        IF(wum.meta_value > 10, '$roleSuperAdmin', NULL)
                      )
                    )
                END) AS role
              FROM $uTable wu
              INNER JOIN $umTable wum
                ON wu.id = wum.user_id AND wum.meta_key = '$userLevel'
              ORDER BY wu.id;";
      $users = $wpdb->get_results($sql, ARRAY_A);

      $k = 0;
      foreach($users as &$val) {
        $k++;
        $val['recid'] = $k;
      }

      $out = $users;
      break;

    case 'sam_ajax_load_combo_data':
      $page = $_GET['page'];
      $rows = $_GET['rows'];
      $searchTerm = $_GET['searchTerm'];
      $offset = ((int)$page - 1) * (int)$rows;

      $sql = "SELECT
                wu.id,
                wu.display_name AS title,
                wu.user_nicename AS slug,
                wu.user_email AS email
              FROM
                $uTable wu
              WHERE wu.user_nicename LIKE '{$searchTerm}%'
              ORDER BY wu.id
              LIMIT $offset, $rows;";
      $users = $wpdb->get_results($sql, ARRAY_A);

      $sql = "SELECT COUNT(*) FROM $uTable wu WHERE wu.user_nicename LIKE '{$searchTerm}%';";
      $rTotal = $wpdb->get_var($sql);
      $total = ceil((int)$rTotal/(int)$rows);

      $out = array(
        'page' => $page,
        'records' => count($users),
        'rows' => $users,
        'total' => $total,
        'offset' => $offset
      );

      break;

    case 'sam_ajax_load_stats':
      $sql = "SELECT
                DATE_FORMAT(ss.event_time, %s) AS ed,
                DATE_FORMAT(LAST_DAY(ss.event_time), %s) AS days,
                COUNT(*) AS hits
              FROM $sTable ss
              WHERE (EXTRACT(YEAR_MONTH FROM NOW()) - %d = EXTRACT(YEAR_MONTH FROM ss.event_time)) AND ss.event_type = %d
              GROUP BY ed;";
      $hits = $wpdb->get_results($wpdb->prepare($sql, '%d', '%d', 0, 0), ARRAY_A);
      $clicks = $wpdb->get_results($wpdb->prepare($sql, '%d', '%d', 0, 1), ARRAY_A);
      $days = $hits[0]['days'];
      for($i = 1; $i <= $days; $i++) {
        $hitsFull[$i - 1] = array( $i, 0);
        $clicksFull[$i - 1] = array($i, 0);
      }
      foreach($hits as $hit) $hitsFull[$hit['ed']][1] = $hit['hits'];
      foreach($clicks as $click) $clicksFull[$click['ed']][1] = $click['hits'];
      $out = array('hits' => $hitsFull, 'clicks' => $clicksFull, 'sql' => $sql);
      break;

    case 'sam_ajax_get_error':
      $eTypes = array(
        ((isset($_POST['wa'])) ? $_POST['wa'] : 'Warning'),
        ((isset($_POST['ue'])) ? $_POST['wa'] : 'Update Error'),
        ((isset($_POST['oe'])) ? $_POST['wa'] : 'Output Error')
      );
      if(isset($_POST['id'])) {
        $id = $_POST['id'];
        $eSql = "SELECT
                  se.id,
                  se.error_date,
                  UNIX_TIMESTAMP(se.error_date) as date,
                  se.table_name as name,
                  se.error_type,
                  se.error_msg as msg,
                  se.error_sql as es,
                  se.resolved
                FROM $eTable se WHERE se.id = %d";
        $out = $wpdb->get_row($wpdb->prepare($eSql, $id), ARRAY_A);
        if(!empty($out['date'])) $out['date'] = date_i18n(get_option('date_format').' '.get_option('time_format'), $out['date']);
        $out['type'] = $eTypes[$out['error_type']];
      }
      else $out = array("status" => "error", "message" => "ID Error");
      break;

    case 'sam_ajax_upload_ad_image':
      if(isset($_POST['path'])) {
        $uploadDir = $_POST['path'];
        $file = $uploadDir . basename($_FILES['uploadfile']['name']);

        if ( move_uploaded_file( $_FILES['uploadfile']['tmp_name'], $file )) {
          $out = array('status' => "success");
        } else {
          $out = array('status' => "error");
        }
      }
      break;

    default:
      $out = array("status" => "error", "message" => "Error");
      break;
  }
  echo json_encode( $out ); wp_die();
}
else $out = array("status" => "error", "message" => "Error");
//exit(json_encode($output));
wp_send_json_error($out);
