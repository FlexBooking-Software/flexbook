<?php

class LoginValidator extends Validator {

  protected function _insert() {
    $app = Application::get();

    $this->addValidatorVar(new ValidatorVar('login_username'));
    $this->addValidatorVar(new ValidatorVar('login_password'));
    $this->addValidatorVar(new ValidatorVar('login_provider'));
    $this->addValidatorVar(new ValidatorVar('login_facebook'));
    $this->addValidatorVar(new ValidatorVar('login_google'));
    $this->addValidatorVar(new ValidatorVar('login_twitter'));

    $this->addValidatorVar(new ValidatorVar('login_accounts'));
  }
}

?>
