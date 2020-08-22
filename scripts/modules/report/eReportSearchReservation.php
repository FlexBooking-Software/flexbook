<?php

class ModuleReportSearchReservation extends ModuleReportSearch {
  
  public function __construct() {
    parent::__construct();
    
    $this->_formValidator = Validator::get('reservationReport','ReservationReportValidator');
  }

  private function _fillPaymentInfo(& $resultLine, $journalPayrecord) {
    $journalPayrecordElements = explode('|', $journalPayrecord);
    if (isset($journalPayrecordElements[1])) {
      $journalPayrecordRest = explode(',', $journalPayrecordElements[1]);
      $paymentId = substr($journalPayrecordRest[0],strpos($journalPayrecordRest[0],':')+1);
    } else $paymentId = null;

    if (isset($resultLine['payment_type'])) $resultLine['payment_type'] = isset($journalPayrecordElements[0])?$this->_app->textStorage->getText('label.report_reservation_paymentType_'.$journalPayrecordElements[0]):null;
    if (isset($resultLine['payment_id'])) $resultLine['payment_id'] = $paymentId;
  }

  private function _fillAttendee($columnsMask, $row, & $resultLine) {
    if (isset($row['eventattendeeperson_user'])&&$row['eventattendeeperson_user']) {
      if (in_array('eventattendeeperson_firstname', $columnsMask)) $resultLine['eventattendeeperson_firstname'] = $row['eventattendeeperson_user_firstname'];
      if (in_array('eventattendeeperson_lastname', $columnsMask)) $resultLine['eventattendeeperson_lastname'] = $row['eventattendeeperson_user_lastname'];
      if (in_array('eventattendeeperson_email', $columnsMask)) $resultLine['eventattendeeperson_email'] = $row['eventattendeeperson_user_email'];
    }
  }
  
