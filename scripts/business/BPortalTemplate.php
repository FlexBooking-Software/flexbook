<?php

class BPortalTemplate extends BusinessObject {

  private function _checkAccess() {
    $this->_load();
    
    $ret = $this->_app->auth->isAdministrator()||$this->_app->auth->isProvider();

    return $ret;
  }
  
  private function _checkBeforeSave($params) {
    if (!$this->_id) {
      if (!isset($params['name'])) throw new ExceptionUserTextStorage('error.savePortalTemplate_emptyName');
    }
    
    // tyto nesmi byt prazdny nikdy
    if (isset($params['name'])&&!$params['name']) throw new ExceptionUserTextStorage('error.savePortalTemplate_emptyName');
  }

  protected function _load() {
    if ($this->_id&&!$this->_loaded) {
      $oPortalTemplate = new OPortalTemplate($this->_id);
      $data = $oPortalTemplate->getData();
      $returnData['id'] = $data['portaltemplate_id'];
      $returnData['name'] = $data['name'];
      $returnData['css'] = $data['css'];
      $returnData['preview'] = $data['preview'];
      $returnData['content'] = $data['content'];
      
      $s = new SPortalTemplatePageTemplate;
      $s->addStatement(new SqlStatementBi($s->columns['portaltemplate'], $this->_id, '%s=%s'));
      $s->addOrder(new SqlStatementAsc($s->columns['menu_sequence_code']));
      $s->setColumnsMask(array('pagetemplate'));
      $res = $this->_app->db->doQuery($s->toString());
      $returnData['page'] = array();
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $returnData['page'][] = $row['pagetemplate'];
      }

      if ($data['preview']) {
        $o = new OFile($data['preview']);
        $oData = $o->getData();
        $returnData['previewHash'] = $oData['hash'];
      } else $returnData['previewHash'] = null;

      $this->_data = $returnData;

      $this->_loaded = true;
    }
  }

  public function getData() {
    if (!$this->_checkAccess()) throw new ExceptionUserTextStorage('error.accessDenied');

    $this->_load();

    return $this->_data;
  }
  
  private function _savePreview($params) {
    if (isset($params['preview'])&&count($params['preview'])) {
      $oPT = new OPortalTemplate($this->_id);
      
      if ($this->_data['preview']) {
        $oPT->setData(array('preview'=>null));
        $oPT->save();
        
        $o = new OFile($this->_data['preview']);
        $o->delete();
      }

      $b = new BFile;
      $id = $b->save(array(
        'name' => $params['preview']['name'],
        'file' => basename($params['preview']['file']),
      ));

      $oPT->setData(array('preview'=>$id));
      $oPT->save();
    }
  }
  
  private function _savePageTemplate($params) {
    if (isset($params['page'])) {
      // smazu puvodni stranky portalu
      $s = new SPortalTemplatePageTemplate;
      $s->addStatement(new SqlStatementBi($s->columns['portaltemplate'], $this->_id, '%s=%s'));
      $s->setColumnsMask(array('pagetemplate'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $o = new OPortalTemplatePageTemplate(array('portaltemplate'=>$this->_id, 'pagetemplate'=>$row['pagetemplate']));
        $o->delete();
      }
      
      // zalozim nove stranky
      $count = 0;
      foreach ($params['page'] as $page) {
        $o = new OPortalTemplatePageTemplate;
        $o->setData(array('portaltemplate'=>$this->_id, 'pagetemplate'=>$page, 'menu_sequence_code'=>'ITEM_'.++$count));
        $o->save();
      }
    }
  }
  
  private function _save($params) {
    $this->_load();
    
    $this->_app->db->beginTransaction();

    $o = new OPortalTemplate($this->_id?$this->_id:null);
    $oData = array();
    if (isset($params['name'])) $oData['name'] = trim($params['name']);
    if (isset($params['css'])) $oData['css'] = trim($params['css']);
    if (isset($params['content'])) $oData['content'] = trim($params['content']);
    
    if (count($oData)) {
      $o->setData($oData);
      $o->save();
    }
    $this->_id = $o->getId();
    
    $this->_savePreview($params);
    $this->_savePageTemplate($params);
    
    $this->_app->db->commitTransaction();
  }

  public function save($params) {
    if (!$this->_checkAccess()) throw new ExceptionUserTextStorage('error.accessDenied');
    
    $this->_checkBeforeSave($params);
    
    $this->_save($params);
  }
  
  private function _delete() {
    $this->_app->db->beginTransaction();
    
    $o = new OPortalTemplate($this->_id);
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
    
    $newPortalTemplate = new BPortalTemplate;
    $this->_data['name'] .= ' (kopie)';
    $newPortalTemplate->save($this->_data);
    
    return $ret;
  }
}

?>
