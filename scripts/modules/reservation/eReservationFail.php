<?php

class ModuleReservationFail extends ExecModule {

  protected function _userRun() {
    if ($id = $this->_app->request->getParams('id')) {  
      $bReservation = new BReservation($id);
      $number = $bReservation->fail();
    
      $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.listReservation_failOk'), $number));
    }

    if (!$this->_app->request->getParams('editReservation')) $this->_app->response->addParams(array('backwards'=>1));
    return 'eBack';
  }
}

?>
