<?php

class DocumentTemplateValidator extends Validator {

  protected function _insert() {
    $app = Application::get();

    $this->addValidatorVar(new ValidatorVar('id'));
    $this->addValidatorVar(new ValidatorVar('providerId', true));
    $this->addValidatorVar(new ValidatorVar('name', true, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('target', true));
    $this->addValidatorVar(new ValidatorVar('description'));
    $this->addValidatorVar(new ValidatorVar('item'));

    $this->addValidatorVar(new ValidatorVar('fromEvent'));
    $this->addValidatorVar(new ValidatorVar('fromResource'));

    $this->getVar('providerId')->setLabel($app->textStorage->getText('label.editDocumentTemplate_provider'));
    $this->getVar('name')->setLabel($app->textStorage->getText('label.editDocumentTemplate_name'));
    $this->getVar('target')->setLabel($app->textStorage->getText('label.editDocumentTemplate_target'));
  }

  public function loadData($id) {
    $app = Application::get();

    $bDocumentTemplate = new BDocumentTemplate($id);
    $data = $bDocumentTemplate->getData();
    
    $this->setValues($data);
  }
}

?>
