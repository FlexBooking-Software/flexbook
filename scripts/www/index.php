<?php

error_reporting(E_ALL|E_STRICT);
ini_set('display_errors','1');

require dirname(__FILE__) . '/../init.php';

global $DOWN;
if (isset($DOWN)) die($DOWN);

global $DEBUG;
new FlexBook(array(
      'debug' => $DEBUG,
      'defaultAction' => 'eMain',
      ));

Application::get()->run();

?>
