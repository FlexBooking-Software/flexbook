<?php

class AjaxSendRegistrationEmail extends AjaxAction {

  protected function _userRun() {    
    if (!isset($this->_params['email'])||!$this->_params['email']) throw new ExceptionUserTextStorage('error.ajax_profile_emailMissing');
    
    $b = new BUser;
    $b->sendRegistrationEmail(array('userEmail'=>$this->_params['email'], 'provider'=>$this->_params['provider']));
    
    $this->_result = array();
  }
}

?>
