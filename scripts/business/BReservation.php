<?php

class BReservation extends BusinessObject {
  private $_creatingNew;
  private $_oldData;

  public static function getProviderFromOnlinePaymentTarget($targetId) {
    $app = Application::get();

    $ids = str_replace('|',',',substr($targetId, 1, -1));

    $provider = null;
    $s = new SReservation;
    $s->addStatement(new SqlStatementMono($s->columns['reservation_id'], sprintf('%%s IN (%s)', $ids)));
    $s->setColumnsMask(array('provider'));
    $res = $app->db->doQuery($s->toString());
    while ($row = $app->db->fetchAssoc($res)) {
      if ($provider&&($provider!=$row['provider'])) return false;
      $provider = $row['provider'];
    }

    return $provider;
  }

  private function _exists() {
    $o = new OReservation($this->_id);
    $oData = $o->getData();

    return $oData!==null;
  }

  private function _checkAccess($params=array()) {
    $ret = false;
    
    $this->_load();
    
    while (true) {
      if ($user=$this->_app->auth->isUser()) {
        $isOrganiser = false;

        // to je tady kvuli kontrole pristupu pro getData, kdy $params neni nastaveno
        if (!isset($params['userId'])) $params['userId'] = ifsetor($this->_data['userId']);
        if (!isset($params['eventParams']['eventId'])) $params['eventParams']['eventId'] = ifsetor($this->_data['eventId']);
        if (!isset($params['mandatory'])) $params['mandatory'] = ifsetor($this->_data['mandatory']);

        // pokud je rezervace na akci, zjistim organizatora
        if (isset($params['eventParams']['eventId'])) {
          $s = new SEvent;
          $s->addStatement(new SqlStatementBi($s->columns['event_id'], $params['eventParams']['eventId'], '%s=%s'));
          $s->setColumnsMask(array('organiser'));
          $res = $this->_app->db->doQuery($s->toString());
          $row = $this->_app->db->fetchAssoc($res);
          $isOrganiser = $row['organiser']==$user;
        }

        // organizator muze delat rezervace na svoje akce
        if (($params['userId']!=$user)&&!$isOrganiser) break;

        // mandatory rezervace muze delat pouze organizator akce
        if (isset($params['mandatory'])&&($params['mandatory']=='Y')&&!$isOrganiser) break;

        // uzivatel rezervace musi mit registraci u poskytovatele (todle tady je pro jistotu aby nesla udelat rezervace bez registrace u poskytovatele)
        if (!$this->_id&&$params['userId']) {
          $s = new SUserRegistration;
          $s->addStatement(new SqlStatementBi($s->columns['user'], $params['userId'], '%s=%s'));
          $s->addStatement(new SqlStatementBi($s->columns['provider'], $params['providerId'], '%s=%s'));
          $s->setColumnsMask(array('credit'));
          $res = $this->_app->db->doQuery($s->toString());
          if (!$this->_app->db->getRowsNumber($res)) break;
        }
      } elseif ($this->_app->auth->isProvider()) {
        if ($this->_id&&!in_array($this->_data['centerId'],$this->_app->auth->getAllowedCenter('array'))) break;
        if (isset($params['centerId'])&&!in_array($params['centerId'],$this->_app->auth->getAllowedCenter('array'))) break;

        $reservationProvider = ifsetor($params['providerId'], $this->_data['providerId']);
        // kdyz je poskytovatel a nema pravo reservation_admin ani power_organiser, neulozi
        if (!$this->_app->auth->haveRight('reservation_admin', $reservationProvider)&&!$this->_app->auth->haveRight('power_organiser', $reservationProvider)) break;
        elseif ($this->_app->auth->haveRight('reservation_admin', $reservationProvider)) {
          // kdyz je to admin a ma pravo ukladat rezervace
        } elseif ($this->_app->auth->haveRight('power_organiser', $reservationProvider)) {
          $user=$this->_app->auth->getUserId();
          if (!isset($params['userId'])) $params['userId'] = ifsetor($this->_data['userId']);
          if (!isset($params['eventParams']['eventId'])) $params['eventParams']['eventId'] = ifsetor($this->_data['eventId']);

          // power organizator muze delat rezervace na svoje akce
          if ($params['userId']!=$user) {
            if (!isset($params['eventParams']['eventId'])) break;
            else {
              $s = new SEvent;
              $s->addStatement(new SqlStatementBi($s->columns['event_id'], $params['eventParams']['eventId'], '%s=%s'));
              $s->setColumnsMask(array('organiser'));
              $res = $this->_app->db->doQuery($s->toString());
              $row = $this->_app->db->fetchAssoc($res);
              if ($row['organiser']!=$user) break;
            }
          }
        }
      } elseif ($this->_app->auth->haveRight('reservation_admin')) {
        // kdyz je to admin a ma pravo ukladat rezervace
      } else {
        break;
      }
      
      $ret = true;
      break;
    }
    
    return $ret;
  }

  private function _checkDeleteAccess() {
    $ret = $this->_app->auth->haveRight('delete_record', $this->_app->auth->getActualProvider());

    return $ret;
  }

  private function _checkPaymentAccess($action, $params=array()) {
    if (!strcmp($action, 'pay')) {
      if (isset($params['arrangeCredit'])&&($params['arrangeCredit']=='Y')) {
        if (!$this->_app->auth->isAdministrator()&&!$this->_app->auth->isProvider()) throw new ExceptionUserTextStorage('error.accessDenied');
      }
    }
  }

  public function checkReservationCancelCondition() {
    $skipCondition = array();

    if (isset($this->_data['eventId'])&&$this->_data['eventId']) $o = new OEvent($this->_data['eventId']);
    elseif (isset($this->_data['resourceId'])&&$this->_data['resourceId']) $o = new OResource($this->_data['resourceId']);
    $oData = $o->getData();
    $condition = ifsetor($oData['reservationcondition']);
    if (!$condition) return true;

    $bReservationCondition = new BReservationCondition($condition);
    $conditionData = $bReservationCondition->getData();
    $messages = array();
    foreach ($conditionData['item'] as $item) {
      $message = '';
      // kontrola, jestli je podminka platna dle casu
      $from = $item['timeFrom']?$item['timeFrom']:date('Y-m-d H:i:s');
      $to = $item['timeTo']?$item['timeTo']:date('Y-m-d H:i:s');
      $now = date('Y-m-d H:i:s');
      if (($now<$from)||($to<$now)) continue;

      // kontrola na nejzassi termin zruseni nezaplacene rezervace
      if (isset($item['cancelBefore'])&&$item['cancelBefore']&&!in_array('cancelBefore', $skipCondition)) {
        if (!$this->_data['payed']&&(strtotime('now')>strtotime($this->_data['start'])-60*$item['cancelBefore'])) {
          $newMessage = $item['cancelBeforeMessage']?$item['cancelBeforeMessage']:
            sprintf($this->_app->textStorage->getText('error.cancelReservation_conditionCancel'), convertPeriodToHuman($item['cancelBefore']));

          if ($message) $message .= sprintf(' %s %s', $this->_app->textStorage->getText('error.saveReservation_conditionAND'), $newMessage);
          else $message = ucfirst($newMessage);
        }
      }
      // kontrola na nejzassi termin zruseni zaplacene rezervace
      if (isset($item['cancelPayedBefore'])&&$item['cancelPayedBefore']&&!in_array('cancelPayedBefore', $skipCondition)) {
        if ($this->_data['payed']&&(strtotime('now')>strtotime($this->_data['start'])-60*$item['cancelPayedBefore'])) {
          $newMessage = $item['cancelPayedBeforeMessage']?$item['cancelPayedBeforeMessage']:
            sprintf($this->_app->textStorage->getText('error.cancelReservation_conditionCancel'), convertPeriodToHuman($item['cancelPayedBefore']));

          if ($message) $message .= sprintf(' %s %s', $this->_app->textStorage->getText('error.saveReservation_conditionAND'), $newMessage);
          else $message = ucfirst($newMessage);
        }
      }

      if ($message) $messages[] = $message.'.';
    }

    if (count($messages)) {
      $errorLabel = implode('<br/>', $messages);
      throw new ExceptionUser($errorLabel);
    }

    return true;
  }
  
  private function _checkEvent($params) {
  	if (!$this->_id) {
			if (!isset($params['eventParams']['eventId'])||!$params['eventParams']['eventId']) throw new ExceptionUserTextStorage('error.saveReservation_emptyEvent');
			if (!isset($params['eventParams']['eventPlaces'])||!($params['eventParams']['eventPlaces']>0)) throw new ExceptionUserTextStorage('error.saveReservation_invalidEventPlaces');
      if (!isset($params['eventParams']['eventAttendeePerson'])||!is_array($params['eventParams']['eventAttendeePerson'])||
        (count($params['eventParams']['eventAttendeePerson'])<>($params['eventParams']['eventPlaces']*$params['eventParams']['eventCoAttendees']))) throw new ExceptionUserTextStorage('error.saveReservation_missingEventAttendee');
  	} else {
      if (isset($params['eventParams']['eventAttendeePerson'])&&(!is_array($params['eventParams']['eventAttendeePerson'])||
          (count($params['eventParams']['eventAttendeePerson'])<>(ifsetor($params['eventParams']['eventPlaces'],$this->_data['eventPlaces'])*$this->_data['eventCoAttendees'])))) throw new ExceptionUserTextStorage('error.saveReservation_missingEventAttendee');
		}
  	$attendees = array();
    foreach ($params['eventParams']['eventAttendeePerson'] as $attendee) {
      if (BCustomer::getProviderSettings($params['providerId'],'userSubaccount')=='Y') {
        if (!isset($attendee['user'])||!$attendee['user']) throw new ExceptionUserTextStorage('error.saveReservation_missingEventAttendeeUser');
        else $attendees[] = $attendee['user'];
      } else {
        if (!isset($attendee['firstname'])||!$attendee['firstname']) throw new ExceptionUserTextStorage('error.saveReservation_missingEventAttendeeFirstname');
        if (!isset($attendee['lastname'])||!$attendee['lastname']) throw new ExceptionUserTextStorage('error.saveReservation_missingEventAttendeeLastname');
        #if (!isset($attendee['email'])||!$attendee['email']) throw new ExceptionUserTextStorage('error.saveReservation_missingEventAttendeeEmail');
      }
    }
    // kazdy ucastnik muze byt jenom jednou
    if (BCustomer::getProviderSettings($params['providerId'],'userSubaccount')=='Y') {
    	if (count(array_unique($attendees))<($params['eventParams']['eventPlaces'])) throw new ExceptionUserTextStorage('error.saveReservation_duplicateEventAttendeeUser');
		}
  }
  
  private function _checkResource($params,$allowPast) {
    if (!isset($params['resourceId'])||!$params['resourceId']) throw new ExceptionUserTextStorage('error.saveReservation_emptyResource');
    if (!isset($params['resourceFrom'])||!$params['resourceFrom']) throw new ExceptionUserTextStorage('error.saveReservation_emptyResourceFrom');
    if (!isset($params['resourceTo'])||!$params['resourceTo']) throw new ExceptionUserTextStorage('error.saveReservation_emptyResourceTo');
    
    // kontrola na korektni datumy pro zdroj
    if (!isset($allowPast)||!$allowPast) {
      if ($params['resourceFrom']<date('Y-m-d H:i:s')) throw new ExceptionUserTextStorage('error.saveReservation_resourceInvalidTime');
      if ($params['resourceTo']<date('Y-m-d H:i:s')) throw new ExceptionUserTextStorage('error.saveReservation_resourceInvalidTime');
    }
    $reservationLength = (strtotime($params['resourceTo'])-strtotime($params['resourceFrom']))/60;
    if ($reservationLength<=0) throw new ExceptionUserTextStorage('error.saveReservation_resourceInvalidTime');
  }
  
  private function _checkBeforeSave($params) {
    // kdyz se zaklada nova rezervace jsou tyto atributy povinne
    if (!$this->_id) {
      #if (!isset($params['userId'])) throw new ExceptionUserTextStorage('error.saveReservation_emptyUser');
      if (!isset($params['eventParams'])&&!isset($params['resourceParams'])) throw new ExceptionUserTextStorage('error.saveReservation_emptyCommodity');
    } else {
      // u existujici rezervace nelze menit zdroj za akci
      if (isset($this->_data['eventId'])&&isset($params['resourceParams'])) throw new ExceptionUserTextStorage('error.saveReservation_commodityChangeNotAllowed');
      if (isset($this->_data['resourceId'])&&isset($params['eventParams'])) throw new ExceptionUserTextStorage('error.saveReservation_commodityChangeNotAllowed');
    }
    // tyto nesmi byt prazdny nikdy
    #if (isset($params['userId'])&&!$params['userId']) throw new ExceptionUserTextStorage('error.saveReservation_emptyUser');
    
    if (isset($params['eventParams'])&&isset($params['resourceParams'])) throw new ExceptionUserTextStorage('error.saveReservation_toManyCommodities');
    if (isset($params['eventParams'])) $this->_checkEvent($params);
    if (isset($params['resourceParams'])) $this->_checkResource($params['resourceParams'],ifsetor($params['allowPast']));
  }
  
  private function _checkBeforeCancel($skipConditions=false) {
    // rezervace nesmi uz byt zrusena
    if ($this->_data['cancelled']) throw new ExceptionUserTextStorage('error.cancelReservation_alreadyCancelled');
    if ($this->_data['failed']) throw new ExceptionUserTextStorage('error.cancelReservation_alreadyFailed');

    if (!$skipConditions) {
      $this->checkReservationCancelCondition();

      // a nesmi na rezervaci zrovna probihat online platba
      if ($this->_data['openOnlinepayment']) throw new ExceptionUserTextStorage('error.cancelReservation_onlinePaymentInProgress');
    }
  }

  private function _checkBeforeDelete() {
    // rezervace nesmi byt zaplacen
    if ($this->_data['payed']) throw new ExceptionUserTextStorage('error.deleteReservation_payed');
  }

  private function _checkBeforeFail() {
    // rezervace nesmi uz byt zrusena a propadla
    if ($this->_data['cancelled']) throw new ExceptionUserTextStorage('error.cancelReservation_cancelled');
    if ($this->_data['failed']) throw new ExceptionUserTextStorage('error.cancelReservation_alreadyFailed');
  }
  
