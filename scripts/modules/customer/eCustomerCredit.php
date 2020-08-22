<?php

class ModuleCustomerCredit extends ExecModule {

  protected function _userRun() {
    $customer = $this->_app->request->getParams('id');
    if ($provider = $this->_app->request->getParams('provider')) {  
      if (!$change = $this->_app->request->getParams('credit_'.$provider)) {
        throw new ExceptionUserTextStorage('error.editCustomerCredit_credit');
      }
    
      $b = new BCustomer($customer);
      $b->changeCredit($provider, $change);
    
      $this->_app->messages->addMessage('userInfo', $this->_app->textStorage->getText('info.editCustomerCredit_ok'));
    }

    $this->_app->response->addParams(array('backwards'=>1));
    return 'eBack';
  }
}

?>
