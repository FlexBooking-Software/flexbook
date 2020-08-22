<?php

class ModuleReservationConditionSave extends ExecModule {
  
  private function _saveCondition($validator) {
    $newCondition = $this->_app->request->getParams('newCondition');
    
    $condition = array();
    if (is_array($newCondition)) {
      foreach ($newCondition as $index=>$oneCondition) {
        $cParams = explode(';', $oneCondition);
        
        $params = array();
        foreach ($cParams as $par) {
          list($key,$value) = explode('~',$par);
          if (!strcmp($value,'null')) $value = null;
          $params[$key] = $value;
        }
        
        $condition[$index] = $params;
      }
    }
    
    $validator->setValues(array('condition'=>$condition));
  }
  
  private function _getItem($condition) {
    $item = array();

    foreach ($condition as $t) {
      $i = array('name'=>ifsetor($t['name']),
                 'timeFrom'=>$this->_app->regionalSettings->convertHumanToDateTime($t['from']),
                 'timeTo'=>$this->_app->regionalSettings->convertHumanToDateTime($t['to']));
      if (isset($t['conditionId'])&&$t['conditionId']) $i['itemId'] = $t['conditionId'];
      $i['limitCenter'] = ifsetor($t['center']);
      $i['limitCenterMessage'] = ifsetor($t['centerMessage']);
      $i['limitQuantity'] = ifsetor($t['quantity']);
      $i['limitQuantityPeriod'] = ifsetor($t['period']);
      $i['limitQuantityType'] = ifsetor($t['type']);
      $i['limitQuantityScope'] = ifsetor($t['scope']);
      $i['limitQuantityMessage'] = ifsetor($t['quantityMessage']);
      $i['limitOtherScope'] = ifsetor($t['otherScope']);
      $i['limitTotalQuantity'] = ifsetor($t['totalQuantity']);
      $i['limitTotalQuantityPeriod'] = ifsetor($t['totalQuantityPeriod']);
      $i['limitTotalQuantityType'] = ifsetor($t['totalQuantityType']);
      $i['limitTotalQuantityTag'] = ifsetor($t['totalQuantityTag']);
      $i['limitTotalQuantityMessage'] = ifsetor($t['totalQuantityMessage']);
      $i['limitOverlapQuantity'] = ifsetor($t['overlapQuantity']);
      $i['limitOverlapQuantityScope'] = ifsetor($t['overlapQuantityScope']);
      $i['limitOverlapQuantityTag'] = ifsetor($t['overlapQuantityTag']);
      $i['limitOverlapQuantityMessage'] = ifsetor($t['overlapQuantityMessage']);
      $i['requiredEvent'] = ifsetor($t['event']);
      $i['requiredEventExists'] = ifsetor($t['eventExists'],'Y');
      $i['requiredEventPayed'] = ifsetor($t['eventPayed'],'N');
      $i['requiredEventAll'] = ifsetor($t['eventAll'],'Y');
      $i['requiredEventMessage'] = ifsetor($t['eventMessage']);
      $i['requiredResource'] = ifsetor($t['resource']);
      $i['requiredResourceExists'] = ifsetor($t['resourceExists'],'Y');
      $i['requiredResourcePayed'] = ifsetor($t['resourcePayed'],'N');
      $i['requiredResourceAll'] = ifsetor($t['resourceAll'],'Y');
      $i['requiredResourceMessage'] = ifsetor($t['resourceMessage']);
      
      $firstTimeBefore = ifsetor($t['firstTimeBeforeCount']);
      if (!strcmp($t['firstTimeBeforeUnit'],'hour')) $firstTimeBefore = 60*$firstTimeBefore;
      elseif (!strcmp($t['firstTimeBeforeUnit'],'day')) $firstTimeBefore = 1440*$firstTimeBefore;
      $lastTimeBefore = ifsetor($t['lastTimeBeforeCount']);
      if (!strcmp($t['lastTimeBeforeUnit'],'hour')) $lastTimeBefore = 60*$lastTimeBefore;
      elseif (!strcmp($t['lastTimeBeforeUnit'],'day')) $lastTimeBefore = 1440*$lastTimeBefore;
      $advancePayment = ifsetor($t['advancePaymentCount']);
      if (!strcmp($t['advancePaymentUnit'],'hour')) $advancePayment = 60*$advancePayment;
      elseif (!strcmp($t['advancePaymentUnit'],'day')) $advancePayment = 1440*$advancePayment;
      $cancelBefore = ifsetor($t['cancelBeforeCount']);
      if (!strcmp($t['cancelBeforeUnit'],'hour')) $cancelBefore = 60*$cancelBefore;
      elseif (!strcmp($t['cancelBeforeUnit'],'day')) $cancelBefore = 1440*$cancelBefore;
      $cancelPayedBefore = ifsetor($t['cancelPayedBeforeCount']);
      if (!strcmp($t['cancelPayedBeforeUnit'],'hour')) $cancelPayedBefore = 60*$cancelPayedBefore;
      elseif (!strcmp($t['cancelPayedBeforeUnit'],'day')) $cancelPayedBefore = 1440*$cancelPayedBefore;
      $anonymousBefore = ifsetor($t['anonymousBeforeCount']);
      if (!strcmp($t['anonymousBeforeUnit'],'hour')) $anonymousBefore = 60*$anonymousBefore;
      elseif (!strcmp($t['anonymousBeforeUnit'],'day')) $anonymousBefore = 1440*$anonymousBefore;
    
      $i['limitAfterStartEvent'] = ifsetor($t['afterStartEvent']);
      $i['limitAfterStartEventMessage'] = ifsetor($t['afterStartEventMessage']);
      $i['limitFirstTimeBeforeMessage'] = ifsetor($t['firstTimeBeforeMessage']);
      $i['limitFirstTimeBeforeStart'] = $firstTimeBefore;
      $i['limitFirstTimeBeforeMessage'] = ifsetor($t['firstTimeBeforeMessage']);
      $i['limitLastTimeBeforeStart'] = $lastTimeBefore;
      $i['limitLastTimeBeforeMessage'] = ifsetor($t['lastTimeBeforeMessage']);
      $i['advancePayment'] = $advancePayment;
      $i['advancePaymentMessage'] = ifsetor($t['advancePaymentMessage']);
      $i['cancelBefore'] = $cancelBefore;
      $i['cancelBeforeMessage'] = ifsetor($t['cancelBeforeMessage']);
      $i['cancelPayedBefore'] = $cancelPayedBefore;
      $i['cancelPayedBeforeMessage'] = ifsetor($t['cancelPayedBeforeMessage']);
      $i['limitAnonymousBeforeStart'] = $anonymousBefore;
      $i['limitAnonymousBeforeMessage'] = ifsetor($t['anonymousBeforeMessage']);
      
      $item[] = $i;
    }
    
    return $item;
  }

