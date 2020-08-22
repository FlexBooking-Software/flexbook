<?php

class CzechTourismValidator extends Validator {

  protected function _insert() {
    $app = Application::get();

    $this->addValidatorVar(new ValidatorVar('company', true, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('street', true, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('city', true, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('postalCode', true));
    $this->addValidatorVar(new ValidatorVar('state', true));
    $this->addValidatorVar(new ValidatorVar('ic', true, new ValidatorTypeString(8)));
    $this->addValidatorVar(new ValidatorVar('dic', false, new ValidatorTypeString(12)));
    
    $this->addValidatorVar(new ValidatorVar('firstname1', true, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('lastname1', true, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('role1', true, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('email1', true, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('phone1', true, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('firstname2', false, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('lastname2', false, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('role2', false, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('email2', false, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('phone2', false, new ValidatorTypeString(255)));
    
    $this->addValidatorVar(new ValidatorVar('catering'));
    $this->addValidatorVar(new ValidatorVar('fee', true));
    
    $this->getVar('company')->setLabel($app->textStorage->getText('label.inpage_registration_company'));
    $this->getVar('street')->setLabel($app->textStorage->getText('label.inpage_registration_street'));
    $this->getVar('city')->setLabel($app->textStorage->getText('label.inpage_registration_city'));
    $this->getVar('postalCode')->setLabel($app->textStorage->getText('label.inpage_registration_postalCode'));
    $this->getVar('state')->setLabel($app->textStorage->getText('label.inpage_registration_state'));
    $this->getVar('ic')->setLabel($app->textStorage->getText('label.inpage_registration_ic'));
    $this->getVar('dic')->setLabel($app->textStorage->getText('label.inpage_registration_dic'));
    
    $this->getVar('firstname1')->setLabel($app->textStorage->getText('label.inpage_registration_firstname'));
    $this->getVar('lastname1')->setLabel($app->textStorage->getText('label.inpage_registration_lastname'));
    $this->getVar('role1')->setLabel($app->textStorage->getText('label.inpage_registration_role'));
    $this->getVar('email1')->setLabel($app->textStorage->getText('label.inpage_registration_email'));
    $this->getVar('phone1')->setLabel($app->textStorage->getText('label.inpage_registration_phone'));
    $this->getVar('firstname2')->setLabel($app->textStorage->getText('label.inpage_registration_firstname'));
    $this->getVar('lastname2')->setLabel($app->textStorage->getText('label.inpage_registration_lastname'));
    $this->getVar('role2')->setLabel($app->textStorage->getText('label.inpage_registration_role'));
    $this->getVar('email2')->setLabel($app->textStorage->getText('label.inpage_registration_email'));
    $this->getVar('phone2')->setLabel($app->textStorage->getText('label.inpage_registration_phone'));
    
    $this->getVar('fee')->setLabel($app->textStorage->getText('label.inpage_registration_fee'));
  }
}

?>
