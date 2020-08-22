<?php

class BResourcePool extends BusinessObject {

  private function _checkAccess($params=null,$access='all') {
    $ret = false;
    $this->_load();
    
    $requiredRight = $access=='all'?'commodity_admin':'commodity_read';
 
    while (true) {
      if ($user=$this->_app->auth->isUser()) {
        // normalni uzivatel nema pravo delat cokoliv se zdrojema
        break;
      } elseif ($this->_app->auth->isProvider()) {
        // kdyz je to provider a ma pravo pracovat se svyma zdrojema
        $allowedProvider = $this->_app->auth->getAllowedProvider($requiredRight,'array');
        $allowedCenter = $this->_app->auth->getAllowedCenter('array');

        if ($this->_id) {
          if (!in_array($this->_data['providerId'], $allowedProvider)) break;
          if (!in_array($this->_data['centerId'], $allowedCenter)) break;
        }
        if (isset($params['providerId'])&&!in_array($params['providerId'], $allowedProvider)) break;
        if (isset($params['centerId'])&&!in_array($params['centerId'], $allowedCenter)) break;
      } elseif ($this->_app->auth->haveRight($requiredRight)) {
        // kdyz je to admin a ma pravo ukladat zdroje
      } else {
        break;
      }
      
      $ret = true;
      break;
    }
    
    return $ret;
  }
  
  private function _checkBeforeSave($params) {
    // kdyz se zaklada nova akce jsou tyto atributy povinne
    if (!$this->_id) {
      if (!isset($params['name'])) throw new ExceptionUserTextStorage('error.saveResource_emptyName');
      if (!isset($params['providerId'])) throw new ExceptionUserTextStorage('error.saveResource_emptyProvider');
    }
    
    // tyto nesmi byt prazdny nikdy
    if (isset($params['name'])&&!$params['name']) throw new ExceptionUserTextStorage('error.saveResource_emptyName');
    if (isset($params['providerId'])&&!$params['providerId']) throw new ExceptionUserTextStorage('error.saveResource_emptyProvider');
    
    // test na unikatnost
    if (isset($params['externalId'])&&$params['externalId']) {
      $s = new SResourcePool;
      $s->addStatement(new SqlStatementBi($s->columns['external_id'], $params['externalId'], '%s=%s'));
      $s->addStatement(new SqlStatementBi($s->columns['resourcepool_id'], $this->_id, '%s<>%s'));
      $s->addStatement(new SqlStatementBi($s->columns['provider'], $params['providerId'], '%s=%s'));
      $s->setColumnsMask(array('resource_id'));
      $result = $this->_app->db->doQuery($s->toString());
      if ($this->_app->db->getRowsNumber($result) > 0) throw new ExceptionUserTextStorage('error.saveResourcePool_externalIdNotUnique');
    }
  }
  
  protected function _checkBeforeDelete() { }
  
  protected function _checkBeforeDisable() { }

