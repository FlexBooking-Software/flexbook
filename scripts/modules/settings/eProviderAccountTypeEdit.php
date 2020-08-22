<?php

class ModuleProviderAccountTypeEdit extends ExecModule {

  protected function _userRun() {
    $validator = Validator::get('providerAccountType','ProviderAccountTypeValidator',true);
    if ($id = $this->_app->request->getParams('id')) $validator->loadData($id);

    return 'vProviderAccountTypeEdit';
  }
}

?>
