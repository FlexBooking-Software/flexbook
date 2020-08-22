<?php

class ModuleProviderPortalPageSave extends ExecModule {
  
  protected function _userRun() {
    $validator = Validator::get('providerPortal','ProviderPortalValidator');
    $validator->initValues();
    
    $validator->validateLastValues();
    
    $bProviderPortal = new BProviderPortal($validator->getVarValue('id'));
    $data = array(
        'id'            => $validator->getVarValue('pageId'),
        'shortName'     => $validator->getVarValue('pageShortName'),
        'name'          => $validator->getVarValue('pageName'),
        'content'       => $validator->getVarValue('pageContent'),
        );
    $bProviderPortal->savePage(array($data));
    
    $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.editProviderPortalPage_saveOk'), $validator->getVarValue('pageName')));

    $this->_app->response->addParams(array('backwards'=>1));
    return 'eBack';
  }
}

?>
