<?php

class ModuleProviderAccountTypeSave extends ExecModule {
  
  protected function _userRun() {
    if (!$this->_app->auth->haveRight('settings_admin', $this->_app->auth->getActualProvider())) throw new ExceptionUserTextStorage('error.accessDenied');

    $validator = Validator::get('providerAccountType','ProviderAccountTypeValidator');
    $validator->initValues();
    
    $validator->validateValues();

    $id = $validator->getVarValue('id');
    
    $bProviderAccountType = new BProviderAccountType($id?$id:null);
    $bProviderAccountType->save(array(
        'name'        => $validator->getVarValue('name'),
        'providerId'  => $validator->getVarValue('providerId'),
        ));

    $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.editProviderAccountType_saveOk'), $validator->getVarValue('name')));

    if ($validator->getVarValue('fromEvent')) {
      $eValidator = Validator::get('event', 'EventValidator');
      $eValidator->setValues(array('accountTypeId' => $bProviderAccountType->getId()));
    } elseif ($validator->getVarValue('fromResource')) {
      $eValidator = Validator::get('resource', 'ResourceValidator');
      $eValidator->setValues(array('accountTypeId' => $bProviderAccountType->getId()));
    }

    return 'eBack';
  }
}

?>
