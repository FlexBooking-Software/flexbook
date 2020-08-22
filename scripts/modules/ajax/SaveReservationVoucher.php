<?php

class AjaxSaveReservationVoucher extends AjaxAction {

  protected function _userRun() {
    if ($this->_app->session->getExpired()||!$this->_app->auth->getUserId()) throw new ExceptionUserTextStorage('error.ajax_sessionExpired');
    
    $reservation = ifsetor($this->_params['id']);

    $params = array();
    if (isset($this->_params['voucher'])) $params['voucher'] = $this->_params['voucher'];

    if (count($params)) {
      $b = new BReservation($reservation);
      $ret = $b->saveVoucher($params);

      $id = $b->getId();

      $this->_result = array('error'=>false,'id'=>$id,'payed'=>$ret['payedByVoucher']);
    }
  }
}

?>
