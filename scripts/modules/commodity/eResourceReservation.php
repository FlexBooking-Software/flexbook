<?php

class ModuleResourceReservation extends ExecModule {

  protected function _userRun() {
    $id = $this->_app->request->getParams('id');
    
    $validator = Validator::get('reservation','ReservationValidator',true);
    $validator->setValues(array(
      'providerId'    => $this->_app->auth->getActualProvider(),
      'resourceId'    => $id,
      'commodity'     => 'resource',
      'resourceFrom'  => $this->_app->regionalSettings->convertDateTimeToHuman($this->_app->regionalSettings->increaseDateTime(date('Y-m-d H:00:00'),0,0,0,1)),
    ));

    return 'vReservationEdit';
  }
}

?>
