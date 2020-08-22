<?php

class ModuleAvailProfileSave extends ExecModule {

  protected function _userRun() {
    if (!$this->_app->auth->haveRight('settings_admin', $this->_app->auth->getActualProvider())) throw new ExceptionUserTextStorage('error.accessDenied');

    $validator = Validator::get('availProfile','AvailProfileValidator');
    $validator->initValues();
    
    $weekdayFrom = $validator->getVarValue('weekdayFrom');
    $weekdayTo = $validator->getVarValue('weekdayTo');
    foreach (array('mon','tue','wed','thu','fri','sat','sun') as $day) {
      $time = explode(':', $weekdayFrom[$day]);
      if (!isset($time[1])) {
        switch (strlen($time[0])) {
          case 3: $time[1] = substr($time[0],1,2); $time[0] = substr($time[0],0,1); break;
          case 4: $time[1] = substr($time[0],2,2); $time[0] = substr($time[0],0,2); break;
          default: $time[1] = '00';
        }
      }
      $weekdayFrom[$day] = sprintf('%s:%s', $time[0], $time[1]);

      $time = explode(':', $weekdayTo[$day]);
      if (!isset($time[1])) {
        switch (strlen($time[0])) {
          case 3: $time[1] = substr($time[0],1,2); $time[0] = substr($time[0],0,1); break;
          case 4: $time[1] = substr($time[0],2,2); $time[0] = substr($time[0],0,2); break;
          default: $time[1] = '00';
        }
      }
      $weekdayTo[$day] = sprintf('%s:%s', $time[0], $time[1]);
    }
    $validator->setValues(array('weekdayFrom'=>$weekdayFrom,'weekdayTo'=>$weekdayTo));
    
    $validator->validateValues();
    
    $weekdayFrom = $validator->getVarValue('weekdayFrom');
    $weekdayTo = $validator->getVarValue('weekdayTo');
    $weekday = array();
    foreach (array('mon','tue','wed','thu','fri','sat','sun') as $day) {
      if ($weekdayFrom[$day]&&!$weekdayTo[$day]) throw new ExceptionUserTextStorage('error.editAvailProfile_weekdayBothNeeded');
      if ($weekdayTo[$day]&&!$weekdayFrom[$day]) throw new ExceptionUserTextStorage('error.editAvailProfile_weekdayBothNeeded');
      
      if ($weekdayFrom[$day]&&$weekdayTo[$day]) {
        $weekday[$day] = array(
                'from'  => $this->_app->regionalSettings->convertHumanToTime($weekdayFrom[$day],'h:m'),
                'to'    => $this->_app->regionalSettings->convertHumanToTime($weekdayTo[$day],'h:m')
                );
      }
    }
    
    $id = $validator->getVarValue('id');
    
    $bAvailProfile = new BAvailabilityProfile($id?$id:null);
    $bAvailProfile->save(array(
        'name'        => $validator->getVarValue('name'),
        'providerId'  => $validator->getVarValue('providerId'),
        'weekday'     => $weekday
        ));

    $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.editAvailProfile_saveOk'), $validator->getVarValue('name')));

    if ($validator->getVarValue('fromEvent')) {
      $eValidator = Validator::get('event', 'EventValidator');
      $eValidator->setValues(array('availProfile' => $bAvailProfile->getId()));
    } elseif ($validator->getVarValue('fromResource')) {
      $eValidator = Validator::get('resource', 'ResourceValidator');
      $eValidator->setValues(array('availProfile' => $bAvailProfile->getId()));
    }

    return 'eBack';
  }
}

?>
