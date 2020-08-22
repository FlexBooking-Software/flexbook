<?php

class TagValidator extends Validator {

  protected function _insert() {
    $app = Application::get();

    $this->addValidatorVar(new ValidatorVar('id'));
    $this->addValidatorVar(new ValidatorVar('name', true, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVarArray('portal'));
    
    $this->getVar('name')->setLabel($app->textStorage->getText('label.editTag_name'));
  }

  public function loadData($id) {
    $bTag = new BTag($id);
    $data = $bTag->getData();
   
    $this->setValues($data);
  }
}

?>
