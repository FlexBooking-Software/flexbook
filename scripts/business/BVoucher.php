<?php

class BVoucher extends BusinessObject {

  private function _checkAccess($params=array()) {
    $this->_load();
    
    $provider = ifsetor($params['providerId'],$this->_data['providerId']);
    
    $ret = $this->_app->auth->haveRight('commodity_admin', $provider);
    
    return $ret;
  }

  private function _checkBeforeSave($params) {
    if (!$this->_id) {
      if (!isset($params['name'])) throw new ExceptionUserTextStorage('error.saveVoucher_emptyName');
      if (!isset($params['providerId'])) throw new ExceptionUserTextStorage('error.saveVoucher_emptyProvider');
      if (!isset($params['discountAmount'])&&!isset($params['discountProportion'])) throw new ExceptionUserTextStorage('error.saveVoucher_emptyDiscount');
    }
    
    // tyto nesmi byt prazdny nikdy
    if (isset($params['name'])&&!$params['name']) throw new ExceptionUserTextStorage('error.saveVoucher_emptyName');
    if (isset($params['code'])&&!$params['code']) throw new ExceptionUserTextStorage('error.saveVoucher_emptyCode');
    if (isset($params['discountAmount'])&&isset($params['discountProportion'])) {
      if (!$params['discountProportion']&&!$params['discountAmount']) throw new ExceptionUserTextStorage('error.saveVoucher_emptyDiscount');
      if ($params['discountProportion']&&$params['discountAmount']) throw new ExceptionUserTextStorage('error.saveVoucher_multipleDiscount');
    }
    if (!isset($params['providerId'])) throw new ExceptionUserTextStorage('error.saveVoucher_emptyProvider');

    if (isset($params['discountProportion'])&&($params['discountProportion']>100)) throw new ExceptionUserTextStorage('error.saveVoucher_invalidDiscount');

    if (isset($params['code'])) {
      // test na unikatnost kodu
      $s = new SVoucher;
      $s->addStatement(new SqlStatementBi($s->columns['code'], $params['code'], '%s=%s'));
      $s->addStatement(new SqlStatementBi($s->columns['voucher_id'], $this->_id, '%s<>%s'));
      $s->addStatement(new SqlStatementBi($s->columns['provider'], ifsetor($params['providerId'],$this->_data['providerId']), '%s=%s'));
      $s->setColumnsMask(array('voucher_id'));
      $result = $this->_app->db->doQuery($s->toString());
      if ($this->_app->db->getRowsNumber($result) > 0) throw new ExceptionUserTextStorage('error.saveVoucher_codeNotUnique');

      // kod nejde menit, pokud je uz voucher pouzit
      if ($this->_id&&strcmp($params['code'],$this->_data['code'])) {
        $s = new SReservation;
        $s->addStatement(new SqlStatementBi($s->columns['voucher'], $this->_id, '%s=%s'));
        $s->setColumnsMask(array('reservation_id','number'));
        $res = $this->_app->db->doQuery($s->toString());
        if ($row = $this->_app->db->fetchAssoc($res)) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveVoucher_reservationExists'), $row['number']));
      }
    }
  }
  
  private function _checkBeforeDelete() {
    $s = new SReservation;
    $s->addStatement(new SqlStatementBi($s->columns['voucher'], $this->_id, '%s=%s'));
    $s->setColumnsMask(array('reservation_id','number'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($row = $this->_app->db->fetchAssoc($res)) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.deleteVoucher_reservationExists'), $row['number']));
  }

  protected function _load() {
    if ($this->_id&&!$this->_loaded) {
      $oVoucher = new OVoucher($this->_id);
      $data = $oVoucher->getData();
      $returnData['id'] = $data['voucher_id'];
      $returnData['name'] = $data['name'];
      $returnData['code'] = $data['code'];
      
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
      $returnData['validityFrom'] = $data['validity_from'];
      $returnData['validityTo'] = $data['validity_to'];
      $returnData['discountAmount'] = $data['discount_amount'];
      $returnData['discountProportion'] = $data['discount_proportion'];
      $returnData['applicationTotal'] = $data['application_total'];
      $returnData['applicationUser'] = $data['application_user'];
      $returnData['active'] = $data['active'];
      
      $returnData['subjectTag'] = '';
      if ($data['subject_tag']) {
        $s = new STag;
        $s->addStatement(new SqlStatementMono($s->columns['tag_id'], sprintf('%%s IN (%s)', $data['subject_tag'])));
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
    
    $o = new OVoucher($this->_id);
    $o->setData(array('subject_tag'=>$tagId));
    $o->save();
  }

  private function _save($params) {
    $this->_app->db->beginTransaction();

    $o = new OVoucher($this->_id?$this->_id:null);
    $oData = array();
    if (isset($params['providerId'])) $oData['provider'] = $params['providerId'];
    if (isset($params['name'])) $oData['name'] = $params['name'];
    if (isset($params['code'])) $oData['code'] = strtoupper($params['code']);
    if (isset($params['center'])) $oData['center'] = $params['center']?$params['center']:null;
    if (isset($params['validityFrom'])) $oData['validity_from'] = $params['validityFrom']?$params['validityFrom']:null;
    if (isset($params['validityTo'])) $oData['validity_to'] = $params['validityTo']?$params['validityTo']:null;
    if (isset($params['discountAmount'])) $oData['discount_amount'] = $params['discountAmount']?$params['discountAmount']:null;
    if (isset($params['discountProportion'])) $oData['discount_proportion'] = $params['discountProportion']?$params['discountProportion']:null;
    if (isset($params['applicationTotal'])) $oData['application_total'] = $params['applicationTotal']?$params['applicationTotal']:null;
    if (isset($params['applicationUser'])) $oData['application_user'] = $params['applicationUser']?$params['applicationUser']:null;
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

    $this->_load();

    $this->_checkBeforeSave($params);
    
    $this->_save($params);
  }
  
  private function _delete() {
    $this->_app->db->beginTransaction();
    
    $o = new OVoucher($this->_id);
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
    
    $newVoucher = new BVoucher;
    $this->_data['name'] .= ' (kopie)';
    $this->_data['code'] .= '-copy';
    $newVoucher->save($this->_data);
    
    return $ret;
  }

  public function getDiscount($price) {
    $ret = null;

    $this->_load();

    if ($price) {
      $ret = $this->_data['discountAmount']?$this->_data['discountAmount']:round($price*($this->_data['discountProportion']/100),2);
      if ($ret>$price) $ret = $price;
    }

    return $ret;
  }
}

?>
