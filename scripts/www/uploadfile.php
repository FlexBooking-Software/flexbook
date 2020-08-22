<?php

header('Access-Control-Allow-Origin: *');

require_once(dirname(__FILE__).'/../config.php');

if (isset($_FILES["uploadfile"])) {
  $ret = null;
	
	$name = $_FILES["uploadfile"]["name"];

	$linkName = tempnam($TMP_DIR,'');
  unlink($linkName);
  $linkName = basename($linkName);

  $fileName = $linkName.'_'.str_replace(' ','<space>',$name);

 	if (move_uploaded_file($_FILES["uploadfile"]["tmp_name"],$TMP_DIR.$fileName)) {
    symlink($TMP_DIR.$fileName, $TMP_DIR.$linkName);
 	  $ret=array('name'=>$name,'id'=>$linkName);
  }
	
  echo json_encode($ret);
}
?>