<?php

class EventGroupValidator extends Validator {

  protected function _insert() {
    $app = Application::get();
    
    $this->addValidatorVar(new ValidatorVar('groupStart', true, new ValidatorTypeDateTime));
    $this->addValidatorVar(new ValidatorVar('groupEnd', true, new ValidatorTypeDateTime));
    $this->addValidatorVar(new ValidatorVar('groupException'));
    $this->addValidatorVar(new ValidatorVar('groupCount', false, new ValidatorTypeInteger(500)));
    $this->addValidatorVar(new ValidatorVar('name', true, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('groupLength', true, new ValidatorTypeInteger(500)));
    $this->addValidatorVar(new ValidatorVar('groupPause', false, new ValidatorTypeInteger(65535)));

    $this->addValidatorVar(new ValidatorVar('externalId', false, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('providerId', true));
    $this->addValidatorVar(new ValidatorVar('centerId', true));
    $this->addValidatorVar(new ValidatorVarArray('resource'));
    $this->addValidatorVar(new ValidatorVar('organiserId', true));
    $this->addValidatorVar(new ValidatorVar('description', true));
    $this->addValidatorVar(new ValidatorVar('tag'));
    $this->addValidatorVar(new ValidatorVar('maxAttendees', true, new ValidatorTypeInteger(500)));
    $this->addValidatorVar(new ValidatorVar('maxCoAttendees', false, new ValidatorTypeInteger(500)));
    $this->addValidatorVar(new ValidatorVar('maxSubstitutes', false, new ValidatorTypeInteger(500)));
    $this->addValidatorVar(new ValidatorVar('price', false, new ValidatorTypeNumber(100,2)));
    $this->addValidatorVar(new ValidatorVar('reservationConditionId'));
    $this->addValidatorVar(new ValidatorVar('notificationTemplateId'));
    $this->addValidatorVar(new ValidatorVar('documentTemplateId'));
    $this->addValidatorVar(new ValidatorVar('reservationMaxAttendees', false, new ValidatorTypeInteger(500)));
    $this->addValidatorVar(new ValidatorVar('badge'));
    $this->addValidatorVar(new ValidatorVar('active'));
    $this->addValidatorVar(new ValidatorVarArray('portal'));
    
    $this->addValidatorVar(new ValidatorVar('urlDescription'));
    $this->addValidatorVar(new ValidatorVar('urlPrice'));
    $this->addValidatorVar(new ValidatorVar('urlOpening'));
    $this->addValidatorVar(new ValidatorVar('urlPhoto'));
    
    $this->getVar('groupStart')->setLabel($app->textStorage->getText('label.editEvent_groupStart'));
    $this->getVar('groupEnd')->setLabel($app->textStorage->getText('label.editEvent_groupEnd'));
    $this->getVar('groupCount')->setLabel($app->textStorage->getText('label.editEvent_groupCount'));
    $this->getVar('groupLength')->setLabel($app->textStorage->getText('label.editEvent_groupLength'));
    $this->getVar('groupPause')->setLabel($app->textStorage->getText('label.editEvent_groupPause'));
    $this->getVar('name')->setLabel($app->textStorage->getText('label.editEvent_name'));
    $this->getVar('description')->setLabel($app->textStorage->getText('label.editEvent_description'));
    $this->getVar('maxAttendees')->setLabel($app->textStorage->getText('label.editEvent_maxAttendees'));
    $this->getVar('maxCoAttendees')->setLabel($app->textStorage->getText('label.editEvent_maxCoAttendees'));
    $this->getVar('maxSubstitutes')->setLabel($app->textStorage->getText('label.editEvent_maxSubstitutes'));
    $this->getVar('price')->setLabel($app->textStorage->getText('label.editEvent_price'));
    $this->getVar('centerId')->setLabel($app->textStorage->getText('label.editEvent_center'));
    $this->getVar('providerId')->setLabel($app->textStorage->getText('label.editEvent_provider'));
    $this->getVar('organiserId')->setLabel($app->textStorage->getText('label.editEvent_organiser'));
    
  }
}

?>
