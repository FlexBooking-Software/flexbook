<?php

class ModuleEventGroupSave extends ExecModule {

  private function _saveNewException($validator) {
    $newException = $this->_app->request->getParams('newException');
    
    $exception = array();
    if (is_array($newException)) {
      foreach ($newException as $key=>$cLine) {
        $cParams = explode(';',$cLine);
        
        $params = array();
        foreach ($cParams as $par) {
          list($name,$value) = explode('#',$par);
          if (!$this->_app->regionalSettings->checkHumanDateTime($value)) throw new ExceptionUserTextStorage('error.saveEventGroup_invalidExceptionItem');
          $params[$name] = $value;
        }
        if ($params['from']>=$params['to']) throw new ExceptionUserTextStorage('error.saveEventGroup_invalidException');
        $exception[$key] = $params;
      }
    }
    
    $validator->setValues(array('groupException'=>$exception));
  }

  protected function _userRun() {
    $validator = Validator::get('eventGroup','EventGroupValidator');
    $validator->initValues();
    
    $this->_saveNewException($validator);
    
    //adump($validator->getValues());die;
    
    parseNextActionFromRequest($nextAction, $nextActionParams);
    switch ($nextAction) {
      case 'newOrganiser':  
        $providerId = $validator->getVarValue('providerId');
        $validator = Validator::get('user','UserValidator',true);
        $validator->loadData(null, $providerId);
        $validator->setValues(array('fromEventGroup'=>1));
        return 'vUserEdit';
      default: break;
    }
    
    $validator->validateValues();
        
    $generatedCount = 0;
    $pause = $validator->getVarValue('groupPause');
    $length = $validator->getVarValue('groupLength');
    $count = $validator->getVarValue('groupCount');
    $start = $this->_app->regionalSettings->convertHumanToDateTime($validator->getVarValue('groupStart'));
    // konec akce spocitam podle zacatku a delky kazde akce
    $end = $this->_app->regionalSettings->increaseDateTime($start, 0, 0, 0, 0, $length);
    $plannedEnd = $this->_app->regionalSettings->convertHumanToDateTime($validator->getVarValue('groupEnd'));
    $exception = $validator->getVarValue('groupException');
    foreach ($exception as $key=>$val) {
      $val['from'] = $this->_app->regionalSettings->convertHumanToDateTime($val['from']);
      $val['to'] = $this->_app->regionalSettings->convertHumanToDateTime($val['to']);
      $exception[$key] = $val;
    }
    $nameTemplate = $validator->getVarValue('name');
    $externalTemplate = $validator->getVarValue('externalId');
    
    $this->_app->db->beginTransaction();
    
    // akce generuju dokud nejsem na konci obdobi nebo uz mam pozadovany pocet akci
    while (($end<=$plannedEnd)&&(!$count||$generatedCount<$count)) {
      // kontrola, jestli generovana akce nepada do terminu, kde se nema generovat
      $inException = false;
      foreach ($exception as $key=>$val) {
        if ((($val['from']<=$start)&&($start<$val['to']))||
            (($val['from']<$end)&&($end<=$val['to']))) {
          $inException = $key;
          break;
        }
      }
      if ($inException) {
        $start = $exception[$inException]['to'];
        $end = $this->_app->regionalSettings->increaseDateTime($start, 0, 0, 0, 0, $length);
        continue;
      }
      
      $generatedCount++;
      // v nazvu akce mohou byt specialni tagy
      //  {%date-from%}, {%date-to%}, {%time-from%}, {%time-to%}, {%count%}
      list($dateFrom,$timeFrom) = explode(' ', $start);
      list($dateTo,$timeTo) = explode(' ', $end);
      $dateFrom = $this->_app->regionalSettings->convertDateToHuman($dateFrom);
      $timeFrom = $this->_app->regionalSettings->convertTimeToHuman($timeFrom, 'h:m');
      $dateTo = $this->_app->regionalSettings->convertDateToHuman($dateTo);
      $timeTo = $this->_app->regionalSettings->convertTimeToHuman($timeTo, 'h:m');
      $name = str_replace(array('{%date-from%}','{%time-from%}','{%date-to%}','{%time-to%}','{%count%}'),
                          array($dateFrom, $timeFrom, $dateTo, $timeTo ,sprintf('%03d', $generatedCount)),
                          $nameTemplate);
      $external = str_replace(array('{%date-from%}','{%time-from%}','{%date-to%}','{%time-to%}','{%count%}'),
                              array($dateFrom, $timeFrom, $dateTo, $timeTo ,sprintf('%03d', $generatedCount)),
                              $externalTemplate);
      
      $data = array(
        'providerId'              => $validator->getVarValue('providerId'),
        'centerId'                => $validator->getVarValue('centerId'),
        'start'                   => $start,
        'end'                     => $end,
        'resource'                => $validator->getVarValue('resource'),
        'name'                    => $name,
        'externalId'              => $external,
        'organiserId'             => $validator->getVarValue('organiserId'),
        'description'             => $validator->getVarValue('description'),
        'tag'                     => $validator->getVarValue('tag'),
        'price'                   => $validator->getVarValue('price'),
        'active'                  => $validator->getVarValue('active'),
        'maxAttendees'            => $validator->getVarValue('maxAttendees'),
        'maxCoAttendees'          => $validator->getVarValue('maxCoAttendees'),
        'maxSubstitutes'          => $validator->getVarValue('maxSubstitutes'),
        'reservationConditionId'  => $validator->getVarValue('reservationConditionId'),
        'notificationTemplateId'  => $validator->getVarValue('notificationTemplateId'),
        'documentTemplateId'      => $validator->getVarValue('documentTemplateId'),
        'reservationMaxAttendees' => $validator->getVarValue('reservationMaxAttendees'),
        'portal'                  => $validator->getVarValue('portal'),
        'badge'                   => $validator->getVarValue('badge'),
        'urlDescription'          => $validator->getVarValue('urlDescription'),
        'urlPrice'                => $validator->getVarValue('urlPrice'),
        'urlOpening'              => $validator->getVarValue('urlOpening'),
        'urlPhoto'                => $validator->getVarValue('urlPhoto'),
        );
      
      $bEvent = new BEvent;
      $bEvent->save($data);
      
      // dalsi potencionalni zacatek akce bude podle konce predchozi akce
      $start = $end;
      // jeste muze byt definovana default mezera mezi akcemi
      if ($pause) $start = $this->_app->regionalSettings->increaseDateTime($start, 0, 0, 0, 0, $pause);
      $end = $this->_app->regionalSettings->increaseDateTime($start, 0, 0, 0, 0, $length);
    }
    
    $this->_app->db->commitTransaction();
    
    $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.editEventGroup_saveOk'), $generatedCount));

    $this->_app->response->addParams(array('backwards'=>1));
    return 'eBack';
  }
}

?>
