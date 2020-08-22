<?php

error_reporting(E_ALL|E_STRICT);
ini_set('display_errors','1');

require dirname(__FILE__) . '/../../../flexbook.lib/phpqrcode/qrlib.php';

$code = isset($_REQUEST['code'])?$_REQUEST['code']:'_NOCODE_';
QRcode::png($code);

?>
