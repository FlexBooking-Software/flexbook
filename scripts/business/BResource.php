<?php

class BResource extends BusinessObject {

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
      if (!isset($params['centerId'])) throw new ExceptionUserTextStorage('error.saveResource_emptyCenter');
      if (!isset($params['price'])) throw new ExceptionUserTextStorage('error.saveResource_emptyPrice');
      if (!isset($params['availProfile'])) throw new ExceptionUserTextStorage('error.saveResource_emptyAvailProfile');
      if (!isset($params['unitProfile'])) throw new ExceptionUserTextStorage('error.saveResource_emptyUnitProfile');
    }
    
    // tyto nesmi byt prazdny nikdy
    if (isset($params['name'])&&!$params['name']) throw new ExceptionUserTextStorage('error.saveResource_emptyName');
    if (isset($params['providerId'])&&!$params['providerId']) throw new ExceptionUserTextStorage('error.saveResource_emptyProvider');
    if (isset($params['centerId'])&&!$params['centerId']) throw new ExceptionUserTextStorage('error.saveResource_emptyCenter');    
    if (isset($params['price'])&&($params['price']=='')) throw new ExceptionUserTextStorage('error.saveResource_emptyPrice');
    if (isset($params['availProfile'])&&!$params['availProfile']) throw new ExceptionUserTextStorage('error.saveResource_emptyAvailProfile');
    if (isset($params['unitProfile'])&&!$params['unitProfile']) throw new ExceptionUserTextStorage('error.saveResource_emptyUnitProfile');
    
    if ($this->_id) {
      // pokud existuje na zdroj rezervace, uz nejde menit nektere parametry
      $s = new SReservation;
      $s->addStatement(new SqlStatementBi($s->columns['resource'], $this->_id, '%s=%s'));
      $s->addStatement(new SqlStatementMono($s->columns['start'], '%s>=NOW()'));
      $s->addStatement(new SqlStatementMono($s->columns['failed'], '%s IS NULL'));
      $s->addStatement(new SqlStatementMono($s->columns['cancelled'], '%s IS NULL'));
      $s->setColumnsMask(array('reservation_id'));
      $res = $this->_app->db->doQuery($s->toString());
      if ($this->_app->db->getRowsNumber($res)) {
        $this->_load();
        
        if ((isset($params['providerId'])&&($params['providerId']!=$this->_data['providerId']))||
            (isset($params['centerId'])&&($params['centerId']!=$this->_data['centerId']))) {
          throw new ExceptionUserTextStorage('error.saveResource_reservationExists');
        }
      } 
    }
    
    // test na unikatnost
    if (isset($params['externalId'])&&$params['externalId']) {
      $s = new SResource;
      $s->addStatement(new SqlStatementBi($s->columns['external_id'], $params['externalId'], '%s=%s'));
      $s->addStatement(new SqlStatementBi($s->columns['resource_id'], $this->_id, '%s<>%s'));
      $s->addStatement(new SqlStatementBi($s->columns['provider'], $params['providerId'], '%s=%s'));
      $s->setColumnsMask(array('resource_id'));
      $result = $this->_app->db->doQuery($s->toString());
      if ($this->_app->db->getRowsNumber($result) > 0) throw new ExceptionUserTextStorage('error.saveResource_externalIdNotUnique');
    }
  }
  
  private function _checkPool($params) {
    // musim zkontrolovat vsechny skupiny zdroju s timto zdrojem, jestli neobsahuji jine zdroje, nez ty, co se aktualne ukladaji
    
    // kdyz se nemeni cenik, rezervacni jednotky nebo cena, tak nemusim kontrolovat nic
    if (!$this->_id||(
        (!isset($params['price'])||($params['price']==$this->_data['price']))&&
        (!isset($params['priceList'])||($params['priceList']==$this->_data['priceList']))&&
        (!isset($params['unitProfile'])||($params['unitProfile']==$this->_data['unitProfile']))
        )) return;
    #error_log($this->_id);
    #error_log(var_export($params,true));error_log(var_export($this->_data,true));
    
    if (!isset($params['otherId'])) $params['otherId'] = array();
    $s = new SResourcePool;
    $s->addStatement(new SqlStatementBi($s->columns['resource'], $this->_id, '%s=%s'));
    $s->setColumnsMask(array('name','s_resource_all'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $poolResource = explode(',',$row['s_resource_all']);
      if (count(array_diff($poolResource,$params['otherId']))) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveResource_poolConflict'), $row['name']));
    }
  }
  
  protected function _checkBeforeDelete() {
    $s = new SReservation;
    $s->addStatement(new SqlStatementBi($s->columns['resource'], $this->_id, '%s=%s'));
    $s->setColumnsMask(array('number'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($row = $this->_app->db->fetchAssoc($res)) {
      throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.deleteResource_reservationExists'), $row['number']));
    }
    
    $s = new SEvent;
    $s->addStatement(new SqlStatementBi($s->columns['resource'], $this->_id, '%s=%s'));
    $s->setColumnsMask(array('name'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($row = $this->_app->db->fetchAssoc($res)) {
      throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.deleteResource_eventExists'), $row['name']));
    }
  }
  
  protected function _checkBeforeDisable() {
    $s = new SReservation;
    $s->addStatement(new SqlStatementBi($s->columns['resource'], $this->_id, '%s=%s'));
    $s->addStatement(new SqlStatementMono($s->columns['cancelled'], '%s IS NULL'));
    $s->addStatement(new SqlStatementMono($s->columns['failed'], '%s IS NULL'));
    $s->addStatement(new SqlStatementMono($s->columns['start'], '%s>=NOW()'));
    $s->setColumnsMask(array('number'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($row = $this->_app->db->fetchAssoc($res)) {
      throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.disableResource_reservationExists'), $row['number']));
    }
  }

  protected function _load() {
    if ($this->_id&&!$this->_loaded) {
      $oResource = new OResource($this->_id);
      $data = $oResource->getData();
      $returnData['id'] = $data['resource_id'];
      $returnData['externalId'] = $data['external_id'];
      $returnData['name'] = $data['name'];
      $returnData['description'] = $data['description'];
      $returnData['price'] = $data['price'];
      $returnData['priceList'] = $data['pricelist'];
      $returnData['accountTypeId'] = $data['accounttype'];
      $returnData['reservationConditionId'] = $data['reservationcondition'];
      $returnData['notificationTemplateId'] = $data['notificationtemplate'];
      $returnData['documentTemplateId'] = $data['documenttemplate'];
      $returnData['active'] = $data['active'];
      $returnData['availProfile'] = $data['availabilityprofile'];
      $returnData['availExProfile'] = $data['availabilityexceptionprofile'];
      $returnData['unitProfile'] = $data['unitprofile'];

      $returnData['feAllowedPayment'] = array();
      if (1&$data['fe_allowed_payment']) $returnData['feAllowedPayment'][] = 'credit';
      if (10&$data['fe_allowed_payment']) $returnData['feAllowedPayment'][] = 'ticket';
      if (100&$data['fe_allowed_payment']) $returnData['feAllowedPayment'][] = 'online';
      
      $returnData['urlDescription'] = $data['url_description'];
      $returnData['urlPrice'] = $data['url_price'];
      $returnData['urlOpening'] = $data['url_opening'];
      $returnData['urlPhoto'] = $data['url_photo'];
      
      $returnData['centerId'] = $data['center'];  
      if ($data['center']) {
        $s = new SCenter;
        $s->addStatement(new SqlStatementBi($s->columns['center_id'], $data['center'], '%s=%s'));
        $s->setColumnsMask(array('name','street','city','postal_code','state'));
        $res = $this->_app->db->doQuery($s->toString());
        $oCData = $this->_app->db->fetchAssoc($res);
      
        $returnData['centerName'] = $oCData['name'];
        $returnData['street'] = $oCData['street'];
        $returnData['city'] = $oCData['city'];
        $returnData['postalCode'] = $oCData['postal_code'];
        $returnData['state'] = $oCData['state'];
      }

      $returnData['organiserId'] = $data['organiser'];
      if ($data['organiser']) {
        $s = new SOrganiser;
        $s->addStatement(new SqlStatementBi($s->columns['user_id'], $data['organiser'], '%s=%s'));
        $s->setColumnsMask(array('fullname','email'));
        $res = $this->_app->db->doQuery($s->toString());
        $oPData = $this->_app->db->fetchAssoc($res);

        $returnData['organiserName'] = $oPData['fullname'];
        $returnData['organiserEmail'] = $oPData['email'];
      }
      
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
      
      $returnData['tag'] = '';
      $s = new SResourceTag;
      $s->addStatement(new SqlStatementBi($s->columns['resource'], $this->_id, '%s=%s'));
      $s->setColumnsMask(array('name'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        if ($returnData['tag']) $returnData['tag'] .= ',';
        $returnData['tag'] .= $row['name'];
      }
      
      $returnData['portal'] = [];
      $s = new SResource;
      $s->addStatement(new SqlStatementBi($s->columns['resource_id'], $this->_id, '%s=%s'));
      $s->setColumnsMask(array('portal'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $returnData['portal'][] = $row['portal'];
      }
      
      $returnData['attribute'] = array();
      $s = new SResourceAttribute;
      $s->addStatement(new SqlStatementBi($s->columns['resource'], $returnData['id'], '%s=%s'));
      $s->addStatement(new SqlStatementMono($s->columns['applicable'], "%s='COMMODITY'"));
      //$s->addStatement(new SqlStatementMono($s->columns['disabled'], "%s<>'Y'"));
      #$s->addOrder(new SqlStatementAsc($s->columns['category']));
      $s->addOrder(new SqlStatementAsc($s->columns['sequence']));
      $s->setColumnsMask(array('attribute_id','short_name','restricted','category','sequence','type','allowed_values','disabled','value'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $s1 = new SAttributeName;
        $s1->addStatement(new SqlStatementBi($s1->columns['attribute'], $row['attribute_id'], '%s=%s'));
        $s1->setColumnsMask(array('lang','name'));
        $res1 = $this->_app->db->doQuery($s1->toString());
        $name = array(); while ($row1 = $this->_app->db->fetchAssoc($res1)) { $name[$row1['lang']] = $row1['name']; }
        
        $returnData['attribute'][$row['attribute_id']] = array(
              'attributeId'       => $row['attribute_id'],
              'applicable'        => 'COMMODITY',
              'name'              => $name,
              'short'             => $row['short_name'],
              'restricted'        => $row['restricted'],
              'category'          => $row['category'],
              'sequence'          => $row['sequence'],
              'type'              => $row['type'],
              'allowedValues'     => $row['allowed_values'],
              'disabled'          => $row['disabled'],
              'value'             => $row['value'],
              );
        
        if (!strcmp($row['type'],'FILE')) {
          $s2 = new SFile;
          $s2->addStatement(new SqlStatementBi($s2->columns['file_id'], $row['value'], '%s=%s'));
          $s2->setColumnsMask(array('file_id','hash','name'));
          $res2 = $this->_app->db->doQuery($s2->toString());
          $row2 = $this->_app->db->fetchAssoc($res2);

          $returnData['attribute'][$row['attribute_id']]['valueDbId'] = $row2['file_id'];
          $returnData['attribute'][$row['attribute_id']]['valueId'] = $row2['hash'];
          $returnData['attribute'][$row['attribute_id']]['value'] = $row2['name'];
        }
      }
      
      $returnData['reservationAttribute'] = array();
      $s = new SResourceAttribute;
      $s->addStatement(new SqlStatementBi($s->columns['resource'], $returnData['id'], '%s=%s'));
      $s->addStatement(new SqlStatementMono($s->columns['applicable'], "%s='RESERVATION'"));
      //$s->addStatement(new SqlStatementMono($s->columns['disabled'], "%s<>'Y'"));
      $s->addOrder(new SqlStatementAsc($s->columns['category']));
      $s->addOrder(new SqlStatementAsc($s->columns['sequence']));
      $s->setColumnsMask(array('attribute_id','short_name','restricted','mandatory','category','sequence','type','allowed_values','disabled'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $s1 = new SAttributeName;
        $s1->addStatement(new SqlStatementBi($s1->columns['attribute'], $row['attribute_id'], '%s=%s'));
        $s1->setColumnsMask(array('lang','name'));
        $res1 = $this->_app->db->doQuery($s1->toString());
        $name = array(); while ($row1 = $this->_app->db->fetchAssoc($res1)) { $name[$row1['lang']] = $row1['name']; }
        
        $returnData['reservationAttribute'][$row['attribute_id']] = array(
              'attributeId'       => $row['attribute_id'],
              'applicable'        => 'RESERVATION',
              'name'              => $name,
              'short'             => $row['short_name'],
              'restricted'        => $row['restricted'],
              'mandatory'         => $row['mandatory'],
              'category'          => $row['category'],
              'sequence'          => $row['sequence'],
              'type'              => $row['type'],
              'allowedValues'     => $row['allowed_values'],
              'disabled'          => $row['disabled'],
              );
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
  
  public function getAttribute($applicable='COMMODITY', $language=null, $includeRestricted=false, $includeDisabled=false) {
    $attribute = array();
    
    // nactu vsechny atributy evidovane u zdroje
    $s = new SResourceAttribute;
    $s->addStatement(new SqlStatementBi($s->columns['resource'], $this->_id, '%s=%s'));
    $s->addStatement(new SqlStatementBi($s->columns['applicable'], $applicable, '%s=%s'));
    if (!$includeDisabled) $s->addStatement(new SqlStatementMono($s->columns['disabled'], "%s<>'Y'"));
    if (!$includeRestricted) $s->addStatement(new SqlStatementMono($s->columns['restricted'], '%s IS NULL'));
    elseif (!strcmp($includeRestricted,'INTERNAL')) $s->addStatement(new SqlStatementBi($s->columns['restricted'], $s->columns['restricted'], "(%s IS NULL OR %s='INTERNAL')"));
    elseif (!strcmp($includeRestricted,'READONLY')) $s->addStatement(new SqlStatementBi($s->columns['restricted'], $s->columns['restricted'], "(%S IS NULL OR %s='READONLY')"));
    #$s->addOrder(new SqlStatementAsc($s->columns['category']));
    $s->addOrder(new SqlStatementAsc($s->columns['sequence']));
    $s->setColumnsMask(array('attribute_id','value','url','restricted','mandatory','category','sequence','type','allowed_values'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $s1 = new SAttributeName;
      $s1->addStatement(new SqlStatementBi($s1->columns['attribute'], $row['attribute_id'], '%s=%s'));
      $s1->setColumnsMask(array('lang','name'));
      $res1 = $this->_app->db->doQuery($s1->toString());
      $name = array(); while ($row1 = $this->_app->db->fetchAssoc($res1)) { $name[$row1['lang']] = $row1['name']; }
      $name = ifsetor($name[$language], array_values($name)[0]);
      
      $attribute[$row['attribute_id']] = array(
            'attributeId'       => $row['attribute_id'],
            'name'              => $name,
            'url'               => $row['url'],
            'restricted'        => $row['restricted'],
            'mandatory'         => $row['mandatory'],
            'category'          => $row['category'],
            'sequence'          => $row['sequence'],
            'type'              => $row['type'],
            'allowedValues'     => $row['allowed_values'],
            'value'             => $row['value'],
            );
      
      if (!strcmp($row['type'],'FILE')&&$row['value']) {
        $s2 = new SFile;
        $s2->addStatement(new SqlStatementBi($s2->columns['file_id'], $row['value'], '%s=%s'));
        $s2->setColumnsMask(array('file_id','hash','name'));
        $res2 = $this->_app->db->doQuery($s2->toString());
        $row2 = $this->_app->db->fetchAssoc($res2);

        $attribute[$row['attribute_id']]['valueDbId'] = $row2['file_id'];
        $attribute[$row['attribute_id']]['valueId'] = $row2['hash'];
        $attribute[$row['attribute_id']]['value'] = $row2['name'];
      }
    }
    
    return $attribute;
  }

  private function _saveTag($params) {
    if (isset($params['tag'])) {
      // zjistim existujici tagy
      $resourceTags = array();
      $s = new SResourceTag;
      $s->addStatement(new SqlStatementBi($s->columns['resource'], $this->_id, '%s=%s'));
      $s->setColumnsMask(array('resource','tag','name'));
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
          
          $o = new OResourceTag;
          $o->setData(array('resource'=>$this->_id,'tag'=>$b->getId()));
          $o->save();
          
          $resourceSavedTags[$b->getId()] = $t;
        } else $resourceSavedTags[$resourceTags[$t]] = $t; 
      }
      
      // smazu tagy, ktere uz nemaji byt
      if (!isset($params['tagAddOnly'])||!$params['tagAddOnly']) {
        $s = new SResourceTag;
        $s->addStatement(new SqlStatementBi($s->columns['resource'], $this->_id, '%s=%s'));
        if (count($resourceSavedTags)) $s->addStatement(new SqlStatementMono($s->columns['tag'], sprintf('%%s NOT IN (%s)', implode(',',array_keys($resourceSavedTags)))));
        $s->setColumnsMask(array('resource','tag'));
        $res = $this->_app->db->doQuery($s->toString());
        while ($row = $this->_app->db->fetchAssoc($res)) {
          $o = new OResourceTag(array('resource'=>$row['resource'],'tag'=>$row['tag']));
          $o->delete();
        }
      }
    }
  }
  
  private function _savePortal($params) {
    if (isset($params['portal'])) {
      // smazu puvodni portaly
      $s = new SResource;
      $s->addStatement(new SqlStatementBi($s->columns['resource_id'], $this->_id, '%s=%s'));
      $s->setColumnsMask(array('resource_id','portal'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $o = new OResourcePortal(array('resource'=>$row['resource_id'],'portal'=>$row['portal']));
        $o->delete();
      }
      
      foreach ($params['portal'] as $p) {
        if (!$p) continue;
        
        $o = new OResourcePortal;
        $o->setData(array('resource'=>$this->_id,'portal'=>$p));
        $o->save();
      }
    }
  }
  
  private function _cancelReservation($params) {
    if (isset($params['active'])&&($params['active']=='N')&&isset($params['cancelReservation'])&&($params['cancelReservation']=='Y')) {
      $s = new SReservation;
      $s->addStatement(new SqlStatementBi($s->columns['resource'], $this->_id, '%s=%s'));
      $s->addStatement(new SqlStatementMono($s->columns['cancelled'], '%s IS NULL'));
      $s->setColumnsMask(array('reservation_id','provider','number','user_email'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $b = new BReservation($row['reservation_id']);
        if (isset($params['refundReservation'])&&$params['refundReservation']) $b->cancelWithRefund(true);
        else $b->cancel(true);
      }
    }
  }
  
  private function _validateAttribute($params) {
    global $TMP_DIR;
    
    $extendedAttributes = array();
    
    foreach ($params['attribute'] as $id=>$value) {
      $s = new SAttribute;
      $s->addStatement(new SqlStatementBi($s->columns['attribute_id'], $id, '%s=%s'));
      $s->setColumnsMask(array('attribute_id','type','mandatory','allowed_values'));
      $res = $this->_app->db->doQuery($s->toString());
      $row = $this->_app->db->fetchAssoc($res);
      
      $attribute = array('id'=>$id,'type'=>$row['type'],'value'=>$value);
      
      if (isset($params['attributeValidation'])&&$params['attributeValidation']) {      
        $s1 = new SAttributeName;
        $s1->addStatement(new SqlStatementBi($s1->columns['attribute'], $row['attribute_id'], '%s=%s'));
        $s1->setColumnsMask(array('lang','name'));
        $res1 = $this->_app->db->doQuery($s1->toString());
        $name = array(); while ($row1 = $this->_app->db->fetchAssoc($res1)) { $name[$row1['lang']] = $row1['name']; }
        $name = ifsetor($name[ifsetor($params['attributeLanguage'])], array_values($name)[0]);
        if (($row['mandatory']=='Y')&&!strcmp($params['attributeValidation'],'exact')&&!$value)
          throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveUser_attributeMissingValue'), $name));
        
        if ($value) {
          $notConverted = isset($params['attributeConverted'])&&!$params['attributeConverted'];
          switch ($row['type']) {
            case 'DATE': if ($notConverted) {
                            if (!$this->_app->regionalSettings->checkHumanDate($value)) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveUser_attributeInvalidDate'), $name));
                            $attribute['value'] = $this->_app->regionalSettings->convertHumanToDate($value);
                         } elseif (!$this->_app->regionalSettings->checkDate($value)) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveUser_attributeInvalidDate'), $name));
                         break;
            case 'TIME': if ($notConverted) {
                            if (!$this->_app->regionalSettings->checkHumanTime($value,'h:m')) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveUser_attributeInvalidTime'), $name));
                            $attribute['value'] = $this->_app->regionalSettings->convertHumanToTime($value,'h:m');
                         } elseif (!$this->_app->regionalSettings->checkTime($value)) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveUser_attributeInvalidTime'), $name));
                         break;
            case 'DATETIME':
                         if ($notConverted) {
                            if (!$this->_app->regionalSettings->checkHumanDateTime($value)) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveUser_attributeInvalidDatetime'), $name));
                            $attribute['value'] = $this->_app->regionalSettings->convertHumanToDateTime($value);
                         } elseif (!$this->_app->regionalSettings->checkDateTime($value)) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveUser_attributeInvalidDatetime'), $name));
                         break;
            case 'NUMBER':
                         if ($notConverted) {
                            if (!$this->_app->regionalSettings->checkHumanNumber($value,20)) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveUser_attributeInvalidNumber'), $name));
                            $attribute['value'] = $this->_app->regionalSettings->convertHumanToNumber($value,20);
                         } elseif (!$this->_app->regionalSettings->checkNumber($value)) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveUser_attributeInvalidNumber'), $name));
                         break;
            case 'DECIMALNUMBER':
                         if ($notConverted) {
                           if (!$this->_app->regionalSettings->checkHumanNumber($value,20,2)) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveUser_attributeInvalidDecimalNumber'), $name));
                           $attribute['value'] = $this->_app->regionalSettings->convertHumanToNumber($value,20,2);
                         } elseif (!$this->_app->regionalSettings->checkNumber($value)) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveUser_attributeInvalidDecimalNumber'), $name));
                         break;
            case 'LIST': if ($value&&!in_array($value,explode(',',$row['allowed_values'])))
                            throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveUser_attributeInvalidList'), $name));
                         break;
            case 'FILE': if ($value&&strcmp($value,'__no_change__')&&!file_exists($TMP_DIR.$value))
                            throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveUser_attributeInvalidFile'), $name));
                         break;
            default: break;
          }
        }
      }
      
      $extendedAttributes[$id] = $attribute;
    }
    
    return $extendedAttributes;
  }
  
  private function _saveAttribute($params) {
    $attributes = $this->_validateAttribute($params);
    
    $idsToSave = array();
    foreach ($attributes as $id=>$attribute) {
      $s = new SResourceAttribute;
      $s->addStatement(new SqlStatementBi($s->columns['attribute'], $id, '%s=%s'));
      $s->addStatement(new SqlStatementBi($s->columns['resource'], $this->_id, '%s=%s'));
      $s->setColumnsMask(array('attribute','value'));
      $res = $this->_app->db->doQuery($s->toString());
      $row = $this->_app->db->fetchAssoc($res);
      
      // pokud je atribut typu soubor
      if (!strcmp($attribute['type'],'FILE')) {
        if (!$attribute['value']) {
          $attribute['value'] = null;
        } elseif (!strcmp($attribute['value'],'__no_change__')) {
          // kdyz se nic nemeni
          $attribute['value'] = $row['value'];
        } else {
          // jinak ulozim nejdriv soubor
          $bF = new BFile(isset($row['value'])?$row['value']:null);
          $fileParams = $bF->getFileFromLink($attribute['value'], true);
          if (isset($params['keepFile'])) $fileParams['keepFile'] = $params['keepFile'];
          $fileId = $bF->save($fileParams);

          $attribute[ 'value'] = $fileId;
        }
      }
      
      if ($row) {
        $o = new OResourceAttribute(array('resource'=>$this->_id,'attribute'=>$id));
        $o->setData(array('value'=>$attribute['value']));
        $o->save();
      } else {
        $o = new OResourceAttribute;
        $o->setData(array('resource'=>$this->_id,'attribute'=>$id,'value'=>$attribute['value']));
        $o->save();
      }
      $idsToSave[] = $id;
    }
    
    if (!isset($params['attributeAddOnly'])||!$params['attributeAddOnly']) {
      $s = new SResourceAttribute;
      $s->addStatement(new SqlStatementBi($s->columns['resource'], $this->_id, '%s=%s'));
      $s->addStatement(new SqlStatementMono($s->columns['applicable'], "%s='COMMODITY'"));
      if (count($idsToSave)) $s->addStatement(new SqlStatementMono($s->columns['attribute'], sprintf('%%s NOT IN (%s)', implode(',', $idsToSave))));
      $s->setColumnsMask(array('attribute','value','type'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $o = new OResourceAttribute(array('resource'=>$this->_id, 'attribute'=>$row['attribute']));
        $o->delete();
        if (!strcmp($row['type'],'FILE')) {
          $o = new OFile($row['value']);
          $o->delete();
        }
      }
    }
  }
  
  private function _saveReservationAttribute($params) {
    $idsToSave = array();
    foreach ($params['reservationAttribute'] as $id=>$attribute) {
      $s = new SResourceAttribute;
      $s->addStatement(new SqlStatementBi($s->columns['attribute'], $id, '%s=%s'));
      $s->addStatement(new SqlStatementBi($s->columns['resource'], $this->_id, '%s=%s'));
      $s->setColumnsMask(array('attribute','value'));
      $res = $this->_app->db->doQuery($s->toString());
      $row = $this->_app->db->fetchAssoc($res);
      
      if (!$row) {
        $o = new OResourceAttribute;
        $o->setData(array('resource'=>$this->_id,'attribute'=>$id,'value'=>null));
        $o->save();
      }
      $idsToSave[] = $id;
    }
    
    if (!isset($params['reservationAttributeAddOnly'])||!$params['reservationAttributeAddOnly']) {
      $s = new SResourceAttribute;
      $s->addStatement(new SqlStatementBi($s->columns['resource'], $this->_id, '%s=%s'));
      $s->addStatement(new SqlStatementMono($s->columns['applicable'], "%s='RESERVATION'"));
      if (count($idsToSave)) $s->addStatement(new SqlStatementMono($s->columns['attribute'], sprintf('%%s NOT IN (%s)', implode(',', $idsToSave))));
      $s->setColumnsMask(array('attribute','value','type'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $o = new OResourceAttribute(array('resource'=>$this->_id, 'attribute'=>$row['attribute']));
        $o->delete();
      }
    }
  }
  
  private function _save($params) {
    $this->_load();
    
    $this->_app->db->beginTransaction();

    $o = new OResource($this->_id?$this->_id:null);
    $oData = array();
    if (isset($params['providerId'])) $oData['provider'] = $params['providerId'];
    if (isset($params['externalId'])) $oData['external_id'] = $params['externalId'];
    if (isset($params['centerId'])) $oData['center'] = $params['centerId'];
    if (isset($params['organiserId'])) $oData['organiser'] = $params['organiserId']?$params['organiserId']:null;
    if (isset($params['name'])) $oData['name'] = $params['name'];
    if (isset($params['description'])) $oData['description'] = $params['description'];
    if (isset($params['price'])) $oData['price'] = $params['price'];
    if (isset($params['priceList'])) $oData['pricelist'] = $params['priceList']?$params['priceList']:null;
    if (isset($params['accountTypeId'])) $oData['accounttype'] = $params['accountTypeId']?$params['accountTypeId']:null;
    if (isset($params['reservationConditionId'])) $oData['reservationcondition'] = $params['reservationConditionId']?$params['reservationConditionId']:null;
    if (isset($params['notificationTemplateId'])) $oData['notificationtemplate'] = $params['notificationTemplateId']?$params['notificationTemplateId']:null;
    if (isset($params['documentTemplateId'])) $oData['documenttemplate'] = $params['documentTemplateId']?$params['documentTemplateId']:null;
    if (isset($params['active'])) $oData['active'] = $params['active'];
    if (isset($params['availProfile'])) $oData['availabilityprofile'] = $params['availProfile']?$params['availProfile']:null;
    if (isset($params['availExProfile'])) $oData['availabilityexceptionprofile'] = $params['availExProfile']?$params['availExProfile']:null;
    if (isset($params['unitProfile'])) $oData['unitprofile'] = $params['unitProfile']?$params['unitProfile']:null;

    if (isset($params['feAllowedPayment'])) {
      if (is_array($params['feAllowedPayment'])) {
        $oData['fe_allowed_payment'] = 0;
        if (in_array('credit', $params['feAllowedPayment'])) $oData['fe_allowed_payment'] = $oData['fe_allowed_payment'] | 1;
        if (in_array('ticket', $params['feAllowedPayment'])) $oData['fe_allowed_payment'] = $oData['fe_allowed_payment'] | 10;
        if (in_array('online', $params['feAllowedPayment'])) $oData['fe_allowed_payment'] = $oData['fe_allowed_payment'] | 100;
      } else $oData['fe_allowed_payment'] = null;
    }
    
    if (isset($params['urlDescription'])) $oData['url_description'] = $params['urlDescription']?$params['urlDescription']:null;
    if (isset($params['urlPrice'])) $oData['url_price'] = $params['urlPrice']?$params['urlPrice']:null;
    if (isset($params['urlOpening'])) $oData['url_opening'] = $params['urlOpening']?$params['urlOpening']:null;
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
    $params['providerId'] = $oData['provider'];
    
    $this->_saveTag($params);
    $this->_savePortal($params);
    if (isset($params['attribute'])) $this->_saveAttribute($params);
    if (isset($params['reservationAttribute'])) $this->_saveReservationAttribute($params);
    
    // pri zmene availability je potreba upravit tabulku availability
    if (!$this->_data||
        (isset($params['availProfile'])&&($params['availProfile']!=$this->_data['availProfile']))||
        (isset($params['availExProfile'])&&($params['availExProfile']!=$this->_data['availExProfile']))
       ) {
      $this->generateAvailabilityTable();
    } else $this->generateAvailabilityTable(); // @todo proc by se mela availability generovat vzdy?
    
    $this->_cancelReservation($params);

    $this->_app->db->commitTransaction();
  }

  public function save($params) {
    if (!$this->_checkAccess($params)) throw new ExceptionUserTextStorage('error.accessDenied');
    
    $this->_checkBeforeSave($params);
    $this->_checkPool($params);
    
    $this->_save($params);
  }
  
  private function _delete() {
    $this->_app->db->beginTransaction();
    
    $o = new OResource($this->_id);
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
  
    $o = new OResource($this->_id);
    $oData = $o->getData();
    
    $ret = $oData['name'];
    
    $o->setData(array('active'=>'N'));
    $o->save();
    
    return $ret;
  }
  
  public function copy() {
    if (!$this->_checkAccess()) throw new ExceptionUserTextStorage('error.accessDenied');
    
    $this->_load();
    
    $ret = $this->_data['name'];
    
    $this->_data['name'] .= ' (kopie)';
    $this->_data['externalId'] = null;
    foreach ($this->_data['attribute'] as $id=>$attr) {
      if (strcmp($attr['type'],'FILE')) $this->_data['attribute'][$id] = $attr['value'];
      else {
        global $TMP_DIR;

        $linkName = tempnam($TMP_DIR,'');
        unlink($linkName);
        $fileName = $linkName.'_'.str_replace(' ','<space>',$attr['value']);

        $o = new OFile($attr['valueDbId']);
        $oData = $o->getData();
        file_put_contents($fileName, $oData['content']);

        symlink($fileName, $linkName);
        
        $this->_data['attribute'][$id] = basename($linkName);
      }
    }
          
    $newResource = new BResource;
    $newResource->save($this->_data);
    
    return $ret;
  }
  
  public function getAvailabilityProfileData() {
    // nacte availabilitu zdroje dle profilu zdroje
    if (!isset($this->_data['availability'])) {
      $this->_data['availability'] = array();
      $s = new SResource;
      $s->addStatement(new SqlStatementBi($s->columns['resource_id'], $this->_id, '%s=%s'));
      $s->setColumnsMask(array('availabilityprofileitem_weekday','availabilityprofileitem_time_from','availabilityprofileitem_time_to'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        if ($row['availabilityprofileitem_weekday']) {
          $this->_data['availability'][$row['availabilityprofileitem_weekday']] = array(
              'from'  => $row['availabilityprofileitem_time_from'],
              'to'    => !strcmp($row['availabilityprofileitem_time_to'],'00:00:00')?'24:00:00':$row['availabilityprofileitem_time_to'], 
              );
        }
      }
    }
    
    return $this->_data['availability'];
  }
  
  public function getAvailabilityExceptionProfileData($from=null, $to=null) {
    // nacte vyjimky z availability zdroje dle profilu zdroje
    // ! asi by to chtelo nejak optimalizovat az bude hodne dat, zatim to prohledava vsechny vyjimky a
    // ! podle pozadovaneho intervalu to "orizne"; musi se to tak delat kvuli opakujicim se vyjimkam
    // ! asi by bylo idealnni jednou za cas ty podminky "aktualizovat" strojove
    if (!$from) $from = $this->_app->regionalSettings->decreaseDate(date('Y-m-d'),0,6);
    if (!$to) $to = $this->_app->regionalSettings->increaseDate(date('Y-m-d'),0,6);
    
    if (!isset($this->_data['availabilityException'])) {
      $this->_data['availabilityException'] = array();
      $s = new SResource;
      $s->addStatement(new SqlStatementBi($s->columns['resource_id'], $this->_id, '%s=%s'));
      $s->addOrder(new SqlStatementAsc($s->columns['availabilityexceptionprofileitem_time_from']));
      $s->setColumnsMask(array('availabilityexceptionprofileitem_time_from','availabilityexceptionprofileitem_time_to',
                               'availabilityexceptionprofileitem_repeated','availabilityexceptionprofileitem_repeat_cycle',
                               'availabilityexceptionprofileitem_repeat_weekday','availabilityexceptionprofileitem_repeat_until'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        // pro jistotu
        if (($row['availabilityexceptionprofileitem_time_from']>$row['availabilityexceptionprofileitem_time_to'])||
            (($row['availabilityexceptionprofileitem_repeated']=='Y')&&!$row['availabilityexceptionprofileitem_repeat_cycle'])) continue;

        list($date,$time) = explode(' ',$row['availabilityexceptionprofileitem_time_to']);
        if (!strcmp($time,'00:00:00')) {
          $date = $this->_app->regionalSettings->increaseDate($date);
          $row['availabilityexceptionprofileitem_time_to'] = sprintf('%s 00:00:00', $date);
        }
        
        if ($this->_app->regionalSettings->checkIntervalIntersect($from, $to.' 24:00:00', $row['availabilityexceptionprofileitem_time_from'], $row['availabilityexceptionprofileitem_time_to'])) {
          $this->_data['availabilityException'][] = array(
              'from'  => $row['availabilityexceptionprofileitem_time_from'],
              'to'    => $row['availabilityexceptionprofileitem_time_to'], 
              );
        }
        
        if ($row['availabilityexceptionprofileitem_repeated']=='Y') {
          $repeatStart = $row['availabilityexceptionprofileitem_time_from'];
          $repeatEnd = $row['availabilityexceptionprofileitem_time_to'];
          $repeatWeekday = array();
          foreach (array('mon','tue','wed','thu','fri','sat','sun') as $index=>$day) {
            $value = pow(2,$index)&$row['availabilityexceptionprofileitem_repeat_weekday'];
            if ($value) $repeatWeekday[$day] = $value;
          }
          $repeatUntil = $row['availabilityexceptionprofileitem_repeat_until']?$row['availabilityexceptionprofileitem_repeat_until']:$to;
          
          getNextRepeatTerm($row['availabilityexceptionprofileitem_repeat_cycle'], $repeatStart, $repeatEnd, $repeatWeekday);
          while (($repeatStart<$repeatUntil.' 24:00:00')&&($repeatStart<$to.' 24:00:00')) {
            if ($this->_app->regionalSettings->checkIntervalIntersect($from, $to.' 24:00:00', $repeatStart, $repeatEnd)) {
              $this->_data['availabilityException'][] = array(
                  'from'  => $repeatStart,
                  'to'    => $repeatEnd, 
                  );
            }
            
            getNextRepeatTerm($row['availabilityexceptionprofileitem_repeat_cycle'], $repeatStart, $repeatEnd, $repeatWeekday);
          }
        }
      }
    }
    
    return $this->_data['availabilityException'];
  }
  
  public function generateAvailabilityTable($startDate=null) {    
    if (!$startDate) $startDate = date('Y-m-d');
    
    global $RESOURCE_AVAILABILITY;
    $endDate = $this->_app->regionalSettings->increaseDate($startDate, $RESOURCE_AVAILABILITY['future']);
    
    $dataToSave = array();
    $i = 0;
    
    // vezmu "posledni" availabilitu ktera jde "pres" startDate
    $s = new SResourceAvailability;
    $s->addStatement(new SqlStatementBi($s->columns['resource'], $this->_id, '%s=%s'));
    $s->addOrder(new SqlStatementDesc($s->columns['end']));
    $s->setColumnsMask(array('resourceavailability_id','start','end'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      if ($row['start']<$startDate) {
        // kdyz ma availabilita start drive nez od kdy chci generovat
        if ($row['end']>=$startDate) {
          // a zaroven end pozdeji, nez od kdy chci generovat

          // vytvorim prvni vygenerovanou availabilitu se startem podle nalezene a endem podle data, od kdy budu generovat
          $newEnd = $this->_app->regionalSettings->decreaseDate(substr($startDate,0,10)).' 24:00:00';
          $dataToSave = array('i'=>0,'start'=>$row['start'],'end'=>$newEnd,'timeEnd'=>substr($newEnd,11));
          $i = 1;

          // smazu availabilitu, ktera se bude navazovat
          $o = new OResourceAvailability($row['resourceavailability_id']);
          $o->delete();
        }

        // jakmile jsem nasel availabilitu, ktera ma start mensi nez od kdy chci generovat, koncim s mazanim availabilit
        break;
      }
    
      // smazu availabilitu, ktera je "za" startDate
      $o = new OResourceAvailability($row['resourceavailability_id']);
      $o->delete();
    }
    
    $this->getAvailabilityProfileData();

    $dateFrom = $startDate;
    while ($dateFrom <= $endDate) {
      $dayOfWeek = $this->_app->regionalSettings->getDayOfWeek($dateFrom);
      if (isset($this->_data['availability'][$dayOfWeek])) {
        if (isset($dataToSave['i'])) {
          if (($dataToSave['i']+1==$i)&&(
            (($dataToSave['timeEnd']=='24:00:00')&&($this->_data['availability'][$dayOfWeek]['from']=='00:00:00'))||
            ((substr($dataToSave['end'],0,10)==$dateFrom)&&($dataToSave['timeEnd']<=$this->_data['availability'][$dayOfWeek]['to']))
            )) {
            // kdyz availabilita dne primo navazuje na predchozi availabilitu dne nebo se s ni prekryva, prodlouzim predchozi availabilitu dne
            $dataToSave['i'] = $i;
            $dataToSave['timeEnd'] = $this->_data['availability'][$dayOfWeek]['to'];
            $dataToSave['end'] = $dateFrom . ' ' . $this->_data['availability'][$dayOfWeek]['to'];
          } else {
            // kdyz nenavazuje, ulozim prechozi do DB a "zapamatuju" si aktualni
            list($eDate,$eTime) = explode(' ', $dataToSave['end']);
            if (!strcmp($eTime,'24:00:00')) {
              $eDate = $this->_app->regionalSettings->increaseDate($eDate);
              $eTime = '00:00:00';
              $dataToSave['end'] = sprintf('%s %s', $eDate, $eTime);
            }
            
            $o = new OResourceAvailability;
            $o->setData(array(
                      'resource'  => $this->_id,
                      'start'     => $dataToSave['start'],
                      'end'       => $dataToSave['end']));
            $o->save();
            
            $dataToSave = array(
                  'i'       => $i,
                  'start'   => $dateFrom . ' ' . $this->_data['availability'][$dayOfWeek]['from'],
                  'end'     => $dateFrom . ' ' . $this->_data['availability'][$dayOfWeek]['to'],
                  'timeEnd' => $this->_data['availability'][$dayOfWeek]['to'],
                  );
          }
        } else {
          // kdyz neni zadna predchozi, pouze si "zapamatuju" aktualni
          $dataToSave = array(
                  'i'       => $i,
                  'start'   => $dateFrom . ' ' . $this->_data['availability'][$dayOfWeek]['from'],
                  'end'     => $dateFrom . ' ' . $this->_data['availability'][$dayOfWeek]['to'],
                  'timeEnd' => $this->_data['availability'][$dayOfWeek]['to'],
                  );
        }
      }
      
      $dateFrom = $this->_app->regionalSettings->increaseDate($dateFrom);
      $i++;
    }
    if (isset($dataToSave['i'])) {
      // ulozim jeste posledni interval
      list($eDate,$eTime) = explode(' ', $dataToSave['end']);
      if (!strcmp($eTime,'24:00:00')) {
        $eDate = $this->_app->regionalSettings->increaseDate($eDate);
        $eTime = '00:00:00';
        $dataToSave['end'] = sprintf('%s %s', $eDate, $eTime);
      }
      
      $o = new OResourceAvailability;
      $o->setData(array(
                'resource'  => $this->_id,
                'start'     => $dataToSave['start'],
                'end'       => $dataToSave['end']));
      $o->save();
    }
    
    $this->occupyAvailabilityTableByException($startDate, $endDate);
    $this->occupyAvailabilityTableByReservation(null, $startDate);
    $this->occupyAvailabilityTableByEvent(null, $startDate);
  }
  
  public function freeAvailabilityTable($from, $to) {
    // pokusim se spojit interval availability s existujicim intervalem
    $s = new SResourceAvailability;
    $s->addStatement(new SqlStatementBi($s->columns['resource'], $this->_id, '%s=%s'));
    $s->addStatement(new SqlStatementQuad($s->columns['start'], $to, $s->columns['end'], $from, '(%s=%s OR %s=%s)'));
    $s->setColumnsMask(array('resourceavailability_id','start','end'));
    $res = $this->_app->db->doQuery($s->toString());
    $intervalBefore_id = null; $intervalAfter_id = null;
    while ($row = $this->_app->db->fetchAssoc($res)) {
      if ($row['end']==$from) {
        $intervalBefore_from = $row['start'];
        $intervalBefore_id = $row['resourceavailability_id'];
      }
      if ($row['start']==$to) {
        $intervalAfter_to = $row['end'];
        $intervalAfter_id = $row['resourceavailability_id'];
      }
    }
    
    if (!$intervalBefore_id&&!$intervalAfter_id) {
      $o = new OResourceAvailability;
      $o->setData(array('resource'=>$this->_id,'start'=>$from,'end'=>$to));
      $o->save();
    } elseif ($intervalBefore_id&&$intervalAfter_id) {
      $o = new OResourceAvailability($intervalBefore_id);
      $o->setData(array('end'=>$intervalAfter_to));
      $o->save();
      
      $o = new OResourceAvailability($intervalAfter_id);
      $o->delete();
    } elseif ($intervalBefore_id) {
      $o = new OResourceAvailability($intervalBefore_id);
      $o->setData(array('end'=>$to));
      $o->save();
    } elseif ($intervalAfter_id) {
      $o = new OResourceAvailability($intervalAfter_id);
      $o->setData(array('start'=>$from));
      $o->save();
    }
  }
  
  private function _occupyAvailabilityTable($availabilityId, $from, $to) {
    #error_log(sprintf("\noccupying %s from %s to %s:", $availabilityId, $from, $to));
    
    // "roztrhnu" interval availability dle from, to
    $o = new OResourceAvailability($availabilityId);
    $oData = $o->getData();
    $newStart = null; $newEnd = null;
    
    if ($oData['start']<$from) {
      $newEnd = $from;
      #if (strpos($newEnd,'00:00:00')) $newEnd = $this->_app->regionalSettings->decreaseDateTime($newEnd,0,0,0,0,1);  
    }
    if ($to<$oData['end']) {
      $newStart = $to;
      #if (strpos($newStart,'23:59:00')) $newStart = $this->_app->regionalSettings->increaseDateTime($newStart,0,0,0,0,1);
    }
    
    #error_log(sprintf("newStart: %s, newEnd: %s", $newStart, $newEnd));
    
    if (!$newStart&&!$newEnd) {
      // rezervace pokreje celou availabilitu
      $o->delete();
    } else {
      $used = false;
      if ($newEnd) {
        // zbyde availabilita "ze zacatku"
        $o->setData(array('end'=>$newEnd));
        $o->save();
        $used = true;
      }
      if ($newStart) {
        // zbyde availabilita "na konci"
        if (!$used) {
          $o->setData(array('start'=>$newStart));
          $o->save(); 
        } else {
          $o = new OResourceAvailability;
          $o->setData(array('resource'=>$this->_id,'start'=>$newStart,'end'=>$oData['end']));
          $o->save(); 
        }
      }
    }
  }
  
  public function occupyAvailabilityTableByReservation($reservation=null,$dateFrom=null) {
    if (!$dateFrom) $dateFrom = date('Y-m-d');
    
    $s = new SReservation;
    // bud obsazuju konkretni rezervaci nebo rezervace od nejakeho data
    if ($reservation) $s->addStatement(new SqlStatementBi($s->columns['reservation_id'], $reservation, '%s=%s'));
    else $s->addStatement(new SqlStatementBi($s->columns['resource_to'], $dateFrom, 'DATE(%s)>=%s'));
    $s->addStatement(new SqlStatementBi($s->columns['resource'], $this->_id, '%s=%s'));
    $s->addStatement(new SqlStatementMono($s->columns['cancelled'], '%s IS NULL'));
    $s->addStatement(new SqlStatementMono($s->columns['failed'], '%s IS NULL'));
    $s->addOrder(new SqlStatementAsc($s->columns['number']));
    $s->setColumnsMask(array('reservation_id','number','resource','resource_from','resource_to'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      // pro kazdou rezervaci musim vybrat aktualni interval v availabilite zdroje
      $s1 = new SReservation;
      $s1->addStatement(new SqlStatementBi($s1->columns['reservation_id'], $row['reservation_id'], '%s=%s'));
      $s1->setColumnsMask(array('resourceavailability'));
      $res1 = $this->_app->db->doQuery($s1->toString());
      $row1 = $this->_app->db->fetchAssoc($res1);
      if (!$row1['resourceavailability']) {
        throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveResource_availabilityReservationConflict'), $row['number']));
      }
      
      $this->_occupyAvailabilityTable($row1['resourceavailability'], $row['resource_from'], $row['resource_to']);
    }
  } 
  
  public function occupyAvailabilityTableByEvent($event=null,$dateFrom=null) {
    if (!$dateFrom) $dateFrom = date('Y-m-d');
    
    $s = new SEvent;
    // bud obsazuju konkretni udalost nebo udalosti od nejakeho data
    if ($event) $s->addStatement(new SqlStatementBi($s->columns['event_id'], $event, '%s=%s'));
    else $s->addStatement(new SqlStatementBi($s->columns['end'], $dateFrom, 'DATE(%s)>=%s'));
    $s->addStatement(new SqlStatementBi($s->columns['resource'], $this->_id, '%s=%s'));
    $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
    $s->setColumnsMask(array('event_id','name','resource','start','end'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      // pro kazdou udalost musim vybrat aktualni interval v availabilite zdroje
      $s1 = new SEvent;
      $s1->addStatement(new SqlStatementBi($s1->columns['event_id'], $row['event_id'], '%s=%s'));
      $s1->addStatement(new SqlStatementBi($s1->columns['resource'], $this->_id, '%s=%s'));
      $s1->setColumnsMask(array('resourceavailability'));
      $res1 = $this->_app->db->doQuery($s1->toString());
      $row1 = $this->_app->db->fetchAssoc($res1);
      if (!$row1['resourceavailability']) {
        throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveResource_availabilityEventConflict'),$row['event_id'],$row['name']));
      }
      
      #adump($row['start']);adump($row['end']);throw new ExceptionUser('x');
      $this->_occupyAvailabilityTable($row1['resourceavailability'], $row['start'], $row['end']);
    }
  }
  
  public function occupyAvailabilityTableByException($dateFrom=null,$dateTo=null) {
    if (!$dateFrom) $dateFrom = date('Y-m-d');
    
    $data = $this->getAvailabilityExceptionProfileData($dateFrom,$dateTo);
    foreach ($data as $exception) {
      list($eDate,$eTime) = explode(' ', $exception['to']);
      if (!strcmp($eTime,'24:00:00')) {
        $eDate = $this->_app->regionalSettings->increaseDate($eDate);
        $eTime = '00:00:00';
        $exception['to'] = sprintf('%s %s', $eDate, $eTime);
      }
      
      // "vorezu" vsechny availability, do kterych zasahuje vyjimka
      $s = new SResourceAvailability;
      $s->addStatement(new SqlStatementBi($s->columns['resource'], $this->_id, '%s=%s'));
      $s->addStatement(new SqlStatementTri(
                    new SqlStatementQuad($exception['from'], $s->columns['start'], $s->columns['start'], $exception['to'], '(%s<=%s AND %s<=%s)'),
                    new SqlStatementQuad($exception['from'], $s->columns['end'], $s->columns['end'], $exception['to'], '(%s<=%s AND %s<=%s)'),
                    new SqlStatementQuad($s->columns['start'], $exception['from'], $exception['to'], $s->columns['end'], '(%s<=%s AND %s<=%s)'),
                    '(%s OR %s OR %s)'));
      $s->setColumnsMask(array('resourceavailability_id','start','end'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $start = $exception['from'];
        $end = $exception['to'];
        
        // kdyz je vyjimka vetsi nez nalezena availabilita, zmensim vyjimku na availabilitu
        if ($start<$row['start']) $start = $row['start'];
        if ($end>$row['end']) $end = $row['end'];
        
        $this->_occupyAvailabilityTable($row['resourceavailability_id'], $start, $end); 
      }
    }
  } 
  
  public function getAvailability($start,$end=null) {
    if (!$start||!$this->_app->regionalSettings->checkDateTime($start)) return null;
    if ($end&&!$this->_app->regionalSettings->checkDateTime($end)) return null;
    
    $ret = null;
    
    $s = new SResourceAvailability;
    $s->addStatement(new SqlStatementBi($s->columns['resource'], $this->_id, '%s=%s'));
    $s->addStatement(new SqlStatementQuad($s->columns['start'], $start, $start, $s->columns['end'], '(BINARY %s<= BINARY %s AND BINARY %s<= BINARY %s)'));
    if ($end) $s->addStatement(new SqlStatementQuad($s->columns['start'], $end, $end, $s->columns['end'], '(BINARY %s<= BINARY %s AND BINARY %s<= BINARY %s)'));
    $s->setColumnsMask(array('resourceavailability_id'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($row = $this->_app->db->fetchAssoc($res)) {
      $ret = $row['resourceavailability_id'];
    }
    
    return $ret;
  }
  
  public function getReservation($datetime=null) {
    $ret = null;
    
    $s = new SReservation;
    $s->addStatement(new SqlStatementBi($s->columns['resource'], $this->_id, '%s=%s'));
    $s->addStatement(new SqlStatementMono($s->columns['cancelled'], '%s IS NULL'));
    if ($datetime) {
      $s->addStatement(new SqlStatementBi($s->columns['resource_from'], $datetime, '%s<=%s'));
      $s->addStatement(new SqlStatementBi($datetime, $s->columns['resource_to'], '%s<%s')); 
    }
    $s->setColumnsMask(array('reservation_id'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($this->_app->db->getRowsNumber($res)) $ret = array();
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $ret[] = $row['reservation_id'];
    }
    
    return $ret;
  }
  
  private function _getSeasonPrice($from, $to, &$resLength, $season, $unit) {
    // projdu jednotlive dny rezervovaneho obdobi a pokud den spada do sezony, vypoctu cenu za dany den
    // a odectu nacenenou delku rezervovaneho obdobi
    $price = 0;
    
    // upravim data z DB, aby se lip parsovaly
    $seasonDayPrice = array();
    foreach (array('mon','tue','wed','thu','fri','sat','sun') as $day) {
      $seasonDayPrice[$day] = json_decode($season[$day.'_price'], true);
      foreach ($seasonDayPrice[$day] as $index=>$struct) {
        $seasonDayPrice[$day][$index]['from'] = sprintf('%02d:00:00', $seasonDayPrice[$day][$index]['from']);
        $seasonDayPrice[$day][$index]['to'] = sprintf('%02d:00:00', $seasonDayPrice[$day][$index]['to']);
        #if (!strcmp($seasonDayPrice[$day][$index]['to'],'24:00:00')) $seasonDayPrice[$day][$index]['to'] = '23:59:00';
      }
    }
    
    list($dateFrom,$timeFrom) = explode(' ', $from);
    list($dateTo,$timeTo) = explode(' ', $to);
    while ($from<$to) {
      if (($from>=$season['start'])&&($from<$season['end'])) {
        // kdyz je rezervovany den v sezone (datumova kontrola) 
        $dayOfWeekFrom = $this->_app->regionalSettings->getDayOfWeek($from);

        // casove rozmezi, pro ktere pocitam "denni" cenu
        $dayFrom = $timeFrom;
        $dayTo = $timeTo;
        if (strcmp($dateFrom,$dateTo)) $dayTo = '24:00:00';
        // projdu cenove/casove intervaly daneho dne
        foreach ($seasonDayPrice[$dayOfWeekFrom] as $dayInterval) {
          // kdyz je rezervovane v intervalu
          if (($timeFrom<=$dayInterval['to'])&&($timeTo>=$dayInterval['from'])) {
            // zjistim prunik v minutach
            $intersectFrom = $dayFrom; $intersectTo = $dayTo;
            if ($intersectFrom<$dayInterval['from']) $intersectFrom = $dayInterval['from'];
            if ($intersectTo>$dayInterval['to']) $intersectTo = $dayInterval['to'];
            
            $length = (strtotime($intersectTo)-strtotime($intersectFrom))/60;
            if ($length) {
              $intervalPrice = $dayInterval['default']==1?$season['base_price']:$dayInterval['price'];
              
              $seasonPrice = $intervalPrice/$unit*$length;
              $price += $seasonPrice;
              $resLength -= $length;
              
              #error_log(sprintf('fitting price season: %s, interval: %s-%s, day: %s, length: %s, unit price: %s, season price: %s', $season['name'], $intersectFrom, $intersectTo, $dayOfWeekFrom, $length, $intervalPrice, $seasonPrice));
            }
          }
        }
      }
      
      // posunu se na dalsi den (ale od 00:00:00)
      $from = substr($from,0,10).' 00:00:00';
      $from = $this->_app->regionalSettings->increaseDateTime($from);
      list($dateFrom,$timeFrom) = explode(' ', $from);
    }
    
    return $price;
  }
  
  public function getPrice($from,$to) {
    list($date,$time) = explode(' ', $to);
    if (!strcmp($time,'00:00:00')) {
      $date = $this->_app->regionalSettings->decreaseDate($date);
      $to = sprintf('%s 24:00:00', $date);
    }
      
    $price = 0;
    
    if ($this->_id) {
      // rok u vypoctu ceny musi byt dle from/to
      $year = substr($from,0,4);
      
      $s = new SResource;
      $s->addStatement(new SqlStatementBi($s->columns['resource_id'], $this->_id, '%s=%s'));
      $s->setColumnsMask(array('price','unit','unit_rounding','pricelist'));
      $res = $this->_app->db->doQuery($s->toString());
      $rData = $this->_app->db->fetchAssoc($res);

      if (!strcmp($rData['unit_rounding'],'day')) {
        // kdyz jsou rezervacni jednotky den, tak nechci aby cenu ovlivnoval cas v ramci dne (typicky se tak budou rezervovat kola)
        // nastavim cas "to" stejny jako "from"
        // a datum "to" zvetsim o den (aby vypocet ceny byl podle celych dnu)
        $to = $this->_app->regionalSettings->increaseDate(substr($to,0,10)).substr($from,10);
      } elseif (!strcmp($rData['unit_rounding'],'night')) {
        // kdyz jsou rezervacni jednotky noc, tak nechci aby cenu ovlivnoval cas v ramci noci (typicky se tak budou rezervovat pokoje)
        // nastavim cas "to" stejny jako "from"
        // tady se datum "to" zvetsovat nemusi, protoze predpokladam, ze pri rezervaci noci bude datum "from" mensi nez datum "to"
        $to = substr($to,0,10).substr($from,10);
      }

      $reservationLength = (strtotime($to)-strtotime($from))/60;

      // zkusim najit co pokryje cenik
      $s = new SSeason;
      $s->addStatement(new SqlStatementBi($s->columns['pricelist'], $rData['pricelist'], '%s=%s'));
      $s->addOrder(new SqlStatementAsc($s->columns['start']));
      $s->setColumnsMask(array('name','start','end','base_price','mon_price','tue_price','wed_price','thu_price','fri_price','sat_price','sun_price'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        // start/end u sezony me zajima bez roku
        $row['start'] = $year.'-'.substr($row['start'],5,5).' 00:00:00';
        $row['end'] = $year.'-'.substr($row['end'],5,5).' 24:00:00';
        // kdyz je "do" v sezone mensi jak "od", posunu "do" do dolsiho roku
        if ($row['end']<$row['start']) $row['end'] = $this->_app->regionalSettings->increaseDateTime($row['end'],0,0,1);
        
        // kdyz je sezona aplikovatelna na rezervovany termin
        if (($from<=$row['end'])&&($to>=$row['start'])) $price += $this->_getSeasonPrice($from,$to,$reservationLength,$row,$rData['unit']);
      }
      
      // kdyz zbylo neco co neni pokryto cenikem, vemu na to fixni cenu
      if ($reservationLength) $price += $rData['price']/$rData['unit']*$reservationLength;
    }
    
    return $price;
  }

  public function isPaymentNeeded($start, $price=null, $when=null) {
    $ret = false;

    if ($this->_id) {
      if (!$when) $when = date('Y-m-d H:i:s');

      $o = new OResource($this->_id);
      $oData = $o->getData();

      // kdyz je cena za rezervaci=0, neni platba potreba nikdy
      if ($price) {
        $s = new SReservationConditionItem;
        $s->addStatement(new SqlStatementBi($s->columns['reservationcondition'], $oData['reservationcondition'], '%s=%s'));
        $s->setColumnsMask(array('advance_payment'));
        $res = $this->_app->db->doQuery($s->toString());
        while ($row = $this->_app->db->fetchAssoc($res)) {
          if ($row['advance_payment']) {
            $paymentTime = $this->_app->regionalSettings->decreaseDateTime($start,0,0,0,0,$row['advance_payment']);
            if ($when>$paymentTime) {
              $ret = true;
              break;
            }
          }
        }
      }
    }

    return $ret;
  }
}

?>
