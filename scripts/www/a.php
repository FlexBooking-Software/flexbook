<?php
 require(dirname(__FILE__).'/../config.php');
 
 $inpageUrl = dirname($AJAX['url']).'/inpage.php';
 $ajaxUrl = dirname($AJAX['url']).'/customer-sample/index_new.php';
?>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
</head>
<body>
<p>
<b>FlexBook s.r.o.</b>
</p>
<p>
Vyhledavaci portal <a href="https://portal.flexbook.cz">zde</a>.
<br>Administrace <a href="<?php echo dirname($AJAX['url']); ?>">zde</a>.
<br><br>Stranky poskytovatele 
<form id="main" style="float: left;" method="get" action="<?php echo $inpageUrl; ?>">
<select name="id">
<option value="">-vyberte-</option>
<?php
 $connection = mysqli_connect($DB['server'],$DB['user'],$DB['password']);
 mysqli_select_db($connection, $DB['database']);
 if (isset($DB['encoding'])) mysqli_query($connection, 'SET NAMES '.$DB['encoding']);
 $res = mysqli_query($connection, 'select short_name,name from provider join customer on provider=provider_id');
 while ($row = mysqli_fetch_assoc($res)) {
   echo sprintf('<option value="%s">%s</option>', $row['short_name'], $row['name']);
 }
?>
</select>
<input id="check" type="checkbox" name="javascript" value="1" onclick="var frm = document.getElementById('main'); if (this.checked) frm.action='<?php echo $ajaxUrl; ?>'; else frm.action='<?php echo $inpageUrl; ?>';"/>
javascript
<input type="submit" name="go" value="Jdi" onclick="var frm = document.getElementById('check').checked=false;" />
</form>
</p>
</body>
</html>
