<?php

class ModuleProviderAccountTypeDelete extends ExecModule {

  protected function _userRun() {
    if (!$this->_app->auth->haveRight('settings_admin', $this->_app->auth->getActualProvider())) throw new ExceptionUserTextStorage('error.accessDenied');

    if ($id = $this->_app->request->getParams('id')) {  
      $bProviderAccountType = new BProviderAccountType($id);
      $name = $bProviderAccountType->delete();
    
      $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.listProviderAccountType_deleteOk'), $name));
    }

    $this->_app->response->addParams(array('backwards'=>1));
    return 'eBack';
  }
}

?>
