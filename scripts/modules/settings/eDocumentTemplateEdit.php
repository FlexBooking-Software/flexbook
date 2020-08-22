<?php

class ModuleDocumentTemplateEdit extends ExecModule {

  protected function _userRun() {
    $id = $this->_app->request->getParams('id');
    $validator = Validator::get('documentTemplate','DocumentTemplateValidator',true);
    if ($id) $validator->loadData($id);

    return 'vDocumentTemplateEdit';
  }
}

?>
