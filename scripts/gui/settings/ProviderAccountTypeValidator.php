<?php

class ProviderAccountTypeValidator extends Validator {

  protected function _insert() {
    $app = Application::get();

    $this->addValidatorVar(new ValidatorVar('id'));
    $this->addValidatorVar(new ValidatorVar('name', true, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('providerId', true));

    $this->addValidatorVar(new ValidatorVar('fromEvent'));
    $this->addValidatorVar(new ValidatorVar('fromResource'));
    
    $this->getVar('name')->setLabel($app->textStorage->getText('label.editProviderAccountType_name'));
    $this->getVar('providerId')->setLabel($app->textStorage->getText('label.editProviderAccountType_provider'));
  }

  public function loadData($id) {
    $app = Application::get();

    $bProviderAccountType = new BProviderAccountType($id);
    $data = $bProviderAccountType->getData();
    
    $this->setValues($data);
  }
}

?>
