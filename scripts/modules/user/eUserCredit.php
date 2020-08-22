<?php

class ModuleUserCredit extends ExecModule {

  protected function _userRun() {
    $validator = Validator::get('userCredit','UserCreditValidator',true);
    $validator->setValues(array('userId'=>$this->_app->request->getParams('id')));
    
    return 'vUserCredit';
  }
}

?>
