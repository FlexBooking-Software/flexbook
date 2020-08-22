<?php

require_once(dirname(__FILE__).'/../config.php');

$page = $_GET['page']; // get the requested page
$limit = $_GET['rows']; // get how many rows we want to have into the grid
$sidx = $_GET['sidx']; // get index row - i.e. user click to sort
$sord = $_GET['sord']; // get the direction
$searchTerm = $_GET['searchTerm'];

if (!$sidx) $sidx = 1;
if ($searchTerm == '') {
  $searchTerm = '%';
} else {
  $searchTerm = '%' . $searchTerm . '%';
}
$searchTerm = mysql_escape_string(@iconv('utf-8','iso-8859-2',$searchTerm));

global $DB;
  
mysql_connect($DB['server'], $DB['user'], $DB['password']);
mysql_select_db($DB['database']);

$result = mysql_query("SELECT COUNT(*) AS count FROM customer WHERE name like '$searchTerm'");
$row = mysql_fetch_assoc($result);
$count = $row['count'];
if ($count>0&&$limit) {
  $total_pages = ceil($count/$limit);
} else {
  $total_pages = 0;
}

if ($page>$total_pages) $page = $total_pages;
$start = $limit*$page - $limit; // do not put $limit*($page - 1)
if ($total_pages) {
  $query = "SELECT customer_id AS id, name, email, CONCAT(street,' ',city) AS address FROM customer JOIN address ON address=address_id WHERE name like '$searchTerm' ORDER BY $sidx $sord LIMIT $start , $limit";
} else {
  $query = "SELECT customer_id AS id, name, email, CONCAT(street,' ',city) AS address FROM customer JOIN address ON address=address_id WHERE name like '$searchTerm' ORDER BY $sidx $sord";
}
$result = mysql_query($query) or die("Couldn't execute query.".mysql_error());

$response->page = $page;
$response->total = $total_pages;
$response->records = $count;
$i=0;
while ($row = mysql_fetch_assoc($result)) {
  $response->rows[$i]['id'] = @iconv('iso-8859-2','utf-8',$row['id']);
  $response->rows[$i]['name'] = @iconv('iso-8859-2','utf-8',$row['name']);
  $response->rows[$i]['address'] = @iconv('iso-8859-2','utf-8',$row['address']);
  $response->rows[$i]['email'] = @iconv('iso-8859-2','utf-8',$row['email']);
  $i++;
}

$output = json_encode($response);
echo $output;

?>
