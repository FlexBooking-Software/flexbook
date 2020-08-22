<?php

class ModulePasswordEdit extends ExecModule {

  protected function _userRun() {
    $validator = Validator::get('changePassword','ChangePasswordValidator',true);
    return 'vPasswordEdit';
  }
}

?>
