<?php

class BUnitProfile extends BusinessObject {

  private function _checkAccess($params=array()) {
    $this->_load();
    
    $provider = ifsetor($params['providerId'],$this->_data['providerId']);
    
    $ret = $this->_app->auth->haveRight('commodity_admin', $provider);
    
    return $ret;
  }
  
  private function _checkBeforeSave($params) {
    // kdyz se zaklada novy profil jsou tyto atributy povinne
    if (!$this->_id) {
      if (!isset($params['name'])) throw new ExceptionUserTextStorage('error.saveUnitProfile_emptyName');
      if (!isset($params['providerId'])) throw new ExceptionUserTextStorage('error.saveUnitProfile_emptyProvider');
      if (!isset($params['unit'])) throw new ExceptionUserTextStorage('error.saveUnitProfile_emptyUnit');
      if (!isset($params['minimumUnit'])) throw new ExceptionUserTextStorage('error.saveUnitProfile_emptyMinimumUnit');
      //if (!isset($params['maximumUnit'])) throw new ExceptionUserTextStorage('error.saveUnitProfile_emptyMaximumUnit');
    }
    
    // tyto nesmi byt prazdny nikdy
    if (isset($params['name'])&&!$params['name']) throw new ExceptionUserTextStorage('error.saveUnitProfile_emptyName');
    if (isset($params['providerId'])&&!$params['providerId']) throw new ExceptionUserTextStorage('error.saveUnitProfile_emptyProvider');
    if (isset($params['unit'])&&!$params['unit']) throw new ExceptionUserTextStorage('error.saveUnitProfile_emptyUnit');
    if (isset($params['minimumUnit'])&&!$params['minimumUnit']) throw new ExceptionUserTextStorage('error.saveUnitProfile_emptyMinimumUnit');
    //if (isset($params['maximumUnit'])&&!$params['maximumUnit']) throw new ExceptionUserTextStorage('error.saveUnitProfile_emptyMaximumUnit');

    $startTimeFrom = ifsetor($params['alignmentTimeFrom']);
    $startTimeTo = ifsetor($params['alignmentTimeTo']);
    $timeAligment = ifsetor($params['alignmentTimeGrid']);
    $endTimeFrom = ifsetor($params['endTimeFrom']);
    $endTimeTo = ifsetor($params['endTimeTo']);

    // musi byt vyplneny odpovidaji pole pro omezeni startu/konce rezervace
    if (($startTimeFrom&&!$startTimeTo)||
        (!$startTimeFrom&&$startTimeTo)||
        (!$startTimeFrom&&$timeAligment)||
        ($endTimeFrom&&!$endTimeTo)||
        (!$endTimeFrom&&$endTimeTo)
    ) {
      throw new ExceptionUserTextStorage('error.saveUnitProfile_invalidTimeAlignment');
    }

    // kontrola, jestli omezeni startu/konce rezervace dava smysl s nastavenim rezervacnich jednotek
    // pouze kdyz nejsou rezervacni jednotky den/noc
    if (!isset($params['unitRounding'])&&!$params['unitRounding']&&
        $startTimeTo&&$endTimeFrom
    ) {
      $start = sprintf('%s %s', date('Y-m-d'), $startTimeTo);
      $end = sprintf('%s %s', date('Y-m-d'), $endTimeFrom);
      $minimalReservationLengthFromUnit = $params['unit']*$params['minimumUnit'];
      $minimalReservationLengthFromTime = $this->_app->regionalSettings->calculateDateTimeDifference($end, $start);

      if ($minimalReservationLengthFromTime>$minimalReservationLengthFromUnit) throw new ExceptionUserTextStorage('error.saveUnitProfile_invalidUnitTime');
    }
  }

  private function _checkBeforeDelete() {
    $s = new SResource;
    $s->addStatement(new SqlStatementBi($s->columns['unitprofile'], $this->_id, '%s=%s'));
    $s->setColumnsMask(array('resource_id','name'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($row = $this->_app->db->fetchAssoc($res)) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.deleteUnitProfile_resourceExists'), $row['name']));
  }
  
  protected function _load() {
    if ($this->_id&&!$this->_loaded) {
      $oUnitProfile = new OUnitProfile($this->_id);
      $data = $oUnitProfile->getData();
      $returnData['id'] = $data['unitprofile_id'];
      $returnData['name'] = $data['name'];
      $returnData['unit'] = $data['unit'];
      $returnData['unitRounding'] = $data['unit_rounding'];
      $returnData['minimumUnit'] = $data['minimum_quantity'];
      $returnData['maximumUnit'] = $data['maximum_quantity'];
      $returnData['alignmentTimeFrom'] = $data['time_alignment_from'];
      $returnData['alignmentTimeTo'] = $data['time_alignment_to'];
      $returnData['alignmentTimeGrid'] = $data['time_alignment_grid'];
      $returnData['endTimeFrom'] = $data['time_end_from'];
      $returnData['endTimeTo'] = $data['time_end_to'];

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

    $o = new OUnitProfile($this->_id?$this->_id:null);
    $oData = array();
    if (isset($params['providerId'])) $oData['provider'] = $params['providerId'];
    if (isset($params['name'])) $oData['name'] = $params['name'];
    if (isset($params['unit'])) $oData['unit'] = $params['unit'];
    if (isset($params['unitRounding'])||($params['unitRounding']===null)) $oData['unit_rounding'] = $params['unitRounding'];
    if (isset($params['minimumUnit'])) $oData['minimum_quantity'] = $params['minimumUnit']?$params['minimumUnit']:null;
    if (isset($params['maximumUnit'])) $oData['maximum_quantity'] = $params['maximumUnit']?$params['maximumUnit']:null;
    if (isset($params['alignmentTimeFrom'])) $oData['time_alignment_from'] = $params['alignmentTimeFrom']?$params['alignmentTimeFrom']:null;
    if (isset($params['alignmentTimeTo'])) $oData['time_alignment_to'] = $params['alignmentTimeTo']?$params['alignmentTimeTo']:null;
    if (isset($params['alignmentTimeGrid'])) $oData['time_alignment_grid'] = $params['alignmentTimeGrid']?$params['alignmentTimeGrid']:null;
    if (isset($params['endTimeFrom'])) $oData['time_end_from'] = $params['endTimeFrom']?$params['endTimeFrom']:null;
    if (isset($params['endTimeTo'])) $oData['time_end_to'] = $params['endTimeTo']?$params['endTimeTo']:null;

    if (count($oData)) {
      $o->setData($oData);
      $o->save();
    }
    $this->_id = $o->getId();
    
    $this->_app->db->commitTransaction();
  }

  public function save($params) {
    if (!$this->_checkAccess($params)) throw new ExceptionUserTextStorage('error.accessDenied');
    
    $this->_checkBeforeSave($params);
    
    $this->_save($params);
  }
  
  private function _delete() {
    $this->_app->db->beginTransaction();
    
    $o = new OUnitProfile($this->_id);
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
    
    $newProfile = new BUnitProfile;
    $this->_data['name'] .= ' (kopie)';
    $newProfile->save($this->_data);
    
    return $ret;
  }
}

?>
