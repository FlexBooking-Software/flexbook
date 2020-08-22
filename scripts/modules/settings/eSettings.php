<?php

class ModuleSettings extends ExecModule {

  protected function _userRun() {
    $validator = Validator::get('settings','SettingsValidator',true);
    $validator->loadData();

    return 'vSettingsEdit';
  }
}

?>
