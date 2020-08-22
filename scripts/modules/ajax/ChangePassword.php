<?php

class AjaxChangePassword extends AjaxAction {

  protected function _userRun() {
    if ($this->_app->session->getExpired()||!$this->_app->auth->getUserId()) throw new ExceptionUserTextStorage('error.ajax_sessionExpired');
    
    if (!isset($this->_params['oldPassword'])||!$this->_params['oldPassword']) {
      throw new ExceptionUserTextStorage('error.changePassword_invalidPassword');
    }
    if (!isset($this->_params['newPassword'])||!$this->_params['newPassword']) {
      throw new ExceptionUserTextStorage('error.changePassword_noPassword');
    }
    if (!isset($this->_params['retypePassword'])||($this->_params['newPassword']!=$this->_params['retypePassword'])) {
      throw new ExceptionUserTextStorage('error.changePassword_newNoMatch');
    }
    
    $b = new BUser($this->_app->auth->getUserId());
    $b->changePassword($this->_params['oldPassword'], $this->_params['newPassword']);
    
    $this->_result = array();
  }
}

?>
