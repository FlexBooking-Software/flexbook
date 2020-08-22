<?php

class AjaxGetReservation extends AjaxAction {

  protected function _userRun() {
    if ($this->_app->session->getExpired()||!$this->_app->auth->getUserId()) throw new ExceptionUserTextStorage('error.ajax_sessionExpired');
    
    $this->_result = array();
    
    if ($reservation = ifsetor($this->_params['id'])) {
      $s = new SReservation;
      if (!$this->_app->auth->isAdministrator()) {
        $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
        if (!$this->_app->auth->isProvider()) $s->addStatement(new SqlStatementBi($s->columns['user'], $this->_app->auth->getUserId(), '%s=%s'));
      }
      $s->addStatement(new SqlStatementBi($s->columns['reservation_id'], $reservation, '%s=%s'));
      $s->addStatement(new SqlStatementMono($s->columns['cancelled'], '%s IS NULL'));
      $s->setColumnsMask(array('number','provider','user_id','user_name','total_price','payed','failed','note',
                               'resource','resource_from','resource_to',
                               'event','event_name','event_places','fe_allowed_payment'));
      $res = $this->_app->db->doQuery($s->toString());
      if (!$row = $this->_app->db->fetchAssoc($res)) return;
      $output = array(
        'id'                => $reservation,
        'number'            => $row['number'],
        'userId'            => $row['user_id'],
        'userName'          => $row['user_name'],
        'price'             => $this->_app->regionalSettings->convertNumberToHuman($row['total_price']),
        'priceRaw'          => $row['total_price'],
        'currency'          => $this->_app->textStorage->getText('label.currency_CZK'),
        'payed'             => $row['payed'],
        'failed'            => $row['failed'],
        'allowedPayment'    => $row['fe_allowed_payment'],
        'note'              => $row['note'],
        );

      $providerSettings = BCustomer::getProviderSettings($row['provider'],array('disableCredit','disableTicket','disableOnline'));
      if ($providerSettings['disableCredit']=='Y') $output['allowedPayment'] -= 1;
      if ($providerSettings['disableTicket']=='Y') $output['allowedPayment'] -= 10;
      if ($providerSettings['disableOnline']=='Y') $output['allowedPayment'] -= 100;
      if ($row['payed']) {
        $s1 = new SReservationJournal;
        $s1->addStatement(new SqlStatementBi($s1->columns['reservation'], $reservation, '%s=%s'));
        $s1->addStatement(new SqlStatementMono($s1->columns['action'], "%s='PAY'"));
        $s1->setColumnsMask(array('note'));
        $res1 = $this->_app->db->doQuery($s1->toString());
        if ($row1 = $this->_app->db->fetchAssoc($res1)) {
          $parts = explode('|', $row1['note']);
          // anonymni rezervace je vzdy placena hotove
          if (!$row['user_id']) $parts[0] = 'cash';
          $output['payedBy'] = $this->_app->textStorage->getText('label.ajax_reservation_payment_'.$parts[0]);
        }
      }
      if ($row['resource']) {
        $output['fromRaw'] = $row['resource_from'];
        $output['toRaw'] = $row['resource_to'];
        $output['from'] = $this->_app->regionalSettings->convertDateTimeToHuman($row['resource_from']);
        if (isset($this->_params['format']['datetime'])) $output['from'] = date($this->_params['format']['datetime'], strtotime($output['from']));
        $output['to']   = $this->_app->regionalSettings->convertDateTimeToHuman($row['resource_to']);
        if (isset($this->_params['format']['datetime'])) $output['to'] = date($this->_params['format']['datetime'], strtotime($output['to']));
      } elseif ($row['event']) {
        $output['event'] = $row['event'];
        $output['eventName'] = $row['event_name'];
        $output['places'] = $row['event_places'];
      }
      $this->_result = $this->_request->convertOutput($output);
    } else {
      // platebni moznosti uzivatele a nastaveni poskytovatele kvuli moznosti zaplatit
      $s1 = new SUser;
      $s1->addStatement(new SqlStatementBi($s1->columns['user_id'], $this->_app->auth->getUserId(), '%s=%s'));
      $s1->addStatement(new SqlStatementBi($s1->columns['registration_provider'], $this->_params['provider'], '%s=%s'));
      $s1->setColumnsMask(array('registration_credit'));
      $res = $this->_app->db->doQuery($s1->toString());
      $row = $this->_app->db->fetchAssoc($res);
      $userCredit = $row['registration_credit'];
      $bUser = new BUser($this->_app->auth->getUserId());
      $userTicket = $bUser->getAvailableTicket($this->_params['provider'], true);

      $providerSettings = BCustomer::getProviderSettings($this->_params['provider'],array('disableCredit','disableTicket','disableOnline'));

      $providerPaymentGateway = BCustomer::getProviderPaymentGateway($this->_params['provider']);

      $s = new SReservation;
      $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_params['provider'], '%s=%s'));
      if (isset($this->_params['center'])&&$this->_params['center']) {
        if (!is_array($this->_params['center'])) $this->_params['center'] = array($this->_params['center']);
        $s->addStatement(new SqlStatementMono($s->columns['center'], sprintf("%%s IN (%s)", $this->_app->db->escapeString(implode(',',$this->_params['center'])))));
      }
      $s->addStatement(new SqlStatementBi($s->columns['user'], $this->_app->auth->getUserId(), '%s=%s'));
      if (!isset($this->_params['past'])||strcmp(strtoupper($this->_params['past']),'Y')) {
        $s->addStatement(new SqlStatementMono($s->columns['cancelled'], '%s IS NULL'));
        $s->addStatement(new SqlStatementMono($s->columns['failed'], '%s IS NULL'));
        $s->addStatement(new SqlStatementMono($s->columns['end'], '%s>NOW()'));
      }
      $s->addOrder(new SqlStatementDesc($s->columns['start']));
      $s->setColumnsMask(array(
        'reservation_id','number','mandatory','created','cancelled','payed','failed','total_price','start','end',
        'event','event_start','event_end','event_name','event_places',
        'resource','resource_from','resource_to','resource_name',
        'fe_allowed_payment_credit','fe_allowed_payment_ticket','fe_allowed_payment_online','center','all_resource_tag','all_event_tag'
      ));
      #error_log($s->toString());
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $line = array('type'=>'reservation','id'=>$row['reservation_id'],'number'=>$row['number']);

