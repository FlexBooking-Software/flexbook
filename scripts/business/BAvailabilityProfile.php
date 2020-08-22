<?php

class BAvailabilityProfile extends BusinessObject {

  private function _checkAccess($params=array()) {
    $this->_load();
    
    $provider = ifsetor($params['providerId'],$this->_data['providerId']);
    
    $ret = $this->_app->auth->haveRight('commodity_admin', $provider);
    
    return $ret;
  }
  
  private function _checkBeforeSave($params) {
    // kdyz se zaklada novy profil jsou tyto atributy povinne
    if (!$this->_id) {
      if (!isset($params['name'])) throw new ExceptionUserTextStorage('error.saveAvailProfile_emptyName');
      if (!isset($params['providerId'])) throw new ExceptionUserTextStorage('error.saveAvailProfile_emptyProvider');
    }
    
    // tyto nesmi byt prazdny nikdy
    if (isset($params['name'])&&!$params['name']) throw new ExceptionUserTextStorage('error.saveAvailProfile_emptyName');
    if (isset($params['providerId'])&&!$params['providerId']) throw new ExceptionUserTextStorage('error.saveAvailProfile_emptyProvider');
    
    if (isset($params['weekday'])&&is_array($params['weekday'])) {
      foreach ($params['weekday'] as $day=>$daySpec) {
        if (!isset($daySpec['from'])||!$daySpec['from']||!isset($daySpec['to'])||!$daySpec['to']) {
          throw new ExceptionUserTextStorage('error.saveAvailProfile_incompleteWeekday');
        }
      }
    }
  }
  
  private function _checkBeforeDelete() {
    $s = new SResource;
    $s->addStatement(new SqlStatementBi($s->columns['availabilityprofile'], $this->_id, '%s=%s'));
    $s->setColumnsMask(array('resource_id','name'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($row = $this->_app->db->fetchAssoc($res)) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.deleteAvailProfile_resourceExists'), $row['name']));
  }

  protected function _load() {
    if ($this->_id&&!$this->_loaded) {
      $oAvailProfile = new OAvailabilityProfile($this->_id);
      $data = $oAvailProfile->getData();
      $returnData['id'] = $data['availabilityprofile_id'];
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
      
      $returnData['weekday'] = array();
      $s = new SAvailabilityProfileItem;
      $s->addStatement(new SqlStatementBi($s->columns['availabilityprofile'], $this->_id, '%s=%s'));
      $s->setColumnsMask(array('availabilityprofileitem_id','weekday','time_from','time_to'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $to = $row['time_to'];
        if (!strcmp($to,'00:00:00')) $to = '24:00:00';
        
        $returnData['weekday'][$row['weekday']] = array('id'=>$row['availabilityprofileitem_id'],'from'=>$row['time_from'],'to'=>$to);
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
  
  private function _save($params) {
    $this->_load();
    
    $this->_app->db->beginTransaction();

    $o = new OAvailabilityProfile($this->_id?$this->_id:null);
    $oData = array();
    if (isset($params['providerId'])) $oData['provider'] = $params['providerId'];
    if (isset($params['name'])) $oData['name'] = $params['name'];
    
    if (count($oData)) {
      $o->setData($oData);
      $o->save();
    }
    $this->_id = $o->getId();
    
    $this->_saveWeekday($params);
    
    $this->_checkResourceAvailability();
    
    $this->_app->db->commitTransaction();
  }
  
  private function _saveWeekday($params) {
    if (isset($params['weekday'])) {
      if (isset($this->_data['weekday'])) {
        foreach ($this->_data['weekday'] as $day=>$spec) {
          if (!isset($params['weekday'][$day])) {
            $o = new OAvailabilityProfileItem($this->_data['weekday'][$day]['id']);
            $o->delete();
          }
        }
      }
      
      foreach ($params['weekday'] as $day=>$spec) {
        $to = $spec['to'];
        if (!strcmp($to,'24:00:00')) $to = '00:00:00';
        
        $id = isset($this->_data['weekday'][$day])?$this->_data['weekday'][$day]['id']:null;
        $o = new OAvailabilityProfileItem($id);
        $o->setData(array(
              'availabilityprofile' => $this->_id,
              'weekday'             => $day,
              'time_from'           => $spec['from'],
              'time_to'             => $to,
              ));
        $o->save();
      }
    }
  }

  public function save($params) {
    if (!$this->_checkAccess($params)) throw new ExceptionUserTextStorage('error.accessDenied');
    
    $this->_checkBeforeSave($params);
    
    $this->_save($params);
  }
  
  private function _delete() {
    $this->_app->db->beginTransaction();
    
    $o = new OAvailabilityProfile($this->_id);
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
    
    $newAvailProfile = new BAvailabilityProfile;
    $this->_data['name'] .= ' (kopie)';
    $newAvailProfile->save($this->_data);
    
    return $ret;
  }
  
  private function _checkResourceAvailability() {
    $s = new SResource;
    $s->addStatement(new SqlStatementBi($s->columns['availabilityprofile'], $this->_id, '%s=%s'));
    $s->setColumnsMask(array('resource_id'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $b = new BResource($row['resource_id']);
      $b->generateAvailabilityTable(date('Y-m-d'));
    }
  }
}

?>
