<?php

class AjaxPayReservation extends AjaxAction {

  protected function _userRun() {
    if ($this->_app->session->getExpired()||!$this->_app->auth->getUserId()) throw new ExceptionUserTextStorage('error.ajax_sessionExpired');
    
    $reservation = ifsetor($this->_params['id']);
    
    $payType = ifsetor($this->_params['type'],'credit');
    $payParams = array();
    if (isset($this->_params['ticket'])) $payParams['ticket'] = $this->_params['ticket'];
    if (isset($this->_params['arrangeCredit'])) $payParams['arrangeCredit'] = $this->_params['arrangeCredit'];
    if (isset($this->_params['arrangeCreditAmount'])) $payParams['arrangeCreditAmount'] = $this->_params['arrangeCreditAmount'];
    
    $b = new BReservation($reservation);
    $b->pay($payType, $payParams);
    
    $this->_result = array('error'=>false);
  }
}

?>