        $line['totalPriceRaw'] = $row['total_price'];
        $line['totalPrice'] = sprintf('%s&nbsp;%s', $this->_app->regionalSettings->convertNumberToHuman($row['total_price'],2), $this->_app->textStorage->getText('label.currency_CZK'));

        $line['startRaw'] = $row['start'];
        $line['start'] = $this->_app->regionalSettings->convertDateTimeToHuman($row['start']);
        if (isset($this->_params['format']['datetime'])) $line['start'] = date($this->_params['format']['datetime'], strtotime($line['start']));
        if (substr($row['start'],0,10)==substr($row['end'],0,10)) {
          $line['end'] = $this->_app->regionalSettings->convertTimeToHuman(substr($row['end'],11),'h:m');
          if (isset($this->_params['format']['time'])) $line['end'] = date($this->_params['format']['time'], strtotime($line['end']));
        } else {
          $line['end'] = $this->_app->regionalSettings->convertDateTimeToHuman($row['end']);
          if (isset($this->_params['format']['datetime'])) $line['end'] = date($this->_params['format']['datetime'], strtotime($line['end']));
        }

        if ($row['event']) {
          $line['commodity'] = sprintf('%s - %dx %s - %s', $row['event_name'], $row['event_places'], $line['start'], $line['end']);
        } else {
          $line['commodity'] = sprintf('%s %s - %s', $row['resource_name'], $line['start'], $line['end']);
        }

        $line['mandatory'] = $row['mandatory'];
        $line['created'] = $this->_app->regionalSettings->convertDateTimeToHuman($row['created']);
        if (isset($this->_params['format']['datetime'])) $line['created'] = date($this->_params['format']['datetime'], strtotime($line['created']));
        $line['cancelled'] = $this->_app->regionalSettings->convertDateTimeToHuman($row['cancelled']);
        if ($line['cancelled']&&isset($this->_params['format']['datetime'])) $line['cancelled'] = date($this->_params['format']['datetime'], strtotime($line['cancelled']));
        $line['payed'] = $this->_app->regionalSettings->convertDateTimeToHuman($row['payed']);
        if ($line['payed']&&isset($this->_params['format']['datetime'])) $line['payed'] = date($this->_params['format']['datetime'], strtotime($line['payed']));
        $line['failed'] = $this->_app->regionalSettings->convertDateTimeToHuman($row['failed']);
        if ($line['failed']&&isset($this->_params['format']['datetime'])) $line['failed'] = date($this->_params['format']['datetime'], strtotime($line['failed']));

