<?php

class UserCreditValidator extends Validator {

  protected function _insert() {
    $app = Application::get();

    $this->addValidatorVar(new ValidatorVar('userId', true));
    $this->addValidatorVar(new ValidatorVar('newTicket', true));
  }
}

?>
