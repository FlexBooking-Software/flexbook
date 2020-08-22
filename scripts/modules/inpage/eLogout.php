<?php

class ModuleInPageLogout extends ExecModule {

  protected function _userRun() {
    $this->_app->auth->reset();
    
    $validator = Validator::get('login', 'InPageLoginValidator', true);
    
    $this->_app->response->addParams(array('backwards'=>1));
    return 'eBack';
  }
}

?>
