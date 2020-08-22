<?php

class AjaxSwapSubstitute extends AjaxAction {

  protected function _userRun() {
    if ($this->_app->session->getExpired()||!$this->_app->auth->getUserId()) throw new ExceptionUserTextStorage('error.ajax_sessionExpired');
    
    $event = ifsetor($this->_params['event']);
    if ($event) {
      $params = array();
      if (isset($this->_params['substitute'])) $params['substituteId'] = $this->_params['substitute'];
      if (isset($this->_params['reservation'])) $params['reservationId'] = $this->_params['reservation'];
      #adump($params);die;
      
      $b = new BEvent($event);
      $result = $b->swapSubstitute($params);
      
      $this->_result = array('error'=>false,'popup'=>$result);
    } else $this->_result = array('error'=>true);
  }
}

?>
