<?php

class AjaxSendPassword extends AjaxAction {

  protected function _userRun() {    
    if (!isset($this->_params['email'])||!$this->_params['email']) throw new ExceptionUserTextStorage('error.ajax_profile_emailMissing');
    
    $b = new BUser;
    $b->sendPassword($this->_params['email'], $this->_params['provider']);
    
    $this->_result = array();
  }
}

?>
