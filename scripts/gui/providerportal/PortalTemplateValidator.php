<?php

class PortalTemplateValidator extends Validator {

  protected function _insert() {
    $app = Application::get();

    $this->addValidatorVar(new ValidatorVar('id'));
    $this->addValidatorVar(new ValidatorVar('name', true, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('css'));
    $this->addValidatorVar(new ValidatorVar('content'));
    $this->addValidatorVar(new ValidatorVar('preview'));
    $this->addValidatorVar(new ValidatorVar('previewHash'));
    $this->addValidatorVar(new ValidatorVar('previewNew'));
    $this->addValidatorVar(new ValidatorVarArray('page'));
    
    $this->getVar('name')->setLabel($app->textStorage->getText('label.editPortalTemplate_name'));
  }

  public function loadData($id) {
    $app = Application::get();
    
    $bPortalTemplate = new BPortalTemplate($id);
    $data = $bPortalTemplate->getData();
   
    $this->setValues($data);
  }
}

?>
