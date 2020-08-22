<?php

class BPageTemplate extends BusinessObject {

  private function _checkAccess() {
    $this->_load();
    
    $ret = $this->_app->auth->isAdministrator();

    return $ret;
  }
  
  private function _checkBeforeSave($params) {
    if (!$this->_id) {
      if (!isset($params['name'])) throw new ExceptionUserTextStorage('error.savePageTemplate_emptyName');
    }
    
    // tyto nesmi byt prazdny nikdy
    if (isset($params['name'])&&!$params['name']) throw new ExceptionUserTextStorage('error.savePageTemplate_emptyName');
  }

  protected function _load() {
    if ($this->_id&&!$this->_loaded) {
      $oPageTemplate = new OPageTemplate($this->_id);
      $data = $oPageTemplate->getData();
      $returnData['id'] = $data['pagetemplate_id'];
      $returnData['name'] = $data['name'];
      $returnData['content'] = $data['content'];
      
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

    $o = new OPageTemplate($this->_id?$this->_id:null);
    $oData = array();
    if (isset($params['name'])) $oData['name'] = trim($params['name']);
    if (isset($params['content'])) $oData['content'] = trim($params['content']);
    
    if (count($oData)) {
      $o->setData($oData);
      $o->save();
    }
    $this->_id = $o->getId();
    
    $this->_app->db->commitTransaction();
  }

  public function save($params) {
    if (!$this->_checkAccess()) throw new ExceptionUserTextStorage('error.accessDenied');
    
    $this->_checkBeforeSave($params);
    
    $this->_save($params);
  }
  
  private function _delete() {
    $this->_app->db->beginTransaction();
    
    $o = new OPageTemplate($this->_id);
    $o->delete();
    
    $this->_app->db->commitTransaction();
  }
  
  public function delete() {
    if (!$this->_checkAccess()) throw new ExceptionUserTextStorage('error.accessDenied');
  
    $this->_load();
    
    $ret = $this->_data['name'];
  
    $this->_delete();
    
    return $ret;
  }
  
  public function copy() {
    if (!$this->_checkAccess()) throw new ExceptionUserTextStorage('error.accessDenied');
    
    $this->_load();
    
    $ret = $this->_data['name'];
    
    $newPageTemplate = new BPageTemplate;
    $this->_data['name'] .= ' (kopie)';
    $newPageTemplate->save($this->_data);
    
    return $ret;
  }
}

?>
