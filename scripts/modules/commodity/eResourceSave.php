<?php

class ModuleResourceSave extends ExecModule {
  
  private function _saveNewAttribute($validator) {
    $newAttr = $this->_app->request->getParams('newResourceAttribute');
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
  
  private function _prepareSingleData($validator) {
    $data = array();
    
    if (!$validator->getVarValue('reservation')) {
      $data = array(
          'providerId'            => $validator->getVarValue('providerId'),
          );
    }
    
    $data['centerId'] = $validator->getVarValue('centerId');
    $data['name'] = $validator->getVarValue('name');
    $data['externalId'] = $validator->getVarValue('externalId');
    $data['organiserId'] = $validator->getVarValue('organiserId');
    $data['description'] = $validator->getVarValue('description');
    $data['tag'] = $validator->getVarValue('tag');
    $data['availProfile'] = $validator->getVarValue('availProfile');
    $data['availExProfile'] = $validator->getVarValue('availExProfile');
    $data['unitProfile'] = $validator->getVarValue('unitProfile');
    $data['price'] = $validator->getVarValue('price');
    $data['priceList'] = $validator->getVarValue('priceList');
    $data['accountTypeId'] = $validator->getVarValue('accountTypeId');
    $data['reservationConditionId'] = $validator->getVarValue('reservationConditionId');
    $data['notificationTemplateId'] = $validator->getVarValue('notificationTemplateId');
    $data['documentTemplateId'] = $validator->getVarValue('documentTemplateId');
    $data['active'] = $validator->getVarValue('active');
    $data['portal'] = $validator->getVarValue('portal');
    $data['feAllowedPayment'] = $validator->getVarValue('feAllowedPayment');
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
    $validator = Validator::get('resource','ResourceValidator');
    $validator->initValues();
    
    $this->_saveNewAttribute($validator);
    
    parseNextActionFromRequest($nextAction, $nextActionParams);

    switch ($nextAction) {
      case 'reload':
        $this->_app->response->addParams(array('backwards'=>1));
        return 'eBack';
      case 'newAccountType':
        $validator = Validator::get('providerAccountType','ProviderAccountTypeValidator',true);
        $validator->setValues(array('fromResource'=>1));
        return 'vProviderAccountTypeEdit';
      case 'newReservationCondition':
        $validator = Validator::get('reservationCondition','ReservationConditionValidator', true);
        $validator->setValues(array('fromResource'=>1));
        return 'vReservationConditionEdit';
      case 'newNotificationTemplate':
        $validator = Validator::get('notificationTemplate','NotificationTemplateValidator',true);
        $validator->setValues(array('fromResource'=>1));
        return 'vNotificationTemplateEdit';
      case 'newAvailProfile':
        $validator = Validator::get('availProfile','AvailProfileValidator',true);
        $validator->setValues(array('fromResource'=>1));
        return 'vAvailProfileEdit';
      case 'newAvailExProfile':
        $validator = Validator::get('availExProfile','AvailExProfileValidator',true);
        $validator->setValues(array('fromResource'=>1));
        return 'vAvailExProfileEdit';
      case 'newPriceList':
        $validator = Validator::get('priceList','PriceListValidator',true);
        $validator->setValues(array('fromResource'=>1));
        return 'vPriceListEdit';
      case 'newUnitProfile':
        $validator = Validator::get('unitProfile','UnitProfileValidator',true);
        $validator->setValues(array('fromResource'=>1));
        return 'vUnitProfileEdit';
      default: break;
    }
    
    $validator->validateValues(array(),$validator->getVarValue('groupSave')?false:null);
    
    //adump($validator->getValues());die;
  
    $resourceId = $validator->getVarValue('id');    
    // jestli se ma zdroj zneaktivnit a existuji na nem rezervace je potreba nektere veci nechat potvrdit
    if ($resourceId&&($validator->getVarValue('active')!='Y')&&!$this->_app->request->getParams('confirmed')) {
      $s = new SReservation;
      $s->addStatement(new SqlStatementMono($s->columns['resource'], sprintf('%%s IN (%s)', $this->_app->db->escapeString($resourceId))));
      $s->addStatement(new SqlStatementMono($s->columns['start'], '%s>NOW()'));
      $s->addStatement(new SqlStatementMono($s->columns['failed'], '%s IS NULL'));
      $s->addStatement(new SqlStatementMono($s->columns['cancelled'], '%s IS NULL'));
      $s->setColumnsMask(array('reservation_id'));
      $res = $this->_app->db->doQuery($s->toString());
      if ($this->_app->db->getRowsNumber($res)) {
        $this->_app->dialog->set(array(
              'width'     => 400,
              'template'  => '
                  <div class="message">{__label.editResource_confirmDisableWithReservation}</div>
                  <br/><input type="checkbox"  class="inputCheckbox" name="refundReservation" value="1"/> {__label.editResource_refundReservation}
                  <input type="hidden" name="confirmed" value="1"/>
                  <div class="button">
                    <input type="button" class="ui-button inputSubmit" name="save" value="{__button.editResource_disable}" onclick="document.getElementById(\'fb_eResourceSave\').click();"/>
                    <input type="button" class="ui-button inputSubmit" name="save" value="{__button.editResource_disableWithCancel}" onclick="document.getElementById(\'fb_eResourceSaveWithCancel\').click();"/>
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
    $resourceIdArr = explode(',',$resourceId);
    if (count($resourceIdArr)>1) $data['otherId'] = $resourceIdArr;
    
    foreach ($resourceIdArr as $i=>$id) {
      // kvuli mazani souboru u atributu, kdyz se aktualizuje hromadne
      $data['keepFile'] = ($i+1)<count($resourceIdArr);
      
      $bResource = new BResource($id?$id:null);
      $bResource->save($data);
      
      $newId[] = $bResource->getId();
    }
    $this->_app->db->commitTransaction();
    
    // hlaska o ulozeni dat
    $s = new SResource;
    $s->addStatement(new SqlStatementMono($s->columns['resource_id'], sprintf('%%s IN (%s)', implode(',',$newId))));
    $s->addOrder(new SqlStatementAsc($s->columns['name']));
    $s->setColumnsMask(array('name'));
    $res = $this->_app->db->doQuery($s->toString());
    $name = '';
    while ($row = $this->_app->db->fetchAssoc($res)) {
      if ($name) $name .= ', ';
      $name .= $row['name'];
    }
    $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.editResource_saveOk'), $name));

    return 'eBack';
  }
}

?>
