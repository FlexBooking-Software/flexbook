<?php

class ModuleEventGroupCreate extends ExecModule {

  protected function _userRun() {
    $validator = Validator::get('eventGroup','EventGroupValidator', true);
    
    $data = array('active'=>'Y','badge'=>'N','reservationMaxAttendees'=>1,'maxCoAttendees'=>1);
      
    if (!$this->_app->auth->isAdministrator()) {
      $data['providerId'] = $this->_app->auth->getActualProvider();
      $data['centerId'] = $this->_app->auth->getActualCenter();
    }
    
    $validator->setValues($data);

    return 'vEventGroupCreate';
  }
}

?>
