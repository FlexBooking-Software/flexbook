<?php

class ModuleUserProfile extends ExecModule {

  protected function _userRun() {
    $uId = $this->_app->auth->getUserId();
    
    $validator = Validator::get('user','UserValidator',true);
    $validator->loadData($uId);
    $validator->setValues(array('myProfile'=>1));
    
    setcookie('ui-user-tab', 0);

    return 'vUserEdit';
  }
}

?>
