<?php

class ModuleProviderPortalCreate extends ExecModule {

  protected function _userRun() {
    $validator = Validator::get('providerPortal','ProviderPortalValidator');
    $validator->initValues();
    $validator->validateLastValues();
    
    $data = $validator->getValues();
    
    $bPortal = new BProviderPortal;
    $bPortal->create($data);
    
    $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.editProviderPortal_saveOk'), $validator->getVarValue('name')));
    
    return 'eBack';
  }
}

?>
