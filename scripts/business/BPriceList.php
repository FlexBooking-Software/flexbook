<?php

class BPriceList extends BusinessObject {

  private function _checkAccess($params=array()) {
    $this->_load();
    
    $provider = ifsetor($params['providerId'],$this->_data['providerId']);
    
    $ret = $this->_app->auth->haveRight('commodity_admin', $provider);
    
    return $ret;
  }

  private function _checkBeforeSave($params) {
    if (!$this->_id) {
      if (!isset($params['name'])) throw new ExceptionUserTextStorage('error.savePriceList_emptyName');
      if (!isset($params['providerId'])) throw new ExceptionUserTextStorage('error.savePriceList_emptyProvider');
    }
    
    // tyto nesmi byt prazdny nikdy
    if (isset($params['name'])&&!$params['name']) throw new ExceptionUserTextStorage('error.savePriceList_emptyName');
    if (!isset($params['providerId'])) throw new ExceptionUserTextStorage('error.savePriceList_emptyProvider');
    
    // kontrola intervalu sezon
  }
  
  private function _checkBeforeDelete() {
    $s = new SResource;
    $s->addStatement(new SqlStatementBi($s->columns['pricelist'], $this->_id, '%s=%s'));
    $s->setColumnsMask(array('resource_id','name'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($row = $this->_app->db->fetchAssoc($res)) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.deletePriceList_resourceExists'), $row['name']));
  }

  protected function _load() {
    if ($this->_id&&!$this->_loaded) {
      $oPriceList = new OPriceList($this->_id);
      $data = $oPriceList->getData();
      $returnData['id'] = $data['pricelist_id'];
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
      
      $returnData['season'] = array();
      $s = new SSeason;
      $s->addStatement(new SqlStatementBi($s->columns['pricelist'], $this->_id, '%s=%s'));
      $s->addOrder(new SqlStatementAsc($s->columns['start']));
      $s->setColumnsMask(array('season_id','name','start','end','base_price',
                        'mon_price','tue_price','wed_price','thu_price','fri_price',
                        'sat_price','sun_price'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $returnData['season'][] = array(
                          'seasonId'        => $row['season_id'],
                          'name'            => $row['name'],
                          'start'           => $row['start'],
                          'end'             => $row['end'],
                          'basePrice'       => $row['base_price'],
                          'monPrice'        => $row['mon_price'],
                          'tuePrice'        => $row['tue_price'],
                          'wedPrice'        => $row['wed_price'],
                          'thuPrice'        => $row['thu_price'],
                          'friPrice'        => $row['fri_price'],
                          'satPrice'        => $row['sat_price'],
                          'sunPrice'        => $row['sun_price'],
                          );
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
  
  private function _saveSeason($params) {
    if (isset($params['season'])) {
      $ids = array();
      
      foreach ($params['season'] as $season) {
        $o = new OSeason(ifsetor($item['seasonId']));
        $o->setData(array(
                'pricelist'   => $this->_id,
                'name'        => $season['name'],
                'start'       => $season['start'],
                'end'         => $season['end'],
                'base_price'  => $season['basePrice'],
                'mon_price'   => $season['monPrice'],
                'tue_price'   => $season['tuePrice'],
                'wed_price'   => $season['wedPrice'],
                'thu_price'   => $season['thuPrice'],
                'fri_price'   => $season['friPrice'],
                'sat_price'   => $season['satPrice'],
                'sun_price'   => $season['sunPrice'],
                ));
        $o->save();
        
        $ids[] = $o->getId();
      }
      
      $ids = implode(',',$ids);
      $s = new SSeason;
      $s->addStatement(new SqlStatementBi($s->columns['pricelist'], $this->_id, '%s=%s'));
      if ($ids) $s->addStatement(new SqlStatementMono($s->columns['season_id'], sprintf('%%s NOT IN (%s)', $ids)));
      $s->setColumnsMask(array('season_id'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $o = new OSeason($row['season_id']);
        $o->delete();
      }
    }
  }

  private function _save($params) {
    $this->_load();
    
    $this->_app->db->beginTransaction();

    $o = new OPriceList($this->_id?$this->_id:null);
    $oData = array();
    if (isset($params['providerId'])) $oData['provider'] = $params['providerId'];
    if (isset($params['name'])) $oData['name'] = $params['name'];
    
    if (count($oData)) {
      $o->setData($oData);
      $o->save();
    }
    $this->_id = $o->getId();
    
    $this->_saveSeason($params);

    $this->_app->db->commitTransaction();
  }

  public function save($params) {
    if (!$this->_checkAccess($params)) throw new ExceptionUserTextStorage('error.accessDenied');
    
    $this->_checkBeforeSave($params);
    
    $this->_save($params);
  }
  
  private function _delete() {
    $this->_app->db->beginTransaction();
    
    $o = new OPriceList($this->_id);
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
    
    $newPriceList = new BPriceList;
    $this->_data['name'] .= ' (kopie)';
    $newPriceList->save($this->_data);
    
    return $ret;
  }
}

?>
