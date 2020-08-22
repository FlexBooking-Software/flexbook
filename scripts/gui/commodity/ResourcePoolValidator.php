<?php

class ResourcePoolValidator extends Validator {

  protected function _insert() {
    $app = Application::get();

    $this->addValidatorVar(new ValidatorVar('id'));
    $this->addValidatorVar(new ValidatorVar('name', true, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('description'));
    $this->addValidatorVar(new ValidatorVar('tag'));
    $this->addValidatorVar(new ValidatorVar('active'));
    $this->addValidatorVar(new ValidatorVar('externalId', false, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('providerId', true));
    $this->addValidatorVar(new ValidatorVar('centerId', true));
    $this->addValidatorVar(new ValidatorVarArray('resource'));
    $this->addValidatorVar(new ValidatorVarArray('resourceId'));

    $this->addValidatorVar(new ValidatorVar('urlPhoto'));
    
    $this->getVar('name')->setLabel($app->textStorage->getText('label.editResourcePool_name'));
    $this->getVar('description')->setLabel($app->textStorage->getText('label.editResourcePool_description'));
    $this->getVar('providerId')->setLabel($app->textStorage->getText('label.editResourcePool_provider'));
    $this->getVar('centerId')->setLabel($app->textStorage->getText('label.editResourcePool_center'));
  }

  public function loadData($id) {
    $app = Application::get();
    
    $bResourcePool = new BResourcePool($id);
    $data = $bResourcePool->getData();
    
    $this->setValues($data);
  }
}

?>
