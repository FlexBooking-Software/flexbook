<?php

class AvailProfileValidator extends Validator {

  protected function _insert() {
    $app = Application::get();

    $this->addValidatorVar(new ValidatorVar('id'));
    $this->addValidatorVar(new ValidatorVar('name', true, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('providerId'));
    $this->addValidatorVar(new ValidatorVarArray('weekdayFrom', false, new ValidatorTypeTime('h:m', '24H'), $app->textStorage->getText('error.editAvailProfile_time')));
    $this->addValidatorVar(new ValidatorVarArray('weekdayTo', false, new ValidatorTypeTime('h:m', '24H'), $app->textStorage->getText('error.editAvailProfile_time')));

    $this->addValidatorVar(new ValidatorVar('fromEvent'));
    $this->addValidatorVar(new ValidatorVar('fromResource'));
    
    $this->getVar('name')->setLabel($app->textStorage->getText('label.editAvailProfile_name'));
    $this->getVar('providerId')->setLabel($app->textStorage->getText('label.editAvailProfile_provider'));
  }

  public function loadData($id) {
    $app = Application::get();

    $bAvailProfile = new BAvailabilityProfile($id);
    $data = $bAvailProfile->getData();
    
    $weekdayFrom = array();
    $weekdayTo = array();
    foreach ($data['weekday'] as $day=>$spec) {
      $weekdayFrom[$day] = $app->regionalSettings->convertTimeToHuman($spec['from'],'h:m');
      $weekdayTo[$day] = $app->regionalSettings->convertTimeToHuman($spec['to'],'h:m');
    }
    $data['weekdayFrom'] = $weekdayFrom;
    $data['weekdayTo'] = $weekdayTo;
    
    unset($data['weekday']);
    
    $this->setValues($data);
  }
}

?>
