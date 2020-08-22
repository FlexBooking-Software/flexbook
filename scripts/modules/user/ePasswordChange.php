<?php

class ModulePasswordChange extends ExecModule {

  protected function _userRun() {
    $validator = Validator::get('changePassword','ChangePasswordValidator');
    $validator->initValues();
    $validator->validateValues();

    if ($validator->getVarValue('newPassword') != $validator->getVarValue('retypePassword')) {
      throw new ExceptionUser($this->_app->textStorage->getText('error.changePassword_newNoMatch'));
    }

    $oldpassword = $validator->getVarValue('oldPassword');
    $newpassword = $validator->getVarValue('newPassword');
    $retypepassword = $validator->getVarValue('retypePassword');
    
    $user = new BUser($this->_app->auth->getUserId());
    $user->changePassword($oldpassword, $newpassword);

    $this->_app->messages->addMessage('userInfo', $this->_app->textStorage->getText('info.changePassword_ok'));

    return 'eBack';
  }
}

?>