  protected function _search($data) {
    #adump($data);
    
    // filtr rezervaci    
    $s = new SReservation;
    $s->addStatement(new SqlStatementMono($s->columns['center'], sprintf('%%s IN (%s)', $this->_app->auth->getAllowedCenter('list'))));
    if ($data['providerId']) $s->addStatement(new SqlStatementBi($s->columns['provider'], $data['providerId'], '%s=%s'));
    if ($data['tag']) {
      $s->addStatement(new SqlStatementBi($s->columns['et_tag'], $s->columns['rt_tag'], sprintf('(%%s IN (%s) OR %%s IN (%s))', $this->_app->db->escapeString($data['tag']), $this->_app->db->escapeString($data['tag']))));
    }
    if ($data['state']) {
      if ($data['state'] == 'FAILED') $s->addStatement(new SqlStatementMono($s->columns['failed'], '%s IS NOT NULL'));
      if ($data['state'] == 'CANCELLED') $s->addStatement(new SqlStatementMono($s->columns['cancelled'], '%s IS NOT NULL'));
      if ($data['state'] == 'ACTIVE') $s->addStatement(new SqlStatementTri($s->columns['failed'], $s->columns['cancelled'], $s->columns['end'], '%s IS NULL AND %s IS NULL AND %s>NOW()'));
      if ($data['state'] == 'REALISED') $s->addStatement(new SqlStatementTri($s->columns['failed'], $s->columns['cancelled'], $s->columns['end'], '%s IS NULL AND %s IS NULL AND %s<=NOW()'));
      if ($data['state'] == 'VALID') $s->addStatement(new SqlStatementMono($s->columns['cancelled'], '%s IS NULL'));
      if ($data['state'] == 'ONLINEPAYMENT') $s->addStatement(new SqlStatementTri($s->columns['payed'], $s->columns['cancelled'], $s->columns['open_onlinepayment'], '%s IS NULL AND %s IS NULL AND %s IS NOT NULL'));
    }
    if ($data['payed']) {
      if ($data['payed'] == 'Y') $s->addStatement(new SqlStatementMono($s->columns['payed'], '%s IS NOT NULL'));
      if ($data['payed'] == 'N') $s->addStatement(new SqlStatementMono($s->columns['payed'], '%s IS NULL'));
    }
    if ($data['mandatory']) {
      $s->addStatement(new SqlStatementBi($s->columns['mandatory'], $data['mandatory'], '%s=%s'));
    }
    if ($data['paymentType']) {
      $s->addStatement(new SqlStatementBi($data['paymentType'], $s->columns['journal_payrecord'], 'POSITION(%s IN %s)=1'));
    }
    if ($data['priceManual']) {
      if ($data['priceManual'] == 'Y') $s->addStatement(new SqlStatementMono($s->columns['price_timestamp'], '%s IS NOT NULL'));
      if ($data['priceManual'] == 'N') $s->addStatement(new SqlStatementMono($s->columns['price_timestamp'], '%s IS NULL'));
    }
    if ($data['centerId']) $s->addStatement(new SqlStatementBi($s->columns['center'], $data['centerId'], '%s=%s'));
    if ($data['resourceId']) {
      $s->addStatement(new SqlStatementQuad($s->columns['resource'], $data['resourceId'],
                                            $s->columns['er_resource'], $data['resourceId'],
                                            '(%s=%s OR %s=%s)'));
    }
    if ($data['eventId']) $s->addStatement(new SqlStatementBi($s->columns['event'], $data['eventId'], '%s=%s'));
    if ($data['pastEventId']) $s->addStatement(new SqlStatementBi($s->columns['event'], $data['pastEventId'], '%s=%s'));
    if ($data['accountTypeId']) $s->addStatement(new SqlStatementQuad($s->columns['event_accounttype'], $data['accountTypeId'], $s->columns['resource_accounttype'], $data['accountTypeId'],  '(%s=%s OR %s=%s)'));
    if ($data['from']) $s->addStatement(new SqlStatementBi($s->columns['created'], $this->_app->regionalSettings->convertHumanToDate($data['from']).' 00:00:00', '%s>=%s'));
    if ($data['to']) $s->addStatement(new SqlStatementBi($s->columns['created'], $this->_app->regionalSettings->convertHumanToDate($data['to']).' 23:59:59', '%s<=%s'));
    if ($data['realiseFrom']) $s->addStatement(new SqlStatementBi($s->columns['start'], $this->_app->regionalSettings->convertHumanToDate($data['realiseFrom']).' 00:00:00', '%s>=%s'));
    if ($data['realiseTo']) $s->addStatement(new SqlStatementBi($s->columns['start'], $this->_app->regionalSettings->convertHumanToDate($data['realiseTo']).' 23:59:59', '%s<=%s'));
    if ($data['cancelledFrom']) $s->addStatement(new SqlStatementBi($s->columns['cancelled'], $this->_app->regionalSettings->convertHumanToDate($data['cancelledFrom']).' 00:00:00', '%s>=%s'));
    if ($data['cancelledTo']) $s->addStatement(new SqlStatementBi($s->columns['cancelled'], $this->_app->regionalSettings->convertHumanToDate($data['cancelledTo']).' 23:59:59', '%s<=%s'));
    if ($data['fulltext']) {
      $likeCond = $this->_app->db->escapeString(sprintf('%%%%%s%%%%', $data['fulltext']));
      $s->addStatement(new SqlStatementQuad($s->columns['number'], $s->columns['user_name'], $s->columns['event_name'], $s->columns['resource_name'],
            sprintf("((%%s LIKE '%s') OR (%%s LIKE '%s') OR (%%s LIKE '%s') OR (%%s LIKE '%s'))", $likeCond, $likeCond, $likeCond, $likeCond)));
    }
    
    $summarySelect = clone $s;
    
    $columnsMask = $this->_getColumnsMask($s);
    
    $result = array($this->_getHeaderLine($columnsMask));

    $s->setColumnsMask($columnsMask);
    $s->addToColumnsMask(array('user_id','reservation_id','event','journal_payrecord'));
    // tabulku s ucastnikama joinuju pouze, kdyz se maji zobrazit ucastnici
    if (in_array('eventattendeeperson_firstname',$columnsMask)||
      in_array('eventattendeeperson_lastname',$columnsMask)||
      in_array('eventattendeeperson_email',$columnsMask)) {
      $s->addToColumnsMask(array('eventattendeeperson_user','eventattendeeperson_user_firstname','eventattendeeperson_user_lastname','eventattendeeperson_user_email'));
    }
    $this->_addGroupToSelect($s);    
    $s->addOrder(new SqlStatementAsc($s->columns['number']));
    
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      if (isset($row['created'])) $row['formatted__created'] = $this->_app->regionalSettings->convertDateTimeToHuman($row['created']);
      if (isset($row['payed'])) $row['formatted__payed'] = $this->_app->regionalSettings->convertDateTimeToHuman($row['payed']);
      if (isset($row['cancelled'])) $row['formatted__cancelled'] = $this->_app->regionalSettings->convertDateTimeToHuman($row['cancelled']);
      if (isset($row['price_timestamp'])) $row['formatted__price_timestamp'] = $this->_app->regionalSettings->convertDateTimeToHuman($row['price_timestamp']);
      if (isset($row['resource_from'])) $row['formatted__resource_from'] = $this->_app->regionalSettings->convertDateTimeToHuman($row['resource_from']);
      if (isset($row['resource_to'])) $row['formatted__resource_to'] = $this->_app->regionalSettings->convertDateTimeToHuman($row['resource_to']);
      if (isset($row['event_start'])) $row['formatted__event_start'] = $this->_app->regionalSettings->convertDateTimeToHuman($row['event_start']);
      if (isset($row['event_end'])) $row['formatted__event_end'] = $this->_app->regionalSettings->convertDateTimeToHuman($row['event_end']);
      if (isset($row['eventattendee_event_start'])) $row['formatted__eventattendee_event_start'] = $this->_app->regionalSettings->convertDateTimeToHuman($row['eventattendee_event_start']);
      if (isset($row['eventattendee_event_end'])) $row['formatted__eventattendee_event_end'] = $this->_app->regionalSettings->convertDateTimeToHuman($row['eventattendee_event_end']);
      
      foreach ($columnsMask as $val) {
        //$newVal = str_replace(array('sum__','count__','list__'),'',$val);
        $resultLine[$val] = ifsetor($row[$val],'');
      }
      $this->_addUserAttributeToResult($resultLine, $row['user_id']);
      $this->_addReservationAttributeToResult($resultLine, $row['reservation_id']);
      $this->_fillPaymentInfo($resultLine, $row['journal_payrecord']);
      $this->_fillAttendee($columnsMask, $row, $resultLine);

      if (isset($row['formatted__created'])) $resultLine['formatted__created'] = $row['formatted__created'];
      if (isset($row['formatted__payed'])) $resultLine['formatted__payed'] = $row['formatted__payed'];
      if (isset($row['formatted__cancelled'])) $resultLine['formatted__cancelled'] = $row['formatted__cancelled'];
      if (isset($row['formatted__price_timestamp'])) $resultLine['formatted__price_timestamp'] = $row['formatted__price_timestamp'];
      if (isset($row['formatted__resource_from'])) $resultLine['formatted__resource_from'] = $row['formatted__resource_from'];
      if (isset($row['formatted__resource_to'])) $resultLine['formatted__resource_to'] = $row['formatted__resource_to'];
      if (isset($row['formatted__event_start'])) $resultLine['formatted__event_start'] = $row['formatted__event_start'];
      if (isset($row['formatted__event_end'])) $resultLine['formatted__event_end'] = $row['formatted__event_end'];
      if (isset($row['formatted__eventattendee_event_start'])) $resultLine['formatted__eventattendee_event_start'] = $row['formatted__eventattendee_event_start'];
      if (isset($row['formatted__eventattendee_event_end'])) $resultLine['formatted__eventattendee_event_end'] = $row['formatted__eventattendee_event_end'];
      
      $result[] = $resultLine;
    }
    #adump($result);die;
    
    $summary = array();
    $summarySelect->addColumn(new SqlColumn(false, new SqlStatementMono($summarySelect->columns['total_price'], 'SUM(%s)'), 'sum__price', true));
    $summarySelect->setColumnsMask(array('sum__price'));
    $res = $this->_app->db->doQuery($summarySelect->toString());
    if ($row = $this->_app->db->fetchAssoc($res)) {
      $summary[] = sprintf($this->_app->textStorage->getText('label.report_reservation_resultSummaryPrice'), $this->_app->regionalSettings->convertNumberToHuman($row['sum__price']?$row['sum__price']:0,2));
    }
  
    return array('data'=>$result,'summary'=>$summary);
  }
}

?>
