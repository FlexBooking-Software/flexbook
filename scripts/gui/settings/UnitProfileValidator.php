<?php

class UnitProfileValidator extends Validator {

  protected function _insert() {
    $app = Application::get();

    $this->addValidatorVar(new ValidatorVar('id'));
    $this->addValidatorVar(new ValidatorVar('name', true, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('providerId'));
    $this->addValidatorVar(new ValidatorVar('unitBase', true));
    $this->addValidatorVar(new ValidatorVar('unitMultiplier', true, new ValidatorTypeInteger));
    $this->addValidatorVar(new ValidatorVar('minimumUnit', true, new ValidatorTypeInteger));
    $this->addValidatorVar(new ValidatorVar('maximumUnit', false, new ValidatorTypeInteger));
    $this->addValidatorVar(new ValidatorVar('alignmentTimeFrom', false, new ValidatorTypeTime('h:m')));
    $this->addValidatorVar(new ValidatorVar('alignmentTimeTo', false, new ValidatorTypeTime('h:m')));
    $this->addValidatorVar(new ValidatorVar('alignmentTimeGridBase'));
    $this->addValidatorVar(new ValidatorVar('alignmentTimeGridMultiplier', false, new ValidatorTypeInteger));
    $this->addValidatorVar(new ValidatorVar('endTimeFrom', false, new ValidatorTypeTime('h:m')));
    $this->addValidatorVar(new ValidatorVar('endTimeTo', false, new ValidatorTypeTime('h:m')));

    $this->addValidatorVar(new ValidatorVar('fromEvent'));
    $this->addValidatorVar(new ValidatorVar('fromResource'));
    
    $this->getVar('name')->setLabel($app->textStorage->getText('label.editUnitProfile_name'));
    $this->getVar('providerId')->setLabel($app->textStorage->getText('label.editUnitProfile_provider'));
    $this->getVar('unitBase')->setLabel($app->textStorage->getText('label.editUnitProfile_unit'));
    $this->getVar('unitMultiplier')->setLabel($app->textStorage->getText('label.editUnitProfile_unit'));
    $this->getVar('minimumUnit')->setLabel($app->textStorage->getText('label.editUnitProfile_minimumUnitVal'));
    $this->getVar('maximumUnit')->setLabel($app->textStorage->getText('label.editUnitProfile_maximumUnitVal'));
    $this->getVar('alignmentTimeFrom')->setLabel($app->textStorage->getText('label.editUnitProfile_alignmentVal'));
    $this->getVar('alignmentTimeTo')->setLabel($app->textStorage->getText('label.editUnitProfile_alignmentVal'));
    $this->getVar('alignmentTimeGridBase')->setLabel($app->textStorage->getText('label.editUnitProfile_alignmentVal'));
    $this->getVar('alignmentTimeGridMultiplier')->setLabel($app->textStorage->getText('label.editUnitProfile_alignmentVal'));
    $this->getVar('endTimeFrom')->setLabel($app->textStorage->getText('label.editUnitProfile_endTime'));
    $this->getVar('endTimeTo')->setLabel($app->textStorage->getText('label.editUnitProfile_endTime'));
  }

  public function loadData($id) {
    $app = Application::get();

    $bUnitProfile = new BUnitProfile($id);
    $data = $bUnitProfile->getData();
    $data['unitBase'] = 'min';
    $data['unitMultiplier'] = $data['unit'];
    if (!strcmp($data['unitRounding'],'day')) { $data['unitMultiplier'] = $data['unit']/1440; $data['unitBase'] = 'day'; }
    elseif (!strcmp($data['unitRounding'],'night')) { $data['unitMultiplier'] = $data['unit']/1440; $data['unitBase'] = 'night'; }
    elseif ($data['unit']%60 === 0) { $data['unitMultiplier'] = $data['unit']/60; $data['unitBase'] = 'hour'; }
    $data['alignmentTimeFrom'] = $app->regionalSettings->convertTimeToHuman($data['alignmentTimeFrom'], 'h:m');
    $data['alignmentTimeTo'] = $app->regionalSettings->convertTimeToHuman($data['alignmentTimeTo'], 'h:m');
    if ($data['alignmentTimeGrid']) {
      $data['alignmentTimeGridBase'] = 'min';
      $data['alignmentTimeGridMultiplier'] = $data['alignmentTimeGrid'];
      if ($data['alignmentTimeGrid']%1440 === 0) { $data['alignmentTimeGridMultiplier'] = $data['alignmentTimeGrid']/1440; $data['alignmentTimeGridBase'] = 'day'; }
      elseif ($data['alignmentTimeGrid']%60 === 0) { $data['alignmentTimeGridMultiplier'] = $data['alignmentTimeGrid']/60; $data['alignmentTimeGridBase'] = 'hour'; }
    }
    $data['endTimeFrom'] = $app->regionalSettings->convertTimeToHuman($data['endTimeFrom'], 'h:m');
    $data['endTimeTo'] = $app->regionalSettings->convertTimeToHuman($data['endTimeTo'], 'h:m');

    $this->setValues($data);
  }
}

?>
