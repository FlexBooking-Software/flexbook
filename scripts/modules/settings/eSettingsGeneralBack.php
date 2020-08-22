<?php

class ModuleSettingsGeneralBack extends ExecModule {

  protected function _userRun() {
    $validator = Validator::get('settings','SettingsValidator',true);
    
    return 'eBack';
  }
}

?>
