<?php

class ModuleEventSave extends ExecModule {

  private function _saveNewAttribute($validator) {
    $newAttr = $this->_app->request->getParams('newEventAttribute');
    $attr = array();
    if (is_array($newAttr)) {
      foreach ($newAttr as $key=>$aLine) {
        $aParams = explode(';',$aLine);
        
        $params = array();
        foreach ($aParams as $par) {
          list($name,$value) = explode(':~',$par);
          $params[$name] = $value;
        }
        $attr[$key] = $params;
      }
    }
    
    $newRAttr = $this->_app->request->getParams('newReservationAttribute');
    $rAttr = array();
    if (is_array($newRAttr)) {
      foreach ($newRAttr as $key=>$aLine) {
        $aParams = explode(';',$aLine);
        
        $params = array();
        foreach ($aParams as $par) {
          list($name,$value) = explode(':~',$par);
          $params[$name] = $value;
        }
        $rAttr[$key] = $params;
      }
    }
    
    $validator->setValues(array('attribute'=>$attr,'reservationAttribute'=>$rAttr));
  }
  
  private function _getAttribute($validator,$type='COMMODITY') {
    if (!strcmp($type,'COMMODITY')) $valName = 'attribute';
    elseif (!strcmp($type,'RESERVATION')) $valName = 'reservationAttribute';
    $attribute = array();
    foreach ($validator->getVarValue($valName) as $key=>$a) {
      // kdyz je atribut soubor a nezmenil se, poslu spec. hodnotu, aby se na nic s timto atributem nedelalo
      if (!strcmp($a['type'],'FILE')) {
        if (!isset($a['changed'])) $attribute[$a['attributeId']] = '__no_change__';
        else $attribute[$a['attributeId']] = $a['fileId'];
      } else $attribute[$a['attributeId']] = $a['value'];
    }
    
    return $attribute;
  }

  private function _saveRepeatIndividual($validator) {
    $valueAr = explode(',',$validator->getVarValue('repeatIndividual'));
    foreach ($valueAr as $index=>$val) {
      $valueAr[$index] = $this->_app->regionalSettings->convertHumanToDate(trim($val));
    }

    $validator->setValues(array('repeatIndividual'=>implode(',', $valueAr)));
  }

  private function _prepareSingleData($validator) {
    $data = array(
      'providerId'            => $validator->getVarValue('providerId'),
      'start'                 => $this->_app->regionalSettings->convertHumanToDateTime($validator->getVarValue('start')),
      'end'                   => $this->_app->regionalSettings->convertHumanToDateTime($validator->getVarValue('end')),
    );

    if ($validator->getVarValue('repeat')) {
      $data['repeat'] = $validator->getVarValue('repeat');
      $data['repeatCycle'] = $validator->getVarValue('repeatCycle');
      $data['repeatUntil'] = $this->_app->regionalSettings->convertHumanToDate($validator->getVarValue('repeatUntil'));
      $data['repeatReservation'] = $validator->getVarValue('repeatReservation');
      $data['repeatPrice'] = $validator->getVarValue('repeatPrice');
      $data['repeatWeekday'] = $validator->getVarValue('repeatWeekday');
      $data['repeatWeekdayOrder'] = !strcmp(substr($data['repeatCycle'],0,5),'MONTH')?$validator->getVarValue('repeatWeekdayOrder'):0;
      $data['repeatIndividual'] = $validator->getVarValue('repeatIndividual');
      $data['repeatSave'] = $this->_app->request->getParams('repeatSave');
    }
    $data['centerId'] = $validator->getVarValue('centerId');
    $data['name'] = $validator->getVarValue('name');
    $data['externalId'] = $validator->getVarValue('externalId');
    $data['organiserId'] = $validator->getVarValue('organiserId');
    $data['description'] = $validator->getVarValue('description');
    $data['tag'] = $validator->getVarValue('tag');
    $data['price'] = $validator->getVarValue('price');
    $data['accountTypeId'] = $validator->getVarValue('accountTypeId');
    $data['reservationConditionId'] = $validator->getVarValue('reservationConditionId');
    $data['notificationTemplateId'] = $validator->getVarValue('notificationTemplateId');
    $data['documentTemplateId'] = $validator->getVarValue('documentTemplateId');
    $data['reservationMaxAttendees'] = $validator->getVarValue('reservationMaxAttendees');
    $data['active'] = $validator->getVarValue('active');
    $data['resource'] = $validator->getVarValue('resource');
    $data['badge'] = $validator->getVarValue('badge');
    $data['maxAttendees'] = $validator->getVarValue('maxAttendees');
    $data['maxCoAttendees'] = $validator->getVarValue('maxCoAttendees');
    $data['maxSubstitutes'] = $validator->getVarValue('maxSubstitutes');
    $data['feAttendeeVisible'] = $validator->getVarValue('feAttendeeVisible');
    $data['feQuickReservation'] = $validator->getVarValue('feQuickReservation');
    $data['feAllowedPayment'] = $validator->getVarValue('feAllowedPayment');
    $data['portal'] = $validator->getVarValue('portal');
    $data['urlDescription'] = $validator->getVarValue('urlDescription');
    $data['urlPrice'] = $validator->getVarValue('urlPrice');
    $data['urlOpening'] = $validator->getVarValue('urlOpening');
    $data['urlPhoto'] = $validator->getVarValue('urlPhoto');
    $data['attribute'] = $this->_getAttribute($validator);
    $data['reservationAttribute'] = $this->_getAttribute($validator,'RESERVATION');
    $data['attributeValidation'] = true;
    $data['attributeConverted'] = false;
    
    if ($data['active']=='N') {
      if ($this->_app->request->getParams('cancelReservation')) $data['cancelReservation'] = 'Y';
      if ($this->_app->request->getParams('refundReservation')) $data['refundReservation'] = 'Y';
      if ($this->_app->request->getParams('sendNotification')) $data['sendCancelReservationNotification'] = 'Y';
    }

    return $data;
  }

