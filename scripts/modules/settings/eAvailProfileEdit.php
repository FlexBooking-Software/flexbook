<?php

class ModuleAvailProfileEdit extends ExecModule {

  protected function _userRun() {
    $id = $this->_app->request->getParams('id');
    $validator = Validator::get('availProfile','AvailProfileValidator',true);
    if ($id) $validator->loadData($id);

    return 'vAvailProfileEdit';
  }
}

?>