  protected function _load() {
    if ($this->_id&&!$this->_loaded) {
      $oResourcePool = new OResourcePool($this->_id);
      $data = $oResourcePool->getData();
      $returnData['id'] = $data['resourcepool_id'];
      $returnData['providerId'] = $data['provider'];
      $returnData['centerId'] = $data['center'];
      $returnData['externalId'] = $data['external_id'];
      $returnData['name'] = $data['name'];
      $returnData['description'] = $data['description'];
      $returnData['active'] = $data['active'];

      $returnData['urlPhoto'] = $data['url_photo'];
      
      $returnData['resource'] = array();
      $returnData['resourceId'] = array();
      $s = new SResourcePoolItem;
      $s->addStatement(new SqlStatementBi($s->columns['resourcepool'], $this->_id, '%s=%s'));
      $s->addOrder(new SqlStatementAsc($s->columns['resource_name']));
      $s->setColumnsMask(array('resource','resource_name','unitprofile_name','resource_price','pricelist_name'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $returnData['resource'][$row['resource']] = array('id'=>$row['resource'],'name'=>$row['resource_name'],
                                                          'unitprofile'=>$row['unitprofile_name'],'pricelist'=>$row['pricelist_name'],
                                                          'price' => sprintf('%s %s', $this->_app->regionalSettings->convertNumberToHuman($row['resource_price'],2), $this->_app->textStorage->getText('label.currency_CZK')));
        $returnData['resourceId'][] = $row['resource'];
      }

      $returnData['tag'] = '';
      $s = new SResourcePoolTag;
      $s->addStatement(new SqlStatementBi($s->columns['resourcepool'], $this->_id, '%s=%s'));
      $s->setColumnsMask(array('name'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        if ($returnData['tag']) $returnData['tag'] .= ',';
        $returnData['tag'] .= $row['name'];
      }
      
      $this->_data = $returnData;
      
      $this->_loaded = true;
    }
  }

  public function getData() {
    if (!$this->_checkAccess(null,'read')) throw new ExceptionUserTextStorage('error.accessDenied');

    $this->_load();

    return $this->_data;
  }
  
  private function _saveResource($params) {
    if (isset($params['resource'])) {
      // smazu puvodni zdroje
      $s = new SResourcePoolItem;
      $s->addStatement(new SqlStatementBi($s->columns['resourcepool'], $this->_id, '%s=%s'));
      $s->setColumnsMask(array('resourcepool','resource'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $o = new OResourcePoolItem(array('resourcepool'=>$row['resourcepool'],'resource'=>$row['resource']));
        $o->delete();
      }
      
      $poolProvider = $params['providerId'];
      $poolCenter = $params['centerId'];
      $poolUnitProfile = null;
      $poolPrice = null;
      $poolPriceList = null;
      foreach ($params['resource'] as $resource) {
        $s = new SResource;
        $s->addStatement(new SqlStatementBi($s->columns['resource_id'], $resource, '%s=%s'));
        $s->setColumnsMask(array('provider','center','unitprofile','price','pricelist'));
        $res = $this->_app->db->doQuery($s->toString());
        $row = $this->_app->db->fetchAssoc($res);
        
        if ($poolProvider&&($poolProvider!=$row['provider'])) throw new ExceptionUserTextStorage('error.saveResourcePool_invalidProviderCombination');
        if ($poolCenter&&($poolCenter!=$row['center'])) throw new ExceptionUserTextStorage('error.saveResourcePool_invalidCenterCombination');
        if ($poolUnitProfile&&($poolUnitProfile!=$row['unitprofile'])) throw new ExceptionUserTextStorage('error.saveResourcePool_invalidUnitProfileCombination');
        if (($poolPrice&&($poolPrice!=$row['price']))||
            ($poolPriceList&&($poolPriceList!=$row['pricelist']))) throw new ExceptionUserTextStorage('error.saveResourcePool_invalidPriceCombination');
        $poolProvider = $row['provider'];
        $poolCenter = $row['center'];
        $poolUnitProfile = $row['unitprofile'];
        $poolPrice = $row['price'];
        $poolPriceList = $row['pricelist'];
        
        $o = new OResourcePoolItem;
        $o->setData(array('resourcepool'=>$this->_id,'resource'=>$resource));
        $o->save();
      }
    }
  }

  private function _saveTag($params) {
    if (isset($params['tag'])) {
      // zjistim existujici tagy
      $resourceTags = array();
      $s = new SResourcePoolTag;
      $s->addStatement(new SqlStatementBi($s->columns['resourcepool'], $this->_id, '%s=%s'));
      $s->setColumnsMask(array('resourcepool','tag','name'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $resourceTags[$row['name']] = $row['tag'];
      }

      // zalozim nove tagy
      $tag = str_replace(array(';',"\n"),array(','),$params['tag']);
      $tagStringArray = explode(',', $tag);
      $resourceSavedTags = array();
      foreach ($tagStringArray as $t) {
        if (!$t = chop($t)) continue;

        if (!in_array($t, array_keys($resourceTags))) {
          $b = new BTag;
          $b->saveFromCommodity(array('name'=>$t,'provider'=>$params['providerId']));

          $o = new OResourcePoolTag;
          $o->setData(array('resourcepool'=>$this->_id,'tag'=>$b->getId()));
          $o->save();

          $resourceSavedTags[$b->getId()] = $t;
        } else $resourceSavedTags[$resourceTags[$t]] = $t;
      }

      // smazu tagy, ktere uz nemaji byt
      if (!isset($params['tagAddOnly'])||!$params['tagAddOnly']) {
        $s = new SResourcePoolTag;
        $s->addStatement(new SqlStatementBi($s->columns['resourcepool'], $this->_id, '%s=%s'));
        if (count($resourceSavedTags)) $s->addStatement(new SqlStatementMono($s->columns['tag'], sprintf('%%s NOT IN (%s)', implode(',',array_keys($resourceSavedTags)))));
        $s->setColumnsMask(array('resourcepool','tag'));
        $res = $this->_app->db->doQuery($s->toString());
        while ($row = $this->_app->db->fetchAssoc($res)) {
          $o = new OResourcePoolTag(array('resourcepool'=>$row['resourcepool'],'tag'=>$row['tag']));
          $o->delete();
        }
      }
    }
  }
  
  private function _save($params) {
    $this->_load();
    
    $this->_app->db->beginTransaction();

    $o = new OResourcePool($this->_id?$this->_id:null);
    $oData = array();
    if (isset($params['externalId'])) $oData['external_id'] = $params['externalId'];
    if (isset($params['name'])) $oData['name'] = $params['name'];
    if (isset($params['providerId'])) $oData['provider'] = $params['providerId'];
    if (isset($params['centerId'])) $oData['center'] = $params['centerId'];
    if (isset($params['description'])) $oData['description'] = $params['description'];
    if (isset($params['active'])) $oData['active'] = $params['active'];
    if (isset($params['urlPhoto'])) {
      $params['urlPhoto'] = str_replace(array("\n",';'), ',', $params['urlPhoto']);
      $oData['url_photo'] = $params['urlPhoto']?$params['urlPhoto']:null;
    }

    if (count($oData)) {
      $o->setData($oData);
      $o->save();
    }
    $this->_id = $o->getId();
    
    $oData = $o->getData();
    if (!$oData['external_id']) {
      global $NODE_ID;
      $o->setData(array('external_id' => $NODE_ID.'_'.$this->_id));
      $o->save();
    }
    
    $params['providerId'] = ifsetor($params['providerId'], $this->_data['providerId']);
    
    $this->_saveResource($params);
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
    
    $o = new OResourcePool($this->_id);
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
  
  public function disable() {
    if (!$this->_checkAccess()) throw new ExceptionUserTextStorage('error.accessDenied');
    
    $this->_checkBeforeDisable();
  
    $o = new OResourcePool($this->_id);
    $oData = $o->getData();
    
    $ret = $oData['name'];
    
    $o->setData(array('active'=>'N'));
    $o->save();
    
    return $ret;
  }
}

?>
