<?php

class BTicket extends BusinessObject {

  private function _checkAccess($params=array()) {
    $this->_load();
    
    $provider = ifsetor($params['providerId'],$this->_data['providerId']);
    
    $ret = $this->_app->auth->haveRight('commodity_admin', $provider);
    
    return $ret;
  }

  private function _checkBeforeSave($params) {
    if (!$this->_id) {
      if (!isset($params['name'])) throw new ExceptionUserTextStorage('error.saveTicket_emptyName');
      if (!isset($params['providerId'])) throw new ExceptionUserTextStorage('error.saveTicket_emptyProvider');
    }
    
    // tyto nesmi byt prazdny nikdy
    if (isset($params['name'])&&!$params['name']) throw new ExceptionUserTextStorage('error.saveTicket_emptyName');
    if (isset($params['price'])&&!$params['price']) throw new ExceptionUserTextStorage('error.saveTicket_emptyPrice');
    if (isset($params['value'])&&!$params['value']) throw new ExceptionUserTextStorage('error.saveTicket_emptyValue');
    if (!isset($params['providerId'])) throw new ExceptionUserTextStorage('error.saveTicket_emptyProvider');

    if ($params['validityType']=='LENGTH') {
      if (!$params['validityCount']||!$params['validityUnit']) throw new ExceptionUserTextStorage('error.editTicket_missingValidity');
    } elseif ($params['validityType']=='PERIOD') {
      if (!$params['validityFrom']&&!$params['validityTo']) throw new ExceptionUserTextStorage('error.editTicket_missingValidity');
      if ($params['validityFrom']>=$params['validityTo']) throw new ExceptionUserTextStorage('error.editTicket_invalidValidity');
    }
  }
  
  private function _checkBeforeDelete() {
    $s = new SUserTicket;
    $s->addStatement(new SqlStatementBi($s->columns['ticket'], $this->_id, '%s=%s'));
    $s->setColumnsMask(array('userticket_id','fullname'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($row = $this->_app->db->fetchAssoc($res)) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.deleteTicket_userTicketExists'), $row['fullname']));
  }

  protected function _load() {
    if ($this->_id&&!$this->_loaded) {
      $oTicket = new OTicket($this->_id);
      $data = $oTicket->getData();
      $returnData['id'] = $data['ticket_id'];
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
      
      $returnData['center'] = $data['center'];
      $returnData['validityType'] = $data['validity_type'];
      $returnData['validityUnit'] = $data['validity_unit'];
      $returnData['validityCount'] = $data['validity_count'];
      $returnData['validityFrom'] = $data['validity_from'];
      $returnData['validityTo'] = $data['validity_to'];
      $returnData['price'] = $data['price'];
      $returnData['value'] = $data['value'];
      $returnData['active'] = $data['active'];
      
      $returnData['subjectTag'] = '';
      if ($data['subject_tag']) {
        $s = new STag;
        $s->addStatement(new SqlStatementMono($s->columns['tag_id'], sprintf('%%s IN (%s)', $this->_app->db->escapeString($data['subject_tag']))));
        $s->setColumnsMask(array('name'));
        $res = $this->_app->db->doQuery($s->toString());
        while ($row = $this->_app->db->fetchAssoc($res)) {
          if ($returnData['subjectTag']) $returnData['subjectTag'] .= ',';
          $returnData['subjectTag'] .= $row['name'];
        }
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
  
  private function _saveTag($params) {
    $tagId = '';
    
    if (isset($params['subjectTag'])&&$params['subjectTag']) {
      foreach (explode(',',$params['subjectTag']) as $tagName) {
        $s = new STag;
        $s->addStatement(new SqlStatementBi($s->columns['name'], $tagName, '%s=%s'));
        $s->setColumnsMask(array('tag_id'));
        $res = $this->_app->db->doQuery($s->toString());
        if ($row = $this->_app->db->fetchAssoc($res)) {
          if ($tagId) $tagId .= ',';
          $tagId .= $row['tag_id'];
        }
      }
    }
    
    $o = new OTicket($this->_id);
    $o->setData(array('subject_tag'=>$tagId));
    $o->save();
  }

  private function _save($params) {
    $this->_load();
    
    $this->_app->db->beginTransaction();

    $o = new OTicket($this->_id?$this->_id:null);
    $oData = array();
    if (isset($params['providerId'])) $oData['provider'] = $params['providerId'];
    if (isset($params['name'])) $oData['name'] = $params['name'];
    if (isset($params['center'])) $oData['center'] = $params['center']?$params['center']:null;
    if (isset($params['validityType'])) $oData['validity_type'] = $params['validityType']?$params['validityType']:null;
    if (isset($params['validityUnit'])) $oData['validity_unit'] = $params['validityUnit']?$params['validityUnit']:null;
    if (isset($params['validityCount'])) $oData['validity_count'] = $params['validityCount']?$params['validityCount']:null;
    if (isset($params['validityFrom'])) $oData['validity_from'] = $params['validityFrom']?$params['validityFrom']:null;
    if (isset($params['validityTo'])) $oData['validity_to'] = $params['validityTo']?$params['validityTo']:null;
    if (isset($params['price'])) $oData['price'] = $params['price'];
    if (isset($params['value'])) $oData['value'] = $params['value'];
    if (isset($params['active'])) $oData['active'] = $params['active'];

    if (count($oData)) {
      $o->setData($oData);
      $o->save();
    }
    $this->_id = $o->getId();
    
    $this->_saveTag($params);
   
    $this->_app->db->commitTransaction();
  }

  public function save($params) {
    if (!$this->_checkAccess($params)) throw new ExceptionUserTextStorage('error.accessDenied');
    
    $this->_checkBeforeSave($params);
    
    $this->_save($params);
  }
  
  private function _delete() {
    $this->_app->db->beginTransaction();
    
    $o = new OTicket($this->_id);
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
    
    $newTicket = new BTicket;
    $this->_data['name'] .= ' (kopie)';
    $newTicket->save($this->_data);
    
    return $ret;
  }
}

?>
