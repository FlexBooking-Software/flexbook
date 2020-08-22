<?php

class ModulePortalTemplateEdit extends ExecModule {

  protected function _userRun() {
    $validator = Validator::get('portalTemplate','PortalTemplateValidator',true);
    if ($id = $this->_app->request->getParams('id')) $validator->loadData($id);

    return 'vPortalTemplateEdit';
  }
}

?>
