<?php

class TicketValidator extends Validator {

  protected function _insert() {
    $app = Application::get();

    $this->addValidatorVar(new ValidatorVar('id'));
    $this->addValidatorVar(new ValidatorVar('name', true, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('providerId'));
    $this->addValidatorVar(new ValidatorVar('validityType', false));
    $this->addValidatorVar(new ValidatorVar('validityUnit', false));
    $this->addValidatorVar(new ValidatorVar('validityCount', false, new ValidatorTypeInteger));
    $this->addValidatorVar(new ValidatorVar('validityFrom', false, new ValidatorTypeDate));
    $this->addValidatorVar(new ValidatorVar('validityTo', false, new ValidatorTypeDate));
    $this->addValidatorVar(new ValidatorVar('center'));
    $this->addValidatorVar(new ValidatorVar('subjectTag'));
    $this->addValidatorVar(new ValidatorVar('price'));
    $this->addValidatorVar(new ValidatorVar('value'));
    $this->addValidatorVar(new ValidatorVar('active'));
    
    $this->getVar('name')->setLabel($app->textStorage->getText('label.editTicket_name'));
    $this->getVar('providerId')->setLabel($app->textStorage->getText('label.editTicket_provider'));
    $this->getVar('validityCount')->setLabel($app->textStorage->getText('label.editTicket_validityCount'));
    $this->getVar('validityFrom')->setLabel($app->textStorage->getText('label.editTicket_validityFrom'));
    $this->getVar('validityTo')->setLabel($app->textStorage->getText('label.editTicket_validityTo'));
    $this->getVar('price')->setLabel($app->textStorage->getText('label.editTicket_price'));
    $this->getVar('value')->setLabel($app->textStorage->getText('label.editTicket_value'));
  }

  public function loadData($id) {
    $app = Application::get();
    
    $b = new BTicket($id);
    $data = $b->getData();

    $data['validityFrom'] = $app->regionalSettings->convertDateToHuman($data['validityFrom']);
    $data['validityTo'] = $app->regionalSettings->convertDateToHuman($data['validityTo']);
   
    $this->setValues($data);
  }
}

?>
