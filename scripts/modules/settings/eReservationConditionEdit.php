<?php

class ModuleReservationConditionEdit extends ExecModule {

  protected function _userRun() {
    $id = $this->_app->request->getParams('id');
    $validator = Validator::get('reservationCondition','ReservationConditionValidator',true);
    if ($id) $validator->loadData($id);

    return 'vReservationConditionEdit';
  }
}

?>