  private function _prepareGroupData($validator) {
    $data = array('id'=>$validator->getVarValue('id'));
    
    foreach ($validator->getVarValue('groupSaveItem') as $name=>$value) {
      if ($value) {
        $validator->getVar($name)->validateValue();
        
        if (!in_array($name,array('attribute','reservationAttribute'))) $data[$name] = $validator->getVarValue($name);
        elseif (!strcmp($name,'attribute')) {
          $data[$name] = $this->_getAttribute($validator);
          $data['attributeValidation'] = true;
          $data['attributeConverted'] = false;
        } elseif (!strcmp($name,'reservationAttribute')) {
          $data[$name] = $this->_getAttribute($validator,'RESERVATION');
          $data['attributeValidation'] = true;
          $data['attributeConverted'] = false;
        }
      }
    }
    $data['tagAddOnly'] = !strcmp($validator->getVarValue('groupSaveItem')['tag'], 'add');
    $data['attributeAddOnly'] = !strcmp($validator->getVarValue('groupSaveItem')['attribute'], 'add');
    $data['reservationAttributeAddOnly'] = !strcmp($validator->getVarValue('groupSaveItem')['reservationAttribute'], 'add');
    
    return $data;
  }
  
  protected function _userRun() {
    $validator = Validator::get('event','EventValidator');
    $validator->initValues();
    
    $this->_saveNewAttribute($validator);
    $this->_saveRepeatIndividual($validator);
    
    parseNextActionFromRequest($nextAction, $nextActionParams);
    switch ($nextAction) {
      case 'newOrganiser':  
        $providerId = $validator->getVarValue('providerId');
        $validator = Validator::get('user','UserValidator',true);
        $validator->loadData(null, $providerId);
        $validator->setValues(array('fromEvent'=>1));
        return 'vUserEdit';
      case 'newAccountType':
        $validator = Validator::get('providerAccountType','ProviderAccountTypeValidator',true);
        $validator->setValues(array('fromEvent'=>1));
        return 'vProviderAccountTypeEdit';
      case 'newReservationCondition':
        $validator = Validator::get('reservationCondition','ReservationConditionValidator', true);
        $validator->setValues(array('fromEvent'=>1));
        return 'vReservationConditionEdit';
      case 'newNotificationTemplate':
        $validator = Validator::get('notificationTemplate','NotificationTemplateValidator',true);
        $validator->setValues(array('fromEvent'=>1));
        return 'vNotificationTemplateEdit';
      default: break;
    }
    
    $validator->validateValues(array(),$validator->getVarValue('groupSave')?false:null);
    
    if ($validator->getVarValue('repeat')) {
      if (!$validator->getVarValue('repeatCycle')) throw new ExceptionUserTextStorage('error.editEvent_repeatCycle_missing');
      if (!$validator->getVarValue('repeatUntil')) throw new ExceptionUserTextStorage('error.editEvent_repeatUntil_missing');
      if (!$validator->getVarValue('repeatReservation')) throw new ExceptionUserTextStorage('error.editEvent_repeatReservation_missing');
      
      /*if (in_array($validator->getVarValue('repeatReservation'),array('SINGLE','BOTH'))) {
        if (!$validator->getVarValue('price')) throw new ExceptionUserTextStorage('error.editEvent_price_missing');
      }
      if (in_array($validator->getVarValue('repeatReservation'),array('PACK','BOTH'))) {
        if (!$validator->getVarValue('repeatPrice')) throw new ExceptionUserTextStorage('error.editEvent_repeatPrice_missing');
      }*/
    }
    
    // potvrzeni zruseni opakovani
    if (!strcmp($this->_app->request->getParams('repeatSave'),'this')&&!$validator->getVarValue('repeatCancelConfirmed')) {
      $this->_app->dialog->set(array(
            'width'     => 400,
            'template'  => '
                <div class="message">{__label.editEvent_confirmRepeatCancel}</div>
                <input type="hidden" name="repeatCancelConfirmed" value="1"/>
                <div class="button">
                  <input type="button" class="ui-button inputSubmit" name="save" value="{__button.editEvent_saveThisConfirmed}" onclick="document.getElementById(\'fb_eEventSave\').click();"/>
                </div>',
          ));
      
      $this->_app->response->addParams(array('backwards'=>1));
      return 'eBack';
    }
    
    // jestli se ma zdroj zneaktivnit a existuji na nem rezervace je potreba nektere veci nechat potvrdit
    if (($eventId=$validator->getVarValue('id'))&&($validator->getVarValue('active')!='Y')&&!$this->_app->request->getParams('confirmed')) {
      $s = new SReservation;
      $s->addStatement(new SqlStatementBi($s->columns['event'], $eventId, '%s=%s'));
      $s->addStatement(new SqlStatementMono($s->columns['start'], '%s>NOW()'));
      $s->addStatement(new SqlStatementMono($s->columns['failed'], '%s IS NULL'));
      $s->addStatement(new SqlStatementMono($s->columns['cancelled'], '%s IS NULL'));
      $s->setColumnsMask(array('reservation_id'));
      $res = $this->_app->db->doQuery($s->toString());
      if ($this->_app->db->getRowsNumber($res)) {
        $this->_app->dialog->set(array(
              'width'     => 400,
              'template'  => '
                  <div class="message">{__label.editEvent_confirmDisableWithReservation}</div>
                  <br/><input type="checkbox"  class="inputCheckbox" name="refundReservation" value="1"/> {__label.editEvent_refundReservation}
                  <input type="hidden" name="confirmed" value="1"/>
                  <div class="button">
                    <input type="button" class="ui-button inputSubmit" name="save" value="{__button.editEvent_disable}" onclick="document.getElementById(\'fb_eEventSave\').click();"/>
                    <input type="button" class="ui-button inputSubmit" name="save" value="{__button.editEvent_disableWithCancel}" onclick="document.getElementById(\'fb_eEventSaveWithCancel\').click();"/>
                  </div>',
            ));
        
        $this->_app->response->addParams(array('backwards'=>1));
        return 'eBack';
      }
    }

    if ($validator->getVarValue('groupSave')) $data = $this->_prepareGroupData($validator);
    else $data = $this->_prepareSingleData($validator);
    #adump($data);die;
    
    $this->_app->db->beginTransaction();
    $newId = array();
    $eventIdArr = explode(',',$eventId);
    foreach ($eventIdArr as $i=>$id) {
      // kvuli mazani souboru u atributu, kdyz se aktualizuje hromadne
      $data['keepFile'] = (isset($data['repeat'])&&$data['repeat'])||(($i+1)<count($eventIdArr));
      
      $bEvent = new BEvent($id?$id:null);
      $bEvent->save($data);
      
      $newId[] = $bEvent->getId();
    }
    $this->_app->db->commitTransaction();
    
    // hlaska o ulozeni dat
    $s = new SEvent;
    $s->addStatement(new SqlStatementMono($s->columns['event_id'], sprintf('%%s IN (%s)', implode(',',$newId))));
    $s->addOrder(new SqlStatementAsc($s->columns['name']));
    $s->setColumnsMask(array('name'));
    $res = $this->_app->db->doQuery($s->toString());
    $name = '';
    while ($row = $this->_app->db->fetchAssoc($res)) {
      if ($name) $name .= ', ';
      $name .= $row['name'];
    }
    $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.editEvent_saveOk'), $name));

    return 'eBack';
  }
}

?>
