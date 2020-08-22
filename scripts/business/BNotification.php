<?php

class BNotification extends BusinessObject {
  private $_passwordReplacement = '<***secured***>';

  protected function _load() {
    if ($this->_id&&!$this->_loaded) {
      $returnData = array();
      
      $s = new SNotification;
      $s->addStatement(new SqlStatementBi($s->columns['notification_id'], $this->_id, '%s=%s'));
      $s->setColumnsMask(array('provider','type','from_address','cc_address','bcc_address','to_address','content_type','subject','body','generate_params','parsed','created','to_send','sent'));
      $res = $this->_app->db->doQuery($s->toString());
      $row = $this->_app->db->fetchAssoc($res);
      
      $returnData['id'] = $this->_id;
      $returnData['provider'] = $row['provider'];
      $returnData['type'] = $row['type'];
      $returnData['fromAddress'] = $row['from_address'];
      $returnData['ccAddress'] = $row['cc_address'];
      $returnData['bccAddress'] = $row['bcc_address'];
      $returnData['toAddress'] = $row['to_address'];
      $returnData['contentType'] = $row['content_type'];
      $returnData['subject'] = $row['subject'];
      $returnData['body'] = $row['body'];
      $returnData['generateParams'] = unserialize($row['generate_params']);
      $returnData['parsed'] = $row['parsed'];
      $returnData['created'] = $row['created'];
      $returnData['toSend'] = $row['to_send'];
      $returnData['sent'] = $row['sent'];

      $returnData['attachment'] = $this->_loadAttachment();

      $this->_data = $returnData;
      
      $this->_loaded = true;
    }
  }

