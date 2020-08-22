<?php

class InPageRegistrationValidator extends Validator {

  protected function _insert() {
    $app = Application::get();

    $this->addValidatorVar(new ValidatorVar('step'));
    $this->addValidatorVar(new ValidatorVar('skipStep2'));
    $this->addValidatorVar(new ValidatorVar('userId'));
    $this->addValidatorVar(new ValidatorVar('email', true, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('password', false, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('validationPassword', false, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('retypePassword', false, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('firstname', true, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('lastname', true, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('street', true, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('city', true, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('postalCode', true));
    $this->addValidatorVar(new ValidatorVar('state', true));
    $this->addValidatorVar(new ValidatorVar('phone', true, new ValidatorTypeString(15)));
    $this->addValidatorVar(new ValidatorVar('advertising'));
    
    $this->addValidatorVar(new ValidatorVar('attribute'));
    $this->addValidatorVar(new ValidatorVarArray('newAttributeValue'));
    
    $this->addValidatorVar(new ValidatorVar('progressAction'));
    $this->addValidatorVar(new ValidatorVar('finishAction'));
    
    $this->getVar('email')->setLabel($app->textStorage->getText('label.inpage_registration_email'));
    $this->getVar('password')->setLabel($app->textStorage->getText('label.inpage_registration_password'));
    $this->getVar('retypePassword')->setLabel($app->textStorage->getText('label.inpage_registration_retypePassword'));
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
