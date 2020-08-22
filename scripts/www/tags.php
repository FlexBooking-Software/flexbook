<?php

require_once(dirname(__FILE__).'/../config.php');

if ($term = $_GET['term']) {
  // zatim predpokladam, ze klient je utf-8
  $term = @iconv('utf-8','iso-8859-2',$term);
  
  $output = '';
  $jsonOutput = array();
  
  global $DB;
  
  mysql_connect($DB['server'], $DB['user'], $DB['password']);
  mysql_select_db($DB['database']);
  
  $query = sprintf("SELECT tag_id AS id,name FROM tag WHERE name LIKE '%%%s%%' ORDER BY name", mysql_escape_string($term));
  $res = mysql_query($query);
  //error_log($query);
  while ($row = mysql_fetch_assoc($res)) {
    if ($output) $output .= ',';
    $output .= sprintf('"%s"', $row['name']);
    
    $row['name'] = @iconv('iso-8859-2','utf-8',$row['name']);
    $jsonOutput[] = $row;
  }
  
  #$output = @iconv('iso-8859-2','utf-8',$output);
  #echo '['.$output.']';

  echo json_encode($jsonOutput);
}

?>
