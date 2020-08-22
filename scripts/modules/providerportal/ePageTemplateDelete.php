<?php

class ModulePageTemplateDelete extends ExecModule {

  protected function _userRun() {
    if ($id = $this->_app->request->getParams('id')) {  
      $bPageTemplate = new BPageTemplate($id);
      $name = $bPageTemplate->delete();
    
      $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.listPageTemplate_deleteOk'), $name));
    }

    $this->_app->response->addParams(array('backwards'=>1));
    return 'eBack';
  }
}

?>