  private function _checkAfterCancel() {
    // zkontroluju vsechny aktivni rezervace, jestli se smazani teto neporusi nejaka podminka
    $s = new SReservation;
    $s->addStatement(new SqlStatementBi($s->columns['reservation_id'], $this->_id, '%s<>%s'));
    $s->addStatement(new SqlStatementBi($s->columns['user'], $this->_data['userId'], '%s=%s'));
    $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_data['providerId'], '%s=%s'));
    $s->addStatement(new SqlStatementMono($s->columns['cancelled'], '%s IS NULL'));
    $s->addStatement(new SqlStatementMono($s->columns['failed'], '%s IS NULL'));
    $s->addStatement(new SqlStatementMono($s->columns['start'], '%s>NOW()'));
    $s->addOrder(new SqlStatementAsc($s->columns['start']));
    $s->setColumnsMask(array('reservation_id','provider','center','number','start','end','user',
                             'event','event_reservationcondition','er_resource','er_reservationcondition',
                             'resource','resource_reservationcondition'));
    $res = $this->_app->db->doQuery($s->toString());
    try {
      while ($row = $this->_app->db->fetchAssoc($res)) {
        #$this->_app->messages->addMessage('userInfo', 'condition_check: '.$row['reservation_id']);
        $params = array('start'=>$row['start'],'end'=>$row['end'],'userId'=>$row['user'],'providerId'=>$row['provider'],'centerId'=>$row['center']);
        if ($row['resource']) {
          $params['resourceParams']['resourceId'] = $row['resource'];
          if ($row['resource_reservationcondition']) $params['reservationCondition'] = array('resource'=>$row['resource_reservationcondition']);
        } else {
          $params['eventParams']['eventId'] = $row['event'];
          if ($row['event_reservationcondition']) $params['reservationCondition'] = array('event'=>$row['event_reservationcondition']);
          if ($row['er_reservationcondition']) $params['reservationCondition']['resource'] = $row['er_reservationcondition'];
        }
        $params['skipCondition'] = array('limitFirstTimeBeforeStart','limitLastTimeBeforeStart','advancePayment','limitAnonymousBeforeStart',
          'limitOverlapQuantity','limitTotalQuantityPeriod','limitTotalQuantity','limitQuantity');
        #$this->_app->messages->addMessage('userInfo', 'params: '.var_export($params, true));
        
        $br = new BReservation($row['reservation_id']);
        $br->checkReservationCondition($params);
      }
    } catch (Exception $e) {
      throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.deleteReservation_requiredForOtherReservation'), $row['number']));
    }
  }
  
  private function _checkReservationAttribute(& $params) {
    if (!isset($params['attribute'])) {
      $params['attribute'] = array();
      
      if ($this->_id) {
        $s = new SReservationAttribute;
        $s->addStatement(new SqlStatementBi($s->columns['reservation'], $this->_id, '%s=%s'));
        $s->setColumnsMask(array('attribute','value'));
        $res = $this->_app->db->doQuery($s->toString());
        while ($row = $this->_app->db->fetchAssoc($res)) {
          $params['attribute'][$row['attribute']] = $row['value'];
        }
      }
    }

    // exietujici rezervace lze ukladat bez ID commodity => musime ziskat ID commodity rezervace
    $eventId = $resourceId = null;
    if (isset($params['eventParams']['eventId'])) $eventId = $params['eventParams']['eventId'];
    elseif (isset($params['resourceParams']['resourceId'])) $resourceId = $params['resourceParams']['resourceId'];
    elseif (isset($this->_data['eventId'])&&$this->_data['eventId']) $eventId = $this->_data['eventId'];
    elseif (isset($this->_data['resourceId'])&&$this->_data['resourceId']) $resourceId = $this->_data['resourceId'];
    
    $commodityMandatoryAttr = array();
    if ($eventId) {
      $s = new SEventAttribute;
      $s->addStatement(new SqlStatementBi($s->columns['event'], $eventId, '%s=%s'));
      $s->addStatement(new SqlStatementMono($s->columns['applicable'], "%s='RESERVATION'"));
      $s->addStatement(new SqlStatementMono($s->columns['mandatory'], "%s='Y'"));
      $s->setColumnsMask(array('attribute'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) $commodityMandatoryAttr[] = $row['attribute'];
    } elseif ($resourceId) {
      $s = new SResourceAttribute;
      $s->addStatement(new SqlStatementBi($s->columns['resource'], $resourceId, '%s=%s'));
      $s->addStatement(new SqlStatementMono($s->columns['applicable'], "%s='RESERVATION'"));
      $s->addStatement(new SqlStatementMono($s->columns['mandatory'], "%s='Y'"));
      $s->setColumnsMask(array('attribute'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) $commodityMandatoryAttr[] = $row['attribute'];
    }
    foreach ($commodityMandatoryAttr as $id) {
      if (!isset($params['attribute'][$id])) $params['attribute'][$id] = null;
    }
    
    global $TMP_DIR;
    
    $extendedAttributes = array();
    
    foreach ($params['attribute'] as $id=>$value) {
      $s = new SAttribute;
      $s->addStatement(new SqlStatementBi($s->columns['attribute_id'], $id, '%s=%s'));
      $s->setColumnsMask(array('attribute_id','type','mandatory','allowed_values'));
      $res = $this->_app->db->doQuery($s->toString());
      $row = $this->_app->db->fetchAssoc($res);
      
      $attribute = array('id'=>$id,'type'=>$row['type'],'value'=>$value);
        
      $s1 = new SAttributeName;
      $s1->addStatement(new SqlStatementBi($s1->columns['attribute'], $row['attribute_id'], '%s=%s'));
      $s1->setColumnsMask(array('lang','name'));
      $res1 = $this->_app->db->doQuery($s1->toString());
      $name = array(); while ($row1 = $this->_app->db->fetchAssoc($res1)) { $name[$row1['lang']] = $row1['name']; }
      $name = ifsetor($name[ifsetor($params['attributeLanguage'])], array_values($name)[0]);
      if (($row['mandatory']=='Y')&&!$value) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveReservation_attributeMissingValue'), $name));
      
      if ($value) {
        $notConverted = !isset($params['attributeConverted'])||!$params['attributeConverted'];
        switch ($row['type']) {
          case 'DATE': if ($notConverted) {
                          if (!$this->_app->regionalSettings->checkHumanDate($value)) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveReservation_attributeInvalidDate'), $name));
                          $attribute['value'] = $this->_app->regionalSettings->convertHumanToDate($value);
                       } elseif (!$this->_app->regionalSettings->checkDate($value)) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveReservation_attributeInvalidDate'), $name));
                       break;
          case 'TIME': if ($notConverted) {
                          if (!$this->_app->regionalSettings->checkHumanTime($value,'h:m')) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveReservation_attributeInvalidTime'), $name));
                          $attribute['value'] = $this->_app->regionalSettings->convertHumanToTime($value,'h:m');
                       } elseif (!$this->_app->regionalSettings->checkTime($value)) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveReservation_attributeInvalidTime'), $name));
                       break;
          case 'DATETIME':
                       if ($notConverted) {
                          if (!$this->_app->regionalSettings->checkHumanDateTime($value)) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveReservation_attributeInvalidDatetime'), $name));
                          $attribute['value'] = $this->_app->regionalSettings->convertHumanToDateTime($value);
                       } elseif (!$this->_app->regionalSettings->checkDateTime($value)) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveReservation_attributeInvalidDatetime'), $name));
                       break;
          case 'NUMBER':
                       if ($notConverted) {
                          if (!$this->_app->regionalSettings->checkHumanNumber($value,20)) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveReservation_attributeInvalidNumber'), $name));
                          $attribute['value'] = $this->_app->regionalSettings->convertHumanToNumber($value,20);
                       } elseif (!$this->_app->regionalSettings->checkNumber($value)) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveReservation_attributeInvalidNumber'), $name));
                       break;
          case 'DECIMALNUMBER':
                      if ($notConverted) {
                        if (!$this->_app->regionalSettings->checkHumanNumber($value,20,2)) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveReservation_attributeInvalidDecimalNumber'), $name));
                        $attribute['value'] = $this->_app->regionalSettings->convertHumanToNumber($value,20,2);
                      } elseif (!$this->_app->regionalSettings->checkNumber($value)) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveReservation_attributeInvalidDecimalNumber'), $name));
                      break;
          case 'LIST': if ($value&&!in_array($value,explode(',',$row['allowed_values'])))
                          throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveReservation_attributeInvalidList'), $name));
                       break;
          case 'FILE': if ($value&&strcmp($value,'__no_change__')&&!file_exists($TMP_DIR.$value))
                          throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveReservation_attributeInvalidFile'), $name));
                       break;
          default: break;
        }
      }
      
      $extendedAttributes[$id] = $attribute;
    }
    
    $params['attribute'] = $extendedAttributes;
  }

  private function _getAttendeeForCondition($params) {
    $ret = array();

    if (isset($params['eventParams']['eventAttendeePerson'])) {
      foreach ($params['eventParams']['eventAttendeePerson'] as $person) {
        if (isset($person['user'])&&$person['user']) $ret[] = $person['user'];
      }
    }

    return $ret;
  }

  private function _checkConditionCommodityQuantity($commodity, $item, $params) {
    $ret = false;

    $attendee = array();
    if (!strcmp($item['limitQuantityScope'],'ATTENDEE')) {
      $attendee = $this->_getAttendeeForCondition($params);
      if (!count($attendee)) return false;
    }

    $s = new SReservation;
    if ($this->_id) $s->addStatement(new SqlStatementBi($s->columns['reservation_id'], $this->_id, '%s<>%s'));
    $s->addStatement(new SqlStatementMono($s->columns['cancelled'], '%s IS NULL'));
    $s->addStatement(new SqlStatementMono($s->columns['failed'], '%s IS NULL'));
    if (!strcmp($item['limitQuantityScope'],'USER')) $s->addStatement(new SqlStatementBi($s->columns['user'], $params['userId'], '%s=%s'));
    if (isset($params['eventParams'])) {
      // kdyz se rezervuje akce, ale mam podminku pro zdroj, kde je akce
      if (!strcmp($commodity,'resource')) $s->addStatement(new SqlStatementBi($s->columns['er_resource'], $params['eventParams']['resourceId'], '%s=%s'));
      else $s->addStatement(new SqlStatementBi($s->columns['event'], $params['eventParams']['eventId'], '%s=%s'));
    } else $s->addStatement(new SqlStatementBi($s->columns['resource'], $params['resourceParams']['resourceId'], '%s=%s'));
    if ($item['limitQuantityType']) {
      if ($item['limitQuantityType']=='MANDATORY') $s->addStatement(new SqlStatementMono($s->columns['mandatory'], "%s='Y'"));
      elseif ($item['limitQuantityType']=='NON_MANDATORY') $s->addStatement(new SqlStatementMono($s->columns['mandatory'], "%s='N'"));
    }
    if ($item['limitQuantityPeriod']) {
      $expr = null;
      switch ($item['limitQuantityPeriod']) {
        case 'DAY_1':   $expr = '1 DAY'; break;
        case 'WEEK_1':  $expr = '1 WEEK'; break;
        case 'WEEK_2':  $expr = '2 WEEK'; break;
        case 'WEEK_3':  $expr = '3 WEEK'; break;
        case 'MONTH_1': $expr = '1 MONTH'; break;
        case 'MONTH_2': $expr = '2 MONTH'; break;
        case 'YEAR_1':  $expr = '1 YEAR'; break;
      }
      if ($expr) $s->addStatement(new SqlStatementMono($s->columns['created'], sprintf('%%s>=DATE_SUB(NOW(),INTERVAL %s)', $expr)));
    }
    $s->setColumnsMask(array('reservation_id'));

    // cyklus je kvuli mozne podmince na ucastniky akce, v takovem pripade je potreba zkontrolovat kazdeho ucastnika zvlast
    do {
      $sClone = clone $s;
      if (count($attendee)) {
        $sClone->addStatement(new SqlStatementBi($sClone->columns['eventattendeeperson_user'], array_pop($attendee), '%s=%s'));
      }

      #error_log($sClone->toString());
      $res = $this->_app->db->doQuery($sClone->toString());
      if ($this->_app->db->getRowsNumber($res)>=$item['limitQuantity']) {
        $ret = $item['limitQuantityMessage']?$item['limitQuantityMessage']:sprintf($this->_app->textStorage->getText('error.saveReservation_conditionQuantity'), $item['limitQuantity']);
      }
    } while (!$ret&&count($attendee));

    return $ret;
  }

  private function _checkConditionRequiredResource($item, $params) {
    $ret = false;

    $requiredResource = explode(',',$item['requiredResource']);
    // ulozeni poctu rezervaci kdyz musi existovat vsechny nebo nejaka
    // nebo nesmi existovat zadna nebo nejaka
    if ($item['requiredResourceExists']=='Y') $requiredCount = $item['requiredResourceAll']=='Y'?count($requiredResource):1;
    elseif ($item['requiredResourceExists']=='N') $requiredCount = $item['requiredResourceAll']=='Y'?0:count($requiredResource)-1;

    // podminka na ucastnika zatim vzdy ok, u rezervace zdroje nemame ucastniky
    if (!strcmp($item['limitOtherScope'],'ATTENDEE')) return false;

    $s = new SReservation;
    $s->addStatement(new SqlStatementMono($s->columns['cancelled'], '%s IS NULL'));
    $s->addStatement(new SqlStatementMono($s->columns['failed'], '%s IS NULL'));
    if (!strcmp($item['limitOtherScope'],'USER')) $s->addStatement(new SqlStatementBi($s->columns['user'], $params['userId'], '%s=%s'));
    if ($item['requiredResourcePayed']=='Y') $s->addStatement(new SqlStatementBi($s->columns['payed'], $s->columns['resource'], sprintf('(%%s IS NOT NULL AND %%s IN (%s))', $item['requiredResource'])));
    else $s->addStatement(new SqlStatementMono($s->columns['resource'], sprintf('%%s IN (%s)', $item['requiredResource'])));
    $s->setColumnsMask(array('resource'));
    $s->setDistinct(true);
    $res = $this->_app->db->doQuery($s->toString());
    $reservedCount = $this->_app->db->getRowsNumber($res);
    if ((($item['requiredResourceExists']=='Y')&&($reservedCount<$requiredCount))||
      (($item['requiredResourceExists']=='N')&&($reservedCount>$requiredCount))) {
      if ($item['requiredResourceMessage']) {
        $ret = $item['requiredResourceMessage'];
      } else {
        $reservedResource = array();
        while ($row = $this->_app->db->fetchAssoc($res)) $reservedResource[] = $row['resource'];

        $subjectMessage = '';
        $subject = $item['requiredResourceExists']=='Y'?array_diff($requiredResource,$reservedResource):$reservedResource;
        foreach ($subject as $resource) {
          $oResource = new OResource($resource);
          $oData = $oResource->getData();

          if ($subjectMessage) $subjectMessage .= ', ';
          $subjectMessage .= $oData['name'];
        }
        if ($item['requiredResourceExists']=='Y') {
          if ($item['requiredResourceAll']=='Y') $ret = sprintf($this->_app->textStorage->getText('error.saveReservation_conditionOtherReqExistsAll'), $subjectMessage);
          else $ret = sprintf($this->_app->textStorage->getText('error.saveReservation_conditionOtherReqExistsAny'), $subjectMessage);
        } else {
          if ($item['requiredResourceAll']=='Y') $ret = sprintf($this->_app->textStorage->getText('error.saveReservation_conditionOtherReqNotExistsAll'), $subjectMessage);
          else $ret = sprintf($this->_app->textStorage->getText('error.saveReservation_conditionOtherReqNotExistsAny'), $subjectMessage);
        }
      }
    }

    return $ret;
  }

  private function _checkConditionRequiredEvent($item, $params) {
    $ret = false;

    $requiredEvent = explode(',',$item['requiredEvent']);
    // ulozeni poctu rezervaci kdyz musi existovat vsechny nebo nejaka
    // nebo nesmi existovat zadna nebo nejaka
    if ($item['requiredEventExists']=='Y') $requiredCount = $item['requiredEventAll']=='Y'?count($requiredEvent):1;
    elseif ($item['requiredEventExists']=='N') $requiredCount = $item['requiredEventAll']=='Y'?0:count($requiredEvent)-1;

    $attendee = array();
    if (!strcmp($item['limitOtherScope'],'ATTENDEE')) {
      $attendee = $this->_getAttendeeForCondition($params);
      if (!count($attendee)) return false;
    }

    $s = new SReservation;
    $s->addStatement(new SqlStatementMono($s->columns['cancelled'], '%s IS NULL'));
    $s->addStatement(new SqlStatementMono($s->columns['failed'], '%s IS NULL'));
    if (!strcmp($item['limitOtherScope'],'USER')) $s->addStatement(new SqlStatementBi($s->columns['user'], $params['userId'], '%s=%s'));
    if ($item['requiredEventPayed']=='Y') $s->addStatement(new SqlStatementBi($s->columns['payed'], $s->columns['event'], sprintf('(%%s IS NOT NULL AND %%s IN (%s))', $item['requiredEvent'])));
    else $s->addStatement(new SqlStatementMono($s->columns['event'], sprintf('%%s IN (%s)', $item['requiredEvent'])));
    $s->setColumnsMask(array('event'));
    $s->setDistinct(true);

    // cyklus je kvuli mozne podmince na ucastniky akce, v takovem pripade je potreba zkontrolovat kazdeho ucastnika zvlast
    do {
      $sClone = clone $s;
      if (count($attendee)) {
        $sClone->addStatement(new SqlStatementBi($sClone->columns['eventattendeeperson_user'], array_pop($attendee), '%s=%s'));
      }

      $res = $this->_app->db->doQuery($sClone->toString());
      $reservedCount = $this->_app->db->getRowsNumber($res);
      if ((($item['requiredEventExists']=='Y')&&($reservedCount<$requiredCount))||
        (($item['requiredEventExists']=='N')&&($reservedCount>$requiredCount))) {
        if ($item['requiredEventMessage']) {
          $ret = $item['requiredEventMessage'];
        } else {
          $reservedEvent = array();
          while ($row = $this->_app->db->fetchAssoc($res)) $reservedEvent[] = $row['event'];

          $subjectMessage = '';
          $subject = $item['requiredEventExists']=='Y'?array_diff($requiredEvent,$reservedEvent):$reservedEvent;
          foreach ($subject as $event) {
            if ($subjectMessage) $subjectMessage .= ', ';
            $oEvent = new OEvent($event);
            $oData = $oEvent->getData();
            $subjectMessage .= $oData['name'];
          }
          if ($item['requiredEventExists']=='Y') {
            if ($item['requiredEventAll']=='Y') $ret = sprintf($this->_app->textStorage->getText('error.saveReservation_conditionOtherReqExistsAll'), $subjectMessage);
            else $ret = sprintf($this->_app->textStorage->getText('error.saveReservation_conditionOtherReqExistsAny'), $subjectMessage);
          } else {
            if ($item['requiredEventAll']=='Y') $ret = sprintf($this->_app->textStorage->getText('error.saveReservation_conditionOtherReqNotExistsAll'), $subjectMessage);
            else $ret = sprintf($this->_app->textStorage->getText('error.saveReservation_conditionOtherReqNotExistsAny'), $subjectMessage);
          }
        }
      }
    } while (!$ret&&count($attendee));

    return $ret;
  }

  private function _checkConditionTotalQuantity($item, $params) {
		$ret = false;

		$attendee = array();
		if (!strcmp($item['limitOtherScope'],'ATTENDEE')) {
      $attendee = $this->_getAttendeeForCondition($params);
			if (!count($attendee)) return false;
		}

		$s = new SReservation;
		if ($this->_id) $s->addStatement(new SqlStatementBi($s->columns['reservation_id'], $this->_id, '%s<>%s'));
		$s->addStatement(new SqlStatementMono($s->columns['cancelled'], '%s IS NULL'));
		$s->addStatement(new SqlStatementMono($s->columns['failed'], '%s IS NULL'));
		if (!strcmp($item['limitOtherScope'],'USER')) $s->addStatement(new SqlStatementBi($s->columns['user'], $params['userId'], '%s=%s'));
		$s->addStatement(new SqlStatementBi($s->columns['provider'], $params['providerId'], '%s=%s'));
		if ($item['limitTotalQuantityTag']) {
			$s->addStatement(new SqlStatementTri($s->columns['et_tag'], $s->columns['ert_tag'], $s->columns['rt_tag'],
				sprintf('(%%s IN (%s) OR %%s IN (%s) OR %%s IN (%s))',
					$item['limitTotalQuantityTag'], $item['limitTotalQuantityTag'], $item['limitTotalQuantityTag'])));
		}
		if ($item['limitTotalQuantityType']) {
			if ($item['limitTotalQuantityType']=='MANDATORY') $s->addStatement(new SqlStatementMono($s->columns['mandatory'], "%s='Y'"));
			elseif ($item['limitTotalQuantityType']=='NON_MANDATORY') $s->addStatement(new SqlStatementMono($s->columns['mandatory'], "%s='N'"));
		}
		if ($item['limitTotalQuantityPeriod']) {
			$expr = null;
			switch ($item['limitTotalQuantityPeriod']) {
				case 'DAY_1':   $expr = '1 DAY'; break;
				case 'WEEK_1':  $expr = '1 WEEK'; break;
				case 'WEEK_2':  $expr = '2 WEEK'; break;
				case 'WEEK_3':  $expr = '3 WEEK'; break;
				case 'MONTH_1': $expr = '1 MONTH'; break;
				case 'MONTH_2': $expr = '2 MONTH'; break;
				case 'YEAR_1':  $expr = '1 YEAR'; break;
			}
			if ($expr) $s->addStatement(new SqlStatementMono($s->columns['created'], sprintf('%%s>=DATE_SUB(NOW(),INTERVAL %s)', $expr)));
		}
		$s->setDistinct(true);
		$s->setColumnsMask(array('reservation_id'));

		// cyklus je kvuli mozne podmince na ucastniky akce, v takovem pripade je potreba zkontrolovat kazdeho ucastnika zvlast
    do {
      $sClone = clone $s;
      if (count($attendee)) {
        $sClone->addStatement(new SqlStatementBi($sClone->columns['eventattendeeperson_user'], array_pop($attendee), '%s=%s'));
      }

      #error_log($sClone->toString());
      $res = $this->_app->db->doQuery($sClone->toString());
			if ($this->_app->db->getRowsNumber($res)>=$item['limitTotalQuantity']) {
				$ret = $item['limitTotalQuantityMessage']?$item['limitTotalQuantityMessage']:
					sprintf($this->_app->textStorage->getText('error.saveReservation_conditionTotalQuantity'), $item['limitTotalQuantity']);
			}
		} while (!$ret&&count($attendee));

		return $ret;
	}

	private function _checkConditionOverlapQuantity($item, $params) {
    $ret = false;

    $attendee = array();
    if (!strcmp($item['limitOtherScope'],'ATTENDEE')) {
      $attendee = $this->_getAttendeeForCondition($params);
      if (!count($attendee)) return false;
    }

    $s = new SReservation;
    if ($this->_id) $s->addStatement(new SqlStatementBi($s->columns['reservation_id'], $this->_id, '%s<>%s'));
    $s->addStatement(new SqlStatementMono($s->columns['cancelled'], '%s IS NULL'));
    $s->addStatement(new SqlStatementMono($s->columns['failed'], '%s IS NULL'));
    if (!strcmp($item['limitOtherScope'],'USER')) $s->addStatement(new SqlStatementBi($s->columns['user'], $params['userId'], '%s=%s'));
    $s->addStatement(new SqlStatementQuad($params['end'], $s->columns['start'], $params['start'], $s->columns['end'], '(%s>%s AND %s<%s)'));
    if (!strcmp($item['limitOverlapQuantityScope'],'CENTER')) $s->addStatement(new SqlStatementBi($s->columns['center'], $params['centerId'], '%s=%s'));
    else $s->addStatement(new SqlStatementBi($s->columns['provider'], $params['providerId'], '%s=%s'));
    if ($item['limitOverlapQuantityTag']) {
      $s->addStatement(new SqlStatementTri($s->columns['et_tag'], $s->columns['ert_tag'], $s->columns['rt_tag'],
        sprintf('(%%s IN (%s) OR %%s IN (%s) OR %%s IN (%s))',
          $item['limitOverlapQuantityTag'], $item['limitOverlapQuantityTag'], $item['limitOverlapQuantityTag'])));
    }
    $s->setDistinct(true);
    $s->setColumnsMask(array('reservation_id'));

    // cyklus je kvuli mozne podmince na ucastniky akce, v takovem pripade je potreba zkontrolovat kazdeho ucastnika zvlast
    do {
      $sClone = clone $s;
      if (count($attendee)) {
        $sClone->addStatement(new SqlStatementBi($sClone->columns['eventattendeeperson_user'], array_pop($attendee), '%s=%s'));
      }

      $res = $this->_app->db->doQuery($sClone->toString());
      if ($this->_app->db->getRowsNumber($res)>=$item['limitOverlapQuantity']) {
        $ret = $item['limitOverlapQuantityMessage']?$item['limitOverlapQuantityMessage']:
          sprintf($this->_app->textStorage->getText('error.saveReservation_conditionOverlapQuantity'), $item['limitOverlapQuantity']);
      }
    } while (!$ret&&count($attendee));

    return $ret;
  }

  private function _checkConditionAdvancePayment($item, $params) {
    $ret = false;

    if (strtotime('now')>strtotime($params['start'])-60*$item['advancePayment']) {
      if (!isset($params['pay'])||($params['pay']!='Y')) {
        $price = null;

        // kdyz bude cena za rezervaci=0 nemusi byt zaplacena
        if (isset($params['priceManual'])&&$params['priceManual']) {
          $price = ifsetor($params['price'], $this->_data['price']);
        } else {
          if (isset($params['resourceParams'])) {
            $bResource = new BResource($params['resourceParams']['resourceId']);
            $price = $bResource->getPrice($params['resourceParams']['resourceFrom'], $params['resourceParams']['resourceTo']);
          } elseif (isset($params['eventParams'])) {
            $s = new SEvent;
            $s->addStatement(new SqlStatementBi($s->columns['event_id'], $params['eventParams']['eventId'], '%s=%s'));
            $s->setColumnsMask(array('price','repeat_price'));
            $res = $this->_app->db->doQuery($s->toString());
            $row = $this->_app->db->fetchAssoc($res);
            $price = isset($params['eventParams']['eventPack'])&&($params['eventParams']['eventPack']=='Y')?$row['repeat_price']:$row['price'];
            $price = $price * $params['eventParams']['eventPlaces'];
          }
        }
        // slevovy kod nemusim resit, kdyz se zadava slevovy kod, tak se plati
        #if ($price&&isset($params['voucher'])&&$params['voucher']) {
        #  $b = new BVoucher($params['voucher']);
        #  $price = $b->getDiscount($price);
        #}

        if (is_null($price)||($price>0)) {
          $ret = $item['advancePaymentMessage'] ? $item['advancePaymentMessage'] :
            sprintf($this->_app->textStorage->getText('error.saveReservation_conditionAdvancePayment'), convertPeriodToHuman($item['advancePayment']));
        }
      }
    }

    return $ret;
  }

  public function checkReservationCondition($params, $reportAll=false) {
    //error_log(var_export($params,true));
    $skipCondition = ifsetor($params['skipCondition']);
    if (!is_array($skipCondition)) {
      if (!strcmp($skipCondition,'all')&&($this->_app->auth->isAdministrator()||$this->_app->auth->isProvider())) {
        return false;
      }

      $skipCondition = array();
    }
    
    // defaultni podminka. kdyz neni nastavena jina
    if (!isset($params['reservationCondition']['event'])) {
      $params['reservationCondition']['special'] = array(array('limitAfterStartEvent'=>'Y','limitAfterStartEventMessage'=>'',
                                                               'timeFrom'=>'','timeTo'=>'')); 
    }
    
    if (isset($params['reservationCondition'])&&count($params['reservationCondition'])) {
      foreach ($params['reservationCondition'] as $commodity=>$condition) {
        if (strcmp($commodity,'special')) {
          $bReservationCondition = new BReservationCondition($condition);
          $conditionData = $bReservationCondition->getData();
          if (!count($conditionData['item'])) $conditionData['item'] = $params['reservationCondition']['special'];
          $allMustBeTrue = !strcmp($conditionData['evaluation'],'ALL');
        } else {
          $conditionData['item'] = $condition;
          $allMustBeTrue = true;
        }
        
        $messages = array();
        foreach ($conditionData['item'] as $item) {
          $message = '';
          // kontrola, jestli je podminka platna dle casu
          $from = $item['timeFrom']?$item['timeFrom']:date('Y-m-d H:i:s');
          $to = $item['timeTo']?$item['timeTo']:date('Y-m-d H:i:s');
          $now = date('Y-m-d H:i:s');
          if (($now<$from)||($to<$now)) continue;

          // kontrola na stredisko zdroje/akce
          if (isset($item['limitCenter'])&&$item['limitCenter']&&!in_array('limitCenter', $skipCondition)) {
            if ($params['centerId']!=$item['limitCenter']) {
              $s = new SCenter;
              $s->addStatement(new SqlStatementBi($s->columns['center_id'], $item['limitCenter'], '%s=%s'));
              $s->setColumnsMask(array('name'));
              $res = $this->_app->db->doQuery($s->toString());
              $row = $this->_app->db->fetchAssoc($res);
              $newMessage = $item['limitCenterMessage']?$item['limitCenterMessage']:
                sprintf($this->_app->textStorage->getText('error.saveReservation_conditionCenter'), $row['name']);

              if ($message) $message .= sprintf(' %s %s', $this->_app->textStorage->getText('error.saveReservation_conditionAND'), $newMessage);
              else $message = ucfirst($newMessage);
            }
          }
          // kontrola na pocatecni cas, kdy lze rezervovat
          if (isset($item['limitFirstTimeBeforeStart'])&&$item['limitFirstTimeBeforeStart']&&!in_array('limitFirstTimeBeforeStart', $skipCondition)) {
            if (strtotime('now')<strtotime($params['start'])-60*$item['limitFirstTimeBeforeStart']) {
              $newMessage = $item['limitFirstTimeBeforeMessage']?$item['limitFirstTimeBeforeMessage']:
                        sprintf($this->_app->textStorage->getText('error.saveReservation_conditionFirstTime'), convertPeriodToHuman($item['limitFirstTimeBeforeStart']));
              
              if ($message) $message .= sprintf(' %s %s', $this->_app->textStorage->getText('error.saveReservation_conditionAND'), $newMessage);
              else $message = ucfirst($newMessage);
            }
          }
          // kontrola na konecny cas, kdy lze rezervovat
          if (isset($item['limitLastTimeBeforeStart'])&&$item['limitLastTimeBeforeStart']&&!in_array('limitLastTimeBeforeStart', $skipCondition)) {
            if (strtotime('now')>strtotime($params['start'])-60*$item['limitLastTimeBeforeStart']) {
              $newMessage = $item['limitLastTimeBeforeMessage']?$item['limitLastTimeBeforeMessage']:
                        sprintf($this->_app->textStorage->getText('error.saveReservation_conditionLastTime'), convertPeriodToHuman($item['limitLastTimeBeforeStart']));
              
              if ($message) $message .= sprintf(' %s %s', $this->_app->textStorage->getText('error.saveReservation_conditionAND'), $newMessage);
              else $message = ucfirst($newMessage);
            }
          }
          // kontrola na rezervaci akce v prubehu
          if (isset($item['limitAfterStartEvent'])&&$item['limitAfterStartEvent']&&!in_array('limitAfterStartEvent', $skipCondition)) {
            if (isset($params['eventParams']['eventId'])&&($item['limitAfterStartEvent']=='Y')&&(!isset($params['allowPast'])||!$params['allowPast'])) {
              if ((strtotime($params['start'])<=strtotime('now'))&&(strtotime('now')<strtotime($params['end']))) {
                $newMessage = $item['limitAfterStartEventMessage']?$item['limitAfterStartEventMessage']:$this->_app->textStorage->getText('error.saveReservation_conditionAfterStartEvent');
              
                if ($message) $message .= sprintf(' %s %s', $this->_app->textStorage->getText('error.saveReservation_conditionAND'), $newMessage);
                else $message = ucfirst($newMessage);
              }
            }
          }
          // kontrola na zaplacenou rezervaci
          if (isset($item['advancePayment'])&&$item['advancePayment']&&!in_array('advancePayment', $skipCondition)) {
            if ($newMessage = $this->_checkConditionAdvancePayment($item, $params)) {
              if ($message) $message .= sprintf(' %s %s', $this->_app->textStorage->getText('error.saveReservation_conditionAND'), $newMessage);
              else $message = ucfirst($newMessage);
            }
          }
          // kontrola na anonymni rezervaci
          if (isset($item['limitAnonymousBeforeStart'])&&$item['limitAnonymousBeforeStart']&&!in_array('limitAnonymousBeforeStart', $skipCondition)) {
            if ((strtotime('now')<strtotime($params['start'])-60*$item['limitAnonymousBeforeStart'])&&!$params['userId']) {
              $newMessage = $item['limitAnonymousBeforeMessage']?$item['limitAnonymousBeforeMessage']:
                        sprintf($this->_app->textStorage->getText('error.saveReservation_conditionAnonymous'), convertPeriodToHuman($item['limitAnonymousBeforeStart']));
              
              if ($message) $message .= sprintf(' %s %s', $this->_app->textStorage->getText('error.saveReservation_conditionAND'), $newMessage);
              else $message = ucfirst($newMessage);
            }
          }
          // kontrola na pocet rezervaci dane komodity
          if (isset($item['limitQuantity'])&&($item['limitQuantity']>=0)&&!in_array('limitQuantity', $skipCondition)) {
            // pokud nebude mandatory ukladane rezervace souhlasit s priznakem u podminky, neni potreba kontrolovat
            $newReservationMandatoryFlag = isset($params['mandatory'])&&($params['mandatory']=='Y')?'MANDATORY':'NON_MANDATORY';
            $checkCondition = ($item['limitQuantityType']=='ALL')||($newReservationMandatoryFlag == $item['limitQuantityType']);

            if ($checkCondition) {
              if ($newMessage = $this->_checkConditionCommodityQuantity($commodity, $item, $params)) {
                if ($message) $message .= sprintf(' %s %s', $this->_app->textStorage->getText('error.saveReservation_conditionAND'), $newMessage);
                else $message = ucfirst($newMessage);
              }
            }
          }
          // kontrola na pozadovane akce
          if (isset($item['requiredEvent'])&&$item['requiredEvent']&&!in_array('requiredEvent', $skipCondition)) {
            if ($newMessage = $this->_checkConditionRequiredEvent($item, $params)) {
              if ($message) $message .= sprintf(' %s %s', $this->_app->textStorage->getText('error.saveReservation_conditionAND'), $newMessage);
              else $message = ucfirst($newMessage);
            }
          }
          // kontrola na pozadovane zdroje
          if (isset($item['requiredResource'])&&$item['requiredResource']&&!in_array('requiredResource', $skipCondition)) {
            if ($newMessage = $this->_checkConditionRequiredResource($item, $params)) {
              if ($message) $message .= sprintf(' %s %s', $this->_app->textStorage->getText('error.saveReservation_conditionAND'), $newMessage);
              else $message = ucfirst($newMessage);
            }
          }
          // kontrola na celkovy pocet rezervaci
          if (isset($item['limitTotalQuantity'])&&($item['limitTotalQuantity']>=0)&&!in_array('limitTotalQuantity', $skipCondition)) {
            $checkCondition = true;
            // pokud bude rezervace na zdroj/akci s jinym tagem, nez je podminka, tak neni potreba kontrolovat
            if ($item['limitTotalQuantityTag']) {
              $conditionTag = explode(',', $item['limitTotalQuantityTag']);
              $commodityTag = explode(',', $params['commodityTag']);
              $checkCondition = count(array_intersect($conditionTag,$commodityTag));
            }
            // pokud nebude mandatory ukladane rezervace souhlasit s priznakem u podminky, neni potreba kontrolovat
            $newReservationMandatoryFlag = isset($params['mandatory'])&&($params['mandatory']=='Y')?'MANDATORY':'NON_MANDATORY';
            $checkCondition = $checkCondition && (($item['limitTotalQuantityType']=='ALL')||($newReservationMandatoryFlag==$item['limitTotalQuantityType']));
            
            if ($checkCondition) {
							if ($newMessage = $this->_checkConditionTotalQuantity($item, $params)) {
								if ($message) $message .= sprintf(' %s %s', $this->_app->textStorage->getText('error.saveReservation_conditionAND'), $newMessage);
								else $message = ucfirst($newMessage);
							}
            }
          }
          // kontrola na pocet rezervaci v jeden cas
          if (isset($item['limitOverlapQuantity'])&&($item['limitOverlapQuantity']>=0)&&!in_array('limitOverlapQuantity', $skipCondition)) {
            $checkCondition = true;
            // pokud bude rezervace na zdroj/akci s jinym tagem, nez je podminka, tak neni potreba kontrolovat
            if ($item['limitOverlapQuantityTag']) {
              $conditionTag = explode(',', $item['limitOverlapQuantityTag']);
              $commodityTag = explode(',', $params['commodityTag']);
              $checkCondition = count(array_intersect($conditionTag,$commodityTag));
            }

            if ($checkCondition) {
              if ($newMessage = $this->_checkConditionOverlapQuantity($item, $params)) {
                if ($message) $message .= sprintf(' %s %s', $this->_app->textStorage->getText('error.saveReservation_conditionAND'), $newMessage);
                else $message = ucfirst($newMessage);
              }
            }
          }
          // pridam do hlasky info o obdobi podminky
          if ($message&&($item['timeFrom']||$item['timeTo'])) {
            $fromL = $toL = '';
            if ($item['timeFrom']) $fromL = sprintf('%s %s', $this->_app->textStorage->getText('error.saveReservation_conditionTermFrom'),
                                                         $this->_app->regionalSettings->convertDateTimeToHuman($from));
            if ($item['timeTo']) $toL = sprintf(' %s %s', $this->_app->textStorage->getText('error.saveReservation_conditionTermTo'),
                                                     $this->_app->regionalSettings->convertDateTimeToHuman($to));
            $message = sprintf("%s %s %s %s:\n%s", $this->_app->textStorage->getText('error.saveReservation_conditionTerm'), $fromL, $toL,
                               $this->_app->textStorage->getText('error.saveReservation_conditionTermEnd'), $message);
          }
        
          if ($message) $messages[] = $message.'.';
        }

        if (count($messages)) {
          if ($allMustBeTrue&&!$reportAll) {
            // kdyz museji byt splneni vsechny podminky, budu zobrazovat pouze prvni nesplnenou podminku
            throw new ExceptionUser($messages[0]);
          } elseif ($reportAll||(count($messages)==count($conditionData['item']))) {
            // kdyz musi byt splnena alespon jedna a neni zadna, budu zobrazovat vsechny podminky
            $errorLabel = implode($allMustBeTrue?'<br/>':'<br/>'.$this->_app->textStorage->getText('error.saveReservation_conditionOR').'<br/>',
              $messages);
            throw new ExceptionUser($errorLabel);
          }
        }
      }
    }

    return true;
  }

  protected function _load() {
    if ($this->_id&&!$this->_loaded) {
      $oReservation = new OReservation($this->_id);
      $data = $oReservation->getData();
      $returnData['id'] = $data['reservation_id'];
      $returnData['number'] = $data['number'];
      $returnData['mandatory'] = $data['mandatory'];
      $returnData['receiptNumber'] = $data['receipt_number'];
      $returnData['invoiceNumber'] = $data['invoice_number'];
      $returnData['created'] = $data['created'];
      $returnData['failed'] = $data['failed'];
      $returnData['cancelled'] = $data['cancelled'];
      $returnData['payed'] = $data['payed'];
      $returnData['payedBy'] = $data['payed_by'];
      $returnData['payedTicket'] = $data['payed_ticket'];
      $returnData['pool'] = $data['pool'];
      $returnData['note'] = $data['note'];

      $returnData['start'] = $data['start'];
      $returnData['end'] = $data['end'];
      
      $returnData['price'] = $data['total_price'];
      $returnData['priceTimestamp'] = $data['price_timestamp'];
      $returnData['priceComment'] = $data['price_comment'];
      $returnData['priceUser'] = $data['price_user'];

      $returnData['openOnlinepayment'] = $data['open_onlinepayment'];

      $returnData['voucher'] = $data['voucher'];
      if ($data['voucher']) {
        $returnData['voucherDiscount'] = $data['voucher_discount_amount'];

        $o = new OVoucher($data['voucher']);
        $oData = $o->getData();
        $returnData['voucherCode'] = $oData['code'];
        $returnData['voucherDiscountType'] = $oData['discount_amount']?'SUM':'PROPORTION';
        $returnData['voucherDiscountValue'] = $oData['discount_amount']?$oData['discount_amount']:$oData['discount_proportion'];
      }
      
      $returnData['userId'] = $data['user'];  
      if ($data['user']) {
        $s = new SUser;
        $s->addStatement(new SqlStatementBi($s->columns['user_id'], $data['user'], '%s=%s'));
        $s->setColumnsMask(array('fullname','email'));
        $res = $this->_app->db->doQuery($s->toString());
        $oUData = $this->_app->db->fetchAssoc($res);
        
        $returnData['userName'] = $oUData['fullname'];
        $returnData['userEmail'] = $oUData['email'];
      }
      
      $returnData['customerId'] = $data['customer'];  
      if ($data['customer']) {
        $s = new SCustomer;
        $s->addStatement(new SqlStatementBi($s->columns['customer_id'], $data['customer'], '%s=%s'));
        $s->setColumnsMask(array('name','email'));
        $res = $this->_app->db->doQuery($s->toString());
        $oCData = $this->_app->db->fetchAssoc($res);
        
        $returnData['customerName'] = $oCData['name'];
        $returnData['customerEmail'] = $oCData['email'];
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
      
      $returnData['centerId'] = $data['center'];
      if ($data['center']) {
        $s = new SCenter;
        $s->addStatement(new SqlStatementBi($s->columns['center_id'], $data['center'], '%s=%s'));
        $s->setColumnsMask(array('name','street','city','postal_code'));
        $res = $this->_app->db->doQuery($s->toString());
        $oCData = $this->_app->db->fetchAssoc($res);
        
        $returnData['centerName'] = $oCData['name'];
        $returnData['centerStreet'] = $oCData['street'];
        $returnData['centerCity'] = $oCData['city'];
        $returnData['centerPostalCode'] = $oCData['postal_code'];
      }

      $returnData['reservationCondition'] = array();
      $returnData['commodityTag'] = '';
      if ($data['event']) {
        $returnData['eventId'] = $data['event'];
        $returnData['eventPlaces'] = $data['event_places'];
        $returnData['eventPack'] = $data['event_pack'];
        
        $s = new SEvent;
        $s->addStatement(new SqlStatementBi($s->columns['event_id'], $data['event'], '%s=%s'));
        $s->setColumnsMask(array('name','description','start','end','price','repeat_price','repeat_parent','repeat_reservation','max_coattendees','reservationcondition','resource','all_tag','fe_allowed_payment'));
        $res = $this->_app->db->doQuery($s->toString());
        if ($row = $this->_app->db->fetchAssoc($res)) {
          //$returnData['eventName'] = sprintf('%s - %s', $row['name'], $this->_app->regionalSettings->convertDateTimeToHuman($row['start']));
          $returnData['eventName'] = $row['name'];
          $returnData['eventDescription'] = $row['description'];
          $returnData['eventStart'] = $row['start'];
          $returnData['eventDateStart'] = substr($row['start'], 0, 10);
          $returnData['eventTimeStart'] = substr($row['start'], 11);
          $returnData['eventEnd'] = $row['end'];
          $returnData['eventDateEnd'] = substr($row['end'], 0, 10);
          $returnData['eventTimeEnd'] = substr($row['end'], 11);
          $returnData['eventPrice'] = $row['price'];
          $returnData['eventRepeat'] = $row['repeat_parent'];
          $returnData['eventRepeatPrice'] = $row['repeat_price'];
          $returnData['eventRepeatReservation'] = $row['repeat_reservation'];
          $returnData['eventCoAttendees'] = $row['max_coattendees'];

          $returnData['feAllowedPayment'] = array('voucher');
          if (1&$row['fe_allowed_payment']) $returnData['feAllowedPayment'][] = 'credit';
          if (10&$row['fe_allowed_payment']) $returnData['feAllowedPayment'][] = 'ticket';
          if (100&$row['fe_allowed_payment']) {
            $returnData['feAllowedPayment'][] = 'online';
            $returnData['feAllowedPayment'][] = 'comgate';
            $returnData['feAllowedPayment'][] = 'csob';
            $returnData['feAllowedPayment'][] = 'gpwebpay';
            $returnData['feAllowedPayment'][] = 'deminimis';
          }

					if ($row['all_tag']) $returnData['commodityTag'] = $row['all_tag'];
          if ($row['reservationcondition']) $returnData['reservationCondition']['event'] = $row['reservationcondition'];

          if ($row['resource']) {
          	$s1 = new SResource;
          	$s1->addStatement(new SqlStatementBi($s1->columns['resource_id'], $row['resource'], '%s=%s'));
          	$s1->setColumnsMask(array('reservationcondition','all_tag'));
          	$res1 = $this->_app->db->doQuery($s1->toString());
          	$row1 = $this->_app->db->fetchAssoc($res1);
						if ($row1['reservationcondition']) $returnData['reservationCondition']['resource'] = $row1['reservationcondition'];
						if ($row1['all_tag']) {
						  if ($returnData['commodityTag']) $returnData['commodityTag'] .= ',';
						  $returnData['commodityTag'] .= $row1['all_tag'];
            }
					}
        }
        
        $returnData['eventPackId'] = array();
        $returnData['eventPackStart'] = array();
        $returnData['eventPackFailed'] = array();
        $returnData['eventAttendeeId'] = array();
        $returnData['eventAttendeePerson'] = array();
        $s = new SEventAttendee;
        $s->addStatement(new SqlStatementBi($s->columns['reservation'], $this->_id, '%s=%s'));
        $s->addOrder(new SqlStatementAsc($s->columns['start']));
        $s->setColumnsMask(array('eventattendee_id','event','start','failed'));
        $res = $this->_app->db->doQuery($s->toString());
        while ($row = $this->_app->db->fetchAssoc($res)) {
          $returnData['eventAttendeeId'][] = $row['eventattendee_id'];
          
          if (!count($returnData['eventAttendeePerson'])) {
            $s1 = new SEventAttendeePerson;
            $s1->addStatement(new SqlStatementBi($s->columns['eventattendee'], $row['eventattendee_id'], '%s=%s'));
            $s1->setColumnsMask(array('eventattendeeperson_id','subaccount','subaccount_firstname','subaccount_lastname','subaccount_email',
              'firstname','lastname','email'));
            $res1 = $this->_app->db->doQuery($s1->toString());
            while ($row1 = $this->_app->db->fetchAssoc($res1)) {
              $returnData['eventAttendeePerson'][] = array(
                'id'					  => $row1['eventattendeeperson_id'],
                'user'          => $row1['subaccount'],
                'userFirstname' => $row1['subaccount_firstname'],
                'userLastname'  => $row1['subaccount_lastname'],
                'userEmail'     => $row1['subaccount_email'],
                'firstname'     => $row1['firstname'],
                'lastname'      => $row1['lastname'],
                'email'         => $row1['email'],
              );
            }
          }
          
          if ($returnData['eventPack']=='Y') {
            $returnData['eventPackId'][] = $row['event'];
            $returnData['eventPackStart'][$row['event']] = $row['start'];
            $returnData['eventPackFailed'][$row['event']] = $row['failed'];
          }
        }
      } elseif ($data['resource']) {
        $returnData['resourceId'] = $data['resource'];
        if ($data['resource_from']) {
          $returnData['resourceFrom'] = $data['resource_from'];
          $returnData['resourceDateFrom'] = substr($data['resource_from'], 0, 10);
          $returnData['resourceTimeFrom'] = substr($data['resource_from'], 11);
        }
        if ($data['resource_to']) {
          $returnData['resourceTo'] = $data['resource_to'];
          $returnData['resourceDateTo'] = substr($data['resource_to'], 0, 10);
          $returnData['resourceTimeTo'] = substr($data['resource_to'], 11);
        }
        $s = new SResource;
        $s->addStatement(new SqlStatementBi($s->columns['resource_id'], $data['resource'], '%s=%s'));
        $s->setColumnsMask(array('name','description','price','unit','reservationcondition','all_tag','fe_allowed_payment'));
        $res = $this->_app->db->doQuery($s->toString());
        if ($row = $this->_app->db->fetchAssoc($res)) {
          $returnData['resourceName'] = $row['name'];
          $returnData['resourceDescription'] = $row['description'];
          $returnData['resourcePrice'] = $row['price'];
          $returnData['resourceUnit'] = $row['unit'];

          $returnData['feAllowedPayment'] = array('voucher');
          if (1&$row['fe_allowed_payment']) $returnData['feAllowedPayment'][] = 'credit';
          if (10&$row['fe_allowed_payment']) $returnData['feAllowedPayment'][] = 'ticket';
          if (100&$row['fe_allowed_payment']) {
            $returnData['feAllowedPayment'][] = 'online';
            $returnData['feAllowedPayment'][] = 'comgate';
            $returnData['feAllowedPayment'][] = 'csob';
            $returnData['feAllowedPayment'][] = 'gpwebpay';
            $returnData['feAllowedPayment'][] = 'deminimis';
          }

          if ($row['reservationcondition']) $returnData['reservationCondition']['resource'] = $row['reservationcondition'];

					if ($row['all_tag']) $returnData['commodityTag'] = $row['all_tag'];
        }
      }
      
      $returnData['attribute'] = $this->getAttribute();
      
      $returnData['journal'] = array();
      $s = new SReservationJournal;
      $s->addStatement(new SqlStatementBi($s->columns['reservation'], $this->_id, '%s=%s'));
      $s->setColumnsMask(array('change_timestamp','action','fullname','note'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $returnData['journal'][] = array('timestamp'=>$row['change_timestamp'],'action'=>$row['action'],'user'=>$row['fullname'],'note'=>$row['note']);
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
  
  public function getAttribute() {
    $ret = array();
    
    $s = new SReservationAttribute;
    $s->addStatement(new SqlStatementBi($s->columns['reservation'], $this->_id, '%s=%s'));
    if ($this->_app->auth->isUser()) {
      $s->addStatement(new SqlStatementMono($s->columns['disabled'], "%s='N'"));
      $s->addStatement(new SqlStatementBi($s->columns['restricted'], $s->columns['restricted'], "(%s IS NULL OR %s='READONLY')"));
    }
    #$s->addOrder(new SqlStatementAsc($s->columns['category']));
    $s->addOrder(new SqlStatementAsc($s->columns['sequence']));
    $s->setColumnsMask(array('attribute_id','short_name','restricted','mandatory','category','type','allowed_values','value','disabled'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $s1 = new SAttributeName;
      $s1->addStatement(new SqlStatementBi($s1->columns['attribute'], $row['attribute_id'], '%s=%s'));
      $s1->setColumnsMask(array('lang','name'));
      $res1 = $this->_app->db->doQuery($s1->toString());
      $name = array(); while ($row1 = $this->_app->db->fetchAssoc($res1)) { $name[$row1['lang']] = $row1['name']; }
      
      $ret[$row['attribute_id']] = array(
        'attributeId'           => $row['attribute_id'],
        'name'                  => $name,
        'shortName'             => $row['short_name'],
        'restricted'            => $row['restricted'],
        'mandatory'             => $row['mandatory'],
        'category'              => $row['category'],
        'type'                  => $row['type'],
        'allowedValues'         => !strcmp($row['type'],'LIST')?$row['allowed_values']:null,
        'value'                 => $row['value'],
        'disabled'              => $row['disabled'],
      );

      if (!strcmp($row['type'],'FILE')) {
        $s2 = new SFile;
        $s2->addStatement(new SqlStatementBi($s2->columns['file_id'], $row['value'], '%s=%s'));
        $s2->setColumnsMask(array('hash','name'));
        $res2 = $this->_app->db->doQuery($s2->toString());
        $row2 = $this->_app->db->fetchAssoc($res2);

        $ret[$row['attribute_id']]['id'] = $row['value'];
        $ret[$row['attribute_id']]['valueId'] = $row2['hash'];
        $ret[$row['attribute_id']]['value'] = $row2['name'];
      }
    }
    
    return $ret;
  }
  
  private function _generateNumber($providerId) {
    $s = new SReservation;
    $s->addStatement(new SqlStatementBi($s->columns['provider'], $providerId, '%s=%s'));
    $s->addOrder(new SqlStatementDesc($s->columns['number']));
    $s->setForUpdate(true);
    $s->setColumnsMask(array('number'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($row = $this->_app->db->fetchAssoc($res)) $lastNumber = $row['number'];
    else $lastNumber = 0;

    $number = sprintf('%08d', $lastNumber+1);
    
    $o = new OReservation($this->_id);
    $o->setData(array('number'=>$number));
    $o->save();
    
    return $number;
  }
  
  private function _checkEventCapacity($ids, $params) {
    if ($this->_id) {
      $this->_data['eventSubscription'] = array();
      
      // uvolneni puvodnich obsazenych mist z puvodni akce
      $s = new SEventAttendee;
      $s->addStatement(new SqlStatementBi($s->columns['reservation'], $this->_id, '%s=%s'));
      $s->setColumnsMask(array('eventattendee_id','event','subscription_time'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $this->_data['eventSubscription'][$row['event']] = $row['subscription_time'];
        
        $o = new OEventAttendee($row['eventattendee_id']);
        $o->delete();
      }
    }
    
    $s = new SEvent;
    $s->addStatement(new SqlStatementMono($s->columns['event_id'], sprintf('%%s IN (%s)', implode(',',$ids))));
    $s->setColumnsMask(array('event_id','name','start','end','free','reservation_max_attendees','repeat_reservation'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $subject = sprintf('%s (%s)', $row['name'], $this->_app->regionalSettings->convertDateTimeToHuman($row['start']));
      if (isset($params['eventParams']['eventPack'])&&($params['eventParams']['eventPack']=='Y')) {
        if (!strcmp($row['repeat_reservation'],'SINGLE')) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveReservation_eventPackNotAllowed'), $subject));
      } elseif (!isset($params['eventParams']['eventPack'])||($params['eventParams']['eventPack']=='N')) {
        if (!strcmp($row['repeat_reservation'],'PACK')) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveReservation_eventSingleNotAllowed'), $subject));
      }
      if ($row['reservation_max_attendees']<$params['eventParams']['eventPlaces']) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveReservation_eventPlacesAllowed'), $subject, $row['reservation_max_attendees']));
      if ($row['free']<$params['eventParams']['eventPlaces']) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveReservation_eventNotEnoughPlaces'), $subject));
      if (!isset($params['allowPast'])||!$params['allowPast']) {
        if ($row['end']<=date('Y-m-d H:i:s')) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveReservation_eventInvalidTime'), $subject));
      }
    }
  }
    
  private function _getEventAttr($params) {
    $s = new SEvent;
    $s->addStatement(new SqlStatementBi($s->columns['event_id'], $params['eventParams']['eventId'], '%s=%s'));
    $s->setColumnsMask(array('start','price','repeat_price','repeat_parent'));
    $res = $this->_app->db->doQuery($s->toString());
    if (!$row = $this->_app->db->fetchAssoc($res)) throw new ExceptionUserTextStorage('error.saveReservation_invalidEvent');
    
    $ret = array(
    	'start' 		=> $row['start'],
			'price'     => isset($params['eventParams']['eventPack'])&&($params['eventParams']['eventPack']=='Y')?$row['repeat_price']:$row['price'],
			'packId' 		=> array($params['eventParams']['eventId'])
		);
    
    // kdyz se rezervuje cele opakovani musim vratit ID-cka vsech akci, kterych se to tyka
    if (isset($params['eventParams']['eventPack'])&&($params['eventParams']['eventPack']=='Y')) {
      // pokud uz byla akce jednou ulozena, nehledam aktivni akce, ale ukladam porad stejny cyklus
      if (isset($params['eventParams']['eventPackId'])) $ret['packId'] = $params['eventParams']['eventPackId'];
      else {
        $ret['packId'] = array();
      
        $s = new SEvent;
        $s->addStatement(new SqlStatementBi($s->columns['repeat_parent'], $row['repeat_parent'], '%s=%s'));
        $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
        $s->addOrder(new SqlStatementAsc($s->columns['start']));
        $s->setColumnsMask(array('event_id'));
        $res = $this->_app->db->doQuery($s->toString());
        while ($row = $this->_app->db->fetchAssoc($res)) {
          $ret['packId'][] = $row['event_id'];
        }
      }
    }
    
    return $ret;
  }
  
  private function _checkResourceAvailability($params) {
    // uvolneni puvodni blokace zdroje
    if ($this->_data) {
      if (isset($this->_data['resourceId'])) {
        $b = new BResource($this->_data['resourceId']);
        $b->freeAvailabilityTable($this->_data['resourceFrom'], $this->_data['resourceTo']);
      }
    }
    
    // kontrola na availabilitu
    $b = new BResource($params['resourceParams']['resourceId']);
    $avail1 = $b->getAvailability($params['resourceParams']['resourceFrom']);
    $avail2 = $b->getAvailability($params['resourceParams']['resourceTo']);
    if (!$avail1||($avail1!=$avail2)) {
      throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveReservation_availabilityConflict')));
    }
  }
    
  private function _getResourceAttr($params) {
    $ret = array();
    
    // kontrola na minimalni pocet jednotek + vypocet ceny
    $s = new SResource;
    $s->addStatement(new SqlStatementBi($s->columns['resource_id'], $params['resourceParams']['resourceId'], '%s=%s'));
    $s->setColumnsMask(array('unit','unit_rounding','minimum_quantity','maximum_quantity','time_alignment_from','time_alignment_to','time_alignment_grid','time_end_from','time_end_to'));
    $res = $this->_app->db->doQuery($s->toString());
    if (!$row = $this->_app->db->fetchAssoc($res)) throw new ExceptionUserTextStorage('error.saveReservation_invalidResource');
    
    $number = $row['unit'];
    $unit = $this->_app->textStorage->getText('label.editUnitProfile_unitMin');
    if (in_array($row['unit_rounding'],array('day','night'))) { $number = $number/1440; $unit = $this->_app->textStorage->getText('label.editUnitProfile_unitDay'); }
    else if ($number%60===0) { $number = $number/60; $unit = $this->_app->textStorage->getText('label.editUnitProfile_unitHour'); }

    if (!strcmp($row['unit_rounding'],'day')) {
      // kdyz jsou rezervacni jednotky den, tak nechci aby cenu ovlivnoval cas v ramci dne (typicky se tak budou rezervovat kola)
      // nastavim cas "to" stejny jako "from"
      // a datum "to" zvetsim o den (aby vypocet ceny byl podle celych dnu)
      $modifiedResourceTo = $this->_app->regionalSettings->increaseDate(substr($params['resourceParams']['resourceTo'],0,10)).substr($params['resourceParams']['resourceFrom'],10);
    } elseif (!strcmp($row['unit_rounding'],'night')) {
      // kdyz jsou rezervacni jednotky noc, tak nechci aby cenu ovlivnoval cas v ramci noci (typicky se tak budou rezervovat pokoje)
      // nastavim cas "to" stejny jako "from"
      // tady se datum "to" zvetsovat nemusi, protoze predpokladam, ze pri rezervaci noci bude datum "from" mensi nez datum "to"
      $modifiedResourceTo = substr($params['resourceParams']['resourceTo'],0,10).substr($params['resourceParams']['resourceFrom'],10);
    } else {
      $modifiedResourceTo = $params['resourceParams']['resourceTo'];
    }
    $reservationLength = (strtotime($modifiedResourceTo)-strtotime($params['resourceParams']['resourceFrom']))/60;

    // kontrola na pocet jednotek
    $unitRatio = $reservationLength/$row['unit'];
    if (($unitRatio<$row['minimum_quantity'])||
        ($row['maximum_quantity']&&($unitRatio>$row['maximum_quantity']))||
        ($unitRatio-floor($unitRatio))) {
      $message = sprintf($this->_app->textStorage->getText('error.saveReservation_resourceRatioLength'), $number, $unit);
      if ($unitRatio<$row['minimum_quantity']) {
        $message .= ' '.sprintf($this->_app->textStorage->getText('error.saveReservation_resourceMinLength'), $row['minimum_quantity']);
      }
      if ($row['maximum_quantity']&&($unitRatio>$row['maximum_quantity'])) {
        $message .= ' '.sprintf($this->_app->textStorage->getText('error.saveReservation_resourceMaxLength'), $row['maximum_quantity']);
      }
      throw new ExceptionUser($message);
    }
    
    // kontrola na time alignment (zacatek rezervace)
    if ($row['time_alignment_from']) {
      $alignmentStart = strtotime(substr($params['resourceParams']['resourceFrom'],0,11).$row['time_alignment_from']);
      $alignmentEnd = $row['time_alignment_to']?strtotime(substr($params['resourceParams']['resourceFrom'],0,11).$row['time_alignment_to']):null;
      $reservationStart = strtotime($params['resourceParams']['resourceFrom']);
      
      $error = ($reservationStart<$alignmentStart)||($alignmentEnd&&($alignmentEnd<$reservationStart));
      $message = '';
      if ($row['time_alignment_grid']) {
        if ((($reservationStart-$alignmentStart)/60)%$row['time_alignment_grid']) {
          $error = true;
          
          $n = $row['time_alignment_grid'];
          $u = $this->_app->textStorage->getText('label.editUnitProfile_unitMin');
          if ($n%1440===0) { $n = $n/1440; $u = $this->_app->textStorage->getText('label.editUnitProfile_unitDay'); }
          else if ($n%60===0) { $n = $n/60; $u = $this->_app->textStorage->getText('label.editUnitProfile_unitHour'); }

          $message = sprintf($this->_app->textStorage->getText('error.saveReservation_resourceTimeAlignment_grid'), $n, $u ).' '.$message;
        }
      }
      if ($error) {
        $message = $this->_app->textStorage->getText('error.saveReservation_resourceTimeAlignment').' '.$message;
        $message .= ' '.sprintf($this->_app->textStorage->getText('error.saveReservation_resourceTimeAlignment_from'), $this->_app->regionalSettings->convertTimeToHuman($row['time_alignment_from'], 'h:m'));
        if ($alignmentEnd) $message .= ' '.sprintf($this->_app->textStorage->getText('error.saveReservation_resourceTimeAlignment_to'), $this->_app->regionalSettings->convertTimeToHuman($row['time_alignment_to'], 'h:m'));
        throw new ExceptionUser($message);
      }
    }

    // kontrola na konec rezervace
    if ($row['time_end_from']&&$row['time_end_to']) {
      $endFrom = strtotime(substr($params['resourceParams']['resourceTo'], 0, 11) . $row['time_end_from']);
      $endTo = strtotime(substr($params['resourceParams']['resourceTo'], 0, 11) . $row['time_end_to']);
      $reservationEnd = strtotime($params['resourceParams']['resourceTo']);

      $error = ($reservationEnd<$endFrom)||($endTo<$reservationEnd);
      if ($error) {
        $message = $this->_app->textStorage->getText('error.saveReservation_resourceTimeEnd');
        $message .= ' '.sprintf($this->_app->textStorage->getText('error.saveReservation_resourceTimeAlignment_from'), $this->_app->regionalSettings->convertTimeToHuman($row['time_end_from'], 'h:m'));
        $message .= ' '.sprintf($this->_app->textStorage->getText('error.saveReservation_resourceTimeAlignment_to'), $this->_app->regionalSettings->convertTimeToHuman($row['time_end_to'], 'h:m'));
        throw new ExceptionUser($message);
      }
    }
    
    $bResource = new BResource($params['resourceParams']['resourceId']);
    $ret['price'] = $bResource->getPrice($params['resourceParams']['resourceFrom'],$params['resourceParams']['resourceTo']);
      
    return $ret;
  }
  
  private function _saveCommodity($params) {
    if (isset($params['eventParams'])) {
      // zkontroluju ucastniky, aby byli pouze z uzivatelovych poductu
      if (isset($params['userId'])&&$params['userId']&&(BCustomer::getProviderSettings($params['providerId'], 'userSubaccount')=='Y')&&
        isset($params['eventParams']['eventAttendeePerson'])&&count($params['eventParams']['eventAttendeePerson'])) {
        $bUser = new BUser($params['userId']);
        $subAccounts = array_keys($bUser->getSubaccount());

        foreach ($params['eventParams']['eventAttendeePerson'] as $attendee) {
          if (!isset($attendee['user'])||!in_array($attendee['user'], $subAccounts)) throw new ExceptionUserTextStorage('error.saveReservation_invalidAttendeeSubaccount');
        }
      }

			if (!isset($this->_data['payed'])||!$this->_data['payed']) {
				// kdyz neni rezervace zaplacena, aktualizace probiha normalne
				$event = $this->_getEventAttr($params);
				$this->_checkEventCapacity($event['packId'], $params);

				$o = new OReservation($this->_id);
				$oData = array(
						'start'         => $params['start'],
						'end'           => $params['end'],
						'event'         => $params['eventParams']['eventId'],
						'event_pack'    => ifsetor($params['eventParams']['eventPack'],'N'),
						'event_places'  => $params['eventParams']['eventPlaces'],
						);
				$this->_data['eventId'] = $params['eventParams']['eventId'];

				if (!isset($params['priceManual'])||!$params['priceManual']) {
					$oData['total_price'] = $event['price'] * $params['eventParams']['eventPlaces'];

					$this->_data['price'] = round($event['price'] * $params['eventParams']['eventPlaces'],2);
				}

				$o->setData($oData);
				$o->save();

				foreach ($event['packId'] as $id) {
					$o = new OEventAttendee;
					$oData = array(
            'event'             => $id,
            'user'              => isset($params['userId'])&&$params['userId']?$params['userId']:null,
            'reservation'       => $this->_id,
            'subscription_time' => ifsetor($this->_data['eventSubscription'][$id], date('Y-m-d H:i:s')),
            'places'            => $params['eventParams']['eventPlaces'],
            'failed'            => ifsetor($this->_data['eventPackFailed'][$id], null),
          );
					$o->setData($oData);
					$o->save();
					$eaId = $o->getId();

					foreach ($params['eventParams']['eventAttendeePerson'] as $attendee) {
						$o = new OEventAttendeePerson;
						$o->setData(array(
								'eventattendee'   => $eaId,
                'user'            => isset($attendee['user'])&&$attendee['user']?$attendee['user']:null,
								'firstname'       => ifsetor($attendee['firstname']),
								'lastname'        => ifsetor($attendee['lastname']),
								'email'           => ifsetor($attendee['email']),
								));
						$o->save();
					}
				}
			} else {
				// kdyz je rezervace zaplacena, pouze updatnu ucastniky
				foreach ($params['eventParams']['eventAttendeePerson'] as $attendee) {
					if ($attendee['id']) {
						$o = new OEventAttendeePerson($attendee['id']);
						$o->setData(array(
              'user'            => isset($attendee['user'])&&$attendee['user']?$attendee['user']:null,
							'firstname'       => ifsetor($attendee['firstname']),
							'lastname'        => ifsetor($attendee['lastname']),
							'email'           => ifsetor($attendee['email']),
						));
						$o->save();
					}
				}
			}
    } elseif (isset($params['resourceParams'])) {
      list($params['resourceParams']['resourceDateFrom'],$params['resourceParams']['resourceTimeFrom']) = explode(' ', $params['resourceParams']['resourceFrom']);
      list($params['resourceParams']['resourceDateTo'],$params['resourceParams']['resourceTimeTo']) = explode(' ', $params['resourceParams']['resourceTo']);
      
      $this->_checkResourceAvailability($params);
      $resource = $this->_getResourceAttr($params);
      
      $o = new OReservation($this->_id);
      $oData = array(
          'start'          => $params['start'],
          'end'            => $params['end'],
          'resource'       => $params['resourceParams']['resourceId'],
          'resource_from'  => $params['resourceParams']['resourceFrom'],
          'resource_to'    => $params['resourceParams']['resourceTo'],
          );
      $this->_data['resourceId'] = $params['resourceParams']['resourceId'];
      
      if (!isset($params['priceManual'])||!$params['priceManual']) {
        $oData['total_price'] = $resource['price'];
        
        $this->_data['price'] = round($resource['price'],2);
      }
      
      $o->setData($oData);
      $o->save();
      
      $b = new BResource($params['resourceParams']['resourceId']);
      $b->occupyAvailabilityTableByReservation($this->_id);
    }
  }
  
  private function _saveAttribute($params) {
    $idsToSave = array();
    foreach ($params['attribute'] as $id=>$attribute) {
      $s = new SReservationAttribute;
      $s->addStatement(new SqlStatementBi($s->columns['attribute'], $id, '%s=%s'));
      $s->addStatement(new SqlStatementBi($s->columns['reservation'], $this->_id, '%s=%s'));
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
          $fileId = $bF->save($fileParams);
          
          $attribute['value'] = $fileId;
        }
      }
      
      if ($row) {
        $o = new OReservationAttribute(array('reservation'=>$this->_id,'attribute'=>$id));
        $o->setData(array('value'=>$attribute['value']));
        $o->save();
      } else {
        $o = new OReservationAttribute;
        $o->setData(array('reservation'=>$this->_id,'attribute'=>$id,'value'=>$attribute['value']));
        $o->save();
      }
      $idsToSave[] = $id;
    }
    
    $s = new SReservationAttribute;
    $s->addStatement(new SqlStatementBi($s->columns['reservation'], $this->_id, '%s=%s'));
    if (count($idsToSave)) $s->addStatement(new SqlStatementMono($s->columns['attribute'], sprintf('%%s NOT IN (%s)', implode(',', $idsToSave))));
    $s->setColumnsMask(array('attribute','value','type'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $o = new OReservationAttribute(array('reservation'=>$this->_id, 'attribute'=>$row['attribute']));
      $o->delete();
      if (!strcmp($row['type'],'FILE')) {
        $o = new OFile($row['value']);
        $o->delete();
      }
    }
  }

  private function _saveVoucher($params) {
    $ret = array('payedByVoucher'=>false);

    $origPrice = $this->_data['price'];

    // kdyz je voucher, tak ho zkontroluju, ulozim a ponizim o nej celkovou cenu pred zaplacenim
    if (isset($params['voucher'])&&$params['voucher']) {
      if (!$this->_data['userId']) throw new ExceptionUserTextStorage('error.saveReservation_voucher_anonymous');
      //if ($this->_data['voucher']) throw new ExceptionUserTextStorage('error.saveReservation_voucher_alreadyAssigned');
      if ($this->_data['payed']||$this->_data['cancelled']||$this->_data['failed']) throw new ExceptionUserTextStorage('error.saveReservation_voucher_invalidState');

      // nactu tagy commodity z rezervace
      $commodityTag = null;
      if (isset($this->_data['eventId'])&&$this->_data['eventId']) {
        $s = new SEvent;
        $s->addStatement(new SqlStatementBi($s->columns['event_id'], $this->_data['eventId'], '%s=%s'));
        $s->setColumnsMask(array('all_tag'));
        $res = $this->_app->db->doQuery($s->toString());
        $row = $this->_app->db->fetchAssoc($res);
        $commodityTag = explode(',',$row['all_tag']);
      } else {
        $s = new SResource;
        $s->addStatement(new SqlStatementBi($s->columns['resource_id'], $this->_data['resourceId'], '%s=%s'));
        $s->setColumnsMask(array('all_tag'));
        $res = $this->_app->db->doQuery($s->toString());
        $row = $this->_app->db->fetchAssoc($res);
        $commodityTag = explode(',',$row['all_tag']);
      }

      // pocet pouziti se bude kontrolovat pouze kdyz se prirazuje novy voucher
      $validateVoucherApplication = !isset($this->_data['voucher'])||($params['voucher']!=$this->_data['voucher']);
      $bUser = new BUser($this->_data['userId']);
      $availableVoucher = $bUser->getAvailableVoucher($this->_data['providerId'], $this->_data['price'], $this->_data['centerId'], $commodityTag, true, $validateVoucherApplication);
      if (!isset($availableVoucher[$params['voucher']])) throw new ExceptionUserTextStorage('error.saveReservation_invalidVoucher');

      $o = new OReservation($this->_id);
      $oData = array(
        'voucher'                   => $params['voucher'],
        'voucher_discount_amount'   => $availableVoucher[$params['voucher']]['calculatedDiscountRaw'],
      );
      if (!isset($params['priceManual'])||!$params['priceManual']) {
        $this->_data['price'] -= $availableVoucher[$params['voucher']]['calculatedDiscountRaw'];
        $oData['total_price'] = $this->_data['price'];
      }
      $o->setData($oData);
      $o->save();

      $this->_data['voucher'] = $params['voucher'];
      $this->_data['voucherDiscount'] = $availableVoucher[$params['voucher']]['calculatedDiscountRaw'];
    } elseif (!$this->_data['payed']&&isset($this->_data['voucher'])) {
      // musim prepocitat slevu, protoze se mohla zmenit cena rezervace
      $b = new BVoucher($this->_data['voucher']);
      $discount = $b->getDiscount($this->_data['price']);

      $o = new OReservation($this->_id);
      $oData = array(
        'voucher_discount_amount'   => $discount,
      );
      $this->_data['voucherDiscount'] = $discount;
      if (!isset($params['priceManual'])||!$params['priceManual']) {
        $this->_data['price'] -= $discount;
        $oData['total_price'] = $this->_data['price'];
      }
      $o->setData($oData);
      $o->save();
    }

    if ($this->_data['voucher']&&$origPrice&&!$this->_data['price']) {
      $ret['payedByVoucher'] = true;
    }

    return $ret;
  }

  private function _save($params) {
    $this->_app->db->beginTransaction();

    $o = new OReservation($this->_id?$this->_id:null);
    $oData = array();
    if (!$this->_id) $oData['created'] = date('Y-m-d H:i:s');
    if (isset($params['mandatory'])) {
      $oData['mandatory'] = $params['mandatory']=='Y'?'Y':'N';
    }
    if (isset($params['userId'])) {
      $oData['user'] = $params['userId']?$params['userId']:null;
      $this->_data['userId'] = $params['userId'];
    } else $this->_data['userId'] = null;
    if (isset($params['customerId'])) {
      $oData['customer'] = $params['customerId']?$params['customerId']:null;
      $this->_data['customerId'] = $params['customerId'];
    }
    if (isset($params['providerId'])) {
      $oData['provider'] = $params['providerId'];
      $this->_data['providerId'] = $params['providerId'];
    }
    if (isset($params['centerId'])) {
      $oData['center'] = $params['centerId'];
      $this->_data['centerId'] = $params['centerId'];
    }
    
    if (isset($params['pool'])) $oData['pool'] = $params['pool'];
    
    if (isset($params['note'])) $oData['note'] = $params['note'];

    if (!isset($this->_data['payed'])||!$this->_data['payed']) {
			if (isset($params['priceManual'])) {
				if (!$this->_app->auth->haveRight('reservation_admin', $params['providerId'])) throw new ExceptionUserTextStorage('error.accessDenied');

				if ($params['priceManual']) {
					if (isset($params['price'])) {
						$oData['total_price'] = $params['price'];
						$oData['price_timestamp'] = date('Y-m-d H:i:s');
						$oData['price_user'] = $this->_app->auth->getUserId();

						$this->_data['price'] = $params['price'];
					}
					$oData['price_comment'] = ifsetor($params['priceComment']);
				} else {
					$oData['price_comment'] = null;
					$oData['price_timestamp'] = null;
					$oData['price_user'] = null;
				}
			} else {
				$oData['price_comment'] = null;
				$oData['price_timestamp'] = null;
				$oData['price_user'] = null;
			}
    }

    if (count($oData)) {
      $o->setData($oData);
      $o->save();
    }
    if (!$this->_id) {
      $this->_creatingNew = true;
      
      $this->_id = $o->getId();
      
      $number = $this->_generateNumber(ifsetor($params['providerId'],$this->_data['providerId']));
      $this->_data['number'] = $number;
    } else {
      $this->_creatingNew = false;
    }

    $this->_saveCommodity($params);
    $this->_saveAttribute($params);

    $this->createJournalRecord('SAVE', ifsetor($params['comment']));

    $this->_app->db->commitTransaction();
    
    return $this->_data['number'];
  }
  
  public function getStatus($params) {
    $this->_load();
    
    $status = array('price'=>0,'voucherDiscount'=>0,'provider'=>null);
    
    if (isset($params['eventParams'])) {
      $s = new SEvent;
      $s->addStatement(new SqlStatementBi($s->columns['event_id'], $params['eventParams']['eventId'], '%s=%s'));
      $s->setColumnsMask(array('provider','center'));
      $res = $this->_app->db->doQuery($s->toString());
      $row = $this->_app->db->fetchAssoc($res);
      
      $status['provider'] = $row['provider'];
      $status['center'] = $row['center'];
      
      $params['providerId'] = $row['provider'];
      
      $event = $this->_getEventAttr($params);
      
      $status['price'] = round($event['price'] * $params['eventParams']['eventPlaces'],2);
    } elseif (isset($params['resourceParams'])) {
      $s = new SResource;
      $s->addStatement(new SqlStatementBi($s->columns['resource_id'], $params['resourceParams']['resourceId'], '%s=%s'));
      $s->setColumnsMask(array('provider','center'));
      $res = $this->_app->db->doQuery($s->toString());
      $row = $this->_app->db->fetchAssoc($res);
      
      $status['provider'] = $row['provider'];
      $status['center'] = $row['center'];
      
      $params['providerId'] = $row['provider'];
      
      list($params['resourceParams']['resourceDateFrom'],$params['resourceParams']['resourceTimeFrom']) = explode(' ', $params['resourceParams']['resourceFrom']);
      list($params['resourceParams']['resourceDateTo'],$params['resourceParams']['resourceTimeTo']) = explode(' ', $params['resourceParams']['resourceTo']);
      
      $resource = $this->_getResourceAttr($params);
      
      $status['price'] = round($resource['price'],2);
    }
    if ($this->_data['voucher']) {
      $b = new BVoucher($this->_data['voucher']);
      $discount = $b->getDiscount($status['price']);

      $status['voucherDiscount'] = $discount;
      $status['price'] -= $discount;
    }
    
    return $status;
  }

  public function expandSaveParams(& $params) {
    $this->_load();

    $provider = null;
    $center = null;
    $reservationCondition = array();
    $start = null;
    $end = null;
    $tag = '';

    if (isset($params['eventParams']['eventId'])) {
      // kdyz chce rezervovat vsechna opakovani, rezervuju od prvni aktivni
      if (isset($params['eventParams']['eventPack'])&&($params['eventParams']['eventPack']=='Y')) {
        $o = new OEvent($params['eventParams']['eventId']);
        $oData = $o->getData();
        $s = new SEvent;
        $s->addStatement(new SqlStatementBi($s->columns['repeat_parent'], $oData['repeat_parent'], '%s=%s'));
        $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
        $s->addOrder(new SqlStatementAsc($s->columns['start']));
        $s->setColumnsMask(array('event_id','start','end'));
        $res = $this->_app->db->doQuery($s->toString());
        while ($row = $this->_app->db->fetchAssoc($res)) {
          if (!$start) {
            $start = $row['start'];
            $params['eventParams']['eventId'] = $row['event_id'];
          }
          $end = $row['end'];
        }
      }

      $s = new SEvent;
      $s->addStatement(new SqlStatementBi($s->columns['event_id'], $params['eventParams']['eventId'], '%s=%s'));
      $s->setColumnsMask(array('provider','center','organiser','start','end','max_coattendees','reservationcondition','resource','all_tag'));
      $res = $this->_app->db->doQuery($s->toString());
      if ($row = $this->_app->db->fetchAssoc($res)) {
        $provider = $row['provider'];
        $center = $row['center'];
        $tag = $row['all_tag'];
        if ($row['reservationcondition']) $reservationCondition['event'] = $row['reservationcondition'];
        if (!$start) $start = $row['start'];
        if (!$end) $end = $row['end'];
        // kdyz ma udalost zdroj, budu vyhodnocovat i podminku zdroje
        if ($row['resource']) {
          $params['eventParams']['resourceId'] = $row['resource'];

          $s1 = new SResource;
          $s1->addStatement(new SqlStatementBi($s1->columns['resource_id'], $row['resource'], '%s=%s'));
          $s1->setColumnsMask(array('reservationcondition','all_tag'));
          $res1 = $this->_app->db->doQuery($s1->toString());
          $row1 = $this->_app->db->fetchAssoc($res1);
          if ($row1['reservationcondition']) $reservationCondition['resource'] = $row1['reservationcondition'];
          if ($row1['all_tag']) {
            if ($tag) $tag .= ',';
            $tag .= $row1['all_tag'];
          }
        }

        $params['eventParams']['eventCoAttendees'] = $row['max_coattendees'];

        // muze byt mandatory rezervace organizatorem vynucena nastavenim poskytovatele
        if (!isset($params['mandatory'])||($params['mandatory']=='N')) {
          if ($params['userId']&&($params['userId']!=$this->_app->auth->getUserId())&&($row['organiser']==$this->_app->auth->getUserId())) {
            if ((BCustomer::getProviderSettings($provider, 'allowMandatoryReservation')=='Y')&&(BCustomer::getProviderSettings($provider, 'organiserMandatoryReservation')=='Y')) {
              $params['mandatory'] = 'Y';
            }
          }
        }
      }
    } elseif (isset($params['resourceParams']['resourceId'])) {
      if (isset($params['resourceParams']['resourceTo'])) {
        list($date,$time) = explode(' ',$params['resourceParams']['resourceTo']);
        if (!strcmp($time,'24:00:00')) {
          $date = $this->_app->regionalSettings->increaseDate($date);
          $params['resourceParams']['resourceTo'] = sprintf('%s 00:00:00', $date);
        }
      }

      $s = new SResource;
      $s->addStatement(new SqlStatementBi($s->columns['resource_id'], $params['resourceParams']['resourceId'], '%s=%s'));
      $s->setColumnsMask(array('provider','center','reservationcondition','all_tag'));
      $res = $this->_app->db->doQuery($s->toString());
      if ($row = $this->_app->db->fetchAssoc($res)) {
        $provider = $row['provider'];
        $center = $row['center'];
        $tag = $row['all_tag'];
        if ($row['reservationcondition']) $reservationCondition['resource'] = $row['reservationcondition'];
        $start = ifsetor($params['resourceParams']['resourceFrom'],$this->_id?$this->_data['start']:null);
        $end = ifsetor($params['resourceParams']['resourceTo'],$this->_id?$this->_data['end']:null);
      }
    } elseif ($this->_id) {
    	$provider = $this->_data['providerId'];
    	$center = $this->_data['centerId'];
			$reservationCondition = $this->_data['reservationCondition'];
			$start = $this->_data['start'];
    	$end = $this->_data['end'];
    	$tag = $this->_data['commodityTag'];
		}

    // kdyz nejsou povolene mandatory rezervace, bude se vzdy ukladat mandatory='N'
    if (BCustomer::getProviderSettings($provider, 'allowMandatoryReservation')=='N') {
      $params['mandatory'] = 'N';
    }

    if (!isset($params['userId'])) $params['userId'] = $this->_data['userId'];
    if ($params['userId']) {
      $s = new SUser;
      $s->addStatement(new SqlStatementBi($s->columns['user_id'], $params['userId'], '%s=%s'));
      $s->setColumnsMask(array('reservationcondition'));
      $res = $this->_app->db->doQuery($s->toString());
      $row = $this->_app->db->fetchAssoc($res);
      if ($row['reservationcondition']) $reservationCondition['user'] = $row['reservationcondition'];
    }

    $params['providerId'] = $provider;
    $params['centerId'] = $center;
    $params['reservationCondition'] = $reservationCondition;
    $params['start'] = $start;
    $params['end'] = $end;
    $params['commodityTag'] = $tag;

    // kdyz se plati online, je potreba rezervaci nejdriv ulozit, ikdyz musi byt dle podminek rovnou zaplacena
    if (!isset($params['skipCondition'])) {
      $params['skipCondition'] = array();
      if (isset($params['paymentOnline']) && $params['paymentOnline']) {
        $params['skipCondition'][] = 'advancePayment';
      }
    }

    // kdyz byla cena nastavena rucne a neni parametr vubec musi byt porad nastavena rucne
    if ($this->_id&&$this->_data['priceUser']&&!isset($params['priceManual'])) $params['priceManual'] = true;
  }

  public function save($params) {
  	$origParams = $params;

    $this->expandSaveParams($params);

    if ($this->_id) $this->_oldData = $this->_data;
    
    if (!$this->_checkAccess($params)) throw new ExceptionUserTextStorage('error.accessDenied');

		if ($this->_id&&$this->_data['payed']) {
      // kontrola povolenych parametru pro ulozeni zaplacene rezervace
      unset($origParams['mandatory']);
      unset($origParams['attribute']);
      unset($origParams['note']);

      unset($origParams['eventParams']['eventAttendeePerson']);
      if (isset($origParams['eventParams'])&&!count($origParams['eventParams'])) unset($origParams['eventParams']);

      unset($origParams['resourceParams']['resourceId']);
      unset($origParams['resourceParams']['resourceFrom']);
      unset($origParams['resourceParams']['resourceTo']);
      if (isset($origParams['resourceParams'])&&!count($origParams['resourceParams'])) unset($origParams['resourceParams']);

      if (count($origParams)) throw new ExceptionUserTextStorage('error.saveReservation_payed');

      // kontrola zmeny ceny pri zmene zdroje
      if (isset($params['resourceParams']['resourceId'])) {
        $newStatus = $this->getStatus(array('resourceParams' => array('resourceId'=>$params['resourceParams']['resourceId'], 'resourceFrom'=>$params['start'], 'resourceTo'=>$params['end'])));

        if (!$this->_data['priceUser']&&($newStatus['price']<>$this->_data['price'])) {
          throw new ExceptionUserTextStorage('error.saveReservation_payedDifferentPrice');
        }
      }
    }

		$this->_checkBeforeSave($params);

		$this->_checkReservationAttribute($params);

		// @@todo zatim se nekontroluji rez. podminky, kdyz se uklada zaplacena rezervace
    if (!$this->_id||!$this->_data['payed']) {
		  if (!$this->checkReservationCondition($params)) $params['comment'] = 'RESERVATION_CONDITION_ALERT';
    }

    $this->_app->db->beginTransaction();
    
    $number = $this->_save($params);

    $this->_loaded = false;
    $this->_load();

    $voucherResult = $this->_saveVoucher($params);
    if ($voucherResult['payedByVoucher']) {
      unset($params['paymentOnline']);
      unset($params['payArrangeCredit']);
      unset($params['payArrangeCreditAmount']);

      $params['pay'] = 'Y';
      $params['payType'] = 'voucher';
    }

    // kdyz se plati online, vygeneruje se zaznam o zahajeni online platby
    if (isset($params['paymentOnline'])&&$params['paymentOnline']) {
      $bPayment = new BOnlinePayment;
      $bPayment->openOnlinePayment($this->_data['price'], 'RESERVATION', sprintf('|%s|', $this->_id));
    }

    if ($this->_creatingNew) BDocumentTemplate::generate(array('type'=>'R_CREATE','providerId'=>$params['providerId'],'userId'=>$params['userId'],'reservationId'=>$this->_id));

    $this->_generateNotification($params);

    if (isset($params['pay'])&&($params['pay']=='Y')) {
      $payParams = array();
      if (isset($params['payTicket'])) $payParams['ticket'] = $params['payTicket'];
      if (isset($params['payArrangeCredit'])) $payParams['arrangeCredit'] = $params['payArrangeCredit'];
      if (isset($params['payArrangeCreditAmount'])) $payParams['arrangeCreditAmount'] = $params['payArrangeCreditAmount'];
      $this->pay(ifsetor($params['payType'],'credit'), $payParams);
    }
    
    $this->_app->db->commitTransaction();
    
    return $number;
  }

  public function saveVoucher($params) {
    if (!$this->_checkAccess($params)) throw new ExceptionUserTextStorage('error.accessDenied');

    $this->_app->db->beginTransaction();

    $voucherResult = $this->_saveVoucher($params);
    if ($voucherResult['payedByVoucher']) {
      $this->pay('voucher');
    }

    $this->_app->db->commitTransaction();

    return $voucherResult;
  }
  
  private function _generateNotification($params) {
    // smazu neodeslane notifikace k rezervqci
    $s = new SNotification;
    $s->addStatement(new SqlStatementBi($s->columns['reservation'], $this->_id, '%s=%s'));
    $s->addStatement(new SqlStatementMono($s->columns['type'], "%s NOT IN ('R_CREATE','R_PAY')"));  // tyhle notifikace necham vzdy
    $s->addStatement(new SqlStatementMono($s->columns['sent'], '%s IS NULL'));
    $s->setColumnsMask(array('notification_id'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $o = new ONotification($row['notification_id']);
      $o->delete();
    }
    
    if ($this->_creatingNew) BNotificationTemplate::generate(array('type'=>'R_CREATE','providerId'=>$params['providerId'],'userId'=>$params['userId'],'reservationId'=>$this->_id));
    elseif ($this->_isChange()) BNotificationTemplate::generate(array('type'=>'R_CHANGE','providerId'=>$params['providerId'],'userId'=>$params['userId'],'reservationId'=>$this->_id));
    BNotificationTemplate::generate(array('type'=>'R_BEFORE_START','providerId'=>$params['providerId'],'userId'=>$params['userId'],'reservationId'=>$this->_id));
    if (!isset($params['pay'])||($params['pay']!='Y')) BNotificationTemplate::generate(array('type'=>'R_BEFORE_START_NOTPAYED','providerId'=>$params['providerId'],'userId'=>$params['userId'],'reservationId'=>$this->_id));
    BNotificationTemplate::generate(array('type'=>'R_AFTER_START','providerId'=>$params['providerId'],'userId'=>$params['userId'],'reservationId'=>$this->_id));
    BNotificationTemplate::generate(array('type'=>'R_BEFORE_END','providerId'=>$params['providerId'],'userId'=>$params['userId'],'reservationId'=>$this->_id));
    BNotificationTemplate::generate(array('type'=>'R_AFTER_END','providerId'=>$params['providerId'],'userId'=>$params['userId'],'reservationId'=>$this->_id));
  }

  private function _isChange() {
    $ret = false;

    if ($this->_oldData&&$this->_data) {
      foreach (array('start','end','price','resourceId') as $attr) {
        if (isset($this->_oldData[$attr])&&isset($this->_data[$attr])&&($this->_oldData[$attr]!=$this->_data[$attr])) {
          $ret = true;
          break;
        }
      }
    }

    return $ret;
  }
  
  private function _delete() {
    $this->_app->db->beginTransaction();
    
    if (!$this->_data['cancelled']) {
      if (isset($this->_data['eventId'])) {
        foreach ($this->_data['eventAttendeeId'] as $id) {
          $o = new OEventAttendee($id);
          $o->delete();
        }
      } elseif (isset($this->_data['resourceId'])) {
        $b = new BResource($this->_data['resourceId']);
        $b->freeAvailabilityTable($this->_data['resourceFrom'], $this->_data['resourceTo']);
      }
    }
    
    BNotificationTemplate::generate(array('type'=>'R_DELETE','providerId'=>$this->_data['providerId'],'userId'=>$this->_data['userId'],'reservationId'=>$this->_id));
    
    $o = new OReservation($this->_id);
    $o->delete();
    
    $this->_app->db->commitTransaction();
  }
  
  public function delete() {
    if (!$this->_checkDeleteAccess()) throw new ExceptionUserTextStorage('error.accessDenied');

    $this->_load();

    $this->_checkBeforeDelete();
    
    $ret = $this->_data['number'];
  
    $this->_delete();
    
    return $ret;
  }
  
  public function copy() {
    if (!$this->_checkAccess()) throw new ExceptionUserTextStorage('error.accessDenied');
    
    $newReservation = new BReservation;
    $this->_data['number'] = null;
    $ret = $newReservation->save($this->_data);
    
    return $ret;
  }
  
  public function fail() {
    if (!$this->_checkAccess()) throw new ExceptionUserTextStorage('error.accessDenied');

    $this->_checkBeforeFail();
    
    $number = $this->_data['number'];
    
    $this->_app->db->beginTransaction();
  
    $o = new OReservation($this->_id);
    $o->setData(array('failed'=>date('Y-m-d H:i:s')));
    $o->save();
    
    $this->createJournalRecord('FAIL');
    
    // smazu neodeslane notifikace
    $s = new SNotification;
    $s->addStatement(new SqlStatementBi($s->columns['reservation'], $this->_id, '%s=%s'));
    $s->addStatement(new SqlStatementMono($s->columns['sent'], '%s IS NULL'));
    $s->setColumnsMask(array('notification_id'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $o = new ONotification($row['notification_id']);
      $o->delete();
    }
    
    BNotificationTemplate::generate(array('type'=>'R_FAIL','providerId'=>$this->_data['providerId'],'userId'=>$this->_data['userId'],'reservationId'=>$this->_id));
    
    $this->_app->db->commitTransaction();
    
    return $number;
  }

  private function _reverseOpenPayment() {
    // zatim ma smysl reverse open payment pouze pro deminimis
    $o = new OOnlinePayment($this->_data['openOnlinepayment']);
    $oData = $o->getData();
    if (strcmp($oData['type'],'deminimis')||$oData['end_timestamp']) return;
    $payId = $oData['paymentid'];

    global $PAYMENT_GATEWAY;
    $gw = new Deminimis(array(
      'language'  => strtoupper($this->_app->language->getLanguage()),
      'logFile'   => $PAYMENT_GATEWAY['source']['deminimis']['logFile'],
      'apiUrl'    => $PAYMENT_GATEWAY['source']['deminimis']['apiUrl'],
      'apiKey'    => $PAYMENT_GATEWAY['source']['deminimis']['apiKey'],
    ));

    $gw->reversePayment($payId);

    $bOnlinePayment = new BOnlinePayment;
    $bOnlinePayment->saveRefund('RESERVATION', sprintf('|%s|',$this->_id), $payId);
  }

  private function _cancel($note=null, $reverseOpenPayment=false) {
    $this->_app->db->beginTransaction();

    if ($reverseOpenPayment) $this->_reverseOpenPayment();

    $o = new OReservation($this->_id);
    $o->setData(array('cancelled'=>date('Y-m-d H:i:s')));
    $o->save();

    $this->_checkAfterCancel();

    if (isset($this->_data['eventId'])) {
      foreach ($this->_data['eventAttendeeId'] as $id) {
        $o = new OEventAttendee($id);
        $o->delete();
      }
    } elseif (isset($this->_data['resourceId'])) {
      $b = new BResource($this->_data['resourceId']);
      $b->freeAvailabilityTable($this->_data['resourceFrom'], $this->_data['resourceTo']);
    }

    $this->createJournalRecord('CANCEL', $note);

    // smazu neodeslane notifikace
    $s = new SNotification;
    $s->addStatement(new SqlStatementBi($s->columns['reservation'], $this->_id, '%s=%s'));
    $s->addStatement(new SqlStatementMono($s->columns['sent'], '%s IS NULL'));
    $s->setColumnsMask(array('notification_id'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $o = new ONotification($row['notification_id']);
      $o->delete();
    }

    // kdyz se rusi rezevace a je pozadano o DEMINIMIS platbu, tak se zrusi zadost v DEMINIMIS registru
    #foreach ($this->_data['journal'] as $item) {
    #  if (!strcmp($item['action'],'PAY')&&(strpos($item['note'],'deminimis')===0)) {
    #    $refundTo = 'deminimis';
    #    break;
    #  }
    #}

    $this->_app->db->commitTransaction();
  }
  
  public function cancel($skipConditions=false, $note=null, $reverseOpenPayment=false) {
    if (!$this->_checkAccess()) throw new ExceptionUserTextStorage('error.accessDenied');

    $this->_checkBeforeCancel($skipConditions);
    
    $this->_cancel($note, $reverseOpenPayment);

    BNotificationTemplate::generate(array('type'=>'R_CANCEL','providerId'=>$this->_data['providerId'],'userId'=>$this->_data['userId'],'reservationId'=>$this->_id));
    
    return $this->_data['number'];
  }
  
  public function cancelWithRefund($skipConditions=false, $refundTo='credit', $note=null) {
    if (!$this->_checkAccess()) throw new ExceptionUserTextStorage('error.accessDenied');
    $this->_checkPaymentAccess('refund');

    $this->_checkBeforeCancel($skipConditions);
    
    $this->_app->db->beginTransaction();
    
    $this->_cancel($note);
    
    if ($this->_data['payed']) {
      if (!strcmp($refundTo,'credit')) {
        // kdyz se maji penize vratit na kredit/permanentku
        if ($this->_data['userId']) {
          $b = new BUser($this->_data['userId']);
          if ($this->_data['payedTicket']) $b->changeTicket($this->_data['providerId'], $this->_data['payedTicket'], $this->_data['price'], 'RESERVATION', $this->_data['number']);
          else $b->changeCredit($this->_data['providerId'], $this->_data['price'], 'RESERVATION', $this->_data['number']);
        } else {
          $b = new BUser;
          $b->changeCredit($this->_data['providerId'], $this->_data['price'], 'RESERVATION', $this->_data['number']);
        }
      } else {
        global $PAYMENT_GATEWAY;
        if (in_array($refundTo, array_keys($PAYMENT_GATEWAY['source']))) {
          // kdyz se maji penize vracet pres GW
          $s = new SProviderPaymentGateway;
          $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_data['providerId'], '%s=%s'));
          $s->addStatement(new SqlStatementBi($s->columns['gateway_name'], $refundTo, '%s=%s'));
          $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
          $s->setColumnsMask(array('gateway_params'));
          $res = $this->_app->db->doQuery($s->toString());
          if (!$rowGw = $this->_app->db->fetchAssoc($res)) throw new ExceptionUser('Invalid payment gateway!');
          $gatewayParams = json_decode($rowGw['gateway_params']);
        
          switch ($refundTo) {
            case 'csob':
              $payId = null;
              foreach ($this->_data['journal'] as $journalItem) {
                if (!strcmp($journalItem['action'],'PAY')) {
                  $notePart = explode('|', $journalItem['note']);
                  if (strcmp($notePart[0],'csob')) throw new ExceptionUserTextStorage('error.refundReservation_csob_invalidPayment');

                  $payId = substr($notePart[1],strpos($notePart[1],':')+1,15); // payID pro csob ma delku 14znaku
                  if (!$payId) throw new ExceptionUserTextStorage('error.refundReservation_csob_invalidPayment');

                  break;
                }
              }
              
              $gw = new CSOBGateway(array(
                'language'        => strtoupper($this->_app->language->getLanguage()),
                'logFile'         => $PAYMENT_GATEWAY['source']['csob']['logFile'],
                'cartLabel'       => $this->_app->textStorage->getText('label.ajax_paymentGateway_cartLabel'),
                'gatewayUrl'      => $PAYMENT_GATEWAY['source']['csob']['url'],
                'gatewayKey'      => $PAYMENT_GATEWAY['source']['csob']['key'],
                'merchantId'      => $gatewayParams->merchantId,
                'merchantKey'     => $gatewayParams->keyFile,
              ));
              
              $ret = $gw->getPaymentStatus($payId);
              if ($ret['paymentStatus']==7) $gw->reversePayment($payId);
              else $gw->refundPayment($payId);

              break;
            case 'comgate':
              $payId = null;
              foreach ($this->_data['journal'] as $journalItem) {
                if (!strcmp($journalItem['action'],'PAY')) {
                  $notePart = explode('|', $journalItem['note']);
                  if (strcmp($notePart[0],'comgate')) throw new ExceptionUserTextStorage('error.refundReservation_comgate_invalidPayment');

                  $payId = substr($notePart[1],strpos($notePart[1],':')+1,14); // payID pro comgate ma delku 14znaku
                  if (!$payId) throw new ExceptionUserTextStorage('error.refundReservation_comgate_invalidPayment');

                  break;
                }
              }

              $gw = new COMGATEGateway(array(
                'language'        => strtoupper($this->_app->language->getLanguage()),
                'logFile'         => $PAYMENT_GATEWAY['source']['comgate']['logFile'],
                'gatewayUrl'      => $PAYMENT_GATEWAY['source']['comgate']['url'],
                'merchantId'      => $gatewayParams->merchantId,
                'secret'          => $gatewayParams->secret,
                'test'            => $gatewayParams->test,
              ));

              $gw->refundPayment($payId, $this->_data['price']);

              break;
            case 'gpwebpay':
              $payId = null;
              foreach ($this->_data['journal'] as $journalItem) {
                if (!strcmp($journalItem['action'],'PAY')) {
                  $notePart = explode('|', $journalItem['note']);
                  if (strcmp($notePart[0],'gpwebpay')) throw new ExceptionUserTextStorage('error.refundReservation_gpwebpay_invalidPayment');

                  $noteEl = explode(',',$notePart[1]);
                  $payId = substr($noteEl[0],strpos($notePart[1],':')+1);
                  if (!$payId) throw new ExceptionUserTextStorage('error.refundReservation_csob_invalidPayment');

                  break;
                }
              }

              $gw = new GPWebpayGateway(array(
                'language'            => strtoupper($this->_app->language->getLanguage()),
                'logFile'             => $PAYMENT_GATEWAY['source']['gpwebpay']['logFile'],
                'gatewayUrlWS'        => $PAYMENT_GATEWAY['source']['gpwebpay']['urlWS'],
                'gatewayUrl'          => $PAYMENT_GATEWAY['source']['gpwebpay']['url'],
                'gatewayKey'          => $PAYMENT_GATEWAY['source']['gpwebpay']['key'],
                'merchantId'          => $gatewayParams->merchantId,
                'merchantKey'         => $gatewayParams->keyFile,
                'merchantKeyPassword' => $gatewayParams->keyPassword,
              ));

              $ret = $gw->getPaymentStatus($payId);
              if (in_array($ret['paymentStatus'],array(4))) $gw->reversePaymentAuthorization($payId);
              elseif (in_array($ret['paymentStatus'],array(7))) {
                $gw->reversePaymentCapture($payId);
                $gw->reversePaymentAuthorization($payId);
              } else $gw->refundPayment($payId, $this->_data['price']);

              break;
            case 'deminimis':
              $payId = null;
              foreach ($this->_data['journal'] as $journalItem) {
                if (!strcmp($journalItem['action'],'PAY')) {
                  $notePart = explode('|', $journalItem['note']);
                  if (strcmp($notePart[0],'deminimis')) throw new ExceptionUserTextStorage('error.refundReservation_gpwebpay_invalidPayment');

                  $noteEl = explode(',',$notePart[1]);
                  $payId = substr($noteEl[0],strpos($notePart[1],':')+1);
                  if (!$payId) throw new ExceptionUserTextStorage('error.refundReservation_csob_invalidPayment');

                  break;
                }
              }

              $gw = new Deminimis(array(
                'language'  => strtoupper($this->_app->language->getLanguage()),
                'logFile'   => $PAYMENT_GATEWAY['source']['deminimis']['logFile'],
                'apiUrl'    => $PAYMENT_GATEWAY['source']['deminimis']['apiUrl'],
                'apiKey'    => $PAYMENT_GATEWAY['source']['deminimis']['apiKey'],
              ));

              $gw->reversePayment($payId);

              break;
          }

          $bOnlinePayment = new BOnlinePayment;
          $bOnlinePayment->saveRefund('RESERVATION', sprintf('|%s|',$this->_id), $payId);
        } else throw new ExceptionUserTextStorage('error.refundReservation_uknownMethod');
      }
      
      $o = new OReservation($this->_id);
      $o->setData(array('payed'=>null));
      $o->save();
      
      $this->createJournalRecord('REFUND', $refundTo, $this->_data['price']);

      if ($this->_data['invoiceNumber']) $creditNoteId = $this->generateCreditnote('INVOICE',$this->_data['price']);
      else $creditNoteId = $this->generateCreditnote('RECEIPT',$this->_data['price']);
    }
    
    $this->_app->db->commitTransaction();

    BNotificationTemplate::generate(array('type'=>'R_CANCEL','providerId'=>$this->_data['providerId'],'userId'=>$this->_data['userId'],
      'reservationId'=>$this->_id,'creditNoteId'=>ifsetor($creditNoteId)));
    
    return $this->_data['number'];
  }
  
  public function cancelEventPackItem($eventPackItem) {
    if (!$this->_checkAccess()) throw new ExceptionUserTextStorage('error.accessDenied');

    $this->_checkBeforeCancel();
    
    $s = new SEventAttendee;
    $s->addStatement(new SqlStatementBi($s->columns['reservation'], $this->_id, '%s=%s'));
    $s->addStatement(new SqlStatementBi($s->columns['event'], $eventPackItem, '%s=%s'));
    $s->setColumnsMask(array('eventattendee_id','start','failed'));
    $res = $this->_app->db->doQuery($s->toString());
    if (!($row = $this->_app->db->fetchAssoc($res))||$row['failed']) throw new ExceptionUserTextStorage('error.saveReservation_cancelEventItem_invalidItem');
    
    // kdyz je to posledni akce na rezervaci nebo rezervace je na single akci, zrusim celou rezervaci
    if (($this->_data['eventPack']=='N')||(count($this->_data['eventPackId'])==1)) {
      if ($this->_data['payed']) $this->cancelWithRefund();
      else $this->cancel();
      
      return;
    }
    
    $refund = round($this->_data['price']/count($this->_data['eventPackId']));
    $price = $this->_data['price']-$refund;

    $this->_data['price'] = $price;
    if ($this->_data['priceComment']) $this->_data['priceComment'] .= "\n";
    $this->_data['priceComment'] .= $this->_app->textStorage->getText('label.editReservation_noteOnCancelEventPackItem');

    $this->_app->db->beginTransaction();

    $o = new OReservation($this->_id);
    $o->setData(array('total_price'=>$price,'price_timestamp'=>date('Y-m-d H:i:s'),'price_user'=>$this->_app->auth->getUserId(),
                      'price_comment'=>$this->_data['priceComment']));
    $o->save();
    
    $o = new OEventAttendee($row['eventattendee_id']);
    $o->delete();
    
    $note = sprintf('EVENT:%s (%s)', $eventPackItem, $row['start']);
    $this->createJournalRecord('PARTIAL_CANCEL', $note);

    BNotificationTemplate::generate(array('type'=>'R_CANCEL_PACK_ITEM','providerId'=>$this->_data['providerId'],'userId'=>$this->_data['userId'],'reservationId'=>$this->_id,'eventPackItem'=>$eventPackItem));
    
    if ($this->_data['payed']) {
      // penize za zruseni jedne akce z cyklu pujdou vzdy na kredit
      if ($this->_data['userId']) {
        $b = new BUser($this->_data['userId']);
        $b->changeCredit($this->_data['providerId'], $refund, 'RESERVATION', $this->_data['number']);
      } else {
        $b = new BUser;
        $b->changeCredit($this->_data['providerId'], $refund, 'RESERVATION', $this->_data['number']);
      }
      
      $this->createJournalRecord('REFUND', 'credit', $refund);

      if ($this->_data['invoiceNumber']) $this->generateCreditnote('INVOICE',$refund);
      else $this->generateCreditnote('RECEIPT',$refund);
    }
    
    $this->_app->db->commitTransaction();
  }

  public function failEventPackItem($eventPackItem) {
    // kazda akce cyklu muze mit jineho organizatora, musim tedy kontrolovat pristup k akci, kterou chci propadnout
    if (!$this->_checkAccess(array('eventParams'=>array('eventId'=>$eventPackItem)))) throw new ExceptionUserTextStorage('error.accessDenied');

    $this->_checkBeforeFail();

    $s = new SEventAttendee;
    $s->addStatement(new SqlStatementBi($s->columns['reservation'], $this->_id, '%s=%s'));
    $s->addStatement(new SqlStatementBi($s->columns['event'], $eventPackItem, '%s=%s'));
    $s->setColumnsMask(array('eventattendee_id','start','failed'));
    $res = $this->_app->db->doQuery($s->toString());
    if (!($row = $this->_app->db->fetchAssoc($res))||$row['failed']) throw new ExceptionUserTextStorage('error.saveReservation_failEventItem_invalidItem');

    $this->_app->db->beginTransaction();

    if ($this->_data['eventPack']=='Y') {
      $o = new OEventAttendee($row['eventattendee_id']);
      $o->setData(array('failed'=>date('Y-m-d H:i:s')));
      $o->save();

      $note = sprintf('EVENT:%s (%s)', $eventPackItem, $row['start']);
      $this->createJournalRecord('PARTIAL_FAIL', $note);

      // kdyz je rezervace vsech ostatnich akci v cyklu propadnuta, propadnu celou rezervaci
      $globalFail = true;
      foreach ($this->_data['eventPackId'] as $id) {
        if ($id == $eventPackItem) continue;

        if (!$this->_data['eventPackFailed'][$id]) {
          $globalFail = false;
          break;
        }
      }
      if ($globalFail) $this->fail();
      else {
        BNotificationTemplate::generate(array('type'=>'R_FAIL_PACK_ITEM','providerId'=>$this->_data['providerId'],'userId'=>$this->_data['userId'],'reservationId'=>$this->_id,'eventPackItem'=>$eventPackItem));
      }
    } else {
      // kdyz neni rezervace na cyklus budu rusit celou rezervaci
      $this->fail();
    }

    $this->_app->db->commitTransaction();
  }
  
  private function _pay($params, $robotAction) {
    $this->_load();

    if (!$robotAction) {
      if ($this->_data['payed']) throw new ExceptionUserTextStorage('error.payReservation_alreadyPayed');
      if ($this->_data['cancelled']) throw new ExceptionUserTextStorage('error.payReservation_alreadyCancelled');
    }
    
    $this->_app->db->beginTransaction();

    $receiptStruct = BCustomer::generateAccountingDocumentNumber($this->_data['providerId'], 'receipt');
    $oRData = array(
      'payed'           => date('Y-m-d H:i:s'),
      'receipt_number'  => $receiptStruct['number'],
    );
    
    if (!strcmp($params['type'],'credit')) {
      if ($this->_data['price']>0) {
        if (isset($params['arrangeCredit'])&&($params['arrangeCredit']=='Y')&&$this->_data['userId']) {
          $s = new SUserRegistration;
          $s->addStatement(new SqlStatementBi($s->columns['user'], $this->_data['userId'], '%s=%s'));
          $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_data['providerId'], '%s=%s'));
          $s->setColumnsMask(array('credit'));
          $res = $this->_app->db->doQuery($s->toString());
          $row = $this->_app->db->fetchAssoc($res);

          $arrangeAmount = $this->_data['price']-$row['credit'];
          if (isset($params['arrangeCreditAmount'])&&$params['arrangeCreditAmount']) {
            if ($this->_data['price']>($params['arrangeCreditAmount']+$row['credit'])) throw new ExceptionUserTextStorage('error.payReservation_invalidArrangeCreditAmount');

            $arrangeAmount = $params['arrangeCreditAmount'];
          }

          if ($arrangeAmount>0) {
            $b = new BUser($this->_data['userId']);
            $b->changeCredit($this->_data['providerId'], $arrangeAmount);
          }
        }
      
        $b = new BUser($this->_data['userId']);
        $b->changeCredit($this->_data['providerId'], -$this->_data['price'], 'RESERVATION', $this->_data['number']);
      }
    } elseif (!strcmp($params['type'],'ticket')) {
      if ($this->_data['userId']&&($this->_data['price']>0)) {
        $b = new BUser($this->_data['userId']);
        $b->changeTicket($this->_data['providerId'], $params['ticket'], -$this->_data['price'], 'RESERVATION', $this->_data['number']);
        
        $oRData['payed_ticket'] = $params['ticket'];
      }
    }
    
    $oR = new OReservation($this->_id);
    $oR->setData($oRData);
    $oR->save();

    $journalComment = sprintf('%s%s', $params['type'], isset($params['comment'])&&$params['comment']?'|'.$params['comment']:'');
    $this->createJournalRecord('PAY', $journalComment);

    if ($receiptStruct['counter']) {
      $receiptGui = new GuiReservationReceipt(array('reservation'=>$this->_id));
      $file = new BFile;
      $receiptFile = $file->saveFromString(array('content'=>$receiptGui->render()));
      $oR->setData(array('receipt'=>$receiptFile));
      $oR->save();

      $o = new OProvider($this->_data['providerId']);
      $o->setData(array('document_year' => $receiptStruct['year'], 'receipt_counter' => $receiptStruct['counter']));
      $o->save();
    }

    // smazu neodeslane notifikace na nezaplacenou rezervaci
    $s = new SNotification;
    $s->addStatement(new SqlStatementBi($s->columns['reservation'], $this->_id, '%s=%s'));
    $s->addStatement(new SqlStatementMono($s->columns['reservation_not_payed'], "%s='Y'"));
    $s->addStatement(new SqlStatementMono($s->columns['sent'], '%s IS NULL'));
    $s->setColumnsMask(array('notification_id'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $o = new ONotification($row['notification_id']);
      $o->delete();
    }
    
    BNotificationTemplate::generate(array('type'=>'R_PAY','providerId'=>$this->_data['providerId'],'userId'=>$this->_data['userId'],'reservationId'=>$this->_id));

    $this->_app->db->commitTransaction();
  }
  
  public function pay($type='credit',$params=array(),$robotAction=false) {
    $this->_checkAccess();

    $params['type'] = $type;
    if (!$robotAction) $this->_checkPaymentAccess('pay', $params);

    $this->_load();
    if (!in_array($type,$this->_data['feAllowedPayment'])) throw new ExceptionUserTextStorage('error.payReservation_notAllowedPaymentType');
    
    $this->_pay($params, $robotAction);
    
    return $this->_data['number'];
  }

  public function payOnline($gateway, $params) {
    if ($this->_exists()) {
      $this->_load();

      // kdyz je rezervace zrusena nebo je zaplacena nez dojde k synchronizaci online platby,
      // tak se penize neparuji k rezervaci, ale pridaji se na kredit
      if (($this->_data['cancelled']||$this->_data['payed'])&&$this->_data['userId']) {
        $bUser = new BUser($this->_data['userId']);
        $bUser->changeCredit($this->_data['providerId'], $this->_data['price'], 'RESERVATION', sprintf('%s (%s)', $this->_data['number'], $params['comment']));
      } else {
        $this->pay($gateway, $params, true);
      }
    }
  }
  
  public function createJournalRecord($action, $comment=null, $comment2=null) {
    if ($this->_exists()) {
      $o = new OReservationJournal;
      $oData = array(
        'reservation'       => $this->_id,
        'change_timestamp'  => date('Y-m-d H:i:s'),
        'change_user'       => $this->_app->auth->getUserId(),
        'change_ip'         => ifsetor($_SERVER['REMOTE_ADDR'],'localhost'),
        'action'            => $action,
        'note'              => $comment,
        'note_2'            => $comment2,
      );
      $o->setData($oData);
      $o->save();
    }
  }

  public function generateInvoice() {
    $this->_load();

    $this->_app->db->beginTransaction();

    $oR = new OReservation($this->_id);

    if (!$this->_data['invoiceNumber']) {
      $invoiceStruct = BCustomer::generateAccountingDocumentNumber($this->_data['providerId'], 'invoice');

      if ($invoiceStruct['counter']) {
        $oRData = array(
          'invoice_number'  => $invoiceStruct['number'],
        );
        $oR->setData($oRData);
        $oR->save();
        $this->_data['invoiceNumber'] = $invoiceStruct['number'];

        $o = new OProvider($this->_data['providerId']);
        $o->setData(array('document_year' => $invoiceStruct['year'], 'invoice_counter' => $invoiceStruct['counter']));
        $o->save();
      }
    }

    if ($this->_data['invoiceNumber']) {
      $invoiceGui = new GuiReservationInvoice(array('reservation'=>$this->_id));
      $file = new BFile;
      $invoiceFile = $file->saveFromString(array('content'=>$invoiceGui->render()));

      $oR->setData(array('invoice'=>$invoiceFile));
      $oR->save();
    }

    $this->_app->db->commitTransaction();

    return $this->_data['invoiceNumber'];
  }

  public function generateCreditnote($type, $amount, $date=null) {
    $this->_load();

    if (!strcmp('RECEIPT',$type)) $documentType = 'receipt';
    elseif (!strcmp('INVOICE',$type)) $documentType = 'creditnote';
    if (!$date) $date = date('Y-m-d H:i:s');

    $this->_app->db->beginTransaction();

    $documentStruct = BCustomer::generateAccountingDocumentNumber($this->_data['providerId'], $documentType);

    if ($documentStruct['counter']) {
      $o = new OProvider($this->_data['providerId']);
      $o->setData(array('document_year' => $documentStruct['year'], $documentType.'_counter' => $documentStruct['counter']));
      $o->save();

      $documentObjectName = sprintf('GuiReservation%s', ucfirst($documentType));
      $documentGui = new $documentObjectName(array('reservation'=>$this->_id,'number'=>$documentStruct['number'],'refund'=>true,'refundAmount'=>$amount,'actionDate'=>$date));
      $file = new BFile;
      $invoiceFile = $file->saveFromString(array('content'=>$documentGui->render()));

      $o = new OCreditnote;
      $o->setData(array(
        'type'        => $type,
        'reservation' => $this->_id,
        'number'      => $documentStruct['number'],
        'content'     => $invoiceFile,
      ));
      $o->save();

      $ret = $o->getId();
    } else $ret = null;

    $this->_app->db->commitTransaction();

    return $ret;
  }

  public function saveOpenOnlinePayment($paymentId=null) {
    $o = new OReservation($this->_id);
    $o->setData(array('open_onlinepayment'=>$paymentId));
    $o->save();
  }
}

?>
