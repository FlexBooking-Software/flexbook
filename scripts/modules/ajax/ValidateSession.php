<?php

class AjaxValidateSession extends AjaxAction {

  protected function _userRun() {
    #error_log('param: '.$this->_params['provider']);
    #error_log('session: '.$this->_app->auth->getActualProvider());

    $ret = !$this->_app->session->getExpired()&&($this->_params['provider']==$this->_app->auth->getActualProvider());

    $this->_result = $ret;
  }
}

?>
