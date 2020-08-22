<?php

class AjaxCancelReservationEventPackItem extends AjaxAction {

  protected function _userRun() {
    if ($this->_app->session->getExpired()||!$this->_app->auth->getUserId()) throw new ExceptionUserTextStorage('error.ajax_sessionExpired');
    
    $reservation = ifsetor($this->_params['reservation']);
    $event = ifsetor($this->_params['event']);
    
    if ($reservation&&$event) {
      $b = new BReservation($reservation);
      $b->cancelEventPackItem($event);
    }
    
    $this->_result = array('error'=>false);
  }
}

?>
