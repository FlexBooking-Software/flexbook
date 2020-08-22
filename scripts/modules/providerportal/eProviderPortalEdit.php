<?php

class ModuleProviderPortalEdit extends ExecModule {

  protected function _userRun() {
    $validator = Validator::get('providerPortal','ProviderPortalValidator',true);
    if ($id = $this->_app->request->getParams('id')) $validator->loadData($id);

    return 'vProviderPortalEdit';
  }
}

?>
