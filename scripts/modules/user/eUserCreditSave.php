<?php

class ModuleUserCreditSave extends ExecModule {

  protected function _userRun() {
    $user = $this->_app->request->getParams('id');
    if ($provider = $this->_app->request->getParams('provider')) {  
      if (!$change = $this->_app->request->getParams('credit_'.$provider)) {
        throw new ExceptionUserTextStorage('error.editUserCredit_credit');
      }
    
      $b = new BUser($user);
      $b->changeCredit($provider, $change);
    
      $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.editUserCredit_ok'), $change,
                                                            $this->_app->textStorage->getText('label.currency_CZK')));
    }

    $this->_app->response->addParams(array('backwards'=>1));
    return 'eBack';
  }
}

?>
