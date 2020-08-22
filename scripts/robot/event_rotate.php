<?php

error_reporting(E_ALL|E_STRICT);
ini_set('display_errors','1');

require dirname(__FILE__) . '/init.php';

new FlexbookRobot(array(
      'debug'         => true,
      'defaultAction' => 'eEventRotate',
      ));
Application::get()->run();

?>
