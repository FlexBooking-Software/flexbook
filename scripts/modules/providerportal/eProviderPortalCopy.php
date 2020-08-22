<?php

class ModuleProviderPortalCopy extends ExecModule {

  protected function _userRun() {
    if ($id = $this->_app->request->getParams('id')) {  
      $bProviderPortal = new BProviderPortal($id);
      $name = $bProviderPortal->copy();
    
      $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.listProviderPortal_copyOk'), $name));
    }

    $this->_app->response->addParams(array('backwards'=>1));
    return 'eBack';
  }
}

?>
