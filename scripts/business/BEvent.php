<?php

class BEvent extends BusinessObject {

  private function _checkAccess($params=null,$access='all') {
    $ret = false;
    $this->_load();
 
    $requiredRight = $access=='all'?'commodity_admin':'commodity_read';
 
    while (true) {
      if ($user=$this->_app->auth->isUser()) {
        // normalni uzivatel nema pravo delat cokoliv s akcema
        break;
      } elseif ($this->_app->auth->isProvider()) {
        // kdyz je to provider a ma pravo pracovat se svyma akcema

        // akce musi byt z povoleneho centra
        $allowedCenter = $this->_app->auth->getAllowedCenter('array');
        if ($this->_id&&!in_array($this->_data['centerId'], $allowedCenter)) break;
        if (isset($params['centerId'])&&!in_array($params['centerId'], $allowedCenter)) break;

        $allowedProvider = $this->_app->auth->getAllowedProvider($requiredRight,'array');
        // nejdriv zkontroluju puvodni akci
        if ($this->_id) {
          // kdyz nema pozadovane pravo, tak jeste power organizator muze editovat svoje akce
          if (!in_array($this->_data['providerId'], $allowedProvider)) {
            if (!$this->_app->auth->haveRight('power_organiser', $this->_data['providerId'])) break;
            if ($this->_data['organiserId']!=$this->_app->auth->getUserId()) break;
          }
        }
        // pak zkontroluju nove parametry akce
        $providerId = ifsetor($params['providerId'],$this->_data['providerId']);
        $organiserId = ifsetor($params['organiserId'],$this->_data['organiserId']);
        if (!in_array($providerId, $allowedProvider)) {
          if (!$this->_app->auth->haveRight('power_organiser', $providerId)) break;
          if ($organiserId!=$this->_app->auth->getUserId()) break;
        }
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
  
  private function _checkSubstituteAccess($params=array()) {
    $ret = false;

    $this->_load();

    if ($params['attendeeId']&&(!isset($params['userId'])||!$params['userId'])) {
      $s = new SEventAttendee;
      $s->addStatement(new SqlStatementBi($s->columns['eventattendee_id'], $params['attendeeId'], '%s=%s'));
      $s->setColumnsMask(array('user'));
      $res = $this->_app->db->doQuery($s->toString());
      $row = $this->_app->db->fetchAssoc($res);
      $params['userId'] = $row['user'];
    }

    if (isset($params['userId'])&&$params['userId']) {
      if ($user=$this->_app->auth->isUser()) {
        if ($params['userId']==$user) $ret = true;
        elseif ($this->_data['organiserId']==$this->_app->auth->getUserId()) $ret = true;
      } elseif ($this->_app->auth->isProvider()&&in_array($this->_data['centerId'], $this->_app->auth->getAllowedCenter('array')))  {
        if ($this->_app->auth->haveRight('reservation_admin', $this->_data['providerId'])) $ret = true;
        elseif ($this->_app->auth->haveRight('power_organiser', $this->_data['providerId'])&&($this->_data['organiserId']==$this->_app->auth->getUserId())) $ret = true;
      } elseif ($this->_app->auth->haveRight('reservation_admin')) {
        $ret = true;
      }
    }

    return $ret;
  }
  
  private function _checkBeforeSave($params) {
    // kdyz se zaklada nova akce jsou tyto atributy povinne
    if (!$this->_id) {
      if (!isset($params['name'])) throw new ExceptionUserTextStorage('error.saveEvent_emptyName');
      if (!isset($params['centerId'])) throw new ExceptionUserTextStorage('error.saveEvent_emptyCenter');
      if (!isset($params['providerId'])) throw new ExceptionUserTextStorage('error.saveEvent_emptyProvider');
      if (!isset($params['start'])) throw new ExceptionUserTextStorage('error.saveEvent_emptyStart');
      if (!isset($params['end'])) throw new ExceptionUserTextStorage('error.saveEvent_emptyEnd');
      if (!isset($params['maxAttendees'])) throw new ExceptionUserTextStorage('error.saveEvent_emptyMaxAttendees');
      //if (!isset($params['price'])) throw new ExceptionUserTextStorage('error.saveEvent_emptyPrice');
    }
    
    // tyto nesmi byt prazdny nikdy
    if (isset($params['name'])&&!$params['name']) throw new ExceptionUserTextStorage('error.saveEvent_emptyName');
    if (isset($params['organiserId'])&&!$params['organiserId']) throw new ExceptionUserTextStorage('error.saveEvent_emptyOrganiser');
    if (isset($params['centerId'])&&!$params['centerId']) throw new ExceptionUserTextStorage('error.saveEvent_emptyCenter');
    if (isset($params['providerId'])&&!$params['providerId']) throw new ExceptionUserTextStorage('error.saveEvent_emptyProvider');
    if (isset($params['start'])&&!$params['start']) throw new ExceptionUserTextStorage('error.saveEvent_emptyStart');
    if (isset($params['end'])&&!$params['end']) throw new ExceptionUserTextStorage('error.saveEvent_emptyEnd');
    //if (isset($params['maxAttendees'])&&!$params['maxAttendees']) throw new ExceptionUserTextStorage('error.saveEvent_emptyMaxAttendees');
    //if (isset($params['price'])&&!$params['price']) throw new ExceptionUserTextStorage('error.saveEvent_emptyPrice');
    
    // kontrola na korektni datumy pro zdroj
    #if (isset($params['start'])&&($params['start']<date('Y-m-d H:i:s'))) throw new ExceptionUserTextStorage('error.saveEvent_invalidTime');
    #if (isset($params['end'])&&($params['end']<date('Y-m-d H:i:s'))) throw new ExceptionUserTextStorage('error.saveEvent_invalidTime');
    if (isset($params['start'])&&isset($params['end'])) {
      if ($params['start']>=$params['end']) throw new ExceptionUserTextStorage('error.saveEvent_invalidTime');
    }
    
    // kontrola zdroju
    if (isset($params['resource'])&&count($params['resource'])) {
      $resourceString = implode(',',$params['resource']);
      $s = new SResource;
      $s->addStatement(new SqlStatementTri($s->columns['center'], ifsetor($params['centerId'], $this->_data['centerId']),
                                           $s->columns['active'], "(%s<>%s OR %s='N')"));
      $s->addStatement(new SqlStatementMono($s->columns['resource_id'], sprintf('%%s IN (%s)', $resourceString)));
      $s->setColumnsMask(array('resource_id'));
      $res = $this->_app->db->doQuery($s->toString());
      if ($this->_app->db->getRowsNumber($res)) throw new ExceptionUserTextStorage('error.saveEvent_invalidResource');
    }
    
    // kontrola opakovani
    if (isset($params['repeat'])&&$params['repeat']) {
      if (!isset($params['repeatCycle'])||!$params['repeatCycle']) throw new ExceptionUserTextStorage('error.saveEvent_emptyRepeatCycle');
      if (!isset($params['repeatUntil'])||!$params['repeatUntil']) throw new ExceptionUserTextStorage('error.saveEvent_emptyRepeatUntil');
      if (!isset($params['repeatReservation'])||!$params['repeatReservation']) throw new ExceptionUserTextStorage('error.saveEvent_emptyRepeatReservation');
      
      global $EVENT_REPEAT_RESERVATION;
      if (!in_array($params['repeatReservation'], $EVENT_REPEAT_RESERVATION)) throw new ExceptionUserTextStorage('error.saveEvent_invalidRepeatReservation');
      global $EVENT_REPEAT_CYCLE;
      if (!in_array($params['repeatCycle'], $EVENT_REPEAT_CYCLE)) throw new ExceptionUserTextStorage('error.saveEvent_invalidRepeatCycle');

      //if (!$params['price']&&in_array($params['repeatReservation'],array('SINGLE','BOTH'))) throw new ExceptionUserTextStorage('error.saveEvent_emptyPrice');
      //if (!$params['repeatPrice']&&in_array($params['repeatReservation'],array('PACK','BOTH'))) throw new ExceptionUserTextStorage('error.saveEvent_emptyRepeatPrice');
      if ($params['repeatUntil']<date('Y-m-d H:i:s')) throw new ExceptionUserTextStorage('error.saveEvent_invalidRepeatUntil');
    }
    
    if ($this->_id) {
      $s = new SEventAttendee;
      $s->addStatement(new SqlStatementBi($s->columns['event'], $this->_id, '%s=%s'));
      $s->addStatement(new SqlStatementMono($s->columns['substitute'], "%s='N'"));
      $s->setColumnsMask(array('sum_places'));
      $res = $this->_app->db->doQuery($s->toString());
      $row = $this->_app->db->fetchAssoc($res);
      
      // novy pocet mist nesmi byt mensi, nez kolik uz je rezervaci
      if (isset($params['maxAttendees'])&&($row['sum_places']>$params['maxAttendees'])) throw new ExceptionUserTextStorage('error.saveEvent_tooManyReservation');
      // pokud existuje na akci rezervace, uz nejde menit nektere parametry  
      if ($row['sum_places']) {
        $this->_load();
        
        if ((isset($params['providerId'])&&($params['providerId']!=$this->_data['providerId']))||
            (isset($params['centerId'])&&($params['centerId']!=$this->_data['centerId']))) {
          throw new ExceptionUserTextStorage('error.saveEvent_reservationExists');
        }  
      }
      
      $s = new SEventAttendee;
      $s->addStatement(new SqlStatementBi($s->columns['event'], $this->_id, '%s=%s'));
      $s->addStatement(new SqlStatementMono($s->columns['substitute'], "%s='Y'"));
      $s->setColumnsMask(array('sum_places'));
      $res = $this->_app->db->doQuery($s->toString());
      $row = $this->_app->db->fetchAssoc($res);
      if (isset($params['maxSubstitutes'])&&$params['maxSubstitutes']&&($row['sum_places']>$params['maxSubstitutes'])) throw new ExceptionUserTextStorage('error.saveEvent_tooManySubstitute');
    }
    
    // test na unikatnost
    if (isset($params['externalId'])&&$params['externalId']) {
      $s = new SEvent;
      $s->addStatement(new SqlStatementBi($s->columns['external_id'], $params['externalId'], '%s=%s'));
      $s->addStatement(new SqlStatementBi($s->columns['event_id'], $this->_id, '%s<>%s'));
      $s->addStatement(new SqlStatementBi($s->columns['provider'], ifsetor($params['providerId'],$this->_data['providerId']), '%s=%s'));
      $s->setColumnsMask(array('event_id'));
      $result = $this->_app->db->doQuery($s->toString());
      if ($this->_app->db->getRowsNumber($result) > 0) throw new ExceptionUserTextStorage('error.saveEvent_externalIdNotUnique');
    }
  }
  
  private function _checkBeforeDelete() {
    $s = new SReservation;
    $s->addStatement(new SqlStatementBi($s->columns['event'], $this->_id, '%s=%s'));
    $s->setColumnsMask(array('number'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($row = $this->_app->db->fetchAssoc($res)) {
      throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.deleteEvent_reservationExists'), $this->_data['name'], $row['number']));
    }
  }
  
  protected function _checkBeforeDisable() {
    $s = new SReservation;
    $s->addStatement(new SqlStatementBi($s->columns['event'], $this->_id, '%s=%s'));
    $s->addStatement(new SqlStatementMono($s->columns['cancelled'], '%s IS NULL'));
    $s->addStatement(new SqlStatementMono($s->columns['failed'], '%s IS NULL'));
    $s->addStatement(new SqlStatementMono($s->columns['start'], '%s>=NOW()'));
    $s->setColumnsMask(array('number'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($row = $this->_app->db->fetchAssoc($res)) {
      throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.disableEvent_reservationExists'), $this->_data['name'], $row['number']));
    }
  }
  
  protected function _load($force=false) {
    if ($force) $this->_loaded = false;
    
    if ($this->_id&&!$this->_loaded) {
      $oEvent = new OEvent($this->_id);
      $data = $oEvent->getData();
      $returnData['id'] = $data['event_id'];
      $returnData['externalId'] = $data['external_id'];
      $returnData['name'] = $data['name'];
      $returnData['start'] = $data['start'];
      $returnData['end'] = $data['end'];
      $returnData['description'] = $data['description'];
      $returnData['maxAttendees'] = $data['max_attendees'];
      $returnData['maxCoAttendees'] = $data['max_coattendees'];
      $returnData['maxSubstitutes'] = $data['max_substitutes'];
      $returnData['price'] = $data['price'];
      $returnData['accountTypeId'] = $data['accounttype'];
      $returnData['reservationConditionId'] = $data['reservationcondition'];
      $returnData['notificationTemplateId'] = $data['notificationtemplate'];
      $returnData['documentTemplateId'] = $data['documenttemplate'];
      $returnData['reservationMaxAttendees'] = $data['reservation_max_attendees'];
      $returnData['active'] = $data['active'];
      $returnData['badge'] = $data['badge'];
      $returnData['start'] = $data['start'];
      $returnData['end'] = $data['end'];
      $returnData['repeat'] = $data['repeat_parent'];
      $returnData['repeatParent'] = $data['repeat_parent'];
      $returnData['repeatIndex'] = $data['repeat_index'];
      $returnData['repeatCycle'] = $data['repeat_cycle'];
      $returnData['repeatPrice'] = $data['repeat_price'];
      $returnData['repeatReservation'] = $data['repeat_reservation'];
      $returnData['repeatUntil'] = $data['repeat_until'];
      $returnData['repeatWeekday'] = array();
      foreach (array('mon','tue','wed','thu','fri','sat','sun') as $index=>$day) {
        $returnData['repeatWeekday'][$day] = pow(2,$index)&$data['repeat_weekday'];
      }
      $returnData['repeatWeekdayOrder'] = $data['repeat_weekday_order'];
      $returnData['repeatIndividual'] = $data['repeat_individual'];

      $returnData['feAttendeeVisible'] = $data['fe_attendee_visible'];
      $returnData['feQuickReservation'] = $data['fe_quick_reservation'];
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
      
      $returnData['tag'] = '';
      $s = new SEventTag;
      $s->addStatement(new SqlStatementBi($s->columns['event'], $this->_id, '%s=%s'));
      $s->setColumnsMask(array('name'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        if ($returnData['tag']) $returnData['tag'] .= ',';
        $returnData['tag'] .= $row['name'];
      }
      
      $returnData['portal'] = [];
      $s = new SEvent;
      $s->addStatement(new SqlStatementBi($s->columns['event_id'], $this->_id, '%s=%s'));
      $s->setColumnsMask(array('portal'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $returnData['portal'][] = $row['portal'];
      }
      
      /*$returnData['attendee'] = array();
      $s = new SEventAttendee;
      $s->addStatement(new SqlStatementBi($s->columns['event'], $this->_id, '%s=%s'));
      $s->setColumnsMask(array('customer_name','subscription_time','places','reservation'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $returnData['attendee'][] = array('id'=>$row['reservation'],'name'=>$row['customer_name'],
                                          'time'=>$row['subscription_time'],'places'=>$row['places']);
      }*/

      $s = new SEventAttendee;
      $s->addStatement(new SqlStatementBi($s->columns['event'], $this->_id, '%s=%s'));
      $s->addStatement(new SqlStatementMono($s->columns['substitute'], "%s='N'"));
      $s->setColumnsMask(array('sum_places'));
      $res = $this->_app->db->doQuery($s->toString());
      $row = $this->_app->db->fetchAssoc($res);
      $attendeeNum = ifsetor($row['sum_places'],0);
      $returnData['freeAttendees'] = $returnData['maxAttendees'] - $attendeeNum;
      
      $s = new SEventAttendee;
      $s->addStatement(new SqlStatementBi($s->columns['event'], $this->_id, '%s=%s'));
      $s->addStatement(new SqlStatementMono($s->columns['substitute'], "%s='Y'"));
      $s->setColumnsMask(array('sum_places'));
      $res = $this->_app->db->doQuery($s->toString());
      $row = $this->_app->db->fetchAssoc($res);
      $substitutesNum = ifsetor($row['sum_places'],0);
      $returnData['freeSubstitutes'] = $returnData['maxSubstitutes'] - $substitutesNum;
      
      $returnData['resource'] = array();
      $s = new SEventResource;
      $s->addStatement(new SqlStatementBi($s->columns['event'], $this->_id, '%s=%s'));
      $s->setColumnsMask(array('resource'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $returnData['resource'][] = $row['resource'];
      }
      
      $returnData['attribute'] = array();
      $s = new SEventAttribute;
      $s->addStatement(new SqlStatementBi($s->columns['event'], $returnData['id'], '%s=%s'));
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
      $s = new SEventAttribute;
      $s->addStatement(new SqlStatementBi($s->columns['event'], $returnData['id'], '%s=%s'));
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
    
    // nactu vsechny atributy evidovane u akce
    $s = new SEventAttribute;
    $s->addStatement(new SqlStatementBi($s->columns['event'], $this->_id, '%s=%s'));
    $s->addStatement(new SqlStatementBi($s->columns['applicable'], $applicable, '%s=%s'));
    if (!$includeDisabled) $s->addStatement(new SqlStatementMono($s->columns['disabled'], "%s<>'Y'"));
    if (!$includeRestricted) $s->addStatement(new SqlStatementMono($s->columns['restricted'], '%s IS NULL'));
    elseif (!strcmp($includeRestricted,'INTERNAL')) $s->addStatement(new SqlStatementBi($s->columns['restricted'], $s->columns['restricted'], "(%s IS NULL OR %s='INTERNAL')"));
    elseif (!strcmp($includeRestricted,'READONLY')) $s->addStatement(new SqlStatementBi($s->columns['restricted'], $s->columns['restricted'], "(%S IS NULL OR %s='READONLY')"));
    #$s->addOrder(new SqlStatementAsc($s->columns['category']));
    $s->addOrder(new SqlStatementAsc($s->columns['sequence']));
    $s->setColumnsMask(array('attribute_id','url','value','restricted','mandatory','category','sequence','type','allowed_values'));
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
      $eventTags = array();
      $s = new SEventTag;
      $s->addStatement(new SqlStatementBi($s->columns['event'], $this->_id, '%s=%s'));
      $s->setColumnsMask(array('event','tag','name'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $eventTags[$row['name']] = $row['tag'];
      }
      
      // zalozim nove tagy
      $tag = str_replace(array(';',"\n"),array(','),$params['tag']);
      $tagStringArray = explode(',', $tag);
      $eventSavedTags = array();
      foreach ($tagStringArray as $t) {
        if (!$t = chop($t)) continue;
        
        if (!in_array($t, array_keys($eventTags))) {
          $b = new BTag;
          $b->saveFromCommodity(array('name'=>$t,'provider'=>$params['providerId']));
          
          $o = new OEventTag;
          $o->setData(array('event'=>$this->_id,'tag'=>$b->getId()));
          $o->save();
          
          $eventSavedTags[$b->getId()] = $t;
        } else $eventSavedTags[$eventTags[$t]] = $t; 
      }
      
      // smazu tagy, ktere uz nemaji byt
      if (!isset($params['tagAddOnly'])||!$params['tagAddOnly']) {
        $s = new SEventTag;
        $s->addStatement(new SqlStatementBi($s->columns['event'], $this->_id, '%s=%s'));
        if (count($eventSavedTags)) $s->addStatement(new SqlStatementMono($s->columns['tag'], sprintf('%%s NOT IN (%s)', implode(',',array_keys($eventSavedTags)))));
        $s->setColumnsMask(array('event','tag'));
        $res = $this->_app->db->doQuery($s->toString());
        while ($row = $this->_app->db->fetchAssoc($res)) {
          $o = new OEventTag(array('event'=>$row['event'],'tag'=>$row['tag']));
          $o->delete();
        }
      }
    }
  }
  
  private function _savePortal($params) {
    if (isset($params['portal'])) {
      // smazu puvodni portaly
      $s = new SEvent;
      $s->addStatement(new SqlStatementBi($s->columns['event_id'], $this->_id, '%s=%s'));
      $s->setColumnsMask(array('event_id','portal'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $o = new OEventPortal(array('event'=>$row['event_id'],'portal'=>$row['portal']));
        $o->delete();
      }
      
      foreach ($params['portal'] as $p) {
        if (!$p) continue;
        
        $o = new OEventPortal;
        $o->setData(array('event'=>$this->_id,'portal'=>$p));
        $o->save();
      }
    }
  }
  
  public function freeResource() {
    $this->_load();
    
    $s = new SEventResource;
    $s->addStatement(new SqlStatementBi($s->columns['event'], $this->_id, '%s=%s'));
    $s->setColumnsMask(array('event','resource'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      if ($this->_data&&($this->_data['active']=='Y')) {
        $b = new BResource($row['resource']);
        $b->freeAvailabilityTable($this->_data['start'], $this->_data['end']);
      }
      
      $o = new OEventResource(array('event'=>$row['event'],'resource'=>$row['resource']));
      $o->delete();
    }
  }
  
  private function _saveResource($params) {
    if (isset($params['resource'])) {
      // smazu puvodni zdroje
      $this->freeResource();
      
      // zalozim nove zdroje
      foreach ($params['resource'] as $resource) {
        $o = new OEventResource;
        $o->setData(array('event'=>$this->_id,'resource'=>$resource));
        $o->save();
        
        // kdyz je akce aktivni obsadim zdroje
        if ((isset($params['active'])&&($params['active']=='Y'))||($this->_data['active']=='Y')) {
          try {
            $b = new BResource($resource);
            $b->occupyAvailabilityTableByEvent($this->_id);
          } catch (Exception $e) {
            $o = new OResource($resource);
            $rData = $o->getData();
            $o = new OEvent($this->_id);
            $eData = $o->getData();
            throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveEvent_availabilityConflict'),
                                            $rData['name'],
                                            $this->_app->regionalSettings->convertDateTimeToHuman($eData['start']),
                                            $this->_app->regionalSettings->convertDateTimeToHuman($eData['end'])));;
          }
        }
      }
    }
  }
  
  private function _saveRepeat($params) {
    if (isset($params['repeat'])) {
      $o = new OEvent($this->_id);
      $oData = $o->getData();
      
      $newRepeat = false;
      if ($params['repeat']&&!$oData['repeat_parent']&&(!isset($params['repeatParent'])||!$params['repeatParent'])) {
        // kdyz se uklada opakujici akce a neni nastaven parent, je to nova opakujici udalost
        $newRepeat = true;
        $params['repeatParent'] = $this->_id;
        $params['repeatIndex'] = 0;
      }
      
      if ($params['repeat']) {
        $oData = array();
        if (isset($params['repeatParent'])) $oData['repeat_parent'] = $params['repeatParent'];
        if (isset($params['repeatIndex'])) $oData['repeat_index'] = $params['repeatIndex'];
        $oData['repeat_cycle'] = ifsetor($params['repeatCycle']);
        $oData['repeat_until'] = ifsetor($params['repeatUntil']);
        $oData['repeat_price'] = isset($params['repeatPrice'])&&$params['repeatPrice']?$params['repeatPrice']:0;
        $oData['repeat_reservation'] = ifsetor($params['repeatReservation']);
        
        $repeatWeekday = 0;
        foreach (array('mon','tue','wed','thu','fri','sat','sun') as $index=>$day) {
          if (isset($params['repeatWeekday'][$day])&&$params['repeatWeekday'][$day]) $repeatWeekday |= pow(2,$index);
        }
        $oData['repeat_weekday'] = $repeatWeekday;
        $oData['repeat_weekday_order'] = ifsetor($params['repeatWeekdayOrder'],0);
        $oData['repeat_individual'] = ifsetor($params['repeatIndividual']);
      } else {
        $oData = array('repeat_parent'=>null,'repeat_index'=>null,'repeat_cycle'=>null,
                       'repeat_until'=>null,'repeat_price'=>null,'repeat_reservation'=>null,
                       'repeat_weekday'=>null,'repeat_weekday_order'=>0,'repeat_individual'=>null);
      }
  
      $o->setData($oData);
      $o->save();
      
      $this->_load(true);
      $data = $this->_data;
      $data['repeatSave'] = 'none';
      $data['keepFile'] = true;
      // musim upravit format atributu pro ulozeni
      foreach ($data['attribute'] as $id=>$attribute) {
        if (isset($params['attribute'][$id])&&!strcmp($params['attribute'][$id],'__no_change__')) $data['attribute'][$id] = '__no_change__';
        else $data['attribute'][$id] = $attribute['value'];
      }
      foreach ($data['reservationAttribute'] as $id=>$attribute) $data['reservationAttribute'][$id] = null;
      
      // vygenerovani opakujicich se akci
      if ($params['repeat']) {
        // pole vsech dnu v tydnu 0/1 predelam na pole dnu 1
        $params['repeatAllowedWeekday'] = array();
        if (is_array($params['repeatWeekday'])) {
          foreach ($params['repeatWeekday'] as $index=>$value) {
            if ($value) $params['repeatAllowedWeekday'][$index] = $value;
          }
        }
        // kdyz neni definovan zadny den v tydnu, a neni pozadovano vygenerovani v den v tydnu, bude se nastaveni dnu v tydnu ignorovat
        if (!count($params['repeatAllowedWeekday'])&&!$params['repeatWeekdayOrder']) $params['repeatAllowedWeekday'] = null;
        if ($newRepeat) {
          // upravim external_id
          if (isset($params['externalId'])&&$params['externalId']) {
            $o = new OEvent($this->_id);
            $o->setData(array('external_id'=>$params['externalId'].'_1'));
            $o->save();       
          }
          
          // kdyz je ukladana nova opakujici se akce, vygeneruju opakovani
          $data['externalId'] = '';
          $data['repeatIndex'] = 1;
          
          getNextRepeatTerm($params['repeatCycle'], $data['start'], $data['end'], $params['repeatAllowedWeekday'], $params['repeatWeekdayOrder'], $params['repeatIndividual']);
          while ($data['start']<$params['repeatUntil'].' 23:59:59') {
            if (isset($params['externalId'])&&$params['externalId']) $data['externalId'] = $params['externalId'].'_'.($data['repeatIndex']+1);
            
            $newEvent = new BEvent;
            $newEvent->save($data);
            
            $data['repeatIndex']++;
            getNextRepeatTerm($params['repeatCycle'], $data['start'], $data['end'], $params['repeatAllowedWeekday'], $params['repeatWeekdayOrder'], $params['repeatIndividual']);
          }
        } elseif ($params['repeatSave']=='all') {
          if (isset($data['externalId'])) unset($data['externalId']);
          
          // kdyz se aktualizuji vsechny opakujici se akce
          $s = new SEvent;
          $s->addStatement(new SqlStatementBi($s->columns['repeat_parent'], $this->_data['repeatParent'], '%s=%s'));
          #$s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
          $s->addStatement(new SqlStatementBi($s->columns['repeat_index'], $this->_data['repeatIndex'], '%s>%s'));
          $s->addOrder(new SqlStatementAsc($s->columns['repeat_index']));
          $s->setColumnsMask(array('event_id','start','repeat_index'));
          $res = $this->_app->db->doQuery($s->toString());
          $rows = array();
          while ($row = $this->_app->db->fetchAssoc($res)) {
            // nejdriv musim uvolnit blokaci zdroju kvuli moznym zmenam terminu opakovanych akci
            $b = new BEvent($row['event_id']);
            $b->freeResource();
            
            $rows[] = $row;
          }
          
          // pak ulozim zmeny pro vsechny opakovane akce
          foreach ($rows as $row) {
            // dle repeat_index a repeat_cycle vypoctu termin opakovane akce
            $diff = $row['repeat_index']-$data['repeatIndex'];
            while ($diff--) {
              getNextRepeatTerm($params['repeatCycle'], $data['start'], $data['end'], $params['repeatAllowedWeekday'], $params['repeatWeekdayOrder'], $params['repeatIndividual']);
              $data['repeatIndex']++;
            }
            if ($data['start']<$params['repeatUntil'].' 23:59:59') {
              $newEvent = new BEvent($row['event_id']);
              $newEvent->save($data);
            } else {
              // pokud se zkracuje obdobi opakovani, smazu prebyvajici akce
              $b = new BEvent($row['event_id']);
              $b->delete();
            }
          }
          // pokud se prodluzuje obdobi, pridam nove akce
          getNextRepeatTerm($params['repeatCycle'], $data['start'], $data['end'], $params['repeatAllowedWeekday'], $params['repeatWeekdayOrder'], $params['repeatIndividual']);
          $data['repeatIndex']++;
          while ($data['start']<$params['repeatUntil'].' 23:59:59') {
            $newEvent = new BEvent;
            $newEvent->save($data);
            
            $data['repeatIndex']++;
            getNextRepeatTerm($params['repeatCycle'], $data['start'], $data['end'], $params['repeatAllowedWeekday'], $params['repeatWeekdayOrder'], $params['repeatIndividual']);
          }
        } elseif ($params['repeatSave']=='this') {
          // kdyz se aktualizuje pouze dana akce, vynecham ji opakovani
          $o->setData(array('repeat_parent'=>null,'repeat_index'=>null,'repeat_cycle'=>null,
                            'repeat_until'=>null,'repeat_price'=>null,'repeat_reservation'=>null,
                            'repeat_weekday'=>null, 'repeat_weekday_order'=>0, 'repeat_individual'=>null));
          $o->save();
          
          // kdyz se odlinkovava prvni akce z opakovani, musi se posunout parent na prvni dalsi
          $s = new SEvent;
          $s->addStatement(new SqlStatementBi($s->columns['repeat_parent'], $this->_id, '%s=%s'));
          $s->addOrder(new SqlStatementAsc($s->columns['repeat_index']));
          $s->setColumnsMask(array('event_id'));
          $res = $this->_app->db->doQuery($s->toString());
          $newParent = null;
          while ($row = $this->_app->db->fetchAssoc($res)) {
            if (($row['event_id']!=$this->_id)&&!$newParent) $newParent = $row['event_id'];
            
            $o1 = new OEvent($row['event_id']);
            $o1->setData(array('repeat_parent'=>$newParent));
            $o1->save();
          }
        }
      }
    }
  }
  
  private function _arrangeReservation($params) {
    if (isset($params['active'])&&($params['active']=='N')&&isset($params['cancelReservation'])&&($params['cancelReservation']=='Y')) {
      $s = new SReservation;
      $s->addStatement(new SqlStatementBi($s->columns['event'], $this->_id, '%s=%s'));
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
      $s = new SEventAttribute;
      $s->addStatement(new SqlStatementBi($s->columns['attribute'], $id, '%s=%s'));
      $s->addStatement(new SqlStatementBi($s->columns['event'], $this->_id, '%s=%s'));
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
        $o = new OEventAttribute(array('event'=>$this->_id,'attribute'=>$id));
        $o->setData(array('value'=>$attribute['value']));
        $o->save();
      } else {
        $o = new OEventAttribute;
        $o->setData(array('event'=>$this->_id,'attribute'=>$id,'value'=>$attribute['value']));
        $o->save();
      }
      $idsToSave[] = $id;
    }
    
    if (!isset($params['attributeAddOnly'])||!$params['attributeAddOnly']) {
      $s = new SEventAttribute;
      $s->addStatement(new SqlStatementBi($s->columns['event'], $this->_id, '%s=%s'));
      $s->addStatement(new SqlStatementMono($s->columns['applicable'], "%s='COMMODITY'"));
      if (count($idsToSave)) $s->addStatement(new SqlStatementMono($s->columns['attribute'], sprintf('%%s NOT IN (%s)', implode(',', $idsToSave))));
      $s->setColumnsMask(array('attribute','value','type'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $o = new OEventAttribute(array('event'=>$this->_id, 'attribute'=>$row['attribute']));
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
      $s = new SEventAttribute;
      $s->addStatement(new SqlStatementBi($s->columns['attribute'], $id, '%s=%s'));
      $s->addStatement(new SqlStatementBi($s->columns['event'], $this->_id, '%s=%s'));
      $s->setColumnsMask(array('attribute','value'));
      $res = $this->_app->db->doQuery($s->toString());
      $row = $this->_app->db->fetchAssoc($res);
      
      if (!$row) {
        $o = new OEventAttribute;
        $o->setData(array('event'=>$this->_id,'attribute'=>$id,'value'=>null));
        $o->save();
      }
      $idsToSave[] = $id;
    }
    
    if (!isset($params['reservationAttributeAddOnly'])||!$params['reservationAttributeAddOnly']) {
      $s = new SEventAttribute;
      $s->addStatement(new SqlStatementBi($s->columns['event'], $this->_id, '%s=%s'));
      $s->addStatement(new SqlStatementMono($s->columns['applicable'], "%s='RESERVATION'"));
      if (count($idsToSave)) $s->addStatement(new SqlStatementMono($s->columns['attribute'], sprintf('%%s NOT IN (%s)', implode(',', $idsToSave))));
      $s->setColumnsMask(array('attribute','value','type'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $o = new OEventAttribute(array('event'=>$this->_id, 'attribute'=>$row['attribute']));
        $o->delete();
      }
    }
  }
  
  private function _save($params) {
    //adump($params);die;
    $this->_app->db->beginTransaction();

    $o = new OEvent($this->_id?$this->_id:null);
    $oData = array();
    if (isset($params['organiserId'])) $oData['organiser'] = $params['organiserId'];
    if (isset($params['centerId'])) $oData['center'] = $params['centerId'];
    if (isset($params['providerId'])) $oData['provider'] = $params['providerId'];
    if (isset($params['externalId'])) $oData['external_id'] = $params['externalId'];
    if (isset($params['name'])) $oData['name'] = $params['name'];
    if (isset($params['description'])) $oData['description'] = $params['description'];
    if (isset($params['maxAttendees'])) $oData['max_attendees'] = $params['maxAttendees'];
    if (isset($params['maxCoAttendees'])) $oData['max_coattendees'] = $params['maxCoAttendees']?$params['maxCoAttendees']:1;
    if (isset($params['maxSubstitutes'])) $oData['max_substitutes'] = $params['maxSubstitutes']?$params['maxSubstitutes']:null;
    if (isset($params['price'])) $oData['price'] = $params['price']?$params['price']:0;
    if (isset($params['accountTypeId'])) $oData['accounttype'] = $params['accountTypeId']?$params['accountTypeId']:null;
    if (isset($params['reservationConditionId'])) $oData['reservationcondition'] = $params['reservationConditionId']?$params['reservationConditionId']:null;
    if (isset($params['notificationTemplateId'])) $oData['notificationtemplate'] = $params['notificationTemplateId']?$params['notificationTemplateId']:null;
    if (isset($params['documentTemplateId'])) $oData['documenttemplate'] = $params['documentTemplateId']?$params['documentTemplateId']:null;
    if (isset($params['reservationMaxAttendees'])) $oData['reservation_max_attendees'] = $params['reservationMaxAttendees']?$params['reservationMaxAttendees']:1;
    
    if (isset($params['start'])) $oData['start'] = $params['start'];
    if (isset($params['end'])) {
      list($date,$time) = explode(' ',$params['end']);
      if (!strcmp($time,'24:00:00')) {
        $date = $this->_app->regionalSettings->increaseDate($date);
        $params['end'] = sprintf('%s 00:00:00', $date);
      }
      $oData['end'] = $params['end'];
    }
    
    if (isset($params['active'])) $oData['active'] = $params['active'];
    if (isset($params['badge'])) $oData['badge'] = $params['badge'];

    if (isset($params['feAttendeeVisible'])) $oData['fe_attendee_visible'] = $params['feAttendeeVisible']?$params['feAttendeeVisible']:null;
    if (isset($params['feQuickReservation'])) $oData['fe_quick_reservation'] = $params['feQuickReservation'];
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
    $this->_saveResource($params);
    if (isset($params['attribute'])) $this->_saveAttribute($params);
    if (isset($params['reservationAttribute'])) $this->_saveReservationAttribute($params);
    
    #adump($params);throw new ExceptionUser('X');
    $this->_saveRepeat($params);
    
    $this->_arrangeReservation($params);
    
    $this->_app->db->commitTransaction();
  }

  public function save($params) {
    if (!$this->_checkAccess($params)) throw new ExceptionUserTextStorage('error.accessDenied');
    
    $this->_load();
    
    $this->_checkBeforeSave($params);
    
    $this->_save($params);
  }
  
  private function _delete($wholeCycle) {
    $this->_app->db->beginTransaction();
    
    $this->freeResource();
    
    if ($this->_data['repeatParent']&&($this->_id==$this->_data['repeatParent'])) {
      // kdyz se maze prvni akce z opakovani, musi se posunout parent na prvni dalsi
      $s = new SEvent;
      $s->addStatement(new SqlStatementBi($s->columns['repeat_parent'], $this->_id, '%s=%s'));
      $s->addOrder(new SqlStatementAsc($s->columns['repeat_index']));
      $s->setColumnsMask(array('event_id'));
      $res = $this->_app->db->doQuery($s->toString());
      $newParent = null;
      while ($row = $this->_app->db->fetchAssoc($res)) {
        if ($wholeCycle) {
          // kdyz se ma mazat cely cyklus
          $b = new BEvent($row['event_id']);
          $b->delete();
        } else {
          // jinak hledam noveho parenta
          if (($row['event_id']!=$this->_id)&&!$newParent) $newParent = $row['event_id'];

          $o1 = new OEvent($row['event_id']);
          $o1->setData(array('repeat_parent' => $newParent));
          $o1->save();
        }
      }
    }
    
    $o = new OEvent($this->_id);
    $o->delete();
    
    $this->_app->db->commitTransaction();
  }
  
  public function delete($wholeCycle=false) {
    if (!$this->_checkAccess()) throw new ExceptionUserTextStorage('error.accessDenied');
  
    $this->_load();
    
    $this->_checkBeforeDelete();
    
    $ret = $this->_data['name'];
  
    $this->_delete($wholeCycle);
    
    return $ret;
  }
  
  public function disable() {
    if (!$this->_checkAccess()) throw new ExceptionUserTextStorage('error.accessDenied');
    
    $this->_checkBeforeDisable();
    
    $this->_app->db->beginTransaction();
    
    $this->freeResource();
  
    $o = new OEvent($this->_id);
    $oData = $o->getData();
    
    $ret = $oData['name'];
    
    $o->setData(array('active'=>'N'));
    $o->save();
    
    $this->_app->db->commitTransaction();
    
    return $ret;
  }
  
  public function copy() {
    if (!$this->_checkAccess()) throw new ExceptionUserTextStorage('error.accessDenied');
    
    $this->_load();
    
    $ret = $this->_data['name'];
    
    $this->_data['name'] .= ' (kopie)';
    $this->_data['externalId'] = null;
    if (($this->_data['start']<date('Y-m-d H:i:s'))||count($this->_data['resource'])) {
      list($originalStartDate,$originalStartTime) = explode(' ', $this->_data['start']);
      list($originalEndDate,$originalEndTime) = explode(' ', $this->_data['end']);
      $newDate = $this->_app->regionalSettings->increaseDate(date('Y-m-d'));
      $this->_data['start'] = $newDate.' '.$originalStartTime;
      $this->_data['end'] = $newDate.' '.$originalEndTime;
    }
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
    if ($this->_data['repeatParent']) {
      $this->_data['repeatParent'] = null;
    }
    
    $newEvent = new BEvent;
    $newEvent->save($this->_data);
    
    return $ret;
  }
  
  private function _getSubstituteAttribute($attendee, $includeRestrictedAttribute, $includeDisabledAttribute) {
    $attribute = array();
    
    $s = new SEventAttendeeAttribute;
    $s->addStatement(new SqlStatementBi($s->columns['eventattendee'], $attendee, '%s=%s'));
    #$s->addStatement(new SqlStatementBi($s->columns['applicable'], $applicable, '%s=%s'));
    if (!$includeDisabledAttribute) $s->addStatement(new SqlStatementMono($s->columns['disabled'], "%s<>'Y'"));
    if (!$includeRestrictedAttribute) $s->addStatement(new SqlStatementMono($s->columns['restricted'], '%s IS NULL'));
    elseif (!strcmp($includeRestrictedAttribute,'INTERNAL')) $s->addStatement(new SqlStatementBi($s->columns['restricted'], $s->columns['restricted'], "(%s IS NULL OR %s='INTERNAL')"));
    elseif (!strcmp($includeRestrictedAttribute,'READONLY')) $s->addStatement(new SqlStatementBi($s->columns['restricted'], $s->columns['restricted'], "(%S IS NULL OR %s='READONLY')"));
    $s->addOrder(new SqlStatementAsc($s->columns['category']));
    $s->addOrder(new SqlStatementAsc($s->columns['sequence']));
    $s->setColumnsMask(array('attribute_id','value','restricted','mandatory','category','sequence','type','allowed_values'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $s1 = new SAttributeName;
      $s1->addStatement(new SqlStatementBi($s1->columns['attribute'], $row['attribute_id'], '%s=%s'));
      $s1->setColumnsMask(array('lang','name'));
      $res1 = $this->_app->db->doQuery($s1->toString());
      $name = array(); while ($row1 = $this->_app->db->fetchAssoc($res1)) { $name[$row1['lang']] = $row1['name']; }
      
      $attribute[$row['attribute_id']] = array(
            'attributeId'       => $row['attribute_id'],
            'name'              => $name,
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
        $s2->setColumnsMask(array('hash','name'));
        $res2 = $this->_app->db->doQuery($s2->toString());
        $row2 = $this->_app->db->fetchAssoc($res2);
        
        $attribute[$row['attribute_id']]['id'] = $row['value'];
        $attribute[$row['attribute_id']]['valueId'] = $row2['hash'];
        $attribute[$row['attribute_id']]['value'] = $row2['name'];
      }
    }
    
    return $attribute;
  }
  
  public function getSubstitute($attendee, $includeRestrictedAttribute=false, $includeDisabledAttribute=false) {
    if (!$this->_checkSubstituteAccess(array('attendeeId'=>$attendee))) throw new ExceptionUserTextStorage('error.accessDenied');
    
    $s = new SEventAttendee;
    $s->addStatement(new SqlStatementBi($s->columns['eventattendee_id'], $attendee, '%s=%s'));
    $s->addStatement(new SqlStatementBi($s->columns['event'], $this->_id, '%s=%s'));
    $s->addStatement(new SqlStatementMono($s->columns['substitute'], "%s='Y'"));
    $s->setColumnsMask(array('eventattendee_id','user','fullname','places','provider','event_id','name','description','price',
                             'max_coattendees','reservation_max_attendees','substitute_mandatory'));
    $res = $this->_app->db->doQuery($s->toString());
    $row = $this->_app->db->fetchAssoc($res);
    $row['eventAttendeeId'] = $row['eventattendee_id'];
    $row['providerId'] = $row['provider'];
    $row['userId'] = $row['user'];
    $row['userName'] = $row['fullname'];
    $row['mandatory'] = $row['substitute_mandatory'];
    $row['eventId'] = $row['event_id'];
    $row['eventName'] = $row['name'];
    $row['eventDescription'] = $row['description'];
    $row['eventPrice'] = $row['price'];
    $row['eventCoAttendees'] = $row['max_coattendees'];
    $row['eventReservationMaxAttendees'] = $row['reservation_max_attendees'];
    
    $row['eventAttendeePerson'] = array();
    $s1 = new SEventAttendeePerson;
    $s1->addStatement(new SqlStatementBi($s->columns['eventattendee'], $row['eventattendee_id'], '%s=%s'));
    $s1->setColumnsMask(array('eventattendeeperson_id','subaccount','firstname','lastname','email'));
    $res1 = $this->_app->db->doQuery($s1->toString());
    while ($row1 = $this->_app->db->fetchAssoc($res1)) {
      $row['eventAttendeePerson'][] = array(
          'user'        => $row1['subaccount'],
          'firstname'   => $row1['firstname'],
          'lastname'    => $row1['lastname'],
          'email'       => $row1['email'],
          );
    }
    
    $row['attribute'] = $this->_getSubstituteAttribute($attendee, $includeRestrictedAttribute, $includeDisabledAttribute);
    
    return $row;
  }
  
  private function _saveAttendeeAttribute($params) {
    if (!isset($params['attribute'])) $params['attribute'] = array();
    
    $attributes = $this->_validateAttribute($params);
    
    $idsToSave = array();
    foreach ($attributes as $id=>$attribute) {
      $s = new SEventAttendeeAttribute;
      $s->addStatement(new SqlStatementBi($s->columns['attribute'], $id, '%s=%s'));
      $s->addStatement(new SqlStatementBi($s->columns['eventattendee'], $params['eventAttendeeId'], '%s=%s'));
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

          $attribute['value'] = $fileId;
        }
      }
      
      if ($row) {
        $o = new OEventAttendeeAttribute(array('eventattendee'=>$params['eventAttendeeId'],'attribute'=>$id));
        $o->setData(array('value'=>$attribute['value']));
        $o->save();
      } else {
        $o = new OEventAttendeeAttribute;
        $o->setData(array('eventattendee'=>$params['eventAttendeeId'],'attribute'=>$id,'value'=>$attribute['value']));
        $o->save();
      }
      $idsToSave[] = $id;
    }
    
    $s = new SEventAttendeeAttribute;
    $s->addStatement(new SqlStatementBi($s->columns['eventattendee'], $params['eventAttendeeId'], '%s=%s'));
    if (count($idsToSave)) $s->addStatement(new SqlStatementMono($s->columns['attribute'], sprintf('%%s NOT IN (%s)', implode(',', $idsToSave))));
    $s->setColumnsMask(array('attribute','value','type'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $o = new OEventAttendeeAttribute(array('eventattendee'=>$params['eventAttendeeId'], 'attribute'=>$row['attribute']));
      $o->delete();
      if (!strcmp($row['type'],'FILE')) {
        $o = new OFile($row['value']);
        $o->delete();
      }
    }
  }
  
  public function saveSubstitute($params) {
    if (!$this->_checkSubstituteAccess(array('attendeeId'=>ifsetor($params['attendeeId']),'userId'=>ifsetor($params['userId'])))) throw new ExceptionUserTextStorage('error.accessDenied');
    
    $this->_load();

    if (!isset($params['attendeePerson'])||!is_array($params['attendeePerson'])||(count($params['attendeePerson'])<$params['places'])) throw new ExceptionUserTextStorage('error.saveEventSubstitute_missingEventAttendee');
    foreach ($params['attendeePerson'] as $attendee) {
      if (BCustomer::getProviderSettings($this->_data['providerId'],'userSubaccount')=='Y') {
        if (!isset($attendee['user'])||!$attendee['user']) throw new ExceptionUserTextStorage('error.saveReservation_missingEventAttendeeUser');
      } else {
        if (!isset($attendee['firstname'])||!$attendee['firstname']) throw new ExceptionUserTextStorage('error.saveEventSubstitute_missingEventAttendeeFirstname');
        if (!isset($attendee['lastname'])||!$attendee['lastname']) throw new ExceptionUserTextStorage('error.saveEventSubstitute_missingEventAttendeeLastname');
        #if (!isset($attendee['email'])||!$attendee['email']) throw new ExceptionUserTextStorage('error.saveEventSubstitute_missingEventAttendeeEmail');
      }
    }
    
    list($startDate,$startTime) = explode(' ', $this->_data['start']);
    list($endDate,$endTime) = explode(' ', $this->_data['end']);
    $subject = sprintf('%s (%s)', $this->_data['name'], $this->_app->regionalSettings->convertDateTimeToHuman($this->_data['start']));
    
    $s = new SEvent;
    $s->addStatement(new SqlStatementBi($s->columns['event_id'], $this->_id, '%s=%s'));
    $s->setColumnsMask(array('provider','organiser','free','occupied','free_substitute','occupied_substitute'));
    $res = $this->_app->db->doQuery($s->toString());
    $row = $this->_app->db->fetchAssoc($res);
    
    if (isset($params['attendeeId'])) {
      $o = new OEventAttendee($params['attendeeId']);
      $oData = $o->getData();
      $row['free_substitute']+=$oData['places'];
    }
    
    if ($this->_data['reservationMaxAttendees']<$params['places']) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveEventSubstitute_eventPlacesAllowed'), $subject, $this->_data['reservationMaxAttendees']));
    if ($row['free_substitute']<$params['places']) throw new ExceptionUserTextStorage('error.saveEventSubstitute_eventNotEnoughPlaces');
    if ($this->_data['start']<date('Y-m-d H:i:s')) throw new ExceptionUserTextStorage('error.saveEventSubstitute_eventInvalidTime');

    // muze byt mandatory rezervace organizatorem vynucena nastavenim poskytovatele
    if (($params['userId']!=$this->_app->auth->getUserId())&&($row['organiser']==$this->_app->auth->getUserId())) {
      if ((BCustomer::getProviderSettings($row['provider'], 'allowMandatoryReservation')=='Y')&&(BCustomer::getProviderSettings($row['provider'], 'organiserMandatorySubstitute')=='Y')) {
        $params['mandatory'] = 'Y';
      }
    }
    // kdyz nejsou povolene mandatory rezervace, bude se vzdy ukladat mandatory='N'
    if (BCustomer::getProviderSettings($row['provider'], 'allowMandatoryReservation')=='N') {
      $params['mandatory'] = 'N';
    }
    
    $this->_app->db->beginTransaction();
    
    $o = new OEventAttendee(ifsetor($params['attendeeId']));
    $oData = array(
          'event'                 => $this->_id,
          'user'                  => $params['userId'],
          'substitute'            => 'Y',
          'substitute_mandatory'  => isset($params['mandatory'])&&$params['mandatory']=='Y'?'Y':'N',
          'places'                => $params['places'],
          );
    if (!isset($params['attendeeId'])) $oData['subscription_time'] = date('Y-m-d H:i:s');
    $o->setData($oData);
    $o->save();
    $eaId = $o->getId();
    
    $s1 = new SEventAttendeePerson;
    $s1->addStatement(new SqlStatementBi($s1->columns['eventattendee'], $eaId, '%s=%s'));
    $s1->setColumnsMask(array('eventattendeeperson_id'));
    $res1 = $this->_app->db->doQuery($s1->toString());
    while ($row1 = $this->_app->db->fetchAssoc($res1)) {
      $o = new OEventAttendeePerson($row1['eventattendeeperson_id']);
      $o->delete();
    }
    
    $ret = '';
    foreach ($params['attendeePerson'] as $attendee) {
      $o = new OEventAttendeePerson;
      $o->setData(array(
          'eventattendee'   => $eaId,
          'user'            => isset($attendee['user'])&&$attendee['user']?$attendee['user']:null,
          'firstname'       => ifsetor($attendee['firstname']),
          'lastname'        => ifsetor($attendee['lastname']),
          'email'           => ifsetor($attendee['email']),
          ));
      $o->save();
      
      if ($ret) $ret .= ',';
      if (isset($attendee['user'])&&$attendee['user']) {
        $o1 = new OUser($attendee['user']);
        $o1Data = $o1->getData();
        $ret .= sprintf('%s %s', $o1Data['firstname'], $o1Data['lastname']);
      } else $ret .= sprintf('%s %s', $attendee['firstname'], $attendee['lastname']);
    }
    
    $params['eventAttendeeId'] = $eaId;
    $params['attributeValidation'] = 'exact';
    if (!isset($params['attributeConverted'])) $params['attributeConverted'] = false;
    $this->_saveAttendeeAttribute($params);
    
    if (!isset($params['attendeeId'])) {
      BNotificationTemplate::generate(array('type'=>'S_CREATE','providerId'=>$this->_data['providerId'],'userId'=>$params['userId'],
                                          'data'=>array('commodity_name'=>$this->_data['name'],'commodity_description'=>$this->_data['description'],
                                            'organiser_name'=>$this->_data['organiserName'],'organiser_email'=>$this->_data['organiserEmail'],
                                            'free_places'=>$row['free'],'occupied_places'=>$row['occupied'],
                                            'free_substitute_places'=>$row['free_substitute']-$params['places'],'occupied_substitute_places'=>$row['occupied_substitute']+$params['places'],
                                            'start_date'=>$startDate,'start_time'=>$startTime,'end_date'=>$endDate,'end_time'=>$endTime,
                                            )));
    }

    $this->_app->db->commitTransaction();
    
    return $ret;
  }
  
  public function deleteSubstitute($params) {
    if (!$this->_checkSubstituteAccess(array('attendeeId'=>$params['eventAttendeeId']))) throw new ExceptionUserTextStorage('error.accessDenied');
    
    $this->_load();
    
    $this->_app->db->beginTransaction();
    
    $s = new SEventAttendee;
    $s->addStatement(new SqlStatementMono($s->columns['substitute'], "%s='Y'"));
    $s->addStatement(new SqlStatementBi($s->columns['eventattendee_id'], $params['eventAttendeeId'], '%s=%s'));
    $s->setColumnsMask(array('eventattendee_id','fullname'));
    $res = $this->_app->db->doQuery($s->toString());
    if (!$row = $this->_app->db->fetchAssoc($res)) throw new ExceptionUserTextStorage('error.deleteEventSubstitute_invalidSubstitute');
    
    $o = new OEventAttendee($row['eventattendee_id']);
    $o->delete();
    
    $this->_app->db->commitTransaction();
    
    return $row['fullname'];
  }
  
  public function swapSubstitute($params) {
    if (isset($params['substituteId'])) {
      // nahradnika menim na rezervaci
      $substituteData = $this->getSubstitute($params['substituteId']);
      foreach ($substituteData['attribute'] as $id=>$attr) {
        if (!strcmp($attr['type'],'FILE')) {
          if (isset($attr['id'])&&$attr['id']) {
            $file = new BFile($attr['id']);
            $linkName = $file->export();
            $substituteData['attribute'][$id] = $linkName;
          } else $substituteData['attribute'][$id] = null;
        } else {
          $substituteData['attribute'][$id] = $attr['value'];
        }
      }
      
      $resParams = array('eventParams'=>array('eventId'=>$this->_id));
      if (isset($substituteData['userId'])) $resParams['userId'] = $substituteData['userId'];
      if (isset($substituteData['places'])) $resParams['eventParams']['eventPlaces'] = $substituteData['places'];
      if (isset($substituteData['eventAttendeePerson'])) $resParams['eventParams']['eventAttendeePerson'] = $substituteData['eventAttendeePerson'];
      if (isset($substituteData['attribute'])) {
        $resParams['attribute'] = $substituteData['attribute'];
        $resParams['attributeConverted'] = true;
      }
      #adump($resParams);die;
      
      $this->_app->db->beginTransaction();
      
      $bRes = new BReservation;
      $number = $bRes->save($resParams);
      
      $this->deleteSubstitute(array('eventAttendeeId'=>$params['substituteId']));
      
      $this->_app->db->commitTransaction();
      
      return sprintf($this->_app->textStorage->getText('info.editReservation_saveOk'), $number);
    } elseif (isset($params['reservationId'])) {
      $bRes = new BReservation($params['reservationId']);
      $reservationData = $bRes->getData();
      if ($reservationData['eventPack']=='Y') throw new ExceptionUserTextStorage('error.reservation_swapSubstitute_packNotAllowed');

      foreach ($reservationData['attribute'] as $id=>$attr) {
        if (!strcmp($attr['type'],'FILE')) {
          if (isset($attr['id'])&&$attr['id']) {
            $file = new BFile($attr['id']);
            $linkName = $file->export();
            $reservationData['attribute'][$id] = $linkName;
          } else $reservationData['attribute'][$id] = null;
        } else {
          $reservationData['attribute'][$id] = $attr['value'];
        }
      }
      
      $sParams = array();
      if (isset($reservationData['userId'])) $sParams['userId'] = $reservationData['userId'];
      if (isset($reservationData['eventPlaces'])) $sParams['places'] = $reservationData['eventPlaces'];
      if (isset($reservationData['eventAttendeePerson'])) $sParams['attendeePerson'] = $reservationData['eventAttendeePerson'];
      if (isset($reservationData['attribute'])) {
        $sParams['attribute'] = $reservationData['attribute'];
        $sParams['attributeConverted'] = true;
      }
      #adump($sParams);die;
      
      $this->_app->db->beginTransaction();
      
      $name = $this->saveSubstitute($sParams);
      
      $bRes->delete();
      
      $this->_app->db->commitTransaction();
      
      return sprintf($this->_app->textStorage->getText('info.editEvent_substituteSaveOk'), $name);
    }

    return null;
  }

  public function isPaymentNeeded($when=null) {
    $ret = false;

    if ($this->_id) {
      if (!$when) $when = date('Y-m-d H:i:s');

      $o = new OEvent($this->_id);
      $oData = $o->getData();

      // kdyz je cena za rezervaci=0, neni platba potreba nikdy
      if ((!strcmp($oData['repeat_reservation'],'PACK')&&$oData['repeat_price'])||(strcmp($oData['repeat_reservation'],'PACK')&&$oData['price'])) {
        // rezervacni podminky na zaplaceni "od akce" a "od zdroje akce"
        $conditions = array();
        if ($oData['reservationcondition']) $conditions[] = $oData['reservationcondition'];
        $s = new SEventResource;
        $s->addStatement(new SqlStatementBi($s->columns['event'], $this->_id, '%s=%s'));
        $s->addStatement(new SqlStatementMono($s->columns['reservationcondition'], '%s IS NOT NULL'));
        $s->setColumnsMask(array('reservationcondition'));
        $res = $this->_app->db->doQuery($s->toString());
        while ($row = $this->_app->db->fetchAssoc($res)) {
          $conditions[] = $row['reservationcondition'];
        }

        if (count($conditions)) {
          $s = new SReservationConditionItem;
          $s->addStatement(new SqlStatementMono($s->columns['reservationcondition'], sprintf('%%s IN (%s)', implode(',',$conditions))));
          $s->setColumnsMask(array('advance_payment'));
          $res = $this->_app->db->doQuery($s->toString());
          while ($row = $this->_app->db->fetchAssoc($res)) {
            if ($row['advance_payment']) {
              $paymentTime = $this->_app->regionalSettings->decreaseDateTime($oData['start'], 0, 0, 0, 0, $row['advance_payment']);
              if ($when>$paymentTime) {
                $ret = true;
                break;
              }
            }
          }
        }
      }
    }

    return $ret;
  }
}

?>