  protected function _userRun() {
    if (!$this->_app->auth->haveRight('settings_admin', $this->_app->auth->getActualProvider())) throw new ExceptionUserTextStorage('error.accessDenied');

    $validator = Validator::get('reservationCondition','ReservationConditionValidator');
    $validator->initValues();
    
    $this->_saveCondition($validator);
    
    $validator->validateValues();
    #adump($validator->getValues());

    $id = $validator->getVarValue('id');
    $item = $this->_getItem($validator->getVarValue('condition'));
    #adump($validator->getVarValue('condition'));
    #adump($item);die;

    $bReservationCondition = new BReservationCondition($id?$id:null);
    $bReservationCondition->save(array(
        'name'        => $validator->getVarValue('name'),
        'providerId'  => $validator->getVarValue('providerId'),
        'evaluation'  => $validator->getVarValue('evaluation'),
        'description' => $validator->getVarValue('description'),
        'item'        => $item,
        ));

    $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.editReservationCondition_saveOk'), $validator->getVarValue('name')));

    if ($validator->getVarValue('fromEvent')) {
      $eValidator = Validator::get('event', 'EventValidator');
      $eValidator->setValues(array('reservationConditionId' => $bReservationCondition->getId()));
    } elseif ($validator->getVarValue('fromResource')) {
      $eValidator = Validator::get('resource', 'ResourceValidator');
      $eValidator->setValues(array('reservationConditionId' => $bReservationCondition->getId()));
    }

    return 'eBack';
  }
}

?>
