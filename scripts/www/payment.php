<?php

error_reporting(E_ALL|E_STRICT);
ini_set('display_errors','1');

require dirname(__FILE__) . '/../init.php';

global $DEBUG;
new InPage(array(
      'debug' => $DEBUG,
      'defaultAction' => 'ePaymentGatewayInit',
      ));

Application::get()->run();

?>
