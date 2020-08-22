<?php

class BNotificationTemplate extends BusinessObject {

  private function _checkAccess($params=array()) {
    return true;
    $this->_load();
    
    $provider = ifsetor($params['providerId'],$this->_data['providerId']);
    
    $ret = $this->_app->auth->haveRight('commodity_admin', $provider);
    
    return $ret;
  }
  
  private function _checkBeforeSave($params) {
    if (!$this->_id) {
			if (!isset($params['providerId'])) throw new ExceptionUserTextStorage('error.saveNotificationTemplate_emptyProvider');
      if (!isset($params['name'])) throw new ExceptionUserTextStorage('error.saveNotificationTemplate_emptyName');
			if (!isset($params['target'])) throw new ExceptionUserTextStorage('error.saveNotificationTemplate_emptyTarget');
    }
    
    // tyto nesmi byt prazdny nikdy
		if (isset($params['providerId'])&&!$params['providerId']) throw new ExceptionUserTextStorage('error.saveNotificationTemplate_emptyProvider');
    if (isset($params['name'])&&!$params['name']) throw new ExceptionUserTextStorage('error.saveNotificationTemplate_emptyName');
    if (isset($params['target'])&&!$params['target']) throw new ExceptionUserTextStorage('error.saveNotificationTemplate_emptyTarget');

    // pokud je target COMMODITY, musi byt notifikace pouze pro rezervace
		if (!strcmp($params['target'],'COMMODITY')&&isset($params['item'])) {
			foreach ($params['item'] as $item) {
				if (in_array($item['type'], array('U_CREATE','U_CREATE_VALIDATION','U_PASSWORD','U_CHARGE_CREDIT','U_RESERVATION_COND'))) throw new ExceptionUserTextStorage('error.saveNotificationTemplate_targetItemConflict');
			}
		}
  }
  
