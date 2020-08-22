<?php

class ModuleProviderPortalPageCreate extends ExecModule {

  protected function _userRun() {
    $validator = Validator::get('providerPortal','ProviderPortalValidator');
    $validator->initValues();
    $validator->validateLastValues();
    
    $data = array(
          'fromTemplate'    => $validator->getVarValue('pageFromTemplate'),
          'shortName'       => $validator->getVarValue('pageShortName'),
          'name'            => $validator->getVarValue('pageName'),
          );
    
    $bPortal = new BProviderPortal($validator->getVarValue('id'));
    $page = $bPortal->createPage($data);
    
    $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.editProviderPortalPage_saveOk'), $validator->getVarValue('pageName')));
    
    return 'eBack';
  }
}

?>
