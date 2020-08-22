<?php

class ModuleSettingsGeneralSave extends ExecModule {

  protected function _userRun() {
    if (!$this->_app->auth->haveRight('settings_admin', $this->_app->auth->getActualProvider())) throw new ExceptionUserTextStorage('error.accessDenied');

    $validator = Validator::get('settings','SettingsValidator');
    $validator->initValues();
    $validator->validateValues();
    
    $data = $validator->getValues();

    if ($data['allowOnlinePaymentOnly']=='Y') {
      $data['disableCredit'] = 'Y';
      $data['disableTicket'] = 'Y';
      $data['disableCash'] = 'Y';
      $data['disableOnline'] = 'N';
    } else {
      $data['disableCredit'] = 'N';
      $data['disableTicket'] = 'N';
      $data['disableCash'] = 'N';
      $data['disableOnline'] = 'N';
    }
    unset($data['allowOnlinePaymentOnly']);

    BCustomer::saveProviderSettings($data['providerId'], $data);
    
    $this->_app->messages->addMessage('userInfo', $this->_app->textStorage->getText('info.settingsGeneral_saveOk'));
    
    if (!$this->_app->auth->isAdministrator()) $this->_app->response->addParams(array('backwards'=>1));
    return 'eBack';
  }
}

?>
