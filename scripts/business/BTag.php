<?php

class BTag extends BusinessObject {

  private function _checkAccess($provider=null) {
    $ret = false;

    $this->_load();

    $requiredRight = 'commodity_admin';

    while (true) {
      // pokud neni jeste tag ulozen, muze kdokoliv
      if ($this->_id) {
        if ($this->_app->auth->isProvider()) {
          // kdyz je to provider a ma pravo pracovat se svyma zdrojema
          $allowedProvider = $this->_app->auth->getAllowedProvider($requiredRight,'array');

          if (!count(array_intersect($allowedProvider, $this->_data['provider']))) break;

          // pokud je uveden provider, musi byt pro daneho poskytovatele povoleny
          if ($provider&&!in_array($provider, $allowedProvider)) break;
        } elseif ($this->_app->auth->haveRight($requiredRight)) {
          // kdyz je to admin a ma pravo ukladat zdroje
        } else {
          break;
        }
      }

      $ret = true;
      break;
    }

    return $ret;
  }
  
  private function _checkBeforeSave($params) {
    if (!$this->_id) {
      if (!isset($params['name'])) throw new ExceptionUserTextStorage('error.saveTag_emptyName');
    }
    
    // tyto nesmi byt prazdny nikdy
    if (isset($params['name'])&&!$params['name']) throw new ExceptionUserTextStorage('error.saveTag_emptyName');
  }

