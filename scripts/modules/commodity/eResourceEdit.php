<?php

class ModuleResourceEdit extends ExecModule {

  protected function _userRun() {
    $validator = Validator::get('resource','ResourceValidator',true);
    if ($id = $this->_app->request->getParams('id')) $validator->loadData($id);
    else {
      $data = array('active'=>'Y','feAllowedPayment'=>array('credit','ticket','online'));
      
      if (!$this->_app->auth->isAdministrator()) {
        $data['providerId'] = $this->_app->auth->getActualProvider();
        $data['centerId'] = $this->_app->auth->getActualCenter();
      }
      
      $validator->setValues($data);
    }

    return 'vResourceEdit';
  }
}

?>
