<?php

class ModuleReservationPreparePay extends ExecModule {

  protected function _userRun() {
    if ($id = $this->_app->request->getParams('id')) {
      $validator = Validator::get('reservation','ReservationValidator',true);
      $validator->loadData($id);
      $validator->setValues(array('confirmPast'=>'Y'));
      
      $this->_app->history->insertActual(array('action'=>'vReservationEdit','sessid'=>$this->_app->session->getId()));
    
      $this->_app->response->addParams(array('pay'=>'Y'));    
      return 'eReservationSave';
    }

    $this->_app->response->addParams(array('backwards'=>1));
    return 'eBack';
  }
}

?>
