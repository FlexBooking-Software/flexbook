<?php

class BAvailabilityExceptionProfile extends BusinessObject {

  private function _checkAccess($params=array()) {
    $this->_load();
    
    $provider = ifsetor($params['providerId'],$this->_data['providerId']);
    
    $ret = $this->_app->auth->haveRight('commodity_admin', $provider);
    
    return $ret;
  }
  
  private function _checkBeforeSave($params) {
    // kdyz se zaklada novy profil jsou tyto atributy povinne
    if (!$this->_id) {
      if (!isset($params['name'])) throw new ExceptionUserTextStorage('error.saveAvailExProfile_emptyName');
      if (!isset($params['providerId'])) throw new ExceptionUserTextStorage('error.saveAvailExProfile_emptyProvider');
    }
    
    // tyto nesmi byt prazdny nikdy
    if (isset($params['name'])&&!$params['name']) throw new ExceptionUserTextStorage('error.saveAvailExProfile_emptyName');
    if (isset($params['providerId'])&&!$params['providerId']) throw new ExceptionUserTextStorage('error.saveAvailExProfile_emptyProvider');
  }
  
  private function _checkBeforeDelete() {
    $s = new SResource;
    $s->addStatement(new SqlStatementBi($s->columns['availabilityexceptionprofile'], $this->_id, '%s=%s'));
    $s->setColumnsMask(array('resource_id','name'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($row = $this->_app->db->fetchAssoc($res)) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.deleteAvailExProfile_resourceExists'), $row['name']));
  }

  protected function _load() {
    if ($this->_id&&!$this->_loaded) {
      $oAvailExceptionProfile = new OAvailabilityExceptionProfile($this->_id);
      $data = $oAvailExceptionProfile->getData();
      $returnData['id'] = $data['availabilityexceptionprofile_id'];
      $returnData['name'] = $data['name'];
      
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
      $s = new SAvailabilityExceptionProfileItem;
      $s->addStatement(new SqlStatementBi($s->columns['availabilityexceptionprofile'], $this->_id, '%s=%s'));
      $s->addOrder(new SqlStatementAsc($s->columns['time_from']));
      $s->setColumnsMask(array('availabilityexceptionprofileitem_id', 'name', 'time_from', 'time_to', 'repeated', 'repeat_cycle', 'repeat_weekday', 'repeat_until'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        list($dateTo,$timeTo) = explode(' ',$row['time_to']);
        if (!strcmp($timeTo,'00:00:00')) $timeTo = '24:00:00';
        $to = sprintf('%s %s', $dateTo, $timeTo);
        
        $item = array('itemId'=>$row['availabilityexceptionprofileitem_id'],'name'=>$row['name'],'from'=>$row['time_from'],'to'=>$to,
                                      'repeated'=>$row['repeated'],'repeatCycle'=>$row['repeat_cycle'],'repeatUntil'=>$row['repeat_until']);
        if ($row['repeated']=='Y') {
          foreach (array('mon','tue','wed','thu','fri','sat','sun') as $index=>$day) {
           $item['repeatWeekday_'.$day] = pow(2,$index)&$row['repeat_weekday'];
          } 
        }
        
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
        if ($item['from']>$item['to']) throw new ExceptionUserTextStorage('error.saveAvailExProfile_invalidTerm');
        
        $repeated = ifsetor($item['repeated'],'N');
        list($dateTo,$timeTo) = explode(' ', $item['to']);
        if (!strcmp($timeTo,'24:00:00')) $timeTo = '00:00:00';
        $to = sprintf('%s %s', $dateTo, $timeTo);
      
        $oData = array('availabilityexceptionprofile'=>$this->_id,'name'=>$item['name'],
                       'time_from'=>$item['from'],'time_to'=>$to,'repeated'=>$repeated);
        
        if ($repeated == 'Y') {
          $repeatWeekday = 0;
          foreach (array('mon','tue','wed','thu','fri','sat','sun') as $index=>$day) {
            if (isset($item['repeatWeekday_'.$day])&&$item['repeatWeekday_'.$day]) $repeatWeekday |= pow(2,$index);
          }
          
          $oData['repeat_cycle'] = ifsetor($item['repeatCycle']);
          $oData['repeat_weekday'] = $repeatWeekday;
          $oData['repeat_until'] = ifsetor($item['repeatUntil']);
        } else {
          $oData['repeat_cycle'] = null;
          $oData['repeat_weekday'] = null;
          $oData['repeat_until'] = null;
        }
        
        $o = new OAvailabilityExceptionProfileItem(ifsetor($item['itemId']));
        $o->setData($oData);
        $o->save();
        
        $ids[] = $o->getId();
      }
      
      $ids = implode(',',$ids);
      $s = new SAvailabilityExceptionProfileItem;
      $s->addStatement(new SqlStatementBi($s->columns['availabilityexceptionprofile'], $this->_id, '%s=%s'));
      if ($ids) $s->addStatement(new SqlStatementMono($s->columns['availabilityexceptionprofileitem_id'], sprintf('%%s NOT IN (%s)', $ids)));
      $s->setColumnsMask(array('availabilityexceptionprofileitem_id'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $o = new OAvailabilityExceptionProfileItem($row['availabilityexceptionprofileitem_id']);
        $o->delete();
      }
    }
  }
  
  private function _save($params) {
    $this->_load();
    
    $this->_app->db->beginTransaction();

    $o = new OAvailabilityExceptionProfile($this->_id?$this->_id:null);
    $oData = array();
    if (isset($params['providerId'])) $oData['provider'] = $params['providerId'];
    if (isset($params['name'])) $oData['name'] = $params['name'];
    
    if (count($oData)) {
      $o->setData($oData);
      $o->save();
    }
    $this->_id = $o->getId();
    
    $this->_saveItem($params);
    
    $this->_checkResourceAvailability();
    
    $this->_app->db->commitTransaction();
  }

  public function save($params) {
    if (!$this->_checkAccess($params)) throw new ExceptionUserTextStorage('error.accessDenied');
    
    $this->_checkBeforeSave($params);
    
    $this->_save($params);
  }
  
  private function _checkResourceAvailability() {
    $s = new SResource;
    $s->addStatement(new SqlStatementBi($s->columns['availabilityexceptionprofile'], $this->_id, '%s=%s'));
    $s->setColumnsMask(array('resource_id'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $b = new BResource($row['resource_id']);
      $b->generateAvailabilityTable(date('Y-m-d'));
    }
  }
  
  private function _delete() {
    $this->_app->db->beginTransaction();
    
    $o = new OAvailabilityExceptionProfile($this->_id);
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
    
    $newAvailExceptionProfile = new BAvailabilityExceptionProfile;
    $this->_data['name'] .= ' (kopie)';
    foreach ($this->_data['item'] as $index=>$item) {
      $this->_data['item'][$index]['itemId'] = null;
    }
    $newAvailExceptionProfile->save($this->_data);
    
    return $ret;
  }
}

?>
