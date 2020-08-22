<?php

class AjaxGetUserCreditHistory extends AjaxAction {
  private $_includeOnline = false;
  
  private function _getUserCreditHistory($registration) {
    $ret = array();
    
    // kontrola, jestli ma pravo videt historii
    $o = new OUserRegistration($registration);
    $oData = $o->getData();
    // kdyz neni registrace prihlaseneho uzivatele, musi byt admin, nebo spravny poskytovatel
    if ($this->_app->auth->getUserId()!=$oData['user']) {
      if (!in_array($oData['provider'], $this->_app->auth->getAllowedProvider('credit_admin','array'))) throw new ExceptionUserTextStorage('error.accessDenied');
    }
    
    $s = new SCreditJournal;
    $s->addStatement(new SqlStatementBi($s->columns['userregistration'], $registration, '%s=%s'));
    $s->addOrder(new SqlStatementAsc($s->columns['change_timestamp']));
    $s->setColumnsMask(array('creditjournal_id','amount','change_timestamp','flag','type','note','prepaymentinvoice_id'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      if ($row['flag']=='C') {
        if (in_array($row['type'],array('RESERVATION','TICKET'))) $description = sprintf($this->_app->textStorage->getText('label.ajax_profile_creditRefund_'.$row['type']), $row['note']);
        else $description = sprintf($this->_app->textStorage->getText('label.ajax_profile_creditCharge'), $this->_app->textStorage->getText('label.ajax_profile_creditCharge_'.$row['type']));
      } else {
        if (!strcmp($row['type'],'RESERVATION')) $description = sprintf($this->_app->textStorage->getText('label.ajax_profile_creditDebet_RESERVATION'), $row['note']);
        elseif (!strcmp($row['type'],'TICKET')) $description = sprintf($this->_app->textStorage->getText('label.ajax_profile_creditDebet_TICKET'), $row['note']);
        else $description = sprintf($this->_app->textStorage->getText('label.ajax_profile_creditDebet'), $this->_app->textStorage->getText('label.ajax_profile_creditCharge_'.$row['type']));
      }
      
      $item = array(
                'id'              => $row['creditjournal_id'],
                'invoice'         => $row['prepaymentinvoice_id']?1:0,
                'type'            => 'credit',
                'typeLabel'       => $this->_app->textStorage->getText('label.ajax_profile_creditHistory_CREDIT'),
                'sortCode'        => $row['change_timestamp'].'_a',
                'timestamp'       => $this->_app->regionalSettings->convertDateTimeToHuman($row['change_timestamp']),
                'amount'          => $this->_app->regionalSettings->convertNumberToHuman($row['amount'],2),
                'currency'        => $this->_app->textStorage->getText('label.currency_CZK'),
                'description'     => $description,
                );
      
      $ret[] = $this->_request->convertOutput($item);
    }
    
    return $ret;
  }
  
  private function _getUserOnlineHistoryFromReservation($registration) {
    $ret = array();
    
    // kontrola, jestli ma pravo videt historii
    $o = new OUserRegistration($registration);
    $oData = $o->getData();
    // kdyz neni registrace prihlaseneho uzivatele, musi byt admin, nebo spravny poskytovatel
    if ($this->_app->auth->getUserId()!=$oData['user']) {
      if (!in_array($oData['provider'], $this->_app->auth->getAllowedProvider('credit_admin','array'))) throw new ExceptionUserTextStorage('error.accessDenied');
    }
    
    $s = new SReservationJournal;
    $s->addStatement(new SqlStatementBi($s->columns['provider'], $oData['provider'], '%s=%s'));
    $s->addStatement(new SqlStatementBi($s->columns['user'], $oData['user'], '%s=%s'));
    $s->addStatement(new SqlStatementMono($s->columns['action'], "%s IN ('PAY','REFUND')"));
    $s->addStatement(new SqlStatementMono($s->columns['note'], "SUBSTRING(%s,1,6) NOT IN ('credit','ticket')"));
    $s->addOrder(new SqlStatementAsc($s->columns['change_timestamp']));
    $s->setColumnsMask(array('reservationjournal_id','number','total_price','change_timestamp','action','note'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $notePart = explode('|', $row['note']);
      $onlineDesc = $notePart[0]?$this->_app->textStorage->getText('label.ajax_profile_online_'.$notePart[0]):'N/A';
      if (!strcmp($row['action'],'PAY')) {
        $row['total_price'] = -$row['total_price'];
        $description = sprintf($this->_app->textStorage->getText('label.ajax_profile_creditDebet_RESERVATION'), $row['number']);
      } elseif (!strcmp($row['action'],'REFUND')) $description = sprintf($this->_app->textStorage->getText('label.ajax_profile_creditRefund_RESERVATION'), $row['number']);
        
      $item = array(
                'id'            => $row['reservationjournal_id'],
                'type'          => 'online',
                'typeLabel'     => sprintf($this->_app->textStorage->getText('label.ajax_profile_creditHistory_ONLINE'), $onlineDesc),
                'sortCode'      => $row['change_timestamp'].'_c',
                'timestamp'     => $this->_app->regionalSettings->convertDateTimeToHuman($row['change_timestamp']),
                'amount'        => $this->_app->regionalSettings->convertNumberToHuman($row['total_price'],2),
                'currency'      => $this->_app->textStorage->getText('label.currency_CZK'),
                'description'   => $description,
                );
      
      $ret[] = $this->_request->convertOutput($item);
    }
    
    return $ret;
  }

  private function _getReservationNumberFromTargetId($targetId) {
    $ret = '';

    $ids = str_replace('|',',',substr($targetId, 1, -1));

    $s = new SReservation;
    $s->addStatement(new SqlStatementMono($s->columns['reservation_id'], sprintf('%%s IN (%s)', $ids)));
    $s->setColumnsMask(array('number'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      if ($ret) $ret .= ',';
      $ret .= $row['number'];
    }

    return $ret;
  }

  private function _getUserOnlineHistory($registration) {
    $ret = array();

    // kontrola, jestli ma pravo videt historii
    $o = new OUserRegistration($registration);
    $oData = $o->getData();
    // kdyz neni registrace prihlaseneho uzivatele, musi byt admin, nebo spravny poskytovatel
    if ($this->_app->auth->getUserId()!=$oData['user']) {
      if (!in_array($oData['provider'], $this->_app->auth->getAllowedProvider('credit_admin','array'))) throw new ExceptionUserTextStorage('error.accessDenied');
    }

    $s = new SOnlinePayment;
    $s->addStatement(new SqlStatementBi($s->columns['userregistration'], $registration, '%s=%s'));
    $s->addStatement(new SqlStatementMono($s->columns['payed'], "%s='Y'"));
    $s->addOrder(new SqlStatementAsc($s->columns['end_timestamp']));
    $s->setColumnsMask(array('onlinepayment_id','amount','target','target_id','type','end_timestamp','refund_timestamp','ticket_name'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $onlineDesc = $this->_app->textStorage->getText('label.ajax_profile_online_'.$row['type']);
      if ($row['refund_timestamp']) {
        // kdyz je platba refundovana, pridam navic zaznam o refundoaci
        if (!strcmp($row['target'],'RESERVATION')) $description = sprintf($this->_app->textStorage->getText('label.ajax_profile_creditRefund_RESERVATION'), $this->_getReservationNumberFromTargetId($row['target_id']));
        elseif (!strcmp($row['target'],'TICKET')) $description = sprintf($this->_app->textStorage->getText('label.ajax_profile_creditRefund_TICKET'), $row['ticket_name']);
        elseif (!strcmp($row['target'],'CREDIT')) $description = $this->_app->textStorage->getText('label.ajax_profile_creditRefund_CREDIT');

        $item = array(
          'id'            => $row['onlinepayment_id'],
          'type'          => 'online',
          'typeLabel'     => sprintf($this->_app->textStorage->getText('label.ajax_profile_creditHistory_ONLINE'), $onlineDesc),
          'sortCode'      => $row['refund_timestamp'].'_c',
          'timestamp'     => $this->_app->regionalSettings->convertDateTimeToHuman($row['refund_timestamp']),
          'amount'        => $this->_app->regionalSettings->convertNumberToHuman($row['amount'],2),
          'currency'      => $this->_app->textStorage->getText('label.currency_CZK'),
          'description'   => $description,
        );

        $ret[] = $this->_request->convertOutput($item);
      }

      $row['amount'] = -$row['amount'];
      if (!strcmp($row['target'],'RESERVATION')) $description = sprintf($this->_app->textStorage->getText('label.ajax_profile_creditDebet_RESERVATION'), $this->_getReservationNumberFromTargetId($row['target_id']));
      elseif (!strcmp($row['target'],'TICKET')) $description = sprintf($this->_app->textStorage->getText('label.ajax_profile_creditDebet_TICKET'), $row['ticket_name']);
      elseif (!strcmp($row['target'],'CREDIT')) $description = $this->_app->textStorage->getText('label.ajax_profile_creditDebet_CREDIT');

      $item = array(
        'id'            => $row['onlinepayment_id'],
        'type'          => 'online',
        'typeLabel'     => sprintf($this->_app->textStorage->getText('label.ajax_profile_creditHistory_ONLINE'), $onlineDesc),
        'sortCode'      => $row['end_timestamp'].'_c',
        'timestamp'     => $this->_app->regionalSettings->convertDateTimeToHuman($row['end_timestamp']),
        'amount'        => $this->_app->regionalSettings->convertNumberToHuman($row['amount'],2),
        'currency'      => $this->_app->textStorage->getText('label.currency_CZK'),
        'description'   => $description,
      );

      $ret[] = $this->_request->convertOutput($item);
    }

    return $ret;
  }

  private function _getUserTicketHistory($ticket) {
    $ret = array();
    
    // kontrola, jestli ma pravo videt historii
    $s = new SUserTicket;
    $s->addStatement(new SqlStatementBi($s->columns['userticket_id'], $ticket, '%s=%s'));
    $s->setColumnsMask(array('user','provider'));
    $res = $this->_app->db->doQuery($s->toString());
    $row = $this->_app->db->fetchAssoc($res);
    // kdyz neni registrace prihlaseneho uzivatele, musi byt admin, nebo spravny poskytovatel
    if ($this->_app->auth->getUserId()!=$row['user']) {
      if (!in_array($row['provider'], $this->_app->auth->getAllowedProvider('credit_admin','array'))) throw new ExceptionUserTextStorage('error.accessDenied');
    }
    
    $s = new SUserTicketJournal;
    $s->addStatement(new SqlStatementBi($s->columns['userticket'], $ticket, '%s=%s'));
    $s->addOrder(new SqlStatementAsc($s->columns['change_timestamp']));
    $s->setColumnsMask(array('userticketjournal_id','name','amount','change_timestamp','flag','type','note'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      if (!strcmp($row['type'],'CREATE')) $description = $this->_app->textStorage->getText('label.ajax_profile_ticket_CREATE');
      elseif (!strcmp($row['type'],'REFUND')) $description = $this->_app->textStorage->getText('label.ajax_profile_ticketDebet_REFUND');
      elseif (!strcmp($row['type'],'RESERVATION')) {
        if ($row['flag']=='C') $description = sprintf($this->_app->textStorage->getText('label.ajax_profile_ticketCredit_RESERVATION'), $row['note']);
        else $description = sprintf($this->_app->textStorage->getText('label.ajax_profile_ticketDebet_RESERVATION'), $row['note']);
      }
      
      $item = array(
                'id'            => $row['userticketjournal_id'],
                'type'          => 'ticket',
                'typeLabel'     => sprintf($this->_app->textStorage->getText('label.ajax_profile_creditHistory_TICKET'), $row['name']),
                'sortCode'      => $row['change_timestamp'].'_b',
                'timestamp'     => $this->_app->regionalSettings->convertDateTimeToHuman($row['change_timestamp']),
                'amount'        => $this->_app->regionalSettings->convertNumberToHuman($row['amount'],2),
                'currency'      => $this->_app->textStorage->getText('label.currency_CZK'),
                'description'   => $description,
                );
      
      $ret[] = $this->_request->convertOutput($item);
    }
    
    return $ret;
  }
  
  private function _sortResult() {
    $mappingArray = array();
    foreach ($this->_result as $index=>$item) {
      $mappingArray[$index] = $item['sortCode'];
    }
    
    array_multisort($mappingArray, SORT_DESC, $this->_result);
  }
  
  protected function _userRun() {
    $this->_result = array();
    
    $registration = $ticket = null;
    if (($user=$this->_app->request->getParams('user'))&&($provider=$this->_app->request->getParams('provider'))) {
      $s = new SUserRegistration;
      $s->addStatement(new SqlStatementBi($s->columns['user'], $user, '%s=%s'));
      $s->addStatement(new SqlStatementBi($s->columns['provider'], $provider, '%s=%s'));
      $s->setColumnsMask(array('userregistration_id'));
      $res = $this->_app->db->doQuery($s->toString());
      if ($row = $this->_app->db->fetchAssoc($res)) $registration = $row['userregistration_id'];
      
      $ticket = array();
      $s = new SUserTicket;
      $s->addStatement(new SqlStatementBi($s->columns['user'], $user, '%s=%s'));
      $s->addStatement(new SqlStatementBi($s->columns['provider'], $provider, '%s=%s'));
      $s->setColumnsMask(array('userticket_id'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) $ticket[] = $row['userticket_id'];
      
      $this->_includeOnline = true;
    } else {
      $registration = $this->_app->request->getParams('registration');
      $ticket = $this->_app->request->getParams('ticket');
      if ($ticket) $ticket = array($ticket);
    }
    
    if ($registration) {
      $this->_result = $this->_getUserCreditHistory($registration);
      if ($this->_includeOnline) $this->_result = array_merge($this->_result,$this->_getUserOnlineHistory($registration));
    }
    if ($ticket&&count($ticket)) foreach ($ticket as $t) $this->_result = array_merge($this->_result,$this->_getUserTicketHistory($t));

    $this->_sortResult();
  }
}

?>
