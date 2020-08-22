<?php

class ModuleMyCustomerEdit extends ExecModule {

  protected function _userRun() {
    $validator = Validator::get('customer','CustomerValidator',true);
    
    $id = $this->_app->request->getParams('id');
    $validator->loadData($id);
    $validator->setValues(array('myData'=>1));
    
    setcookie('ui-customer-tab', 0);
    
    $this->_app->auth->setSubSection('profile');
    return 'vCustomerEdit';
  }
}

?>
