<?php

class BReservationCondition extends BusinessObject {

  private function _checkAccess($params=array()) {
    return true;
    $this->_load();
    
    $provider = ifsetor($params['providerId'],$this->_data['providerId']);
    
    $ret = $this->_app->auth->haveRight('commodity_admin', $provider);
    
    return $ret;
  }
  
  private function _checkBeforeSave($params) {
    // kdyz se zaklada novy profil jsou tyto atributy povinne
    if (!$this->_id) {
      if (!isset($params['name'])) throw new ExceptionUserTextStorage('error.saveReservationCondition_emptyName');
      if (!isset($params['providerId'])) throw new ExceptionUserTextStorage('error.saveReservationCondition_emptyProvider');
      if (!isset($params['evaluation'])) throw new ExceptionUserTextStorage('error.saveReservationCondition_emptyEvaluation');
    }
    
    // tyto nesmi byt prazdny nikdy
    if (isset($params['name'])&&!$params['name']) throw new ExceptionUserTextStorage('error.saveReservationCondition_emptyName');
    if (isset($params['providerId'])&&!$params['providerId']) throw new ExceptionUserTextStorage('error.saveReservationCondition_emptyProvider');
    if (isset($params['evaluation'])&&!$params['evaluation']) throw new ExceptionUserTextStorage('error.saveReservationCondition_emptyEvaluation');
  }
  
