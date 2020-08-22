<?php

class ModuleTagDelete extends ExecModule {

  protected function _userRun() {
    if (!$this->_app->auth->haveRight('settings_admin', $this->_app->auth->getActualProvider())) throw new ExceptionUserTextStorage('error.accessDenied');

    if ($id = $this->_app->request->getParams('id')) {  
      $bTag = new BTag($id);
      $name = $bTag->delete();
    
      $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.listTag_deleteOk'), $name));
    }

    $backwards = $this->_app->request->getParams('backwards');
    if (!$backwards) $backwards = 1;
    $this->_app->response->addParams(array('backwards'=>$backwards));
    return 'eBack';
  }
}

?>
