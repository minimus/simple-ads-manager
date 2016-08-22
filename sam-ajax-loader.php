<?php
/**
 * Created by PhpStorm.
 * Author: minimus
 * Date: 12.11.13
 * Time: 11:47
 */

define( 'DOING_AJAX', true );
$body = 'load';

if ( ! isset( $_POST['action'] ) ) {
	die( '-1' );
}
if (!isset( $_REQUEST['wap'] )) die('-1');

$prefix = 'wp';
$suffix = 'php';

$wap      = ( isset( $_REQUEST['wap'] ) ) ? base64_decode( $_REQUEST['wap'] ) : null;
$mlf = "{$prefix}-{$body}.{$suffix}";
$rightWap = ( is_null( $wap ) ) ? false : strpos( $wap, $mlf );
if ( $rightWap === false ) {
	exit;
}

$wpLoadPath = ( is_null( $wap ) ) ? false : $wap;

if ( ! $wpLoadPath ) {
	die( '-1' );
}

ini_set( 'html_errors', 0 );

define( 'SHORTINIT', true );

require_once( $wpLoadPath );

if ( ! defined( 'ABSPATH' ) ) exit;

require_once( ABSPATH . WPINC . '/formatting.php' );
require_once( ABSPATH . WPINC . '/link-template.php' );

/** @see wp_plugin_directory_constants() */
if ( ! defined( 'WP_CONTENT_URL' ) ) {
	define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
}
if ( ! defined( 'WP_PLUGIN_DIR' ) ) {
	define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
}
if ( ! defined( 'WP_PLUGIN_URL' ) ) {
	define( 'WP_PLUGIN_URL', WP_CONTENT_URL . '/plugins' );
}

if ( ! defined( 'WPMU_PLUGIN_DIR' ) ) {
	define( 'WPMU_PLUGIN_DIR', WP_CONTENT_DIR . '/mu-plugins' );
}
if ( ! defined( 'WPMU_PLUGIN_URL' ) ) {
	define( 'WPMU_PLUGIN_URL', WP_CONTENT_URL . '/mu-plugins' );
}
global $wp_plugin_paths;
$wp_plugin_paths = array();

if ( ! defined( 'SAM_URL' ) ) {
	define( 'SAM_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'SAM_IMG_URL' ) ) {
	define( 'SAM_IMG_URL', SAM_URL . 'images/' );
}

function samIsValidURL() {
	$out = false;

	$siteUrl = get_option( 'siteurl', '' );
	if (isset($_SERVER['HTTP_REFERER']) && !empty($siteUrl)) {
		$referer = $_SERVER['HTTP_REFERER'];
		$validUrl = strpos($referer, $siteUrl);
		$out = ($validUrl !== false);
	}

	return $out;
}

if (! samIsValidURL()) die('-1');

global $wpdb;

$oTable  = $wpdb->prefix . 'options';
$oSql    = "SELECT $oTable.option_value FROM $oTable WHERE $oTable.option_name = 'blog_charset'";
$charset = $wpdb->get_var( $oSql );

//Typical headers
@header( "Content-Type: application/json; charset=$charset" );
@header( 'X-Robots-Tag: noindex' );

send_nosniff_header();
nocache_headers();

$action = ! empty( $_POST['action'] ) ? 'sam_ajax_' . stripslashes( $_POST['action'] ) : false;

//A bit of security
$allowed_actions = array(
	'sam_ajax_load_place',
	'sam_ajax_load_ads',
	'sam_ajax_load_zone'
);

if ( in_array( $action, $allowed_actions ) ) {
	switch ( $action ) {
		case 'sam_ajax_load_place':
			echo json_encode( array( 'success' => false, 'error' => 'Deprecated...' ) );
			break;

		case 'sam_ajax_load_ads':
			if ( ( isset( $_POST['ads'] ) && is_array( $_POST['ads'] ) ) && isset( $_POST['wc'] ) ) {
				$clauses = unserialize( base64_decode( $_POST['wc'] ) );
				$places  = $_POST['ads'];
				$ads     = array();
				$ad      = null;
				include_once( 'ad.class.php' );
				foreach ( $places as $value ) {
					$placeId   = (int)$value[0];
					$adId      = (int)$value[1];
					$codes     = (int)$value[2];
					$elementId = $value[3];
					$args      = array( 'id' => ( $adId == 0 ) ? $placeId : $adId );

					if ( $adId == 0 ) {
						$ad = new SamAdPlace( $args, $codes, false, $clauses, true );
					} else {
						$ad = new SamAd( $args, $codes, false, true );
					}

					array_push( $ads, array(
						'ad'  => $ad->ad,
						'id'  => $ad->id,
						'pid' => $ad->pid,
						'cid' => $ad->cid,
						'eid' => $elementId,
						'request_uri' => $_SERVER['SERVER_PROTOCOL'] . '://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'],
						'referrer' => $_SERVER['HTTP_REFERER'],
						'url' => WP_PLUGIN_URL,
						'site_url' => get_option( 'siteurl' )
					) );
				}
				echo json_encode( array(
					'success' => true,
					'ads'     => $ads
				) );
			} else {
				echo json_encode( array( 'success' => false, 'error' => 'Bad input data.' ) );
			}
			break;
	}
} else {
	echo json_encode( array( 'success' => false, 'error' => 'Not allowed.' ) );
}
wp_die();