<?php

class ModulePortalTemplateCopy extends ExecModule {

  protected function _userRun() {
    if ($id = $this->_app->request->getParams('id')) {  
      $bPortalTemplate = new BPortalTemplate($id);
      $name = $bPortalTemplate->copy();
    
      $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.listPortalTemplate_copyOk'), $name));
    }

    $this->_app->response->addParams(array('backwards'=>1));
    return 'eBack';
  }
}

?>
