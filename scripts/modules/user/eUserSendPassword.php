<?php

class ModuleUserSendPassword extends ExecModule {

  protected function _userRun() {
    $bUser = new BUser($this->_app->request->getParams('id'));
    $data = $bUser->getData();
    
    $bUser->sendPassword($data['email'], $this->_app->auth->getActualProvider());

    $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.editUser_passwordSendOk'), $data['email']));

    $this->_app->response->addParams(array('backwards'=>1));
    return 'eBack';
  }
}

?>