  private function _checkBeforeDelete() {
    $s = new SResource;
    $s->addStatement(new SqlStatementBi($s->columns['reservationcondition'], $this->_id, '%s=%s'));
    $s->setColumnsMask(array('resource_id','name'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($row = $this->_app->db->fetchAssoc($res)) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.deleteReservationCondition_resourceExists'), $row['name']));
    
    $s = new SEvent;
    $s->addStatement(new SqlStatementBi($s->columns['reservationcondition'], $this->_id, '%s=%s'));
    $s->setColumnsMask(array('event_id','name'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($row = $this->_app->db->fetchAssoc($res)) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.deleteReservationCondition_eventExists'), $row['name']));
  }

  protected function _load() {
    if ($this->_id&&!$this->_loaded) {
      $oReservationCondition = new OReservationCondition($this->_id);
      $data = $oReservationCondition->getData();
      $returnData['id'] = $data['reservationcondition_id'];
      $returnData['name'] = $data['name'];
      $returnData['evaluation'] = $data['evaluation'];
      $returnData['description'] = $data['description'];
      
      $returnData['providerId'] = $data['provider'];
      if ($data['provider']) {
        $s = new SProvider;
        $s->addStatement(new SqlStatementBi($s->columns['provider_id'], $data['provider'], '%s=%s'));
        $s->setColumnsMask(array('name','email','phone_1','phone_2','www'));
        $res = $this->_app->db->doQuery($s->toString());
        $oPData = $this->_app->db->fetchAssoc($res);
        
        $returnData['providerName'] = $oPData['name'];
        $returnData['providerEmail'] = $oPData['email'];
        $returnData['providerPhone1'] = $oPData['phone_1'];
        $returnData['providerPhone2'] = $oPData['phone_2'];
        $returnData['providerWww'] = $oPData['www'];
      }
      
      $returnData['item'] = array();
      $s = new SReservationConditionItem;
      $s->addStatement(new SqlStatementBi($s->columns['reservationcondition'], $this->_id, '%s=%s'));
      $s->addOrder(new SqlStatementAsc($s->columns['time_from']));
      $s->setColumnsMask(array('reservationconditionitem_id','name','time_from','time_to',
        'limit_center','limit_center_message',
        'limit_quantity','limit_quantity_period','limit_quantity_type','limit_quantity_scope','limit_quantity_message',
        'limit_other_scope',
        'limit_total_quantity','limit_total_quantity_period','limit_total_quantity_type','limit_total_quantity_tag','limit_total_quantity_message',
        'limit_first_time_before_start','limit_first_time_before_message','limit_last_time_before_start','limit_last_time_before_message',
        'limit_overlap_quantity','limit_overlap_quantity_scope','limit_overlap_quantity_tag','limit_overlap_quantity_message',
        'limit_after_start_event','limit_after_start_event_message',
        'cancel_before_start','cancel_before_start_message','cancel_payed_before_start','cancel_payed_before_start_message',
        'advance_payment','advance_payment_message','limit_anonymous_before_start','limit_anonymous_before_message',
        'required_event','required_event_exists','required_event_payed','required_event_all','required_event_message',
        'required_resource','required_resource_exists','required_resource_payed','required_resource_all','required_resource_message'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {  
        $item = array(
          'itemId'=>$row['reservationconditionitem_id'],'name'=>$row['name'],
          'timeFrom'=>$row['time_from'],'timeTo'=>$row['time_to'],
          'limitCenter'=>$row['limit_center'],'limitCenterMessage'=>$row['limit_center_message'],
          'limitQuantity'=>$row['limit_quantity'],'limitQuantityPeriod'=>$row['limit_quantity_period'],
          'limitQuantityType'=>$row['limit_quantity_type'],'limitQuantityScope'=>$row['limit_quantity_scope'],
          'limitQuantityMessage'=>$row['limit_quantity_message'],
          'limitOtherScope'=>$row['limit_other_scope'],
          'limitTotalQuantity'=>$row['limit_total_quantity'],'limitTotalQuantityPeriod'=>$row['limit_total_quantity_period'],'limitTotalQuantityType'=>$row['limit_total_quantity_type'],
          'limitTotalQuantityTag'=>$row['limit_total_quantity_tag'],'limitTotalQuantityMessage'=>$row['limit_total_quantity_message'],
          'limitFirstTimeBeforeStart'=>$row['limit_first_time_before_start'],
          'limitFirstTimeBeforeMessage'=>$row['limit_first_time_before_message'],
          'limitLastTimeBeforeStart'=>$row['limit_last_time_before_start'],
          'limitLastTimeBeforeMessage'=>$row['limit_last_time_before_message'],
          'limitOverlapQuantity'=>$row['limit_overlap_quantity'],'limitOverlapQuantityScope'=>$row['limit_overlap_quantity_scope'],'limitOverlapQuantityTag'=>$row['limit_overlap_quantity_tag'],
          'limitOverlapQuantityMessage'=>$row['limit_overlap_quantity_message'],
          'limitAfterStartEvent'=>$row['limit_after_start_event'],
          'limitAfterStartEventMessage'=>$row['limit_after_start_event_message'],
          'cancelBefore'=>$row['cancel_before_start'],
          'cancelBeforeMessage'=>$row['cancel_before_start_message'],
          'cancelPayedBefore'=>$row['cancel_payed_before_start'],
          'cancelPayedBeforeMessage'=>$row['cancel_payed_before_start_message'],
          'advancePayment'=>$row['advance_payment'],
          'advancePaymentMessage'=>$row['advance_payment_message'],
          'limitAnonymousBeforeStart'=>$row['limit_anonymous_before_start'],
          'limitAnonymousBeforeMessage'=>$row['limit_anonymous_before_message'],
          'requiredEvent'=>$row['required_event'],'requiredEventExists'=>$row['required_event_exists'],'requiredEventPayed'=>$row['required_event_payed'],'requiredEventAll'=>$row['required_event_all'],
          'requiredEventMessage'=>$row['required_event_message'],
          'requiredResource'=>$row['required_resource'],'requiredResourceExists'=>$row['required_resource_exists'],'requiredResourcePayed'=>$row['required_resource_payed'],'requiredResourceAll'=>$row['required_resource_all'],
          'requiredResourceMessage'=>$row['required_resource_message'],
        );
        
        $returnData['item'][] = $item;
      }

      $this->_data = $returnData;
      
      $this->_loaded = true;
    }
  }

  public function getData() {
    if (!$this->_checkAccess()) throw new ExceptionUserTextStorage('error.accessDenied');

    $this->_load();

    return $this->_data;
  }
  
  private function _saveItem($params) {
    if (isset($params['item'])) {
      $ids = array();
      
      foreach ($params['item'] as $item) {
        if ($item['timeFrom']&&$item['timeTo']&&($item['timeFrom']>$item['timeTo'])) throw new ExceptionUserTextStorage('error.saveReservationCondition_invalidTerm');
      
        $oData = array(
          'reservationcondition'=>$this->_id,'name'=>$item['name'],
          'time_from'=>$item['timeFrom']?$item['timeFrom']:null,
          'time_to'=>$item['timeTo']?$item['timeTo']:null,
          'limit_center'=>$item['limitCenter']?$item['limitCenter']:null,
          'limit_center_message'=>$item['limitCenterMessage'],
          'limit_quantity'=>($item['limitQuantity']!=='')?$item['limitQuantity']:null,
          'limit_quantity_period'=>$item['limitQuantityPeriod'],
          'limit_quantity_type'=>$item['limitQuantityType']?$item['limitQuantityType']:null,
          'limit_quantity_scope'=>$item['limitQuantityScope']?$item['limitQuantityScope']:null,
          'limit_quantity_message'=>$item['limitQuantityMessage'],
          'limit_other_scope'=>$item['limitOtherScope']?$item['limitOtherScope']:null,
          'limit_total_quantity'=>($item['limitTotalQuantity']!=='')?$item['limitTotalQuantity']:null,
          'limit_total_quantity_period'=>$item['limitTotalQuantityPeriod'],
          'limit_total_quantity_type'=>$item['limitTotalQuantityType']?$item['limitTotalQuantityType']:null,
          'limit_total_quantity_tag'=>$item['limitTotalQuantityTag'],'limit_total_quantity_message'=>$item['limitTotalQuantityMessage'],
          'limit_overlap_quantity'=>($item['limitOverlapQuantity']!=='')?$item['limitOverlapQuantity']:null,
          'limit_overlap_quantity_scope'=>$item['limitOverlapQuantityScope'],'limit_overlap_quantity_tag'=>$item['limitOverlapQuantityTag'],
          'limit_overlap_quantity_message'=>$item['limitOverlapQuantityMessage'],
          'limit_first_time_before_start'=>$item['limitFirstTimeBeforeStart']?$item['limitFirstTimeBeforeStart']:null,
          'limit_first_time_before_message'=>$item['limitFirstTimeBeforeMessage'],
          'limit_last_time_before_start'=>$item['limitLastTimeBeforeStart']?$item['limitLastTimeBeforeStart']:null,
          'limit_last_time_before_message'=>$item['limitLastTimeBeforeMessage'],
          'limit_after_start_event'=>$item['limitAfterStartEvent']?$item['limitAfterStartEvent']:null,
          'limit_after_start_event_message'=>$item['limitAfterStartEventMessage'],
          'advance_payment'=>$item['advancePayment']?$item['advancePayment']:null,'advance_payment_message'=>$item['advancePaymentMessage'],
          'cancel_before_start'=>$item['cancelBefore']?$item['cancelBefore']:null,'cancel_before_start_message'=>$item['cancelBeforeMessage'],
          'cancel_payed_before_start'=>$item['cancelPayedBefore']?$item['cancelPayedBefore']:null,'cancel_payed_before_start_message'=>$item['cancelPayedBeforeMessage'],
          'limit_anonymous_before_start'=>$item['limitAnonymousBeforeStart']?$item['limitAnonymousBeforeStart']:null,'limit_anonymous_before_message'=>$item['limitAnonymousBeforeMessage'],
          'required_event'=>$item['requiredEvent'],'required_event_message'=>$item['requiredEventMessage'],
          'required_event_exists'=>$item['requiredEventExists']?$item['requiredEventExists']:null,
          'required_event_payed'=>$item['requiredEventPayed']?$item['requiredEventPayed']:null,
          'required_event_all'=>$item['requiredEventAll']?$item['requiredEventAll']:null,
          'required_resource'=>$item['requiredResource'],'required_resource_message'=>$item['requiredResourceMessage'],
          'required_resource_exists'=>$item['requiredResourceExists']?$item['requiredResourceExists']:null,
          'required_resource_payed'=>$item['requiredResourcePayed']?$item['requiredResourcePayed']:null,
          'required_resource_all'=>$item['requiredResourceAll']?$item['requiredResourceAll']:null,
        );
        
        $o = new OReservationConditionItem(ifsetor($item['itemId']));
        $o->setData($oData);
        $o->save();
        
        $ids[] = $o->getId();
      }
      
      $ids = implode(',',$ids);
      $s = new SReservationConditionItem;
      $s->addStatement(new SqlStatementBi($s->columns['reservationcondition'], $this->_id, '%s=%s'));
      if ($ids) $s->addStatement(new SqlStatementMono($s->columns['reservationconditionitem_id'], sprintf('%%s NOT IN (%s)', $ids)));
      $s->setColumnsMask(array('reservationconditionitem_id'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $o = new OReservationConditionItem($row['reservationconditionitem_id']);
        $o->delete();
      }
    }
  }
  
  private function _save($params) {
    $this->_load();
    
    $this->_app->db->beginTransaction();

    $o = new OReservationCondition($this->_id?$this->_id:null);
    $oData = array();
    if (isset($params['providerId'])) $oData['provider'] = $params['providerId'];
    if (isset($params['name'])) $oData['name'] = $params['name'];
    if (isset($params['evaluation'])) $oData['evaluation'] = $params['evaluation'];
    if (isset($params['description'])) $oData['description'] = $params['description'];
    
    if (count($oData)) {
      $o->setData($oData);
      $o->save();
    }
    $this->_id = $o->getId();
    
    $this->_saveItem($params);
    
    $this->_app->db->commitTransaction();
  }

  public function save($params) {
    if (!$this->_checkAccess($params)) throw new ExceptionUserTextStorage('error.accessDenied');
    
    $this->_checkBeforeSave($params);
    
    $this->_save($params);
  }
  
  private function _delete() {
    $this->_app->db->beginTransaction();
    
    $o = new OReservationCondition($this->_id);
    $o->delete();
    
    $this->_app->db->commitTransaction();
  }
  
  public function delete() {
    if (!$this->_checkAccess()) throw new ExceptionUserTextStorage('error.accessDenied');
  
    $this->_checkBeforeDelete();
  
    $this->_load();
    
    $ret = $this->_data['name'];
  
    $this->_delete();
    
    return $ret;
  }
  
  public function copy() {
    if (!$this->_checkAccess()) throw new ExceptionUserTextStorage('error.accessDenied');
    
    $this->_load();
    
    $ret = $this->_data['name'];
    
    $newReservationCondition = new BReservationCondition;
    $this->_data['name'] .= ' (kopie)';
    foreach ($this->_data['item'] as $index=>$item) {
      $this->_data['item'][$index]['itemId'] = null;
    }
    $newReservationCondition->save($this->_data);
    
    return $ret;
  }
}

?>
