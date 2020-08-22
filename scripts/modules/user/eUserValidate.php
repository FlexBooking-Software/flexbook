<?php

class ModuleUserValidate extends ExecModule {

  protected function _userRun() {
    if ($id = $this->_app->request->getParams('id')) {  
      $b = new BUser($id);
      $name = $b->validate();
    
      $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.listUser_validateOk'), $name));
    }

    $backwards = $this->_app->request->getParams('backwards');
    $this->_app->response->addParams(array('backwards'=>ifsetor($backwards,1)));
    return 'eBack';
  }
}

?>
