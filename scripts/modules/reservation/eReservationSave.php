<?php

class ModuleReservationSave extends ExecModule {

  private function _getCommodityTag($vData) {
    $ret = array();
    
    if (!strcmp($vData['commodity'],'event')) {
      $s = new SEventTag;
      $s->addStatement(new SqlStatementBi($s->columns['event'], $vData['eventId'], '%s=%s'));
    } elseif (!strcmp($vData['commodity'],'resource')) {
      $s = new SResourceTag;
      $s->addStatement(new SqlStatementBi($s->columns['resource'], $vData['resourceId'], '%s=%s'));
    }
    if (isset($s)) {
      $s->setColumnsMask(array('tag'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $ret[] = $row['tag'];
      }
    }
    
    return $ret;
  }
  
  private function _getEventAttendee($vData) {
    $ret = array();
    
    foreach ($vData['eventAttendeeFirstname'] as $index=>$value) {
      $ret[$index] = array(
                'id'          => $vData['eventAttendeePersonId'][$index],
                'user'        => $vData['eventAttendeeUser'][$index],
                'firstname'   => $vData['eventAttendeeFirstname'][$index],
                'lastname'    => $vData['eventAttendeeLastname'][$index],
                'email'       => $vData['eventAttendeeEmail'][$index],
                );
    }
    
    return $ret;
  }
  
  protected function _userRun() {
    $validator = Validator::get('reservation','ReservationValidator');
    $validator->initValues();
    #adump($validator->getValues());
    
    parseNextActionFromRequest($nextAction, $nextActionParams);

    switch ($nextAction) {
      case 'newUser':
        $this->_app->response->addParams(array('fromReservation'=>1));
        return 'eUserEdit';
      case 'reload':
        $this->_app->response->addParams(array('backwards'=>1));
        return 'eBack';
      default: break;
    }
    
    $validator->validateValues();
    $vData = $validator->getValues();

    if (!$vData['userName']) {
      // vynulovani uzivatele
      $vData['userId'] = '';
      $vData['userNameSelected'] = '';
    }
    if ($vData['userName']!=$vData['userNameSelected']) throw new ExceptionUserTextStorage('error.editReservation_unknownUser');
    
    if (!strcmp($vData['commodity'],'resource')) {
      if (!$vData['resourceId']) throw new ExceptionUserTextStorage('error.editReservation_missingResource');
      if (!$vData['resourceFrom']) throw new ExceptionUserTextStorage('error.editReservation_missingResourceDateFrom');
      if (!$vData['resourceTo']) throw new ExceptionUserTextStorage('error.editReservation_missingResourceDateTo');
    } elseif (!strcmp($vData['commodity'],'event')) {
      if (!$vData['eventId']) throw new ExceptionUserTextStorage('error.editReservation_missingEvent');
      if (!$vData['eventPlaces']) throw new ExceptionUserTextStorage('error.editReservation_missingEventPlaces');
    }

    if (!$vData['payed']) {
      $data = array(
          'userId'                => $vData['userId'],
          'customerId'            => $vData['customerId'],
          'mandatory'             => $vData['mandatory'],
          'attribute'             => $vData['attribute'],
          'note'                  => $vData['note'],
          );

      $eventInPast = false;
      $eventPackInPast = false;
      // kdyz je opakujici se akce a je moznost rezervovat i cele opakovani, nabidne se tato moznost
      if (!strcmp($vData['commodity'],'event')) {
        $s = new SEvent;
        $s->addStatement(new SqlStatementBi($s->columns['event_id'], $vData['eventId'], '%s=%s'));
        $s->setColumnsMask(array('start','repeat_parent','repeat_index','repeat_reservation'));
        $res = $this->_app->db->doQuery($s->toString());
        $row = $this->_app->db->fetchAssoc($res);
        $eventInPast = $row['start']<date('Y-m-d H:i:s');

        if ($row['repeat_parent']) {
          // pripravim text pro vsechna opakovani akce
          // a zjistim, jestli nejake opakovani neni v minulosti
          $repeatHtml = '';
          $repeatPriceHtml = '';
          $singlePriceHtml = '';
          $s1 = new SEvent;
          $s1->addStatement(new SqlStatementBi($s1->columns['repeat_parent'], $row['repeat_parent'], '%s=%s'));
          #$s1->addStatement(new SqlStatementBi($s1->columns['repeat_index'], $row['repeat_index'], '%s>=%s'));
          #$s1->addStatement(new SqlStatementMono($s1->columns['active'], "%s='Y'"));
          $s1->addOrder(new SqlStatementAsc($s1->columns['start']));
          $s1->setColumnsMask(array('name','start','price','repeat_price'));
          $res1 = $this->_app->db->doQuery($s1->toString());
          while ($row1 = $this->_app->db->fetchAssoc($res1)) {
            if (!$singlePriceHtml) $singlePriceHtml = sprintf('<div class="price">%s: %s %s</div>', $this->_app->textStorage->getText('label.editEvent_singlePrice'), $row1['price'], $this->_app->textStorage->getText('label.currency_CZK'));
            if (!$repeatPriceHtml) $repeatPriceHtml = sprintf('<div class="price">%s: %s %s</div>', $this->_app->textStorage->getText('label.editEvent_repeatPrice'), $row1['repeat_price'], $this->_app->textStorage->getText('label.currency_CZK'));

            $repeatHtml .= sprintf('<div class="event">%s - %s</div>', $row1['name'], $this->_app->regionalSettings->convertDateTimeToHuman($row1['start']));

            if (!$eventPackInPast) $eventPackInPast = $row1['start']<date('Y-m-d H:i:s');
          }

          if (!$vData['eventPack']) {
            if (strcmp($row['repeat_reservation'],'SINGLE')) {
              if (!strcmp($row['repeat_reservation'],'BOTH')) {
                $this->_app->dialog->set(array(
                  'width'     => 500,
                  'template'  => sprintf('
                        <div class="message">{__label.editReservation_selectPackReservation}%s%s%s</div>
                        %s
                        <div class="button">
                          <input type="button" class="ui-button inputSubmit" name="save" value="{__button.editReservation_saveSingle}" onclick="document.getElementById(\'fb_eReservationSaveSingle\').click();"/>
                          <input type="button" class="ui-button inputSubmit" name="save" value="{__button.editReservation_savePack}" onclick="document.getElementById(\'fb_eReservationSavePack\').click();"/>
                        </div>', $repeatHtml, $singlePriceHtml, $repeatPriceHtml,
                    ($this->_app->request->getParams('pay')=='Y')?'<input type="hidden" name="pay" value="Y"/>':''),
                ));
              } elseif (!strcmp($row['repeat_reservation'],'PACK')) {
                $this->_app->dialog->set(array(
                  'width'     => 500,
                  'template'  => sprintf('
                        <div class="message">{__label.editReservation_confirmPackReservation}%s%s</div>
                        %s
                        <div class="button">
                          <input type="button" class="ui-button inputSubmit" name="save" value="{__button.editReservation_savePack}" onclick="document.getElementById(\'fb_eReservationSavePack\').click();"/>
                        </div>', $repeatHtml, $repeatPriceHtml,
                    ($this->_app->request->getParams('pay')=='Y')?'<input type="hidden" name="pay" value="Y"/>':''),
                ));
              }

              $this->_app->response->addParams(array('backwards'=>1));
              return 'eBack';
            }
          }
        }
        if (!$vData['eventPack']) $vData['eventPack'] = 'N';
      }

      if (!strcmp($vData['commodity'],'resource')) {
        $data['resourceParams'] = array(
            'resourceId'          => $vData['resourceId'],
            'resourceFrom'        => $this->_app->regionalSettings->convertHumanToDateTime($vData['resourceFrom']),
            'resourceTo'          => $this->_app->regionalSettings->convertHumanToDateTime($vData['resourceTo']),
            );
      } elseif (!strcmp($vData['commodity'],'event')) {
        $data['eventParams'] = array(
            'eventId'             => $vData['eventId'],
            'eventPlaces'         => $vData['eventPlaces'],
            'eventPack'           => $vData['eventPack'],
            'eventAttendeePerson' => $this->_getEventAttendee($vData),
            );
        if ($vData['eventPack']=='Y') $data['eventParams']['eventPackId'] = $vData['eventPackId'];
      }

      // kdyz ma byt rezervovano neco v minulosti, musi se to potvrdit
      $inPast = false;
      $now = date('Y-m-d H:i:s');
      if (!strcmp($vData['commodity'],'resource')) {
        $inPast = ($data['resourceParams']['resourceFrom']<$now)||($data['resourceParams']['resourceTo']<$now);
      } elseif (!strcmp($vData['commodity'],'event')) {
        $inPast = $eventInPast||(($data['eventParams']['eventPack']=='Y')&&$eventPackInPast);
      }
      if ($inPast) {
        if (!$vData['confirmPast']) {
          $this->_app->dialog->set(array(
                'width'     => 380,
                'template'  => sprintf('
                    <div class="message">{__label.editReservation_confirmPast}</div>
                    <input type="hidden" name="confirmPast" value="1"/>
                    <div class="button">
                      <input type="button" class="ui-button inputSubmit" name="save" value="{__button.editReservation_confirmPast}" onclick="document.getElementById(\'%s\').click();"/>
                    </div>', ($this->_app->request->getParams('pay')=='Y')?'fb_eReservationSavePayNoConfirm':'fb_eReservationSave'),
              ));

          $this->_app->response->addParams(array('backwards'=>1));
          return 'eBack';
        } else {
          $data['allowPast'] = true;
        }
      }

      $bReservation = new BReservation($vData['id']?$vData['id']:null);
      $preData = $bReservation->getStatus($data);

      if ($vData['priceManual']) {
        $data['priceManual'] = true;
        if ($vData['priceNew']) $data['price'] = $vData['price'];
        $preData['price'] = $vData['price'];
        $data['priceComment'] = $vData['priceComment']?$vData['priceComment']:null;
      } else $data['priceManual'] = false;

      if ($vData['pay']) {
        if ($vData['userId']) {
          // zjistim, jestli nema uzivatel pouzitelne slevove kody
          $bUser = new BUser($vData['userId']);
          $voucher = $bUser->getAvailableVoucher($preData['provider'], $preData['price']+$preData['voucherDiscount'], $preData['center'], $this->_getCommodityTag($vData));
          if ($vData['voucher']&&isset($voucher[$vData['voucher']])) {
            // kdyz je vybrany voucher, upravim cenu pro zaplaceni
            $preData['price'] += $preData['voucherDiscount'];
            $preData['price'] -= $voucher[$vData['voucher']]['calculatedDiscountRaw'];
          }
        } else $voucher = array();

        $data['pay'] = 'Y';
        $data['payType'] = $vData['payType'];
        $data['payArrangeCredit'] = $vData['payArrangeCredit'];
        if ($vData['payArrangeCreditAmount']=='Y') $data['payArrangeCreditAmount'] = $preData['price'];
        $data['payTicket'] = $vData['payTicket'];
        $data['voucher'] = $vData['voucher'];

        // kdyz se ma platit rezervace uzivatele (ne-anonymni) a jeste neni vybrano jak
        if ($vData['userId']&&!$vData['payType']) {
          $ticket = $bUser->getAvailableTicket($preData['provider'], true, $preData['center'], $this->_getCommodityTag($vData), $preData['price']);

          $providerSettings = BCustomer::getProviderSettings($preData['provider'],array('disableCredit','disableTicket','disableCash'));

          // kontrola, jestli uzivatel ma dost penez
          $s = new SUserRegistration;
          $s->addStatement(new SqlStatementBi($s->columns['user'], $vData['userId'], '%s=%s'));
          $s->addStatement(new SqlStatementBi($s->columns['provider'], $preData['provider'], '%s=%s'));
          $s->setColumnsMask(array('credit'));
          $res = $this->_app->db->doQuery($s->toString());
          $row = $this->_app->db->fetchAssoc($res);

          $credit = isset($row['credit'])?$row['credit']:0;
          $missing = $preData['price']-$credit;

          $template = '';
          $button = '';

          // kdyz ma dostupne vouchery, nabidnou se
          if (count($voucher)) {
            $template .= '<div class="message">{__label.editReservation_voucher}: <select name="voucher" onchange="document.getElementById(\'fb_eReservationSavePayNoConfirm\').click();"><option value="">{__label.select_choose}</option>';
            foreach ($voucher as $v) {
              $template .= sprintf('<option value="%s"%s>%s / %s %s</option>', $v['id'], ($v['id']==$vData['voucher'])?' selected="yes"':'', $v['code'], $v['calculatedDiscount'], $v['currency']);
            }
            $template .= '</select></div>';
            if ($vData['voucher']) $template .= sprintf('<div class="message bold">{__label.editReservation_priceWithVoucher}: %s {__label.currency_CZK}</div>', $preData['price']);
          }

          // kdyz ma dostupne permanentky, nabidnou se
          if (count($ticket)&&($providerSettings['disableTicket']=='N')) {
            $template .= '<div class="message">{__label.editReservation_payTicket}: <select name="payTicket">';
            foreach ($ticket as $t) {
              $template .= sprintf('<option value="%s">%s / %s %s</option>', $t['id'], $t['name'], $t['value'], $t['currency']);
            }
            $template .= '</select></div>';
            $button .= '<input type="button" class="ui-button inputSubmit" name="save" value="{__button.editReservation_savePayTicket}" onclick="document.getElementById(\'fb_eReservationSavePayTicket\').click();"/>';
          }

          if ($credit&&($providerSettings['disableCredit']=='N')) {
            if ($missing > 0) {
              // kdyz ma nedostatecny kredit, tak se nabidne moznost dobit kredit a zaplatit z kreditu
              $lowCredit = sprintf($this->_app->textStorage->getText('label.editReservation_lowCredit'),
                $this->_app->regionalSettings->convertNumberToHuman($credit, 2), $this->_app->textStorage->getText('label.currency_CZK'));
              #if ($template) $template .= '<br/><br/>';
              $template .= sprintf('<div class="message">%s</div>', $lowCredit);
              $buttonLabel = sprintf($this->_app->textStorage->getText('button.editReservation_savePayArrangeCredit'), $this->_app->regionalSettings->convertNumberToHuman($missing, 2));
              $button .= sprintf('<input type="button" class="ui-button inputSubmit" name="save" value="%s" onclick="document.getElementById(\'fb_eReservationSavePayArrangeCredit\').click();"/>', $buttonLabel);
            } else {
              // kdyz ma dostatecny kredit, tak se nabidne moznost zaplatit z kreditu
              $button .= '<input type="button" class="ui-button inputSubmit" name="save" value="{__button.editReservation_savePayCredit}" onclick="document.getElementById(\'fb_eReservationSavePayCredit\').click();"/>';
            }
          }

          if ($providerSettings['disableCash']=='N') {
            // moznost zaplatit "hotove" (pres kredit)
            $buttonLabel = sprintf($this->_app->textStorage->getText('button.editReservation_savePayCash'), $this->_app->regionalSettings->convertNumberToHuman($preData['price'], 2));
            $button .= sprintf('<input type="button" class="ui-button inputSubmit" name="save" value="%s" onclick="document.getElementById(\'fb_eReservationSavePayCash\').click();"/>', $buttonLabel);
          }

          $this->_app->dialog->set(array(
                'width'     => 480,
                'template'  => sprintf('%s<div class="message bold">{__label.editReservation_savePay_choose}:</div><div class="button">%s</div>', $template, $button),
              ));

          $this->_app->response->addParams(array('backwards'=>1));
          return 'eBack';
        }
      }

      if (BCustomer::getProviderSettings($preData['provider'],'allowSkipReservationCondition')=='Y') {
        if (!$vData['skipCondition']) {
          try {
            $bReservation->expandSaveParams($data);
            $bReservation->checkReservationCondition($data, true);
          } catch (ExceptionUser $e) {
            $template = '<div class="message"><b>{__label.editReservation_save_conditions}</b>:</div>' . $e->printMessage() .
              '<br/><br/><input type="button" class="ui-button inputSubmit" name="save" value="{__button.editReservation_saveSkipCondition}" onclick="document.getElementById(\'fb_eReservationSaveSkipCondition\').click();"/>';

            $this->_app->dialog->set(array(
              'width' => 480,
              'template' => $template,
            ));

            $this->_app->response->addParams(array('backwards' => 1));
            return 'eBack';
          }
        } else {
          $data['skipCondition'] = $vData['skipCondition'];
        }
      }
    } else {
      // kdyz je rezervace zaplacena, omezim, co se bude ukladat
      $data = array(
        'mandatory'             => $vData['mandatory'],
        'attribute'             => $vData['attribute'],
        'note'                  => $vData['note'],
      );

      if (!strcmp($vData['commodity'], 'event')) {
        $data['eventParams'] = array(
          'eventAttendeePerson' => $this->_getEventAttendee($vData),
        );
      } else {
        $data['resourceParams'] = array(
          'resourceId'      => $vData['resourceId'],
          'resourceFrom'    => $this->_app->regionalSettings->convertHumanToDateTime($vData['resourceFrom']),
          'resourceTo'      => $this->_app->regionalSettings->convertHumanToDateTime($vData['resourceTo'])
        );
      }

      $bReservation = new BReservation($vData['id']?$vData['id']:null);

      #error_log(var_export($data, true));
      #throw new ExceptionUser('Saving paid reservation...');
    }

    #adump($data);
    #throw new ExceptionUser('Saving...');
    $number = $bReservation->save($data);
    
    // kdyz se vytvarela rezervace z nahradnika, tak ho smazu
    if ($vData['fromSubstitute']) {
      $bEvent = new BEvent($vData['eventId']);
      $bEvent->deleteSubstitute(array('eventAttendeeId'=>$vData['fromSubstitute']));
    }
    
    $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.editReservation_saveOk'), $number));

    return 'eBack';
  }
}

?>
