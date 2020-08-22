<?php

class ModuleProviderTextStorageInit extends ExecModule {

  protected function _userRun() {
    if (!$this->_app->auth->haveRight('settings_admin', $this->_app->auth->getActualProvider())) throw new ExceptionUserTextStorage('error.accessDenied');

    BCustomer::initTextStorage($this->_app->auth->getActualProvider());

    $this->_app->messages->addMessage('userInfo', $this->_app->textStorage->getText('info.listProviderTextStorage_initOk'));

    $this->_app->response->addParams(array('backwards'=>1));
    return 'eBack';
  }
}

?>
