<?php

class BDocument extends BusinessObject {

  protected function _load() {
    if ($this->_id&&!$this->_loaded) {
      $returnData = array();
      
      $s = new SDocument;
      $s->addStatement(new SqlStatementBi($s->columns['document_id'], $this->_id, '%s=%s'));
      $s->setColumnsMask(array('provider','code','number','user','reservation','created','file_id','file_length'));
      $res = $this->_app->db->doQuery($s->toString());
      $row = $this->_app->db->fetchAssoc($res);
      
      $returnData['id'] = $this->_id;
      $returnData['providerId'] = $row['provider'];
      $returnData['code'] = $row['code'];
      $returnData['number'] = $row['number'];
      $returnData['userId'] = $row['user'];
      $returnData['reservationId'] = $row['reservation'];
      $returnData['created'] = $row['created'];
      $returnData['file'] = $row['file_id'];
      $returnData['lentgh'] = $row['file_length'];

      $this->_data = $returnData;
      
      $this->_loaded = true;
    }
  }

  private function _saveFile($file) {
    if ($this->_data['file']) {
      $o = new OFile($this->_data['file']);
      $o->delete();
    }

    if (is_array($file)) {
      $b = new BFile;
      $fileId = $b->save(array('file'=>$file['file'],'name'=>$file['name']));

      $o = new ODocument($this->_id);
      $o->setData(array('content'=>$fileId));
      $o->save();
    }
  }

  private function _save($params) {
    $this->_app->db->beginTransaction();

    $o = new ODocument($this->_id?$this->_id:null);
    $oData = array();
    if (isset($params['providerId'])) $oData['provider'] = $params['providerId'];
    if (isset($params['code'])) $oData['code'] = $params['code'];
    if (isset($params['type'])) $oData['type'] = $params['type'];
    if (isset($params['number'])) {
      $oData['number'] = $params['number'];
      $this->_data['number'] = $params['number'];
    } else {
      $documentNumberStruct = BCustomer::generateDocumentNumber($params['providerId'], $params['documentTemplateItemId']);
      $oData['number'] = $documentNumberStruct['number'];
      $this->_data['number'] = $oData['number'];
    }
    if (isset($params['userId'])) $oData['user'] = $params['userId'];
    if (isset($params['reservationId'])) $oData['reservation'] = $params['reservationId'];
    
    if (!$this->_id) $oData['created'] = date('Y-m-d H:i:s');
    
    if (count($oData)) {
      $o->setData($oData);
      $o->save();
    }
    $this->_id = $o->getId();

    if (isset($params['contentTemplate'])) $this->_generateFile($params, ifsetor($oData['number'], $this->_data['number']));
    if (isset($params['file'])) $this->_saveFile($params['file']);

    if ($documentNumberStruct['globalCounter']) $this->_saveCounter($params['providerId'], $documentNumberStruct, $params['documentTemplateItemId']);

    $this->_app->db->commitTransaction();
  }

  private function _saveCounter($providerId, $counterStruct, $documentTemplateItemId) {
    $o = new OProvider($providerId);
    $o->setData(array('document_year' => $counterStruct['year'], 'document_counter' => $counterStruct['globalCounter']));
    $o->save();

    $o = new ODocumentTemplateItem($documentTemplateItemId);
    $o->setData(array('counter' => $counterStruct['documentCounter']));
    $o->save();
  }

  public function save($params) {
    $this->_load();

    $this->_save($params);
  }
  
  private function _delete() {
    $this->_load();
    
    $this->_app->db->beginTransaction();
    
    $o = new ODocument($this->_id);
    $o->delete();
    
    $this->_app->db->commitTransaction();
  }
  
  public function delete() {
    if (!$this->_app->auth->haveRight('delete_record', $this->_app->auth->getActualProvider())) throw new ExceptionUserTextStorage('error.accessDenied');

    $this->_delete();
  }

  private function _generateFile(& $params, $number) {
    $dynamicData = $this->_getDocumentDynamicData($params);

    $content = $this->_documentParseDynamicData($params['contentTemplate'], $dynamicData);
    if ($content) {
      global $TMP_DIR, $INVOICE;
      include_once($INVOICE['pdf_creator']);

      $fileName = tempnam($TMP_DIR,'');
      unlink($fileName);
      $mpdf = new mPDF;
      $mpdf->WriteHTML($content);
      $mpdf->Output($fileName, 'F');

      $params['file'] = array('file'=>basename($fileName),'name'=>sprintf('%s.pdf', str_replace(array("/","\\"),'',$number)));
    }
  }

