<?php

class InPageValidator extends Validator {

  protected function _insert() {
    $app = Application::get();

    $this->addValidatorVar(new ValidatorVar('customerId'));
    $this->addValidatorVar(new ValidatorVar('providerId'));
    $this->addValidatorVar(new ValidatorVar('providerName'));
    
    $this->addValidatorVar(new ValidatorVar('portalData'));
    
    $this->addValidatorVar(new ValidatorVar('czechTourism_password'));
    $this->addValidatorVar(new ValidatorVar('czechTourism_resourceTag'));
  }
}

?>
