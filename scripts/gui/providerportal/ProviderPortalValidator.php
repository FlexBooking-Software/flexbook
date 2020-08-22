<?php

class ProviderPortalValidator extends Validator {

  protected function _insert() {
    $app = Application::get();
    
    $this->addValidatorVar(new ValidatorVar('id'));
    $this->addValidatorVar(new ValidatorVar('providerId', true));
    $this->addValidatorVar(new ValidatorVar('name', true, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('urlName', true, new ValidatorTypeString(50)));
    $this->addValidatorVar(new ValidatorVar('fromTemplate', true));
    $this->addValidatorVar(new ValidatorVar('active'));
    $this->addValidatorVar(new ValidatorVar('homePage', true));
    $this->addValidatorVar(new ValidatorVar('css'));
    $this->addValidatorVar(new ValidatorVar('javascript'));
    $this->addValidatorVar(new ValidatorVar('content'));
    $this->addValidatorVar(new ValidatorVarArray('page'));
    $this->addValidatorVar(new ValidatorVarArray('menu'));
    $this->addValidatorVar(new ValidatorVarArray('file'));
    
    $this->getVar('providerId')->setLabel($app->textStorage->getText('label.editProviderPortal_provider'));
    $this->getVar('name')->setLabel($app->textStorage->getText('label.editProviderPortal_name'));
    $this->getVar('urlName')->setLabel($app->textStorage->getText('label.editProviderPortal_urlName'));
    $this->getVar('fromTemplate')->setLabel($app->textStorage->getText('label.editProviderPortal_template'));
    $this->getVar('homePage')->setLabel($app->textStorage->getText('label.editProviderPortal_homePage'));
    
    $this->addValidatorVar(new ValidatorVar('pageId'));
    $this->addValidatorVar(new ValidatorVar('pageShortName', true, new ValidatorTypeString(50)));
    $this->addValidatorVar(new ValidatorVar('pageName', true, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('pageFromTemplate'));
    $this->addValidatorVar(new ValidatorVar('pageContent'));
    
    $this->getVar('pageName')->setLabel($app->textStorage->getText('label.editProviderPortal_pageName'));
    $this->getVar('pageShortName')->setLabel($app->textStorage->getText('label.editProviderPortal_pageShortName'));
  }

  public function loadData($id) {
    $app = Application::get();
    
    $bProviderPortal = new BProviderPortal($id);
    $data = $bProviderPortal->getData();
   
    $this->setValues($data);
  }
}

?>