        // priznak, jestli muze byt rezervace zaplacena
        if (!$line['totalPriceRaw']) $line['readyToPay'] = 0;
        elseif (!$line['payed']) {
          $line['readyToPay'] = ($providerSettings['disableCredit']=='N')&&$row['fe_allowed_payment_credit']&&($userCredit>=$row['total_price']);
          if (!$line['readyToPay']&&($providerSettings['disableTicket']=='N')&&$row['fe_allowed_payment_ticket']) {
            $reservationTags = $row['event']?explode(',',$row['all_event_tag']):explode(',',$row['all_resource_tag']);
            foreach ($userTicket as $ticket) {
              if ($ticket['valueRaw']>$row['total_price']) {
                if ($ticket['validForCenter']&&($row['center']!=$ticket['validForCenter'])) continue;
                if ($ticket['validForTag']) {
                  $ticketTags = explode(',',$ticket['validForTag']);
                  if (!count(array_intersect($reservationTags, $ticketTags))) continue;
                }
                $line['readyToPay'] = true;
                break;
              }
            }
          }
          if (!$line['readyToPay']&&($providerSettings['disableOnline']=='N')&&$row['fe_allowed_payment_credit']) {
            foreach ($providerPaymentGateway as $gw) {
              if (!isset($gw['minimalAmount'])||($row['total_price']>$gw['minimalAmount'])) {
                $line['readyToPay'] = true;
                break;
              }
            }
          }
        }

        $this->_result[] = $this->_request->convertOutput($line);
      }
      
      // jeste nahradniky
      $s = new SEventAttendee;
      $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_params['provider'], '%s=%s'));
      if (isset($this->_params['center'])&&$this->_params['center']) {
        if (!is_array($this->_params['center'])) $this->_params['center'] = array($this->_params['center']);
        $s->addStatement(new SqlStatementMono($s->columns['center'], sprintf("%%s IN (%s)", $this->_app->db->escapeString(implode(',',$this->_params['center'])))));
      }
      $s->addStatement(new SqlStatementBi($s->columns['user'], $this->_app->auth->getUserId(), '%s=%s'));
      if (!isset($this->_params['past'])||strcmp(strtoupper($this->_params['past']),'Y')) $s->addStatement(new SqlStatementMono($s->columns['start'], '%s>NOW()'));
      $s->addStatement(new SqlStatementMono($s->columns['substitute'], "%s='Y'"));
      $s->setColumnsMask(array('eventattendee_id','name','start','end','places','subscription_time'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $start = $this->_app->regionalSettings->convertDateTimeToHuman($row['start']);
        if (isset($this->_params['format']['datetime'])) $start = date($this->_params['format']['datetime'], strtotime($start));
        if (substr($row['start'],0,10)==substr($row['end'],0,10)) {
          $row['end'] = $this->_app->regionalSettings->convertTimeToHuman(substr($row['end'],11),'h:m');
          if (isset($this->_params['format']['time'])) $row['end'] = date($this->_params['format']['time'], strtotime($row['end']));
        } else {
          $row['end'] = $this->_app->regionalSettings->convertDateTimeToHuman($row['end']);
          if (isset($this->_params['format']['datetime'])) $row['end'] = date($this->_params['format']['datetime'], strtotime($row['end']));
        }
        $row['commodity'] = sprintf('%s - %dx %s - %s', $row['name'], $row['places'], $start, $row['end']);
        $row['subscription_time'] = $this->_app->regionalSettings->convertDateTimeToHuman($row['subscription_time']);
        if (isset($this->_params['format']['datetime'])) $row['subscription_time'] = date($this->_params['format']['datetime'], strtotime($row['subscription_time']));
        
        $line = array('type'            => 'substitute',
                      'id'              => $row['eventattendee_id'],
                      'number'          => $this->_app->textStorage->getText('label.ajax_reservation_numberSubstitute'),
                      'startRaw'        => $row['start'],
                      'start'           => $start,
                      'end'             => $row['end'],
                      'commodity'       => $row['commodity'],
                      'created'         => $row['subscription_time'],
                      'totalPrice'      => '---',
                      );
        
        $this->_result[] = $this->_request->convertOutput($line);
      }

      $start = array(); foreach ($this->_result as $key=>$line) $start[$key] = $line['startRaw'];
      array_multisort($start, SORT_DESC, $this->_result);
    }
  }
}

?>
