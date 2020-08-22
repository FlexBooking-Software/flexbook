<?php

class ModuleProviderPortalPageDelete extends ExecModule {

  protected function _userRun() {
    if ($providerPortal = $this->_app->request->getParams('portal')) {  
      $bProviderPortal = new BProviderPortal($providerPortal);
      $name = $bProviderPortal->deletePage($this->_app->request->getParams('page'));
    
      $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.listProviderPortalPage_deleteOk'), $name));
    }

    $this->_app->response->addParams(array('backwards'=>1));
    return 'eBack';
  }
}

?>
