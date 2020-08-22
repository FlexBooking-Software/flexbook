<?php

class ModuleUnitProfileEdit extends ExecModule {

  protected function _userRun() {
    $id = $this->_app->request->getParams('id');
    $validator = Validator::get('unitProfile','UnitProfileValidator',true);
    if ($id) $validator->loadData($id);

    return 'vUnitProfileEdit';
  }
}

?>
