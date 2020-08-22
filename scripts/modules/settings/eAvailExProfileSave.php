<?php

class ModuleAvailExProfileSave extends ExecModule {
  
  private function _saveTerm($validator) {
    $newTerm = $this->_app->request->getParams('newTerm');
    
    $term = array();
    if (is_array($newTerm)) {
      foreach ($newTerm as $index=>$oneTerm) {
        $cParams = explode(';', $oneTerm);
        
        $params = array();
        foreach ($cParams as $par) {
          list($key,$value) = explode('~',$par);
          $params[$key] = $value;
        }
        
        $term[$index] = $params;
      }
    }
    
    $validator->setValues(array('term'=>$term));
  }
  
  private function _getItem($term) {
    $item = array();
    foreach ($term as $t) {
      if ($t['type'] == 'Date') {
        $from = $this->_app->regionalSettings->convertHumanToDate($t['date']).' 00:00:00';
        $to = $this->_app->regionalSettings->convertHumanToDate($t['date']).' 24:00:00';
      } elseif ($t['type'] == 'DateRange') {
        $from = $this->_app->regionalSettings->convertHumanToDate($t['dateFrom']).' 00:00:00';
        $to = $this->_app->regionalSettings->convertHumanToDate($t['dateTo']).' 24:00:00';
      } elseif ($t['type'] == 'TimeRange') {
        $from = $this->_app->regionalSettings->convertHumanToDateTime($t['timeFrom']);
        $to = $this->_app->regionalSettings->convertHumanToDateTime($t['timeTo']);
      }
      
      $i = array('name'=>ifsetor($t['name']),'from'=>$from,'to'=>$to);
      if (isset($t['termId'])&&$t['termId']) $i['itemId'] = $t['termId'];
      $i['repeated'] = ifsetor($t['repeated'],'N');
      $i['repeatCycle'] = ifsetor($t['repeatCycle']);
      $i['repeatWeekday_mon'] = ifsetor($t['repeatWeekday_mon']);
      $i['repeatWeekday_tue'] = ifsetor($t['repeatWeekday_tue']);
      $i['repeatWeekday_wed'] = ifsetor($t['repeatWeekday_wed']);
      $i['repeatWeekday_thu'] = ifsetor($t['repeatWeekday_thu']);
      $i['repeatWeekday_fri'] = ifsetor($t['repeatWeekday_fri']);
      $i['repeatWeekday_sat'] = ifsetor($t['repeatWeekday_sat']);
      $i['repeatWeekday_sun'] = ifsetor($t['repeatWeekday_sun']);
      if (isset($t['repeatUntil'])) {
        $i['repeatUntil'] = $this->_app->regionalSettings->convertHumanToDate($t['repeatUntil']);
      } 
      
      $item[] = $i;
    }
    
    return $item;
  }

  protected function _userRun() {
    if (!$this->_app->auth->haveRight('settings_admin', $this->_app->auth->getActualProvider())) throw new ExceptionUserTextStorage('error.accessDenied');

    $validator = Validator::get('availExProfile','AvailExProfileValidator');
    $validator->initValues();
    
    $this->_saveTerm($validator);
    
    $validator->validateValues();

    $id = $validator->getVarValue('id');
    $item = $this->_getItem($validator->getVarValue('term'));

    $bAvailExProfile = new BAvailabilityExceptionProfile($id?$id:null);
    $bAvailExProfile->save(array(
        'name'        => $validator->getVarValue('name'),
        'providerId'  => $validator->getVarValue('providerId'),
        'item'        => $item,
        ));
    #adump($item);die;

    $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.editAvailExProfile_saveOk'), $validator->getVarValue('name')));

    if ($validator->getVarValue('fromEvent')) {
      $eValidator = Validator::get('event', 'EventValidator');
      $eValidator->setValues(array('availExProfile' => $bAvailExProfile->getId()));
    } elseif ($validator->getVarValue('fromResource')) {
      $eValidator = Validator::get('resource', 'ResourceValidator');
      $eValidator->setValues(array('availExProfile' => $bAvailExProfile->getId()));
    }

    return 'eBack';
  }
}

?>
