<?php

class ModuleUnitProfileSave extends ExecModule {

  protected function _userRun() {
    if (!$this->_app->auth->haveRight('settings_admin', $this->_app->auth->getActualProvider())) throw new ExceptionUserTextStorage('error.accessDenied');

    $validator = Validator::get('unitProfile','UnitProfileValidator');
    $validator->initValues();
    $validator->validateValues();

    $id = $validator->getVarValue('id');
    
    $unit = $validator->getVarValue('unitMultiplier');
    $unitBase = $validator->getVarValue('unitBase');
    $unitRounding = null;
    if (!strcmp($unitBase,'hour')) $unit = 60*$unit;
    elseif (!strcmp($unitBase,'day')) {
      $unit = 1440*$unit;
      $unitRounding = 'day';
    } elseif (!strcmp($unitBase,'night')) {
      $unit = 1440*$unit;
      $unitRounding = 'night';
    }
    
    $alignmentGrid = $validator->getVarValue('alignmentTimeGridMultiplier');
    $alignmentGridBase = $validator->getVarValue('alignmentTimeGridBase');
    if (!strcmp($alignmentGridBase,'hour')) $alignmentGrid = 60*$alignmentGrid;
    if (!strcmp($alignmentGridBase,'day')) $alignmentGrid = 1440*$alignmentGrid;
    
    $bUnitProfile = new BUnitProfile($id?$id:null);
    $bUnitProfile->save(array(
      'name'              => $validator->getVarValue('name'),
      'providerId'        => $validator->getVarValue('providerId'),
      'unit'              => $unit,
      'unitRounding'      => $unitRounding,
      'minimumUnit'       => $validator->getVarValue('minimumUnit'),
      'maximumUnit'       => $validator->getVarValue('maximumUnit'),
      'alignmentTimeFrom' => $this->_app->regionalSettings->convertHumanToTime($validator->getVarValue('alignmentTimeFrom'), 'h:m'),
      'alignmentTimeTo'   => $this->_app->regionalSettings->convertHumanToTime($validator->getVarValue('alignmentTimeTo'), 'h:m'),
      'alignmentTimeGrid' => $alignmentGrid,
      'endTimeFrom'       => $this->_app->regionalSettings->convertHumanToTime($validator->getVarValue('endTimeFrom'), 'h:m'),
      'endTimeTo'         => $this->_app->regionalSettings->convertHumanToTime($validator->getVarValue('endTimeTo'), 'h:m'),
    ));

    $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.editUnitProfile_saveOk'), $validator->getVarValue('name')));

    if ($validator->getVarValue('fromEvent')) {
      $eValidator = Validator::get('event', 'EventValidator');
      $eValidator->setValues(array('unitProfile' => $bUnitProfile->getId()));
    } elseif ($validator->getVarValue('fromResource')) {
      $eValidator = Validator::get('resource', 'ResourceValidator');
      $eValidator->setValues(array('unitProfile' => $bUnitProfile->getId()));
    }

    return 'eBack';
  }
}

?>
