<?php

class ModuleAvailExProfileEdit extends ExecModule {

  protected function _userRun() {
    $id = $this->_app->request->getParams('id');
    $validator = Validator::get('availExProfile','AvailExProfileValidator',true);
    if ($id) $validator->loadData($id);

    return 'vAvailExProfileEdit';
  }
}

?>
