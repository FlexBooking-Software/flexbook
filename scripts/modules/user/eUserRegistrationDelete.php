<?php

class ModuleUserRegistrationDelete extends ExecModule {

  protected function _userRun() {
    $bUser = new BUser($this->_app->request->getParams('id'));
    $ret = $bUser->deleteRegistration();

    $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.deleteUserRegistration_ok'), $ret));

    $this->_app->response->addParams(array('backwards'=>1));
    return 'eBack';
  }
}

?>
