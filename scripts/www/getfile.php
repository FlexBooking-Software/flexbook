<?php

require_once(dirname(__FILE__).'/../config.php');

global $DOWN;
if (isset($DOWN)) die($DOWN);

if (isset($_GET['id'])) {
  $id = $_GET['id'];
  
  global $DB;
	$mysqlCon = mysqli_connect('p:'.isset($DB['server'])?$DB['server']:'localhost', $DB['user'], $DB['password']);
  if (isset($DB['encoding'])) mysqli_query($mysqlCon, sprintf('SET NAMES "%s"', $DB['encoding']));
  mysqli_select_db($mysqlCon, $DB['database']);

  $query = sprintf("SELECT f.name, f.content, f.mime FROM file f WHERE f.hash='%s'", mysqli_escape_string($mysqlCon, $id));
  $res = mysqli_query($mysqlCon, $query);
  if ($row = mysqli_fetch_array($res,MYSQLI_ASSOC)) {
    header('Content-Type: '.$row['mime']);
    header('Content-disposition: inline; filename="'.$row['name'].'"');
    echo $row['content'];
  }
}

?>