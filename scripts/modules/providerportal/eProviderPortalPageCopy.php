<?php

class ModuleProviderPortalPageCopy extends ExecModule {

  protected function _userRun() {
    if ($portal = $this->_app->request->getParams('portal')) {
      $bProviderPortal = new BProviderPortal($portal);
      $name = $bProviderPortal->copyPage($this->_app->request->getParams('page'));
    
      $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.listProviderPortalPage_copyOk'), $name));
    }

    $this->_app->response->addParams(array('backwards'=>1));
    return 'eBack';
  }
}

?>
