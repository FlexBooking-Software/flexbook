<?php

class ModuleCustomerEdit extends ExecModule {

  protected function _userRun() {
    $validator = Validator::get('customer','CustomerValidator',true);
    
    $id = $this->_app->request->getParams('id');
    $validator->loadData($id);
    if ($fromReservation = $this->_app->request->getParams('fromReservation')) $validator->setValues(array('fromReservation'=>1));
    
    setcookie('ui-customer-tab', 0);

    return 'vCustomerEdit';
  }
}

?>
