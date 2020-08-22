<?php

class AjaxGetEvent extends AjaxAction {

  protected function _initDefaultParams() {
    if (isset($this->_params['from'])&&!isset($this->_params['dateMin'])) $this->_params['dateMin'] = $this->_params['from'];
    if (isset($this->_params['to'])&&!isset($this->_params['dateMax'])) $this->_params['dateMax'] = $this->_params['to'];
  }

  protected function _userRun() {  
    if (($id = ifsetor($this->_params['id']))||($asset = ifsetor($this->_params['assetId']))) {
      $s = new SEvent;
      if ($id) $s->addStatement(new SqlStatementBi($s->columns['event_id'], $id, '%s=%s'));
      elseif ($asset) $s->addStatement(new SqlStatementBi($s->columns['external_id'], $asset, '%s=%s'));
      #$s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
      $s->setColumnsMask(array('event_id','name','provider','external_id','description','organiser','organiser_fullname','price','start','end',
                               'free','free_substitute','fe_quick_reservation','fe_allowed_payment','fe_attendee_visible',
                               'max_attendees','max_coattendees','reservation_max_attendees',
                               'repeat_price','repeat_parent','repeat_reservation'));
      $res = $this->_app->db->doQuery($s->toString());
      if (count($this->_app->db->getRowsNumber($res))!=1) return;
      $row = $this->_app->db->fetchAssoc($res);

      $output = array(
        'id'                      => $row['event_id'],
        'assetId'                 => $row['external_id'],
        'name'                    => $row['name'],
        'nameWithStart'           => sprintf('%s - %s', $row['name'], $this->_app->regionalSettings->convertDateTimeToHuman($row['start'])),
        'description'             => formatCommodityDescription($row['description']),
        'start'                   => $this->_app->regionalSettings->convertDateTimeToHuman($row['start']),
        'end'                     => $this->_app->regionalSettings->convertDateTimeToHuman($row['end']),
        'organiserId'             => $row['organiser'],
        'organiserName'           => $row['organiser_fullname'],
        'repeat'                  => $row['repeat_parent'],
        'repeatReservation'       => $row['repeat_reservation'],
        'currency'                => $this->_app->textStorage->getText('label.currency_CZK'),
        'maxAttendees'            => $row['max_attendees'],
        'coAttendees'             => $row['max_coattendees'],
        'free'                    => $row['free'],
        'freeSubstitute'          => $row['free_substitute'],
        'quickReservation'        => $row['fe_quick_reservation'],
        'allowedPayment'          => $row['fe_allowed_payment'],
        'reservationMaxAttendees' => $row['reservation_max_attendees'],
        'reserved'                => 0,
        'attendee'                => array(),
        'substitute'              => array(),
        );

      $providerSettings = BCustomer::getProviderSettings($row['provider'],array('disableCredit','disableTicket','disableOnline'));
      if ($providerSettings['disableCredit']=='Y') $output['allowedPayment'] -= 1;
      if ($providerSettings['disableTicket']=='Y') $output['allowedPayment'] -= 10;
      if ($providerSettings['disableOnline']=='Y') $output['allowedPayment'] -= 100;
      
      $output['price'] = !strcmp($row['repeat_reservation'],'PACK')?$row['repeat_price']:$row['price'];
      $output['repeatPrice'] = 0;
      if ($output['price']) $priceHtml = sprintf('%s %s', $output['price'], $this->_app->textStorage->getText('label.currency_CZK'));
      else $priceHtml = $this->_app->textStorage->getText('label.ajax_price_free_of_charge');
      if ($row['repeat_parent']) {
        if (!strcmp($row['repeat_reservation'],'BOTH')) {
          if ($row['repeat_price']) $priceHtml .= sprintf(' (%s %s %s)', $this->_app->textStorage->getText('label.editReservation_commodityRepeatPrice'), $row['repeat_price'], $this->_app->textStorage->getText('label.currency_CZK'));
          else $priceHtml .= sprintf(' (%s %s)', $this->_app->textStorage->getText('label.editReservation_commodityRepeatPrice'), $this->_app->textStorage->getText('label.ajax_price_free_of_charge'));
        }
        $output['repeatPrice'] = $row['repeat_price'];
      }
      $output['priceHtml'] = $priceHtml;

      $bEvent = new BEvent($row['event_id']);
      $output['paymentNeeded'] = $bEvent->isPaymentNeeded();
      
      if ($row['repeat_parent']) {
        $s = new SEvent;
        $s->addStatement(new SqlStatementBi($s->columns['repeat_parent'], $row['repeat_parent'], '%s=%s'));
        $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
        $s->setColumnsMask(array('event_id'));
        $res = $this->_app->db->doQuery($s->toString());
        $output['repeatCount'] = $this->_app->db->getRowsNumber($res);
      }

      if (($row['fe_attendee_visible']!='N')&&
          ($this->_app->auth->getUserId()||($row['fe_attendee_visible']=='Y'))) {
        $s = new SEventAttendeePerson;
        if ($id) $s->addStatement(new SqlStatementBi($s->columns['event'], $id, '%s=%s'));
        elseif ($asset) $s->addStatement(new SqlStatementBi($s->columns['external_id'], $asset, '%s=%s'));
        $s->addStatement(new SqlStatementMono($s->columns['substitute'], "%s='N'"));
        $s->addOrder(new SqlStatementAsc($s->columns['firstname']));
        $s->setColumnsMask(array('eventattendee','user','places','reservation','failed','reservation_event_pack','reservation_payed','reservation_failed',
          'firstname','lastname','email','subaccount','subaccount_firstname','subaccount_lastname','subaccount_email',));
        $res = $this->_app->db->doQuery($s->toString());
        while ($row = $this->_app->db->fetchAssoc($res)) {
          if ($row['user']==$this->_app->auth->getUserId()) $output['reserved'] = 1;
          $output['attendee'][] = array(
            'id'            => $row['eventattendee'],
            'name'          => sprintf('%s %s', $row['subaccount']?$row['subaccount_firstname']:$row['firstname'], $row['subaccount']?$row['subaccount_lastname']:$row['lastname']),
            'email'         => $row['subaccount']?$row['subaccount_email']:$row['email'],
            'places'        => $row['places'],
            'user'          => $row['user'],
            'reservation'   => $row['reservation'],
            'failed'        => $row['failed']?$row['failed']:$row['reservation_failed'],
            'payed'         => $row['reservation_payed'],
            'eventPack'     => $row['reservation_event_pack']
          );
        }
        $s = new SEventAttendeePerson;
        if ($id) $s->addStatement(new SqlStatementBi($s->columns['event'], $id, '%s=%s'));
        elseif ($asset) $s->addStatement(new SqlStatementBi($s->columns['external_id'], $asset, '%s=%s'));
        $s->addStatement(new SqlStatementMono($s->columns['substitute'], "%s='Y'"));
        $s->addOrder(new SqlStatementAsc($s->columns['firstname']));
        $s->setColumnsMask(array('eventattendee','user','firstname','lastname','email','places','reservation'));
        $res = $this->_app->db->doQuery($s->toString());
        while ($row = $this->_app->db->fetchAssoc($res)) {
          $output['substitute'][] = array(
            'id'            => $row['eventattendee'],
            'name'          => sprintf('%s %s', $row['firstname'], $row['lastname']),
            'email'         => $row['email'],
            'places'        => $row['places'],
            'reservation'   => $row['reservation']
          );
        }
      }

      $this->_result = $this->_request->convertOutput($output);
    } elseif (isset($this->_params['provider'])||isset($this->_params['center'])) {
      $s = new SEvent;
      if (isset($this->_params['provider'])) $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_params['provider'], '%s=%s'));
      if (!isset($this->_params['inactive'])||($this->_params['inactive']!='Y')) $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
      if (isset($this->_params['active'])&&($this->_params['active']=='N')) $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='N'"));
      if (isset($this->_params['center'])) {
        if (is_array($this->_params['center'])) {
          $expr = '';
          foreach ($this->_params['center'] as $center) {
            if ($expr) $expr .= ',';
            $expr .= $this->_app->db->escapeString($center);
          }
          $s->addStatement(new SqlStatementMono($s->columns['center'], sprintf('%%s IN (%s)', $expr)));
        } else $s->addStatement(new SqlStatementBi($s->columns['center'], $this->_params['center'], '%s=%s'));
      }
      if (isset($this->_params['resource'])) $s->addStatement(new SqlStatementBi($s->columns['resource'], $this->_params['resource'], '%s=%s'));
      if (isset($this->_params['name'])) $s->addStatement(new SqlStatementMono($s->columns['name'], sprintf("LOWER(%%s) LIKE '%%%%%s%%%%'", $this->_app->db->escapeString($this->_params['name']))));
      #if (isset($this->_params['tag'])) $s->addStatement(new SqlStatementMono($s->columns['tag'], sprintf('%%s IN (%s)', $this->_params['tag'])));
      if (isset($this->_params['tag'])) {
        $s->addStatement(new SqlStatementMono($s->columns['tag_count'], '%s>0'));
        $s->sTag->addStatement(new SqlStatementMono($s->sTag->columns['tag'], sprintf('%%s IN (%s)', $this->_app->db->escapeString($this->_params['tag']))));
      }
      if (isset($this->_params['dateMin'])) {
        if ($this->_app->regionalSettings->checkHumanDate($this->_params['dateMin'])) {
          $s->addStatement(new SqlStatementBi($s->columns['start'], $this->_app->regionalSettings->convertHumanToDate($this->_params['dateMin']), '%s>=%s'));
        } elseif ($this->_app->regionalSettings->checkDate($this->_params['dateMin'])) {
          $s->addStatement(new SqlStatementBi($s->columns['start'], $this->_params['dateMin'], '%s>=%s'));
        }
      }
      if (isset($this->_params['dateMax'])) {
        if ($this->_app->regionalSettings->checkHumanDate($this->_params['dateMax'])) {
          $s->addStatement(new SqlStatementBi($s->columns['end'], $this->_app->regionalSettings->convertHumanToDate($this->_params['dateMax']) . ' 23:59:59', '%s<=%s'));
        } elseif ($this->_app->regionalSettings->checkDate($this->_params['dateMax'])) {
          $s->addStatement(new SqlStatementBi($s->columns['end'], $this->_params['dateMax'] . ' 23:59:59', '%s<=%s'));
        }
      }
      $s->addOrder(new SqlStatementAsc($s->columns['start']));
      $s->addOrder(new SqlStatementAsc($s->columns['name']));
      $s->setColumnsMask(array('event_id','name', 'start', 'end', 'all_resource_name'));
      $res = $this->_app->db->doQuery($s->toString());
      $this->_result = array();
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $row['id'] = $row['event_id'];
        $row['nameWithStart'] = sprintf('%s - %s', $row['name'], $this->_app->regionalSettings->convertDateTimeToHuman($row['start']));
        $row['info'] = sprintf('%s %s- %s', $row['name'], $row['all_resource_name']?' ('.$row['all_resource_name'].') ':'', $this->_app->regionalSettings->convertDateTimeToHuman($row['start']));
        $this->_result[] = $this->_request->convertOutput($row);
      }
    }
  }
}

?>
