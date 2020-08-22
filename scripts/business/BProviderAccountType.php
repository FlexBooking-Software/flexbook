<?php

class BProviderAccountType extends BusinessObject {

  private function _checkAccess($params=array()) {
    $this->_load();
    
    $provider = ifsetor($params['providerId'],$this->_data['providerId']);
    
    $ret = $this->_app->auth->haveRight('commodity_admin', $provider);
    
    return $ret;
  }

  private function _checkBeforeSave($params) {
    if (!$this->_id) {
      if (!isset($params['name'])) throw new ExceptionUserTextStorage('error.saveProviderAccountType_emptyName');
      if (!isset($params['providerId'])) throw new ExceptionUserTextStorage('error.saveProviderAccountType_emptyProvider');
    }
    
    // tyto nesmi byt prazdny nikdy
    if (isset($params['name'])&&!$params['name']) throw new ExceptionUserTextStorage('error.saveProviderAccountType_emptyName');
    if (!isset($params['providerId'])) throw new ExceptionUserTextStorage('error.saveProviderAccountType_emptyProvider');
  }
  
  private function _checkBeforeDelete() {
    $s = new SResource;
    $s->addStatement(new SqlStatementBi($s->columns['accounttype'], $this->_id, '%s=%s'));
    $s->setColumnsMask(array('resource_id','name'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($row = $this->_app->db->fetchAssoc($res)) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.deleteProviderAccountType_resourceExists'), $row['name']));
    
    $s = new SEvent;
    $s->addStatement(new SqlStatementBi($s->columns['accounttype'], $this->_id, '%s=%s'));
    $s->setColumnsMask(array('event_id','name'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($row = $this->_app->db->fetchAssoc($res)) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.deleteProviderAccountType_eventExists'), $row['name']));
  }

  protected function _load() {
    if ($this->_id&&!$this->_loaded) {
      $oProviderAccountType = new OProviderAccountType($this->_id);
      $data = $oProviderAccountType->getData();
      $returnData['id'] = $data['provideraccounttype_id'];
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

    $o = new OProviderAccountType($this->_id?$this->_id:null);
    $oData = array();
    if (isset($params['providerId'])) $oData['provider'] = $params['providerId'];
    if (isset($params['name'])) $oData['name'] = $params['name'];
    
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
    
    $o = new OProviderAccountType($this->_id);
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
    
    $newProviderAccountType = new BProviderAccountType;
    $this->_data['name'] .= ' (kopie)';
    $newProviderAccountType->save($this->_data);
    
    return $ret;
  }
}

?>