  private function _getDocumentDynamicData($params) {
    $ret = array();

    // cislo dokumentu
    if (isset($this->_data['number'])) $ret['document_number'] = $this->_data['number'];

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
      $s->setColumnsMask(array('firstname','lastname','username','email','phone','password','street','city','postal_code','state'));
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

      unset($row['userregistration_id']);

      // atributy uzivatele
      $bUser = new BUser($params['userId']);
      $attributes = $bUser->getAttribute($params['providerId']);
      foreach ($attributes as $attr) {
        switch ($attr['type']) {
          case 'NUMBER': $value = $this->_app->regionalSettings->convertNumberToHuman($attr['value']); break;
          case 'DECIMALNUMBER': $value = $this->_app->regionalSettings->convertNumberToHuman($attr['value'],2); break;
          case 'DATE': $value = $this->_app->regionalSettings->convertDateToHuman($attr['value']); break;
          case 'TIME': $value = $this->_app->regionalSettings->convertTimeToHuman($attr['value'],'h:m'); break;
          case 'DATETIME': $value = $this->_app->regionalSettings->convertDateTimeToHuman($attr['value']); break;
          default: $value = $attr['value'];
        }
        $row['attribute_'.$attr['shortName']] = $value;
      }

      foreach ($row as $key=>$value) { $ret['user_'.$key] = $value; }
    }

    // data o rezervaci
    if (isset($params['reservationId'])&&$params['reservationId']) {
      $s = new SReservation;
      $s->addStatement(new SqlStatementBi($s->columns['reservation_id'], $params['reservationId'], '%s=%s'));
      $s->setColumnsMask(array('number','total_price','start','end','created','payed',
        'center_name','center_street','center_city','center_postal_code',
        'resource_name','resource_description',
        'event','event_name','event_description',
      ));
      $res = $this->_app->db->doQuery($s->toString());
      $row = $this->_app->db->fetchAssoc($res);

      $row['total_price'] = $this->_app->regionalSettings->convertNumberToHuman($row['total_price'],2,' ');

      list($row['start_date'],$row['start_time']) = explode(' ', $this->_app->regionalSettings->convertDateTimeToHuman($row['start'])); $row['startraw'] = $row['start']; unset($row['start']);
      list($row['end_date'],$row['end_time']) = explode(' ', $this->_app->regionalSettings->convertDateTimeToHuman($row['end'])); $row['endraw'] = $row['end']; unset($row['end']);
      list($row['create_date'],$row['create_time']) = explode(' ', $this->_app->regionalSettings->convertDateTimeToHuman($row['created'])); unset($row['created']);

      if ($row['resource_name']) {
        $row['commodity_name'] = $row['resource_name'];
        $row['commodity_description'] = $row['resource_description'];
      } else {
        $row['commodity_name'] = $row['event_name'];
        $row['commodity_description'] = $row['event_description'];
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
      } else {
        $row['pay_description'] = '';

        $row['pay_date'] = $row['pay_time'] = '';
      }

      $bRes = new BReservation($params['reservationId']);
      $attributes = $bRes->getAttribute();
      foreach ($attributes as $attr) {
        switch ($attr['type']) {
          case 'NUMBER': $value = $this->_app->regionalSettings->convertNumberToHuman($attr['value']); break;
          case 'DECIMALNUMBER': $value = $this->_app->regionalSettings->convertNumberToHuman($attr['value'],2); break;
          case 'DATE': $value = $this->_app->regionalSettings->convertDateToHuman($attr['value']); break;
          case 'TIME': $value = $this->_app->regionalSettings->convertTimeToHuman($attr['value'],'h:m'); break;
          case 'DATETIME': $value = $this->_app->regionalSettings->convertDateTimeToHuman($attr['value']); break;
          default: $value = $attr['value'];
        }
        $row['reservation_attribute_'.$attr['shortName']] = $value;
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

  private function _documentParseDynamicData($target, $data) {
    $ret = str_replace(array_keys($data),array_values($data), $target);

    return $ret;
  }
}

?>
