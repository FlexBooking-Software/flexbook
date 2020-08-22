<?php

class ModuleUserTicketSave extends ExecModule {

  protected function _userRun() {
    $validator = Validator::get('userCredit', 'UserCreditValidator');
    $validator->initValues();
    $validator->validateValues();
    
    $b = new BUser($validator->getVarValue('userId'));
    $name = $b->saveTicket($validator->getVarValue('newTicket'));
    
    $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.saveUserTicket_ok'), $name));
    
    $this->_app->response->addParams(array('backwards'=>1));
    return 'eBack';
  }
}

?>
