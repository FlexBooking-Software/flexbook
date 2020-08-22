<?php

class ModuleReservationPrepareCancel extends ExecModule {

  protected function _userRun() {
    if ($id = $this->_app->request->getParams('id')) {
      $validator = Validator::get('reservation','ReservationValidator',true);
      $validator->loadData($id);
      
      $this->_app->history->insertActual(array('action'=>'vReservationEdit','sessid'=>$this->_app->session->getId()));
    
      $this->_app->response->addParams(array('id'=>$id)); 
      return 'eReservationCancelRefund';
    }

    $this->_app->response->addParams(array('backwards'=>1));
    return 'eBack';
  }
}

?>
