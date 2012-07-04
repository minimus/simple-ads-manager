<?php
/*
Plugin Name: Simple Ads Manager
Plugin URI: http://www.simplelib.com/?p=480
Description: "Simple Ads Manager" is easy to use plugin providing a flexible logic of displaying advertisements. Visit <a href="http://www.simplelib.com/">SimpleLib blog</a> for more details.
Version: 1.6.54
Author: minimus
Author URI: http://blogcoding.ru
*/

/*  Copyright 2011, minimus  (email : minimus@simplelib.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

global $samObject;

define('SAM_MAIN_FILE', __FILE__);

include_once('ad.class.php');
include_once('sam.class.php');

if (is_admin()) {
	include_once('admin.class.php');
	if (class_exists("SimpleAdsManagerAdmin") && class_exists("SimpleAdsManager")) 
		$samObject = new SimpleAdsManagerAdmin();
}
else {
	if (class_exists("SimpleAdsManager")) $samObject = new SimpleAdsManager();
}

include_once('widget.class.php');
if(class_exists('simple_ads_manager_widget')) 
  add_action('widgets_init', create_function('', 'return register_widget("simple_ads_manager_widget");'));
if(class_exists('simple_ads_manager_zone_widget')) 
  add_action('widgets_init', create_function('', 'return register_widget("simple_ads_manager_zone_widget");'));
if(class_exists('simple_ads_manager_ad_widget')) 
  add_action('widgets_init', create_function('', 'return register_widget("simple_ads_manager_ad_widget");'));
if(class_exists('simple_ads_manager_block_widget')) 
  add_action('widgets_init', create_function('', 'return register_widget("simple_ads_manager_block_widget");'));

if(class_exists("SimpleAdsManagerAdmin") || class_exists("SimpleAdsManager")) {
  function drawAd($args = null, $codes = false) {
    global $samObject;
    
    if(is_null($args)) echo '';
    if(is_object($samObject)) echo $samObject->buildSingleAd($args);
    else echo '';
  }
  
  function drawAdsPlace($args = null, $codes = false) {
    global $samObject;
    
    if(is_null($args)) echo '';
    if(is_object($samObject)) echo $samObject->buildAd($args, $codes);
    else echo '';
  }
  
  function drawAdsZone($args = null, $codes = false) {
    global $samObject;
    
    if(is_null($args)) echo '';
    if(is_object($samObject)) echo $samObject->buildAdZone($args, $codes);
    else echo '';
  }
  
  function drawAdsBlock($args = null) {
    global $samObject;
    
    if(is_null($args)) echo '';
    if(is_object($samObject)) echo $samObject->buildAdBlock($args);
    else echo '';
  }
  
  add_action('wp_ajax_nopriv_sam_click', 'samClickHandler');
  add_action('wp_ajax_sam_click', 'samClickHandler');
  function samClickHandler() {
    $error = null;
    if(isset($_POST['sam_ad_id'])) {
      $adId = $_POST['sam_ad_id'];
      $aId = explode('_', $adId);
      $id = (integer) $aId[1];
    }
    else $id = -100;

    if(check_ajax_referer('samNonce') && ($id > 0)) {
      global $wpdb;
      $aTable = $wpdb->prefix . "sam_ads";  
        
      $result = $wpdb->query("UPDATE $aTable SET $aTable.ad_clicks = $aTable.ad_clicks+1 WHERE $aTable.id = $id;");
      if($result) $error = $id;
      else $error = 'error';
    }
    else $error = 'error';
      
    if($error) exit($error);
    else exit;
  }
}
?>
