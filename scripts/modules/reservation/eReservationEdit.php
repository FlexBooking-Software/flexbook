<?php

class ModuleReservationEdit extends ExecModule {

  protected function _userRun() {
    $validator = Validator::get('reservation','ReservationValidator',true);
    if ($id = $this->_app->request->getParams('id')) $validator->loadData($id);
    else {
      $validator->setValues(array(
        'providerId'    => $this->_app->auth->getActualProvider(),
        'eventPlaces'   => 1,
        'resourceFrom'  => $this->_app->regionalSettings->convertDateTimeToHuman($this->_app->regionalSettings->increaseDateTime(date('Y-m-d H:00:00'),0,0,0,1)),
        ));
    }

    return 'vReservationEdit';
  }
}

?>
