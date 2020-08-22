<?php

class ModuleResourcePoolEdit extends ExecModule {

  protected function _userRun() {
    $validator = Validator::get('resourcePool','ResourcePoolValidator',true);
    if ($id = $this->_app->request->getParams('id')) $validator->loadData($id);
    else {
      $data = array('active'=>'Y');
      
      if (!$this->_app->auth->isAdministrator()) {
        $data['providerId'] = $this->_app->auth->getActualProvider();
        $data['centerId'] = $this->_app->auth->getActualCenter();
      }
      
      $validator->setValues($data);
    }

    return 'vResourcePoolEdit';
  }
}

?>