  protected function _load() {
    if ($this->_id&&!$this->_loaded) {
      $oTag = new OTag($this->_id);
      $data = $oTag->getData();
      $returnData['id'] = $data['tag_id'];
      $returnData['name'] = $data['name'];
      
      $returnData['portal'] = array();
      $s = new STag;
      $s->addStatement(new SqlStatementBi($s->columns['tag_id'], $this->_id, '%s=%s'));
      $s->setColumnsMask(array('portal'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $returnData['portal'][] = $row['portal'];
      }

      $returnData['provider'] = array();
      $s = new STagProvider();
      $s->addStatement(new SqlStatementBi($s->columns['tag'], $this->_id, '%s=%s'));
      $s->setColumnsMask(array('provider'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $returnData['provider'][] = $row['provider'];
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
  
  private function _savePortal($params) {
    if (isset($params['portal'])) {
      // smazu puvodni portaly
      $s = new STag;
      $s->addStatement(new SqlStatementBi($s->columns['tag_id'], $this->_id, '%s=%s'));
      $s->setColumnsMask(array('tag_id','portal'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $o = new OTagPortal(array('tag'=>$row['tag_id'],'portal'=>$row['portal']));
        $o->delete();
      }
      
      foreach ($params['portal'] as $p) {
        if (!$p) continue;
        
        $o = new OTagPortal;
        $o->setData(array('tag'=>$this->_id,'portal'=>$p));
        $o->save();
      }
    }
  }
  
  private function _saveProvider($params) {
    if (isset($params['provider'])) {
      $s = new STag;
      $s->addStatement(new SqlStatementBi($s->columns['tag_id'], $this->_id, '%s=%s'));
      $s->addStatement(new SqlStatementBi($s->columns['provider'], $params['provider'], '%s=%s'));
      $s->setColumnsMask(array('tag_id'));
      $res = $this->_app->db->doQuery($s->toString());
      if (!$this->_app->db->getRowsNumber($res)) {
        $o = new OTagProvider;
        $o->setData(array('tag'=>$this->_id,'provider'=>$params['provider']));
        $o->save();
      }
    }
  }

  private function _save($params) {
    $this->_load();
    
    $this->_app->db->beginTransaction();

    $o = new OTag($this->_id?$this->_id:null);
    $oData = array();
    if (isset($params['name'])) $oData['name'] = trim($params['name']);
    
    if (count($oData)) {
      $o->setData($oData);
      $o->save();
    }
    $this->_id = $o->getId();
    
    $this->_savePortal($params);

    $this->_app->db->commitTransaction();
  }

  public function save($params) {
    if (!$this->_checkAccess()) throw new ExceptionUserTextStorage('error.accessDenied');
    
    $this->_checkBeforeSave($params);
    
    $this->_save($params);
    if (isset($params['commodity'])) $this->_saveCommodityAssoc($params['commodity']);
  }
  
  public function saveFromCommodity($params) {
    $this->_checkBeforeSave($params);
    
    // najdu tag se stejnym jmenem (od vsech poskytovatelu)
    $s = new STag;
    $s->addStatement(new SqlStatementBi($s->columns['name'], $params['name'], '%s=%s'));
    $s->setColumnsMask(array('tag_id'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($row = $this->_app->db->fetchAssoc($res)) {
      $this->_id = $row['tag_id'];
    }
          
    $this->_save($params);
    
    $this->_saveProvider($params);
  }

  public function getCommodityAssoc($provider=null) {
    if (!$this->_checkAccess($provider)) throw new ExceptionUserTextStorage('error.accessDenied');

    if (!$provider) $provider = $this->_app->auth->getAllowedProvider(null, 'list');

    $ret = array('event'=>array(),'resource'=>array());

    $s = new SEventTag;
    $s->addStatement(new SqlStatementBi($s->columns['tag'], $this->_id, '%s=%s'));
    $s->addStatement(new SqlStatementMono($s->columns['event_provider'], sprintf('%%s IN (%s)', $provider)));
    $s->setColumnsMask(array('event'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $ret['event'][] = $row['event'];
    }

    $s = new SResourceTag;
    $s->addStatement(new SqlStatementBi($s->columns['tag'], $this->_id, '%s=%s'));
    $s->addStatement(new SqlStatementMono($s->columns['resource_provider'], sprintf('%%s IN (%s)', $provider)));
    $s->setColumnsMask(array('resource'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $ret['resource'][] = $row['resource'];
    }

    $s = new SResourcePoolTag;
    $s->addStatement(new SqlStatementBi($s->columns['tag'], $this->_id, '%s=%s'));
    $s->addStatement(new SqlStatementMono($s->columns['resourcepool_provider'], sprintf('%%s IN (%s)', $provider)));
    $s->setColumnsMask(array('resourcepool'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $ret['resourcePool'][] = $row['resourcepool'];
    }

    return $ret;
  }

  private function _saveCommodityAssoc($params) {
    if (isset($params['event'])) {
      // nactu jiz prirazene akce
      $tagEvent = array();
      $s = new SEventTag;
      $s->addStatement(new SqlStatementBi($s->columns['tag'], $this->_id, '%s=%s'));
      $s->addStatement(new SqlStatementMono($s->columns['event_provider'], sprintf('%%s IN (%s)', $this->_app->auth->getAllowedProvider(null,'list'))));
      $s->setColumnsMask(array('event'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $tagEvent[] = $row['event'];
      }

      // ulozim jeste neprirazene akce
      $savedTagEvent = array();
      foreach ($params['event'] as $event) {
        if (!in_array($event, $tagEvent)) {
          $o = new OEventTag;
          $o->setData(array('event'=>$event,'tag'=>$this->_id));
          $o->save();

          $o = new OEvent($event);
          $oData = $o->getData();
          if (!in_array($oData['provider'],$this->_app->auth->getAllowedProvider(null,'array'))) throw new ExceptionUserTextStorage('error.accessDenied');
          $this->_saveProvider(array('provider'=>$oData['provider']));
        }
        $savedTagEvent[] = $event;
      }

      // smazu neuvedene akce, pokud je treba
      if (!isset($params['addOnly'])||!$params['addOnly']) {
        $s = new SEventTag;
        $s->addStatement(new SqlStatementBi($s->columns['tag'], $this->_id, '%s=%s'));
        $s->addStatement(new SqlStatementMono($s->columns['event_provider'], sprintf('%%s IN (%s)', $this->_app->auth->getAllowedProvider(null,'list'))));
        if (count($savedTagEvent)) $s->addStatement(new SqlStatementMono($s->columns['event'], sprintf('%%s NOT IN (%s)', implode(',',$savedTagEvent))));
        $s->setColumnsMask(array('event','tag'));
        $res = $this->_app->db->doQuery($s->toString());
        while ($row = $this->_app->db->fetchAssoc($res)) {
          $o = new OEventTag(array('event'=>$row['event'],'tag'=>$row['tag']));
          $o->delete();
        }
      }
    }

    if (isset($params['resource'])) {
      // nactu jiz prirazene resource
      $tagResource = array();
      $s = new SResourceTag;
      $s->addStatement(new SqlStatementBi($s->columns['tag'], $this->_id, '%s=%s'));
      $s->addStatement(new SqlStatementMono($s->columns['resource_provider'], sprintf('%%s IN (%s)', $this->_app->auth->getAllowedProvider(null,'list'))));
      $s->setColumnsMask(array('resource'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $tagResource[] = $row['resource'];
      }

      // ulozim jeste neprirazene akce
      $savedTagResource = array();
      foreach ($params['resource'] as $resource) {
        if (!in_array($resource, $tagResource)) {
          $o = new OResourceTag;
          $o->setData(array('resource'=>$resource,'tag'=>$this->_id));
          $o->save();

          $o = new OResource($resource);
          $oData = $o->getData();
          if (!in_array($oData['provider'],$this->_app->auth->getAllowedProvider(null,'array'))) throw new ExceptionUserTextStorage('error.accessDenied');
          $this->_saveProvider(array('provider'=>$oData['provider']));
        }
        $savedTagResource[] = $resource;
      }

      // smazu neuvedene akce, pokud je treba
      if (!isset($params['addOnly'])||!$params['addOnly']) {
        $s = new SResourceTag;
        $s->addStatement(new SqlStatementBi($s->columns['tag'], $this->_id, '%s=%s'));
        $s->addStatement(new SqlStatementMono($s->columns['resource_provider'], sprintf('%%s IN (%s)', $this->_app->auth->getAllowedProvider(null,'list'))));
        if (count($savedTagResource)) $s->addStatement(new SqlStatementMono($s->columns['resource'], sprintf('%%s NOT IN (%s)', implode(',',$savedTagResource))));
        $s->setColumnsMask(array('resource','tag'));
        $res = $this->_app->db->doQuery($s->toString());
        while ($row = $this->_app->db->fetchAssoc($res)) {
          $o = new OResourceTag(array('resource'=>$row['resource'],'tag'=>$row['tag']));
          $o->delete();
        }
      }
    }

    if (isset($params['resourcePool'])) {
      // nactu jiz prirazene resourcepool
      $tagResourcePool = array();
      $s = new SResourcePoolTag;
      $s->addStatement(new SqlStatementBi($s->columns['tag'], $this->_id, '%s=%s'));
      $s->addStatement(new SqlStatementMono($s->columns['resourcepool_provider'], sprintf('%%s IN (%s)', $this->_app->auth->getAllowedProvider(null,'list'))));
      $s->setColumnsMask(array('resourcepool'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $tagResourcePool[] = $row['resourcepool'];
      }

      // ulozim jeste neprirazene akce
      $savedTagResourcePool = array();
      foreach ($params['resourcePool'] as $resourcePool) {
        if (!in_array($resourcePool, $tagResourcePool)) {
          $o = new OResourcePoolTag;
          $o->setData(array('resourcepool'=>$resourcePool,'tag'=>$this->_id));
          $o->save();

          $o = new OResourcePool($resourcePool);
          $oData = $o->getData();
          if (!in_array($oData['provider'],$this->_app->auth->getAllowedProvider(null,'array'))) throw new ExceptionUserTextStorage('error.accessDenied');
          $this->_saveProvider(array('provider'=>$oData['provider']));
        }
        $savedTagResourcePool[] = $resourcePool;
      }

      // smazu neuvedene akce, pokud je treba
      if (!isset($params['addOnly'])||!$params['addOnly']) {
        $s = new SResourcePoolTag;
        $s->addStatement(new SqlStatementBi($s->columns['tag'], $this->_id, '%s=%s'));
        $s->addStatement(new SqlStatementMono($s->columns['resourcepool_provider'], sprintf('%%s IN (%s)', $this->_app->auth->getAllowedProvider(null,'list'))));
        if (count($savedTagResourcePool)) $s->addStatement(new SqlStatementMono($s->columns['resourcepool'], sprintf('%%s NOT IN (%s)', implode(',',$savedTagResourcePool))));
        $s->setColumnsMask(array('resourcepool','tag'));
        $res = $this->_app->db->doQuery($s->toString());
        while ($row = $this->_app->db->fetchAssoc($res)) {
          $o = new OResourcePoolTag(array('resourcepool'=>$row['resourcepool'],'tag'=>$row['tag']));
          $o->delete();
        }
      }
    }
  }

  public function saveCommodityAssoc($params) {
    if (!$this->_checkAccess()) throw new ExceptionUserTextStorage('error.accessDenied');

    $this->_saveCommodityAssoc($params);
  }

  private function _deleteCommodityAssoc($params) {
    if (isset($params['event'])) {
      foreach ($params['event'] as $event) {
        $o = new OEvent($event);
        $oData = $o->getData();
        if (!in_array($oData['provider'], $this->_app->auth->getAllowedProvider(null,'array'))) throw new ExceptionUserTextStorage('error.accessDenied');

        $o = new OEventTag(array('event'=>$event,'tag'=>$this->_id));
        $o->delete();
      }
    }

    if (isset($params['resource'])) {
      foreach ($params['resource'] as $resource) {
        $o = new OResource($resource);
        $oData = $o->getData();
        if (!in_array($oData['provider'], $this->_app->auth->getAllowedProvider(null,'array'))) throw new ExceptionUserTextStorage('error.accessDenied');

        $o = new OResourceTag(array('resource'=>$resource,'tag'=>$this->_id));
        $o->delete();
      }
    }

    if (isset($params['resourcePool'])) {
      foreach ($params['resourcePool'] as $resourcePool) {
        $o = new OResourcePool($resourcePool);
        $oData = $o->getData();
        if (!in_array($oData['provider'], $this->_app->auth->getAllowedProvider(null,'array'))) throw new ExceptionUserTextStorage('error.accessDenied');

        $o = new OResourcePoolTag(array('resourcepool'=>$resourcePool,'tag'=>$this->_id));
        $o->delete();
      }
    }
  }

  public function deleteCommodityAssoc($params) {
    if (!$this->_checkAccess()) throw new ExceptionUserTextStorage('error.accessDenied');

    $this->_deleteCommodityAssoc($params);
  }

  private function _delete() {
    $this->_app->db->beginTransaction();

    $otherEvent = $otherResource = false;
    $allowedProvider = $this->_app->auth->getAllowedProvider(null,'array');

    // nejdriv smazu tag od vsech akci/zdroju daneho poskytovatele
    $s = new SEventTag;
    $s->addStatement(new SqlStatementBi($s->columns['tag'], $this->_id, '%s=%s'));
    $s->setColumnsMask(array('event','event_provider','tag'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      if (in_array($row['event_provider'],$allowedProvider)) {
        $o = new OEventTag(array('event'=>$row['event'],'tag'=>$row['tag']));
        $o->delete();
      } else $otherEvent = true;
    }
    $s = new SResourceTag;
    $s->addStatement(new SqlStatementBi($s->columns['tag'], $this->_id, '%s=%s'));
    $s->setColumnsMask(array('resource','resource_provider','tag'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      if (in_array($row['resource_provider'],$allowedProvider)) {
        $o = new OResourceTag(array('resource'=>$row['resource'],'tag'=>$row['tag']));
        $o->delete();
      } else $otherResource = true;
    }
    $s = new STagProvider;
    $s->addStatement(new SqlStatementBi($s->columns['tag'], $this->_id, '%s=%s'));
    $s->addStatement(new SqlStatementMono($s->columns['provider'], sprintf('%%s IN (%s)', $this->_app->auth->getAllowedProvider(null,'list'))));
    $s->setColumnsMask(array('tag','provider'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $o = new OTagProvider(array('tag' => $row['tag'], 'provider' => $row['provider']));
      $o->delete();
    }
    
    // pokud neni tag registrovan u jineho poskytovatele, tak ho smazu uplne
    if (!$otherEvent&&!$otherResource) {
      $o = new OTag($this->_id);
      $o->delete();
    }
    
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

    $this->_app->db->beginTransaction();

    $ret = $this->_data['name'];
    
    $newTag = new BTag;
    $this->_data['name'] .= ' (kopie)';
    $this->_data['commodity'] = $this->getCommodityAssoc();
    $newTag->save($this->_data);

    $this->_app->db->commitTransaction();
    
    return $ret;
  }
}

?>
