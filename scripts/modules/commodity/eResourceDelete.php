<?php

class ModuleResourceDelete extends ExecModule {

  protected function _userRun() {
    if ($id = $this->_app->request->getParams('id')) {  
      $bResource = new BResource($id);
      $name = $bResource->delete();
    
      $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.listResource_deleteOk'), $name));
    }

    $this->_app->response->addParams(array('backwards'=>1));
    return 'eBack';
  }
}

?>
