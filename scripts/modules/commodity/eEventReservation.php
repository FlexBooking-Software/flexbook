<?php

class ModuleEventReservation extends ExecModule {

  protected function _userRun() {
    $id = $this->_app->request->getParams('id');
    
    $validator = Validator::get('reservation','ReservationValidator',true);
    $validator->setValues(array(
      'providerId'    => $this->_app->auth->getActualProvider(),
      'eventId'       => $id,
      'eventPlaces'   => 1,
      'commodity'     => 'event'
    ));

    return 'vReservationEdit';
  }
}

?>
