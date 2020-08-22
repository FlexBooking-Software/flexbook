<?php

class HomeValidator extends Validator {

  protected function _insert() {
    $app = Application::get();

    $this->addValidatorVar(new ValidatorVarArray('id'));
    $this->addValidatorVar(new ValidatorVar('commodity'));
    $this->addValidatorVar(new ValidatorVar('resourceTag'));
    $this->addValidatorVar(new ValidatorVar('resourcePoolTag'));
    $this->addValidatorVar(new ValidatorVar('eventTag'));
    $this->addValidatorVar(new ValidatorVar('eventFrom'));
    $this->addValidatorVar(new ValidatorVar('eventTo'));
  }
}

?>
