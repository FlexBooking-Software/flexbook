<?php

class ModuleTagEdit extends ExecModule {

  protected function _userRun() {
    $validator = Validator::get('tag','TagValidator',true);
    if ($id = $this->_app->request->getParams('id')) $validator->loadData($id);

    $name = $validator->getVarValue('name');
    $gridSettings = new GridSettingsTag('listSimilarTag');
    $gridSettings->addFilter('name',$name);
    $gridSettings->saveSettings();

    return 'vTagEdit';
  }
}

?>
