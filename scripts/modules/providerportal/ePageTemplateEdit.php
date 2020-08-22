<?php

class ModulePageTemplateEdit extends ExecModule {

  protected function _userRun() {
    $validator = Validator::get('tag','PageTemplateValidator',true);
    if ($id = $this->_app->request->getParams('id')) $validator->loadData($id);

    return 'vPageTemplateEdit';
  }
}

?>
