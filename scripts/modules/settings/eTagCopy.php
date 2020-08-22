<?php

class ModuleTagCopy extends ExecModule {

  protected function _userRun() {
    if (!$this->_app->auth->haveRight('settings_admin', $this->_app->auth->getActualProvider())) throw new ExceptionUserTextStorage('error.accessDenied');

    if ($id = $this->_app->request->getParams('id')) {  
      $bTag = new BTag($id);
      $name = $bTag->copy();
    
      $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.listTag_copyOk'), $name));

      $gridSettings = new GridSettingsTag('listTag');
      $gridSettings->addFilter('name',$name);
      $gridSettings->saveSettings();
    }

    $this->_app->response->addParams(array('backwards'=>1));
    return 'eBack';
  }
}

?>
