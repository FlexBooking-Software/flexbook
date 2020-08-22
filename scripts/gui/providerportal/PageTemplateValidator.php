<?php

class PageTemplateValidator extends Validator {

  protected function _insert() {
    $app = Application::get();

    $this->addValidatorVar(new ValidatorVar('id'));
    $this->addValidatorVar(new ValidatorVar('name', true, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('content'));
    
    $this->getVar('name')->setLabel($app->textStorage->getText('label.editPageTemplate_name'));
  }

  public function loadData($id) {
    $app = Application::get();
    
    $bPageTemplate = new BPageTemplate($id);
    $data = $bPageTemplate->getData();
   
    $this->setValues($data);
  }
}

?>