  private function _checkBeforeDelete() {
    $s = new SResource;
    $s->addStatement(new SqlStatementBi($s->columns['notificationtemplate'], $this->_id, '%s=%s'));
    $s->setColumnsMask(array('resource_id','name'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($row = $this->_app->db->fetchAssoc($res)) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.deleteNotificationTemplate_resourceExists'), $row['name']));
    
    $s = new SEvent;
    $s->addStatement(new SqlStatementBi($s->columns['notificationtemplate'], $this->_id, '%s=%s'));
    $s->setColumnsMask(array('event_id','name'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($row = $this->_app->db->fetchAssoc($res)) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.deleteNotificationTemplate_eventExists'), $row['name']));

		$s = new SProvider;
		$s->addStatement(new SqlStatementBi($s->columns['notificationtemplate'], $this->_id, '%s=%s'));
		$s->setColumnsMask(array('provider_id','name'));
		$res = $this->_app->db->doQuery($s->toString());
		if ($row = $this->_app->db->fetchAssoc($res)) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.deleteNotificationTemplate_providerExists'), $row['name']));
  }

  protected function _load() {
    if ($this->_id&&!$this->_loaded) {
      $oNotificationTemplate = new ONotificationTemplate($this->_id);
      $data = $oNotificationTemplate->getData();
      $returnData['id'] = $data['notificationtemplate_id'];
      $returnData['name'] = $data['name'];
			$returnData['target'] = $data['target'];
      $returnData['description'] = $data['description'];
      
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
      
      $returnData['item'] = array();
      $s = new SNotificationTemplateItem;
      $s->addStatement(new SqlStatementBi($s->columns['notificationtemplate'], $this->_id, '%s=%s'));
      $s->addOrder(new SqlStatementAsc($s->columns['name']));
      $s->setColumnsMask(array('notificationtemplateitem_id','name','type','offset',
                               'to_provider','to_organiser','to_user','to_attendee','to_substitute',
                               'from_address','cc_address','bcc_address',
                               'content_type','subject','body'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {  
        $item = array(
          'itemId'=>$row['notificationtemplateitem_id'],
          'name'=>$row['name'],'type'=>$row['type'],'offset'=>$row['offset'],
          'toProvider'=>$row['to_provider'],'toOrganiser'=>$row['to_organiser'],'toUser'=>$row['to_user'],'toAttendee'=>$row['to_attendee'],'toSubstitute'=>$row['to_substitute'],
          'fromAddress'=>$row['from_address'],'ccAddress'=>$row['cc_address'],'bccAddress'=>$row['bcc_address'],
          'contentType'=>$row['content_type'],'subject'=>$row['subject'],'body'=>$row['body'],
        );
        
        $returnData['item'][] = $item;
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
  
  private function _saveItem($params) {
    if (isset($params['item'])) {
      $ids = array();
      
      foreach ($params['item'] as $item) {
        $oData = array(
          'notificationtemplate'=>$this->_id,
          'name'=>$item['name'],'type'=>$item['type'],'offset'=>$item['offset']?$item['offset']:null,
          'to_provider'=>$item['toProvider'],'to_organiser'=>$item['toOrganiser'],'to_user'=>$item['toUser'],'to_attendee'=>$item['toAttendee'],'to_substitute'=>$item['toSubstitute'],
          'from_address'=>isset($item['fromAddress'])&&$item['fromAddress']?$item['fromAddress']:$params['providerEmail'],
          'cc_address'=>$item['ccAddress'],'bcc_address'=>$item['bccAddress'],
          'content_type'=>$item['contentType'],'subject'=>$item['subject'],'body'=>$item['body']
        );
        
        $o = new ONotificationTemplateItem(ifsetor($item['itemId']));
        $o->setData($oData);
        $o->save();
        
        $ids[] = $o->getId();
      }
      
      $ids = implode(',',$ids);
      $s = new SNotificationTemplateItem;
      $s->addStatement(new SqlStatementBi($s->columns['notificationtemplate'], $this->_id, '%s=%s'));
      if ($ids) $s->addStatement(new SqlStatementMono($s->columns['notificationtemplateitem_id'], sprintf('%%s NOT IN (%s)', $ids)));
      $s->setColumnsMask(array('notificationtemplateitem_id'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $o = new ONotificationTemplateItem($row['notificationtemplateitem_id']);
        $o->delete();
      }
    }
  }
  
  private function _save($params) {
    $this->_load();
    
    if (isset($params['providerId'])) {
      $s = new SProvider;
      $s->addStatement(new SqlStatementBi($s->columns['provider_id'], $params['providerId'], '%s=%s'));
      $s->setColumnsMask(array('email'));
      $res = $this->_app->db->doQuery($s->toString());
      $row = $this->_app->db->fetchAssoc($res);
      
      $params['providerEmail'] = $row['email'];
    }
    
    $this->_app->db->beginTransaction();

    $o = new ONotificationTemplate($this->_id?$this->_id:null);
    $oData = array();
    if (isset($params['providerId'])) $oData['provider'] = $params['providerId'];
    if (isset($params['name'])) $oData['name'] = $params['name'];
		if (isset($params['target'])) $oData['target'] = $params['target'];
    if (isset($params['description'])) $oData['description'] = $params['description'];
    
    if (count($oData)) {
      $o->setData($oData);
      $o->save();
    }
    $this->_id = $o->getId();
    
    $this->_saveItem($params);
    
    $this->_app->db->commitTransaction();
  }

  public function save($params) {
    if (!$this->_checkAccess($params)) throw new ExceptionUserTextStorage('error.accessDenied');
    
    $this->_checkBeforeSave($params);
    
    $this->_save($params);
  }
  
  private function _delete() {
    $this->_app->db->beginTransaction();
    
    $o = new ONotificationTemplate($this->_id);
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
    
    $newNotificationTemplate = new BNotificationTemplate;
    $this->_data['name'] .= ' (kopie)';
    foreach ($this->_data['item'] as $index=>$item) {
      $this->_data['item'][$index]['itemId'] = null;
    }
    $newNotificationTemplate->save($this->_data);
    
    return $ret;
  }
  
  public static function generate($params) {
    $bNot = new BNotificationTemplate;
    $bNot->generateNotification($params);  
  }
  
  private function _getNotificationDynamicData(& $params) {
    if (!isset($params['data'])) $params['data'] = array();
    
    // data o poskytovateli
    if (isset($params['providerId'])&&$params['providerId']) {
      $s = new SProvider;
      $s->addStatement(new SqlStatementBi($s->columns['provider_id'], $params['providerId'], '%s=%s'));
      $s->setColumnsMask(array('email'));
      $res = $this->_app->db->doQuery($s->toString());
      $row = $this->_app->db->fetchAssoc($res);
      foreach ($row as $key=>$value) { $params['data']['provider_'.$key] = $value; }
    }
    
    if (isset($params['userId'])&&$params['userId']) {
      // data o uzivateli
      $s = new SUser;
      $s->addStatement(new SqlStatementBi($s->columns['user_id'], $params['userId'], '%s=%s'));
      $s->setColumnsMask(array('email'));
      $res = $this->_app->db->doQuery($s->toString());
      $row = $this->_app->db->fetchAssoc($res);
      foreach ($row as $key=>$value) { $params['data']['user_'.$key] = $value; }
    }
    
    // data o rezervaci
    if (isset($params['reservationId'])&&$params['reservationId']) {
      $s = new SReservation;
      $s->addStatement(new SqlStatementBi($s->columns['reservation_id'], $params['reservationId'], '%s=%s'));
      $s->setColumnsMask(array('start','end','resource','event'));
      $res = $this->_app->db->doQuery($s->toString());
      $row = $this->_app->db->fetchAssoc($res);
      
      $row['startraw'] = $row['start']; unset($row['start']);
      $row['endraw'] = $row['end']; unset($row['end']);
      
      if ($row['resource']) {
        $row['organiser_email'] = '';
      } else {
        if (isset($params['eventPackItem'])) $row['event'] = $params['eventPackItem'];

        $s1 = new SEvent;
        $s1->addStatement(new SqlStatementBi($s1->columns['event_id'], $row['event'], '%s=%s'));
        $s1->setColumnsMask(array('organiser_email'));
        $res1 = $this->_app->db->doQuery($s1->toString());
        $row1 = $this->_app->db->fetchAssoc($res1);
        $row['organiser_email'] = $row1['organiser_email'];

        $substituteEmail = '';
        $s1 = new SEventAttendee;
        $s1->addStatement(new SqlStatementBi($s1->columns['event'], $row['event'], '%s=%s'));
        $s1->addStatement(new SqlStatementMono($s1->columns['substitute'], "%s='Y'"));
        $s1->setColumnsMask(array('email'));
        $res1 = $this->_app->db->doQuery($s1->toString());
        while ($row1 = $this->_app->db->fetchAssoc($res1)) {
          if ($substituteEmail) $substituteEmail .= ',';
          $substituteEmail .= $row1['email'];
        }
        $row['substitute_email'] = $substituteEmail;

        $attendeeEmail = '';
        $s1 = new SEventAttendee;
        $s1->addStatement(new SqlStatementBi($s1->columns['reservation'], $params['reservationId'], '%s=%s'));
        $s1->addStatement(new SqlStatementBi($s1->columns['event'], $row['event'], '%s=%s'));
        $s1->addStatement(new SqlStatementMono($s1->columns['substitute'], "%s='N'"));
        $s1->setColumnsMask(array('person_user','person_user_email','person_email'));
        $res1 = $this->_app->db->doQuery($s1->toString());
        while ($row1 = $this->_app->db->fetchAssoc($res1)) {
          $mail = $row1['person_user']?$row1['person_user_email']:$row1['person_email'];
          if ($mail) {
            if ($attendeeEmail) $attendeeEmail .= ',';
            $attendeeEmail .= $mail;
          }
        }
        $row['attendee_email'] = $attendeeEmail;
      }
      unset($row['resource']);unset($row['event']);
      
      foreach ($row as $key=>$value) { $params['data'][$key] = $value; }
    }
    
    // upravim index vsech dyn. dat
    foreach ($params['data'] as $key=>$value) {
      $params['data']['@@'.strtoupper($key)] = $value;
      unset($params['data'][$key]);
    }
  }

  private function _getNotificationsFromTemplateItem(& $notification, $select, $notificationType) {
		$select->addStatement(new SqlStatementBi($select->columns['notificationtemplateitem_type'], $notificationType, '%s=%s'));
		$select->setColumnsMask(array('notificationtemplateitem_offset',
			'notificationtemplateitem_to_provider','notificationtemplateitem_to_user','notificationtemplateitem_to_organiser','notificationtemplateitem_to_attendee','notificationtemplateitem_to_substitute',
			'notificationtemplateitem_from_address','notificationtemplateitem_cc_address','notificationtemplateitem_bcc_address',
			'notificationtemplateitem_content_type','notificationtemplateitem_subject','notificationtemplateitem_body'));
		$res = $this->_app->db->doQuery($select->toString());
		while ($row = $this->_app->db->fetchAssoc($res)) {
			$receiverTypes = array();
			if ($row['notificationtemplateitem_to_provider']=='Y') $receiverTypes[] = 'PROVIDER';
      if ($row['notificationtemplateitem_to_organiser']=='Y') $receiverTypes[] = 'ORGANISER';
			if ($row['notificationtemplateitem_to_user']=='Y') $receiverTypes[] = 'USER';
      if ($row['notificationtemplateitem_to_attendee']=='Y') $receiverTypes[] = 'ATTENDEE';
			if ($row['notificationtemplateitem_to_substitute']=='Y') $receiverTypes[] = 'SUBSTITUTE';
			if (!count($receiverTypes)&&($row['notificationtemplateitem_cc_address']||$row['notificationtemplateitem_bcc_address'])) $receiverTypes[] = 'OTHER';

			foreach ($receiverTypes as $type) {
				$notification[] = array(
					'type' 					=> $type,
					'offset' 				=> $row['notificationtemplateitem_offset'],
					'from' 					=> $row['notificationtemplateitem_from_address'],
					'cc' 						=> $row['notificationtemplateitem_cc_address'],
					'bcc' 					=> $row['notificationtemplateitem_bcc_address'],
					'contentType' 	=> $row['notificationtemplateitem_content_type'],
					'subject' 			=> $row['notificationtemplateitem_subject'],
					'body' 					=> $row['notificationtemplateitem_body']
				);
			}
		}
	}
  
  public function generateNotification($params) {
    $notification = array();
    
    // nejdriv pridam globalni notifikace poskytovatele
    $s = new SProvider;
    $s->addStatement(new SqlStatementBi($s->columns['provider_id'], $params['providerId'], '%s=%s'));
    $this->_getNotificationsFromTemplateItem($notification, $s, $params['type']);
    
    // pak pridam notifikace pro zdroje/akce rezervace
    if (isset($params['reservationId'])) {
      $s = new SReservation;
      $s->addStatement(new SqlStatementBi($s->columns['reservation_id'], $params['reservationId'], '%s=%s'));
      $s->setColumnsMask(array('event','resource'));
      $res = $this->_app->db->doQuery($s->toString());
      $row = $this->_app->db->fetchAssoc($res);
      if ($row['event']) {
        $s = new SEvent;
        $s->addStatement(new SqlStatementBi($s->columns['event_id'], $row['event'], '%s=%s'));
      } else {
        $s = new SResource;
        $s->addStatement(new SqlStatementBi($s->columns['resource_id'], $row['resource'], '%s=%s'));
      }
      $this->_getNotificationsFromTemplateItem($notification, $s, $params['type']);
    }

    // nekdy se musi poslat notifikace, ikdyz ji poskytovatel nema nastavenou
    if (in_array($params['type'],array('U_PASSWORD','U_CREATE_VALIDATION'))) {
      $userNotification = false;
      foreach ($notification as $not) {
        if (!strcmp($not['type'],'USER')) {
          $userNotification = true;
          break;
        }
      }

      if (!$userNotification) {
				global $NOTIFICATION;

        if (!strcmp($params['type'],'U_PASSWORD')) {
          $notification[] = array('type'=>'USER','from'=>$NOTIFICATION['defaultAddressFrom'],'cc'=>'','bcc'=>'','offset'=>0,
            'contentType'=>'text/plain','subject'=>$NOTIFICATION['passwordTemplate']['subject'],'body'=>$NOTIFICATION['passwordTemplate']['body']);
        }
        if (!strcmp($params['type'],'U_CREATE_VALIDATION')) {
          $notification[] = array('type'=>'USER','from'=>$NOTIFICATION['defaultAddressFrom'],'cc'=>'','bcc'=>'','offset'=>0,
            'contentType'=>'text/plain','subject'=>$NOTIFICATION['registrationTemplate']['subject'],'body'=>$NOTIFICATION['registrationTemplate']['body']);
        }
      }
    }

    $this->_getNotificationDynamicData($params);
    
    foreach ($notification as $index=>$not) {
      // muze byt vynucene neposilani notifikaci urcitemu typu uzivatele
      if (isset($params['skipReceiver'])&&is_array($params['skipReceiver'])&&in_array($not['type'],$params['skipReceiver'])) continue;

			if (!strcmp($not['type'],'OTHER')) {
				// kdyz je notifikace typu OTHER, bude mit prazdne TO (a vyplnene CC nebo BCC)
				$notification[$index]['to'] = '';
			} elseif (!isset($params['data']['@@'.$not['type'].'_EMAIL'])||!$params['data']['@@'.$not['type'].'_EMAIL']) {
				// kdyz neni adresa prijemce, notifikace se ignoruje (to bude asi jenom pro notifikaci organizatorovi akce)
        unset($notification[$index]);
        continue;
      } else {
        // nahradnici a ucastnici se pridaji do BCC, jinak bude prijemce v TO
			  if (in_array($not['type'],array('ATTENDEE','SUBSTITUTE'))) {
          $notification[$index]['to'] = '';
			    if ($notification[$index]['bcc']) $notification[$index]['bcc'] .= ',';
			    $notification[$index]['bcc'] .= $params['data']['@@'.$not['type'].'_EMAIL'];
        } else $notification[$index]['to'] = $params['data']['@@'.$not['type'].'_EMAIL'];
			}

      $notification[$index]['subject'] = $not['subject'];
      $notification[$index]['body'] = $not['body'];
      $notification[$index]['attachment'] = array();
      $notification[$index]['onlyNotPayed'] = 'N';
      switch ($params['type']) {
        case 'R_BEFORE_START': $notification[$index]['start'] = strtotime($params['data']['@@STARTRAW']); $notification[$index]['offset'] = -$notification[$index]['offset']; break;
        case 'R_BEFORE_START_NOTPAYED': $notification[$index]['start'] = strtotime($params['data']['@@STARTRAW']); $notification[$index]['offset'] = -$notification[$index]['offset']; $notification[$index]['onlyNotPayed'] = 'Y'; break;
        case 'R_AFTER_START': $notification[$index]['start'] = strtotime($params['data']['@@STARTRAW']); break;
        case 'R_BEFORE_END': $notification[$index]['start'] = strtotime($params['data']['@@ENDRAW']); $notification[$index]['offset'] = -$notification[$index]['offset']; break;
        case 'R_AFTER_END': $notification[$index]['start'] = strtotime($params['data']['@@ENDRAW']); break;
        default: $notification[$index]['start'] = time(); $notification[$index]['offset'] = 0;
      }
      
      $nParams = array(
        'provider'              => $params['providerId'],
        'type'									=> $params['type'],
        'fromAddress'           => $notification[$index]['from'],
        'bccAddress'            => $notification[$index]['bcc'],
        'ccAddress'             => $notification[$index]['cc'],
        'toAddress'             => $notification[$index]['to'],
        'contentType'           => $notification[$index]['contentType'],
        'attachment'            => $notification[$index]['attachment'],
        'subject'               => $notification[$index]['subject'],
        'body'                  => $notification[$index]['body'],
        'generateParams'        => array('providerId'=>ifsetor($params['providerId']),
          'userId'=>ifsetor($params['userId']),'newPassword'=>ifsetor($params['newPassword']),
          'reservationId'=>ifsetor($params['reservationId']),'eventPackItem'=>ifsetor($params['eventPackItem']),
          'creditNoteId'=>ifsetor($params['creditNoteId']),'prepaymentInvoiceId'=>ifsetor($params['prepaymentInvoiceId']),
					'reservationConditionId'=>ifsetor($params['reservationConditionId'])),
        'toSend'                => date('Y-m-d H:i:s', $notification[$index]['start']+$notification[$index]['offset']*60),
        'reservationNotPayed'   => $notification[$index]['onlyNotPayed'],
      );
      if (isset($params['reservationId'])) $nParams['reservation'] = $params['reservationId'];
      if (isset($params['forceSend'])) $nParams['forceSend'] = $params['forceSend'];
      
      // notifikaci vygeneruju pouze kdyz by se mela odeslat nejpozdeji pred 5min.
      if (strtotime($nParams['toSend'])>(time()-300)) {
        $b = new BNotification;
        $b->create($nParams);
      }
    }
  }
}

?>