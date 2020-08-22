<?php

class InPageLoginValidator extends Validator {

  protected function _insert() {
    $app = Application::get();

    $this->addValidatorVar(new ValidatorVar('email', true, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('firstname', true, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('lastname', true, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('street', true, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('city', true, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('postalCode', true));
    $this->addValidatorVar(new ValidatorVar('state', true));
    $this->addValidatorVar(new ValidatorVar('phone', true, new ValidatorTypeString(15)));
    
    $this->getVar('email')->setLabel($app->textStorage->getText('label.inpage_registration_email'));
    $this->getVar('firstname')->setLabel($app->textStorage->getText('label.inpage_registration_firstname'));
    $this->getVar('lastname')->setLabel($app->textStorage->getText('label.inpage_registration_lastname'));
    $this->getVar('street')->setLabel($app->textStorage->getText('label.inpage_registration_street'));
    $this->getVar('city')->setLabel($app->textStorage->getText('label.inpage_registration_city'));
    $this->getVar('postalCode')->setLabel($app->textStorage->getText('label.inpage_registration_postalCode'));
    $this->getVar('state')->setLabel($app->textStorage->getText('label.inpage_registration_state'));
    $this->getVar('phone')->setLabel($app->textStorage->getText('label.inpage_registration_phone'));
  }
}

?>
