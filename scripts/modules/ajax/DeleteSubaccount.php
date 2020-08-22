<?php

class AjaxDeleteSubaccount extends AjaxAction {

  protected function _userRun() {
    if ($this->_app->session->getExpired()||!$this->_app->auth->getUserId()) throw new ExceptionUserTextStorage('error.ajax_sessionExpired');
    
    $subaccount = ifsetor($this->_params['id']);
    
    if ($subaccount) {
      $b = new BUser($subaccount);
      $b->delete();
    }
    
    $this->_result = array('error'=>false);
  }
}

?>