<?php

class ReportValidator extends Validator {

  protected function _insert() {
    $this->addValidatorVar(new ValidatorVar('loaded'));
    
    $this->addValidatorVar(new ValidatorVar('resultSummary'));
    $this->addValidatorVar(new ValidatorVar('result'));    
  }
}

?>
