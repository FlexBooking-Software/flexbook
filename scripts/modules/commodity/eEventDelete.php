<?php

class ModuleEventDelete extends ExecModule {

  protected function _userRun() {
    $id = $this->_app->request->getParams('id');
    $deleteCycle = $this->_app->request->getParams('repeat');

    if ($id) {
      $bEvent = new BEvent($id);
      $name = $bEvent->delete($deleteCycle);
    
      $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.listEvent_deleteOk'), $name));
    }

    $this->_app->response->addParams(array('backwards'=>1));
    return 'eBack';
  }
}

?>
