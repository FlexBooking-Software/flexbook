<?php

class ModuleUserEnable extends ExecModule {

  protected function _userRun() {
    $bUser = new BUser($this->_app->request->getParams('id'));
    $data = $bUser->getData();
    $bUser->enable();

    $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.listUser_enableOk'), $data['username']));

    $this->_app->response->addParams(array('backwards'=>1));
    return 'eBack';
  }
}

?>
