<?php

ini_set('memory_limit', '1000M');

require dirname(__FILE__).'/../init.php';

class FlexbookRobot extends Flexbook {

  protected function _initModules($params=array()) {
    $modules = array(
      'eSplitProviderUsers'     => dirname(__FILE__).'/eSplitProviderUsers.php',
    );

    Application::_initModules($modules);
  }

  protected function _createAuth($params) {
    $this->auth = new FakeAuth($params);
  }

  protected function _testAction($action) { return $action; }
}

?>
