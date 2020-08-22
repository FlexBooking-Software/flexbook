<?php

class ChangePasswordValidator extends Validator {

  protected function _insert() {
    $app = Application::get();
    
    $this->addValidatorVar(new ValidatorVar('oldPassword', true));
    $this->addValidatorVar(new ValidatorVar('newPassword', true));
    $this->addValidatorVar(new ValidatorVar('retypePassword', true));
    
    $this->getVar('oldPassword')->setLabel($app->textStorage->getText('label.changePassword_oldPassword'));
    $this->getVar('newPassword')->setLabel($app->textStorage->getText('label.changePassword_newPassword'));
    $this->getVar('retypePassword')->setLabel($app->textStorage->getText('label.changePassword_retypePassword'));
  }
}

?>
