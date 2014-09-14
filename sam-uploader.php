<?php
/**
 * Created by PhpStorm.
 * Author: minimus
 * Date: 18.02.14
 * Time: 12:37
 */

$path = (isset($_REQUEST['path'])) ? $_REQUEST['path'] : '';
$message = '';

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

@set_time_limit(5 * 60);

if(!empty($path)) {
  if (empty($_FILES) || $_FILES["file"]["error"]) {
    $message = 'No Files to Upload...';

  }
  else {
    $fileName = $_FILES["file"]["name"];
    $fe = explode('.', $fileName);
    $fileExt = $fe[count($fe) - 1];
    if($fileExt != 'jpg' && $fileExt != 'jpeg' && $fileExt != 'png' && $fileExt != 'gif' && $fileExt != 'swf')
      die('{"OK" : 0, "ext" : "'.$fileExt.'"}');
    else move_uploaded_file($_FILES["file"]["tmp_name"], $path.$fileName);
  }
}
else die('{"OK" : 0, "path" : "'.$path.'"}');

die('{"OK": 1}');