  private function _loadAttachment() {
    $ret = array();

    $s = new SNotificationFile;
    $s->addStatement(new SqlStatementBi($s->columns['notification'], $this->_id, '%s=%s'));
    $s->setColumnsMask(array('file_id','file_hash','file_name','file_mime','file_length'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $ret[] = array(
        'fileId'    => $row['file_id'],
        'hash'      => $row['file_hash'],
        'name'      => $row['file_name'],
        'mime'      => $row['file_mime'],
        'length'    => $row['file_length'],
      );
    }

    return $ret;
  }

  private function _loadSMTPSettings() {
    $this->_load();

    $this->_data['smtp'] = false;

    global $NOTIFICATION;
    if (isset($NOTIFICATION['smtpHost'])&&$NOTIFICATION['smtpHost']) {
      $this->_data['smtp'] = true;
      $this->_data['smtpHost'] = $NOTIFICATION['smtpHost'];

      if (isset($NOTIFICATION['smtpPort'])&&$NOTIFICATION['smtpPort']) {
        $this->_data['smtpPort'] = $NOTIFICATION['smtpPort'];
      }

      if (isset($NOTIFICATION['smtpSecure'])&&$NOTIFICATION['smtpSecure']) {
        $this->_data['smtpSecure'] = $NOTIFICATION['smtpSecure'];
      }

      if (isset($NOTIFICATION['smtpUser'])&&$NOTIFICATION['smtpUser']) {
        $this->_data['smtpUser'] = $NOTIFICATION['smtpUser'];
        $this->_data['smtpPassword'] = $NOTIFICATION['smtpPassword'];
      }
    }

    $s = new SProviderSettings;
    $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_data['provider'], '%s=%s'));
    $s->setColumnsMask(array('smtp_host','smtp_port','smtp_user','smtp_password','smtp_secure'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($row = $this->_app->db->fetchAssoc($res)) {
      if ($row['smtp_host']) {
        $this->_data['smtp'] = true;
        $this->_data['smtpHost'] = $row['smtp_host'];
        $this->_data['smtpPort'] = $row['smtp_port'];
        $this->_data['smtpSecure'] = $row['smtp_secure'];
        $this->_data['smtpUser'] = $row['smtp_user'];
        $this->_data['smtpPassword'] = $row['smtp_password'];
      }
    }
  }

  public function getData() {
    $this->_load();

    return $this->_data;
  }

  private function _saveAttachment($attachment, $preserveExisting=false) {
    if (!$preserveExisting) {
      $s = new SNotificationFile;
      $s->addStatement(new SqlStatementBi($s->columns['notification'], $this->_id, '%s=%s'));
      $s->setColumnsMask(array('notification', 'file'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $o = new ONotificationFile(array('notification'=>$row['notification'],'file'=>$row['file']));
        $o->delete();
      }
    }

    if (is_array($attachment)) {
      foreach ($attachment as $att) {
        $b = new BFile;
        $fileId = $b->save(array('file'=>$att['file'],'name'=>$att['name']));

        $o = new ONotificationFile;
        $o->setData(array('notification'=>$this->_id,'file'=>$fileId));
        $o->save();
      }
    }
  }
  
  private function _save($params) {
    $this->_app->db->beginTransaction();

    $o = new ONotification($this->_id?$this->_id:null);
    $oData = array();
    if (isset($params['provider'])) $oData['provider'] = $params['provider'];
    if (isset($params['type'])) $oData['type'] = $params['type'];
    if (isset($params['fromAddress'])) $oData['from_address'] = $params['fromAddress'];
    if (isset($params['bccAddress'])) $oData['bcc_address'] = $params['bccAddress'];
    if (isset($params['ccAddress'])) $oData['cc_address'] = $params['ccAddress'];
    if (isset($params['toAddress'])) $oData['to_address'] = $params['toAddress'];
    if (isset($params['contentType'])) $oData['content_type'] = $params['contentType'];
    if (isset($params['subject'])) $oData['subject'] = $params['subject'];
    if (isset($params['body'])) $oData['body'] = $params['body'];
    if (isset($params['generateParams'])) $oData['generate_params'] = is_array($params['generateParams'])?serialize($params['generateParams']):$params['generateParams'];
    if (isset($params['toSend'])) $oData['to_send'] = $params['toSend'];
    if (isset($params['reservation'])) $oData['reservation'] = $params['reservation'];
    if (isset($params['reservationNotPayed'])) $oData['reservation_not_payed'] = $params['reservationNotPayed'];
    
    if (!$this->_id) $oData['created'] = date('Y-m-d H:i:s');
    
    if (count($oData)) {
      $o->setData($oData);
      $o->save();
    }
    $this->_id = $o->getId();

    if (isset($params['attachment'])) $this->_saveAttachment($params['attachment']);
    
    $this->_app->db->commitTransaction();
  }

  public function save($params) {
    $this->_load();
    if ($this->_data['sent']) throw new ExceptionUserTextStorage('error.saveNotification_alreadySent');
    
    //list($params['subject'],$params['body']) = $this->_getContent($params['providerId'], $params['type'], $params['data']);
    
    $this->_save($params);
  }
  
  public function create($params) {
    if (isset($params['contentType'])&&!strcmp($params['contentType'],'text/html')) {
      if (isset($params['body'])&&stripos($params['body'],'<html')===false) {
        $params['body'] = sprintf('<!DOCTYPE html><head></head><body>%s</body></html>', $params['body']);
      }
    }

    $this->_save($params);

    // kdyz se vytvari nekolik rezervaci najednou v multikalendari a nejaka se nepovede,
    // tak nechceme odeslat notifikace protoze ty jiz vytvorene se zase smazou
    // ale lze parametrem "forceSend" vynutit odeslani hned
    if (isset($params['forceSend'])&&$params['forceSend']&&
      isset($params['toSend'])&&($params['toSend']<=date('Y-m-d H:i:s'))) {
      $this->send();
    }
  }
  
  private function _delete() {
    $this->_load();
    if ($this->_data['sent']) throw new ExceptionUserTextStorage('error.deleteNotification_alreadySent');
    
    $this->_app->db->beginTransaction();
    
    $o = new ONotification($this->_id);
    $o->delete();
    
    $this->_app->db->commitTransaction();
  }
  
  public function delete() {
    $this->_delete();
  }
  
  public function send() {
    $this->_loadSMTPSettings();

    if ($this->_data['sent']) {
      throw new ExceptionUserTextStorage('error.sendNotification_alreadySent');
    }

    $ret = $this->_send();

    if (!$ret) $this->_secureSentPassword();

    return $ret;
  }

  private function _secureSentPassword() {
    if (isset($this->_data['generateParams']['newPassword'])&&$this->_data['generateParams']['newPassword']) {
      $this->_data['body'] = str_replace($this->_data['generateParams']['newPassword'], $this->_passwordReplacement, $this->_data['body']);
      $this->_data['generateParams']['newPassword'] = $this->_passwordReplacement;

      $o = new ONotification($this->_id);
      $o->setData(array(
        'body'            => $this->_data['body'],
        'generate_params' => serialize($this->_data['generateParams']),
      ));
      $o->save();
    }
  }

  private function _send() {
    $this->_parse();

    $mail = new PHPMailer;
    if ($this->_data['smtp']) {
      $mail->Host       = $this->_data['smtpHost'];
      $mail->Mailer     = 'smtp';
      $mail->SMTPAutoTLS = false;
      if (isset($this->_data['contentType'])) $mail->ContentType = $this->_data['contentType'];

      if (isset($this->_data['smtpPort'])) {
        $mail->Port = $this->_data['smtpPort'];
      }

      if (isset($this->_data['smtpSecure'])) {
        $mail->SMTPSecure = $this->_data['smtpSecure'];
        $mail->SMTPOptions = array(
          'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
          ));
      }
      
      if (isset($this->_data['smtpUser'])&&$this->_data['smtpUser']) {
        $mail->SMTPAuth = true;
        $mail->Username = $this->_data['smtpUser'];
        $mail->Password = $this->_data['smtpPassword'];
      }
    }
    
    #$mail->SMTPDebug  = 2;
    $mail->CharSet    = 'utf-8';
    $mail->From       = $this->_data['fromAddress'];
    $mail->FromName   = '';
    $mail->Sender     = $this->_data['fromAddress'];
    
    $mData = $this->_prepareData($this->_data);

    foreach (explode(',',$mData['toAddress']) as $e) if ($e) $mail->AddAddress($e);
    foreach (explode(',',$mData['ccAddress']) as $e) if ($e) $mail->AddCC($e);
    foreach (explode(',',$mData['bccAddress']) as $e) if ($e) $mail->AddBCC($e);
    
    $mail->Subject  = $mData['subject'];
    $mail->Body     = $mData['body'];

    foreach ($mData['attachment'] as $a) {
      if (isset($a['filename'])) $mail->AddAttachment($a['filename'], basename($a['filename']));
    }

    $ret = 0;
    if ($mail->Send()) {
      if ($this->_id) {
        // muzu poslat email, ktery neni v DB (testovaci email SMTP), proto kontroluju, jestli mam ID
        $this->_app->db->beginTransaction();

        $o = new ONotification($this->_id);
        $o->setData(array('sent' => date('Y-m-d H:i:s'), 'error_timestamp' => null, 'error_text' => null));
        $o->save();

        $this->_app->db->commitTransaction();
      }
    } else {
      error_log(sprintf('Failed to send notification #%s (%s)', ifsetor($this->_data['id'],'<EMPTY>'), $mail->ErrorInfo));

      if ($this->_id) {
        // muzu poslat email, ktery neni v DB (testovaci email SMTP)
        $this->_app->db->beginTransaction();

        $o = new ONotification($this->_id);
        $o->setData(array('error_timestamp' => date('Y-m-d H:i:s'), 'error_text' => $mail->ErrorInfo));
        $o->save();

        $this->_app->db->commitTransaction();
      }

      $ret = $mail->ErrorInfo;
    }

    // odstranim vygenerovane prilohy z FS
    foreach ($mData['attachment'] as $a) {
      if (isset($a['filename'])&&file_exists($a['filename'])) @unlink($a['filename']);
    }

    return $ret;
  }

  private function _parse() {
    if (!$this->_data['parsed']&&$this->_data['generateParams']) {
      $dynamicData = $this->_getNotificationDynamicData($this->_data['generateParams']);

      $this->_data['subject'] = $this->_notificationParseDynamicData($this->_data['subject'], $dynamicData);
      $this->_data['body'] = $this->_notificationParseDynamicData($this->_data['body'], $dynamicData);
      $newAttachment = $this->_notificationGenerateAttachment($dynamicData);

      $this->_app->db->beginTransaction();

      $o = new ONotification($this->_id);
      $o->setData(array('body'=>$this->_data['body'],'subject'=>$this->_data['subject'],'parsed'=>date('Y-m-d H:i:s')));
      $o->save();

      if (count($newAttachment)) {
        $this->_saveAttachment($newAttachment, true);

        $this->_data['attachment'] = $this->_loadAttachment();
      }

      $this->_app->db->commitTransaction();
    }
  }

  private function _getNotificationDynamicData($params) {
    $ret = array();

    // data o poskytovateli
    if (isset($params['providerId'])&&$params['providerId']) {
      $s = new SProvider;
      $s->addStatement(new SqlStatementBi($s->columns['provider_id'], $params['providerId'], '%s=%s'));
      $s->setColumnsMask(array('name','ic','dic','email','www','phone_1','phone_2','street','city','postal_code','bank_account_number','bank_account_suffix'));
      $res = $this->_app->db->doQuery($s->toString());
      $row = $this->_app->db->fetchAssoc($res);
      $row['phone'] = $row['phone_1'];
      if ($row['phone_2']) {
        if ($row['phone']) $row['phone'] .= ',';
        $row['phone'] .= $row['phone_2'];
      }
      unset($row['phone_1']); unset($row['phone_2']);
      if ($row['bank_account_number']) $row['bank_account'] = sprintf('%s/%', $row['bank_account_number'], $row['bank_account_suffix']);
      else $row['bank_account'] = '';
      unset($row['bank_account_number']);unset($row['bank_account_suffix']);

      foreach ($row as $key=>$value) { $ret['provider_'.$key] = $value; }
    } else {
      // todle tady je, protoze lze poslat heslo bez poskytovatele a v takovem pripade je potreba vyrusit @@PROVIDER_WWW
      $ret['provider_www'] = '';
    }

    if (isset($params['userId'])&&$params['userId']) {
      // data o uzivateli
      $s = new SUser;
      $s->addStatement(new SqlStatementBi($s->columns['user_id'], $params['userId'], '%s=%s'));
      $s->setColumnsMask(array('firstname','lastname','username','email','password'));
      $res = $this->_app->db->doQuery($s->toString());
      $row = $this->_app->db->fetchAssoc($res);
      $row['login'] = $row['username']; unset($row['username']);
      if (isset($params['newPassword'])) $row['password'] = $params['newPassword'];  // kdyz je heslo MD5, musi prijit original heslo "zvenku"
      foreach ($row as $key=>$value) { $ret['user_'.$key] = $value; }

      // validacni url
      $s = new SUserValidation();
      $s->addStatement(new SqlStatementBi($s->columns['user'], $params['userId'], '%s=%s'));
      $s->setColumnsMask(array('validation_string'));
      $res = $this->_app->db->doQuery($s->toString());
      if ($row = $this->_app->db->fetchAssoc($res)) {
        global $URL;
        $ret['user_validation_url'] = $URL['user_validation'].$row['validation_string'];
      } else {
        $ret['user_validation_url'] = '';
      }

      // data o registraci uzivatele
      $s = new SUserRegistration;
      $s->addStatement(new SqlStatementBi($s->columns['user'], $params['userId'], '%s=%s'));
      $s->addStatement(new SqlStatementBi($s->columns['provider'], $params['providerId'], '%s=%s'));
      $s->setColumnsMask(array('userregistration_id','registration_timestamp'));
      $res = $this->_app->db->doQuery($s->toString());
      if ($row = $this->_app->db->fetchAssoc($res)) {
        list($row['registration_date'],$row['registration_time']) = explode(' ', $this->_app->regionalSettings->convertDateTimeToHuman($row['registration_timestamp']));unset($row['registration_timestamp']);
      } else $row['registration_date'] = $row['registration_time'] = '';

      // udaje o zalohove fakture (pouze kdyz patri notifikace k uzivateli)
      if (isset($params['prepaymentInvoiceId'])&&$params['prepaymentInvoiceId']) {
        $s1 = new SPrepaymentInvoice;
        $s1->addStatement(new SqlStatementBi($s1->columns['prepaymentinvoice_id'], $params['prepaymentInvoiceId'], '%s=%s'));
        $s1->addStatement(new SqlStatementBi($s1->columns['userregistration'], $row['userregistration_id'], '%s=%s'));
        $s1->setColumnsMask(array('content'));
        $res1 = $this->_app->db->doQuery($s1->toString());
        if ($row1 = $this->_app->db->fetchAssoc($res1)) {
          $o = new OFile($row1['content']);
          $oData = $o->getData();
          $prepaymentInvoiceHtml = $oData['content'];
        }
      }
      unset($row['userregistration_id']);

      foreach ($row as $key=>$value) { $ret['user_'.$key] = $value; }
      if (isset($prepaymentInvoiceHtml)) $ret['prepaymentinvoice_html'] = $prepaymentInvoiceHtml;
    }

    if (isset($params['reservationConditionId'])&&$params['reservationConditionId']) {
      $s1 = new SReservationCondition;
      $s1->addStatement(new SqlStatementBi($s1->columns['reservationcondition_id'], $params['reservationConditionId'], '%s=%s'));
      $s1->setColumnsMask(array('description'));
      $res1 = $this->_app->db->doQuery($s1->toString());
      $row1 = $this->_app->db->fetchAssoc($res1);
      $ret['reservation_condition_description'] = ifsetor($row1['description'],'');
    } else {
      $ret['reservation_condition_description'] = '';
    }

    // data o rezervaci
    if (isset($params['reservationId'])&&$params['reservationId']) {
      $s = new SReservation;
      $s->addStatement(new SqlStatementBi($s->columns['reservation_id'], $params['reservationId'], '%s=%s'));
      $s->setColumnsMask(array('number','total_price','start','end','created','payed','cancelled',
        'center_name','center_street','center_city','center_postal_code',
        'resource_name','resource_description',
        'event','event_name','event_description',
        'receipt','invoice'
      ));
      $res = $this->_app->db->doQuery($s->toString());
      $row = $this->_app->db->fetchAssoc($res);

      $row['total_price'] = $this->_app->regionalSettings->convertNumberToHuman($row['total_price'],2,' ');

      list($row['start_date'],$row['start_time']) = explode(' ', $this->_app->regionalSettings->convertDateTimeToHuman($row['start'])); $row['startraw'] = $row['start']; unset($row['start']);
      list($row['end_date'],$row['end_time']) = explode(' ', $this->_app->regionalSettings->convertDateTimeToHuman($row['end'])); $row['endraw'] = $row['end']; unset($row['end']);
      list($row['create_date'],$row['create_time']) = explode(' ', $this->_app->regionalSettings->convertDateTimeToHuman($row['created'])); unset($row['created']);

      if ($row['resource_name']) {
        $row['commodity_name'] = $row['resource_name']; $row['commodity_description'] = $row['resource_description'];

        $row['organiser_name'] = $row['organiser_email'] = $row['organiser_phone'] = $row['free_places'] = $row['occupied_places'] = $row['attendees'] = '';
      } else {
        if (isset($params['eventPackItem'])) $row['event'] = $params['eventPackItem'];

        $s1 = new SEvent;
        $s1->addStatement(new SqlStatementBi($s1->columns['event_id'], $row['event'], '%s=%s'));
        $s1->setColumnsMask(array('name','description','start','end','organiser_fullname','organiser_email','organiser_phone','free','occupied'));
        $res1 = $this->_app->db->doQuery($s1->toString());
        $row1 = $this->_app->db->fetchAssoc($res1);
        $row['commodity_name'] = $row1['name'];
        $row['commodity_description'] = $row1['description'];
        $row['organiser_name'] = $row1['organiser_fullname'];
        $row['organiser_email'] = $row1['organiser_email'];
        $row['organiser_phone'] = $row1['organiser_phone'];
        $row['free_places'] = $row1['free'];
        $row['occupied_places'] = $row1['occupied'];
        list($row['event_start_date'],$row['event_start_time']) = explode(' ', $this->_app->regionalSettings->convertDateTimeToHuman($row1['start']));

        $row['attendees'] = '';
        $s1 = new SEventAttendee;
        $s1->addStatement(new SqlStatementBi($s1->columns['event'], $row['event'], '%s=%s'));
        $s1->addStatement(new SqlStatementMono($s1->columns['substitute'], "%s='N'"));
        $s1->setColumnsMask(array('person_firstname','person_lastname','person_email','user','person_user_firstname','person_user_lastname','person_user_email'));
        $res1 = $this->_app->db->doQuery($s1->toString());
        while ($row1 = $this->_app->db->fetchAssoc($res1)) {
          if ($row['attendees']) $row['attendees'] .= ',';
          if ($row['user']) $row['attendees'] .= sprintf('%s %s - %s', $row1['person_user_firstname'], $row1['person_user_lastname'], $row1['person_user_email']);
          else $row['attendees'] .= sprintf('%s %s - %s', $row1['person_firstname'], $row1['person_lastname'], $row1['person_email']);
        }

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
        if ($substituteEmail) $row['substitute_email'] = $substituteEmail;
      }
      unset($row['resource_name']); unset($row['resource_description']); unset($row['event']); unset($row['event_name']); unset($row['event_description']);

      $row['provider_center_name'] = $row['center_name']; unset($row['center_name']);
      $row['provider_center_street'] = $row['center_street']; unset($row['center_street']);
      $row['provider_center_city'] = $row['center_city']; unset($row['center_city']);
      $row['provider_center_postal_code'] = $row['center_postal_code']; unset($row['center_postal_code']);

      if ($row['payed']) {
        $s1 = new SReservationJournal;
        $s1->addStatement(new SqlStatementBi($s1->columns['reservation'], $params['reservationId'], '%s=%s'));
        $s1->addStatement(new SqlStatementMono($s1->columns['action'], "%s='PAY'"));
        $s1->setColumnsMask(array('note'));
        $res1 = $this->_app->db->doQuery($s1->toString());
        if ($row1 = $this->_app->db->fetchAssoc($res1)) {
          $parts = explode('|', $row1['note']);
          $row['pay_description'] = $this->_app->textStorage->getText('label.ajax_reservation_payment_'.$parts[0]);
        } else $row['pay_description'] = '';

        list($row['pay_date'],$row['pay_time']) = explode(' ', $this->_app->regionalSettings->convertDateTimeToHuman($row['payed'])); unset($row['payed']);

        if ($row['receipt']) {
          $o = new OFile($row['receipt']);
          $oData = $o->getData();
          $row['receipt_html'] = $oData['content'];
        }
        if ($row['invoice']) {
          $o = new OFile($row['invoice']);
          $oData = $o->getData();
          $row['invoice_html'] = $oData['content'];
        } else {
          // kdyz je notifikace posilana po konci rezervace, ma obsahovat fakturu a provider generuje faktury,
          // muze se stat, ze jeste faktura neni vygenerovana (dela to robot)
          // v takovem pripade notifikaci necham poslat "pozdeji"
          if (($row['endraw']<date('Y-m-d H:i:s'))&&isset($params['providerId'])&&$params['providerId']&&
              ((strpos($this->_data['body'],'@@INVOICE_HTML')!==false)||(strpos($this->_data['body'],'@@INVOICE_ATTACH')!==false))) {
            $s1 = new SProviderSettings;
            $s1->addStatement(new SqlStatementBi($s1->columns['provider'], $params['providerId'], '%s=%s'));
            $s->setColumnsMask(array('generate_accounting'));
            $res1 = $this->_app->db->doQuery($s1->toString());
            $row1 = $this->_app->db->fetchAssoc($res1);
            if ($row1&&($row1['generate_accounting']=='Y')) throw new ExceptionUser('Waiting for invoice to be generated');
          }
        }
      } else {
        $row['receipt_html'] = '';
        $row['invoice_html'] = '';

        $row['pay_description'] = '';

        $row['pay_date'] = $row['pay_time'] = '';
      }
      unset($row['receipt']); unset($row['invoice']);

      if ($row['cancelled']) {
        $s1 = new SReservationJournal;
        $s1->addStatement(new SqlStatementBi($s1->columns['reservation'], $params['reservationId'], '%s=%s'));
        $s1->addStatement(new SqlStatementMono($s1->columns['action'], "%s='CANCEL'"));
        $s1->setColumnsMask(array('note'));
        $res1 = $this->_app->db->doQuery($s1->toString());
        if ($row1 = $this->_app->db->fetchAssoc($res1)) {
          $row['cancel_reason'] = $row1['note'];
        }

        list($row['cancel_date'],$row['cancel_time']) = explode(' ', $this->_app->regionalSettings->convertDateTimeToHuman($row['cancelled'])); unset($row['cancelled']);
      } else {
        $row['cancel_reason'] = '';

        $row['cancel_date'] = $row['cancel_time'] = '';
      }

      // udaje o dobropisu (pouze kdyz patri notifikace k rezervaci)
      $row['creditnote_html'] = '';
      if (isset($params['creditNoteId'])&&$params['creditNoteId']) {
        $s1 = new SCreditnote;
        $s1->addStatement(new SqlStatementBi($s1->columns['creditnote_id'], $params['creditNoteId'], '%s=%s'));
        $s1->addStatement(new SqlStatementBi($s1->columns['reservation'], $params['reservationId'], '%s=%s'));
        $s1->setColumnsMask(array('content'));
        $res1 = $this->_app->db->doQuery($s1->toString());
        if ($row1 = $this->_app->db->fetchAssoc($res1)) {
          $o = new OFile($row1['content']);
          $oData = $o->getData();
          $row['creditnote_html'] = $oData['content'];
        }
      }

      foreach ($row as $key=>$value) { $ret[$key] = $value; }
    }

    // upravim index vsech dyn. dat
    foreach ($ret as $key=>$value) {
      $ret['@@'.strtoupper($key)] = $value;
      unset($ret[$key]);
    }

    return $ret;
  }

  private function _notificationParseDynamicData($target, $data) {
    $ret = str_replace(array_keys($data),array_values($data), $target);

    return $ret;
  }

  private function _notificationGenerateAttachment($data) {
    $ret = array();

    // tyto prilohy jsou pouze v pripade, ze se jedna o notifikaci k rezervaci
    if (isset($this->_data['generateParams']['reservationId'])) {
      global $INVOICE;
      global $TMP_DIR;
      include_once($INVOICE['pdf_creator']);

      // vstupenka, doklad o zaplaceni a danovy doklad, muzou byt v notifikaci pouze, kdyz je rezervace zaplacena
      if (isset($data['@@PAY_DATE'])&&$data['@@PAY_DATE']) {
        if ((strpos($this->_data['body'], '@@TICKET_ATTACH')!==false)||(strpos($this->_data['body'], '@@TICKET_HTML')!==false)) {
          $ticket = new GuiReservationTicket(array('reservation'=>$this->_data['generateParams']['reservationId'],'skipACL'=>true));
          $ticketHtml = $ticket->render();

          if (strpos($this->_data['body'], '@@TICKET_ATTACH')!==false) {
            $fileName = tempnam($TMP_DIR,'');
            unlink($fileName);
            $mpdf = new mPDF;
            $mpdf->WriteHTML($ticketHtml);
            $mpdf->Output($fileName, 'F');

            $ret[] = array('file'=>basename($fileName),'name'=>'ticket.pdf');
          }
          if (strpos($this->_data['body'], '@@TICKET_HTML')!==false) $this->_data['body'] = str_replace('@@TICKET_HTML', $ticketHtml, $this->_data['body']);
        }
        if ((strpos($this->_data['body'], '@@RECEIPT_ATTACH')!==false)&&$data['@@RECEIPT_HTML']) {
          $fileName = tempnam($TMP_DIR,'');
          unlink($fileName);
          $mpdf = new mPDF;
          $mpdf->WriteHTML($data['@@RECEIPT_HTML']);
          $mpdf->Output($fileName, 'F');

          $ret[] = array('file'=>basename($fileName),'name'=>'receipt.pdf');
        }
        if ((strpos($this->_data['body'], '@@INVOICE_ATTACH')!==false)&&$data['@@INVOICE_HTML']) {
          $fileName = tempnam($TMP_DIR,'');
          unlink($fileName);
          $mpdf = new mPDF;
          $mpdf->WriteHTML($data['@@INVOICE_HTML']);
          $mpdf->Output($fileName, 'F');

          $ret[] = array('file'=>basename($fileName),'name'=>'invoice.pdf');
        }
      } else {
        $this->_data['body'] = str_replace(array('@@TICKET_HTML','@@RECEIPT_HTML','@@INVOICE_HTML'),'',$this->_data['body']);
      }

      // dobropis muze byt v notifikaci ikdyz rezervace uz zaplacena neni
      if ((strpos($this->_data['body'], '@@CREDITNOTE_ATTACH')!==false)&&$data['@@CREDITNOTE_HTML']) {
        $fileName = tempnam($TMP_DIR,'');
        unlink($fileName);
        $mpdf = new mPDF;
        $mpdf->WriteHTML($data['@@CREDITNOTE_HTML']);
        $mpdf->Output($fileName, 'F');

        $ret[] = array('file'=>basename($fileName),'name'=>'creditnote.pdf');
      }
    } else {
      $this->_data['body'] = str_replace(array('@@TICKET_HTML','@@RECEIPT_HTML','@@INVOICE_HTML','@@CREDITNOTE_HTML'),'',$this->_data['body']);
    }

    // tyto prilohy jsou pouze v pripade, ze se jedna o notifikaci k uzivateli
    if (isset($this->_data['generateParams']['userId'])) {
      global $INVOICE;
      global $TMP_DIR;
      include_once($INVOICE['pdf_creator']);

      // zalohova faktura
      if ((strpos($this->_data['body'], '@@PREPAYMENTINVOICE_ATTACH')!==false)&&$data['@@PREPAYMENTINVOICE_HTML']) {
        $fileName = tempnam($TMP_DIR,'');
        unlink($fileName);
        $mpdf = new mPDF;
        $mpdf->WriteHTML($data['@@PREPAYMENTINVOICE_HTML']);
        $mpdf->Output($fileName, 'F');

        $ret[] = array('file'=>basename($fileName),'name'=>'prepaymentinvoice.pdf');
      }
    } else {
      $this->_data['body'] = str_replace(array('@@PREPAYMENTINVOICE_HTML'),'',$this->_data['body']);
    }

    // pridam dokumenty
    $s = new SDocument;
    $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_data['provider'], '%s=%s'));
    $s->addStatement(new SqlStatementBi($s->columns['user'], $this->_data['generateParams']['userId'], '%s=%s'));
    if (isset($this->_data['generateParams']['reservationId'])) $s->addStatement(new SqlStatementBi($s->columns['reservation'], $this->_data['generateParams']['reservationId'], '%s=%s'));
    $s->setColumnsMask(array('document_id','code','type','file_id','file_name'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      if (strpos($this->_data['type'],$row['type'])===false) continue; // zajimaji me dokumenty vznikle pri stejne fire-action (registrace s/bez validace)

      $documentTag = sprintf('@@DOCUMENT_%s', $row['code']);
      if ((strpos($this->_data['body'], $documentTag)!==false)) {
        $bF = new BFile($row['file_id']);
        $linkFile = $bF->export();
        $file = $bF->getFileFromLink($linkFile,true);
        $ret[] = array('file'=>basename($file['file']),'name'=>$row['file_name']);
      }
    }
    // zjistim vsechny kodu dokumentu a zrusim je v tele
    $documentTags = array();
    $s = new SDocumentTemplateItem;
    $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_data['provider'], '%s=%s'));
    $s->setColumnsMask(array('code'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $documentTags[] = sprintf('@@DOCUMENT_%s', $row['code']);
    }

    // vymazu vsechny tagy na prilohy
    $replacement = array_merge($documentTags,array('@@TICKET_ATTACH','@@RECEIPT_ATTACH','@@INVOICE_ATTACH','@@CREDITNOTE_ATTACH','@@PREPAYMENTINVOICE_ATTACH'));
    $this->_data['body'] = str_replace($replacement,'',$this->_data['body']);

    return $ret;
  }
  
  private function _prepareData($data) {
    $mData = $data;

		global $NOTIFICATION;
    if (isset($NOTIFICATION['debugEmail'])) {
      global $NODE_ID;
      
      $mData['subject'] = sprintf('FLEXBOOK - %s: %s', $NODE_ID, $mData['subject']);

      if (!isset($mData['contentType'])||!strcmp($mData['contentType'],'text/plain')) {
				$mData['body'] = sprintf("-------\n\n%s", $mData['body']);
				if ($mData['bccAddress']) $mData['body'] = sprintf("Bcc: %s\n%s", $mData['bccAddress'], $mData['body']);
				if ($mData['ccAddress']) $mData['body'] = sprintf("Cc: %s\n%s", $mData['ccAddress'], $mData['body']);
				if ($mData['toAddress']) $mData['body'] = sprintf("To: %s\n%s", $mData['toAddress'], $mData['body']);
			}
      
      $mData['toAddress'] = $NOTIFICATION['debugEmail'];
      $mData['ccAddress'] = null;
      $mData['bccAddress'] = null;
    }

    // vygeneruju prilohy do FS
    global $TMP_DIR;
    foreach ($mData['attachment'] as $index=>$a) {
      if (!isset($a['filename'])) {
        $o = new OFile($a['fileId']);
        $oData = $o->getData();

        $filename = $TMP_DIR.$oData['hash'].'_'.$oData['name'];
        @file_put_contents($filename, $oData['content']);

        $mData['attachment'][$index]['filename'] = $filename;
      }
    }
    
    return $mData;
  }

  public function sendTestEmail($providerId, $smtpHost, $smtpPort, $smtpUser, $smtpPassword, $smtpSecure) {
    $s = new SProvider;
    $s->addStatement(new SqlStatementBi($s->columns['provider_id'], $providerId, '%s=%s'));
    $s->setColumnsMask(array('email'));
    $res = $this->_app->db->doQuery($s->toString());
    $row = $this->_app->db->fetchAssoc($res);

    return $this->rawSend(array(
      'fromAddress'         => $row['email'],
      'toAddress'           => $this->_app->auth->getEmail(),
      'subject'             => 'SMTP Test email',
      'body'                => "Sending test email via optional SMTP server was successfull.\n\n--\nFlexbook.cz",
      'smtp'                => true,
      'smtpHost'            => $smtpHost,
      'smtpPort'            => $smtpPort,
      'smtpUser'            => $smtpUser,
      'smtpPassword'        => $smtpPassword,
      'smtpSecure'          => $smtpSecure
    ));
  }

  public function rawSend($params) {
    $defaultParams = array(
      'ccAddress'           => '',
      'bccAddress'          => '',
      'contentType'         => 'text/plain',
      'parsed'              => true,
      'generateParams'      => null,
      'attachment'          => array(),
    );

    global $NOTIFICATION;
    if (isset($NOTIFICATION['smtpHost'])&&!isset($params['smtpHost'])) {
      $defaultParams = array_merge($defaultParams, array(
        'smtp'                => true,
        'smtpHost'            => $NOTIFICATION['smtpHost'],
        'smtpPort'            => ifsetor($NOTIFICATION['smtpPort']),
        'smtpUser'            => ifsetor($NOTIFICATION['smtpUser']),
        'smtpPassword'        => ifsetor($NOTIFICATION['smtpPassword']),
        'smtpSecure'          => ifsetor($NOTIFICATION['smtpSecure'])
      ));
    }

    $this->_data = array_merge($defaultParams, $params);

    return $this->_send();
  }
}

?>
