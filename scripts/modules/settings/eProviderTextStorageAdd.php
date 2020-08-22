<?php

class ModuleProviderTextStorageAdd extends ExecModule {

  protected function _userRun() {
    if (!$this->_app->auth->haveRight('settings_admin', $this->_app->auth->getActualProvider())) throw new ExceptionUserTextStorage('error.accessDenied');

    $num = BCustomer::addTextStorage($this->_app->auth->getActualProvider());

    $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.listProviderTextStorage_addOk'), $num));

    $this->_app->response->addParams(array('backwards'=>1));
    return 'eBack';
  }
}

?>
