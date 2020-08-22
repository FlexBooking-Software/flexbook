<?php

class ModuleEventCycleCreate extends ExecModule {

  protected function _userRun() {
    $validator = Validator::get('event','EventValidator', true);

    $data = array('active'=>'Y','repeat'=>'Y','badge'=>'N','reservationMaxAttendees'=>1,'maxCoAttendees'=>1,'feAttendeePublic'=>'N','feQuickReservation'=>'N',
      'feAllowedPayment'=>array('credit','ticket','online'));

    if (!$this->_app->auth->isAdministrator()) {
      $data['providerId'] = $this->_app->auth->getActualProvider();
      $data['centerId'] = $this->_app->auth->getActualCenter();
    }

    $validator->setValues($data);

    return 'vEventEdit';
  }
}

?>
