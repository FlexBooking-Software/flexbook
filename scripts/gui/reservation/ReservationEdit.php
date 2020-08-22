<?php

class GuiEditReservation extends GuiElement {
  private $_readonly = false;
  private $_readonlyPaid = false;

  private function _insertNumber($data) {
    if (!$data['id']) {
      $this->insertTemplateVar('fi_number', '');
    } else {
      $template = sprintf('
        <div class="formItem">
          <label class="bold">%s:</label>
          <input class="mediumText" type="text" readonly="yes" name="num" value="%s"/>
        </div>
        <br />', $this->_app->textStorage->getText('label.editReservation_number'), $data['number']);
      $this->insertTemplateVar('fi_number', $template, false);
    }
  }

  private function _insertMandatorySelect($data) {
    if (BCustomer::getProviderSettings($data['providerId'], 'allowMandatoryReservation')=='Y') {
      $ds = new HashDataSource(new DataSourceSettings, array('N'=>$this->_app->textStorage->getText('label.no'),'Y'=>$this->_app->textStorage->getText('label.yes')));
      $this->insert(new GuiFormSelect(array(
        'id' => 'fi_mandatory',
        'name' => 'mandatory',
        'label' => $this->_app->textStorage->getText('label.editReservation_mandatory'),
        'dataSource' => $ds,
        'readonly' => $this->_readonly,
        'value' => $data['mandatory'],
        'firstOption' => Application::get()->textStorage->getText('label.select_choose'),
        'userTextStorage' => false)), 'fi_mandatory');
    } else $this->insertTemplateVar('fi_mandatory', '<input type="hidden" name="mandatory" value="N" />', false);
  }
  
  private function _insertPrice($data) {
    $template = '<br/>';

    if ($data['voucherDiscount']) {
      $template .= sprintf('
        <div class="formItem">
          <label class="bold">%s:</label>
          <input type="hidden" id="fi_voucherDiscount" data-meaning="%s" value="%s"/>
          %s (%s %s %s)
        </div>', $this->_app->textStorage->getText('label.editReservation_voucherInfo'), $data['voucherDiscountType'], $data['voucherDiscountValue'], $data['voucherCode'],
        $this->_app->textStorage->getText('label.editReservation_voucherDiscount'),
        $this->_app->regionalSettings->convertNumberToHuman($data['voucherDiscount'],2), $this->_app->textStorage->getText('label.currency_CZK'));
    } else {
      $template .= '<input type="hidden" id="fi_voucherDiscount" value="0"/>';
    }

    $template .= sprintf('
      <div class="formItem">
        <label class="bold">%s:</label>
        <input id="fi_priceManual" type="hidden" name="priceManual" value="%s"/>
        <input id="fi_priceNew" type="hidden" name="priceNew" value="%s"/>
        <input id="fi_price" class="shortText" type="text" name="price" value="%s"%s/>
        %s
        <input class="button" id="fb_priceAuto" type="button" value="%s"%s/>
      </div>', $this->_app->textStorage->getText('label.editReservation_price'),
      $data['priceManual'], $data['priceNew'], $data['price'],
      $this->_readonly||$this->_readonlyPaid?' readonly="yes"':'',
      $this->_app->textStorage->getText('label.currency_CZK'),
      $this->_app->textStorage->getText('button.editReservation_priceAuto'),
      $data['priceManual']&&!$this->_readonly&&!$this->_readonlyPaid?'':' style="display:none;"');
    
    $template .= sprintf('
      <div class="formItem" id="fi_priceCommentDiv"%s>
        <label>%s:</label>
        <textarea id="fi_priceComment" name="priceComment"%s>%s</textarea>
      </div>',
      $data['priceManual']?'':' style="display:none;"',
      $this->_app->textStorage->getText('label.editReservation_priceComment'),
      $this->_readonly||$this->_readonlyPaid?' readonly="yes"':'',
      $data['priceComment']);
    
    $this->insertTemplateVar('fi_price', $template, false);
  }
  
  private function _insertState($data) {
    if (!$data['id']) {
      $this->insertTemplateVar('fi_state', '');
    } else {
      if ($data['failed']) $state = $this->_app->textStorage->getText('label.editReservation_stateFAILED');
      elseif ($data['cancelled']) $state = $this->_app->textStorage->getText('label.editReservation_stateCANCELLED');
      else $state = $this->_app->textStorage->getText('label.editReservation_stateCREATED');
      
      if ($data['payed']) $state .= $this->_app->textStorage->getText('label.editReservation_statePAYED');

      // nactu aktualni journal (nebudu zobrazovat s validatoru, to je kvuli eBack u propadnuti a zruseni rezervace opakovani)
      $data['journal'] = array();
      $s = new SReservationJournal;
      $s->addStatement(new SqlStatementBi($s->columns['reservation'], $data['id'], '%s=%s'));
      $s->setColumnsMask(array('change_timestamp','action','fullname','note'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $data['journal'][] = array('timestamp'=>$row['change_timestamp'],'action'=>$row['action'],'user'=>$row['fullname'],'note'=>$row['note']);
      }

      $history = '<table>';
      foreach ($data['journal'] as $index=>$j) {
        if (!strcmp($j['action'],'SAVE')&&$index) $j['action'] = 'UPDATE';
        elseif (!strcmp($j['action'],'PAY')) {
          $note = explode('|',$j['note']);
          $j['note'] = $this->_app->textStorage->getText('label.ajax_reservation_payment_'.$note[0]);
          if (isset($note[1])) $j['note'] .= sprintf('<br/>%s', $note[1]);
        }
        
        $history .= sprintf('<tr><td>%s</td><td>%s</td><td>%s<td><td>%s</td>', $this->_app->regionalSettings->convertDateTimeToHuman($j['timestamp']),
                            $j['user'], $this->_app->textStorage->getText('label.editReservation_journalAction_'.$j['action']), $j['note']);
      }
      $history .= '</table>';
      
      $template = sprintf('
        <br />
        <div class="formItem">
          <label>%s:</label>
          <label class="asInput">%s</label>
        </div>
        <br/><br/>
        <div class="formItem">
          <label>%s:</label>
          <div class="journal">%s</div>
        </div>
        <br/>',
        $this->_app->textStorage->getText('label.editReservation_state'), $state,
        $this->_app->textStorage->getText('label.editReservation_journal'), $history);
      $this->insertTemplateVar('fi_state', $template, false);
    }
  }
  
  private function _insertCustomerSelect($data) {
    $select = new SCustomer;
    $select->setColumnsMask(array('customer_id','name'));
    $ds = new SqlDataSource(new DataSourceSettings, $select);
    $this->insert(new GuiFormSelect(array(
            'id' => 'fi_custmer',
            'name' => 'customerId',
            'dataSource' => $ds,
            'value' => $data['customerId'],
            'readonly' => $data['failed']||$data['cancelled'],
            'firstOption' => Application::get()->textStorage->getText('label.select_choose'),
            'userTextStorage' => false,
            'showDiv' => false)), 'fi_customer');
  }
  
  private function _insertResourceSelect($data) {
    $select = new SResource;
    $select->setColumnsMask(array('resource_id','name'));
    $select->addStatement(new SqlStatementMono($select->columns['active'], "%s='Y'"));
    if (!$this->_app->auth->isAdministrator()) {
      $select->addStatement(new SqlStatementBi($select->columns['provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
      $select->addStatement(new SqlStatementMono($select->columns['center'], sprintf('%%s IN (%s)', $this->_app->auth->getAllowedCenter('list'))));
      if ($this->_app->auth->getActualCenter()) $select->addStatement(new SqlStatementBi($select->columns['center'], $this->_app->auth->getActualCenter(), '%s=%s'));
    }
    $select->addOrder(new SqlStatementAsc($select->columns['name']));
    $res = $this->_app->db->doQuery($select->toString());
    $hash = array();
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $hash[$row['resource_id']] = $row['name'];
    }
    
    if ($data['failed']||$data['cancelled']||
        ($data['resourceId']&&!in_array($data['resourceId'],array_keys($hash)))) {
      $this->insert(new GuiFormInput(array(
              'id' => 'fi_resource',
              'type' => 'hidden',
              'name' => 'resourceId',
              'value' => $data['resourceId'],
              'readonly' => true,
              'showDiv' => false, 
              'userTextStorage' => false)), 'fi_resource');
      if ($data['resourceId']) {
        $this->insert(new GuiFormInput(array(
              'name' => 'resourceName',
              'classLabel' => 'bold',
              'label' => $this->_app->textStorage->getText('label.editReservation_resource'),
              'value' => $data['resourceName'],
              'readonly' => true,
              'userTextStorage' => false)), 'fi_resource');
      } else $this->insertTemplateVar('fi_resource', '');
    } else {
      $ds = new HashDataSource(new DataSourceSettings, $hash);
      $this->insert(new GuiFormSelect(array(
              'id' => 'fi_resource',
              'name' => 'resourceId',
              'classLabel' => 'bold',
              'label' => $this->_app->textStorage->getText('label.editReservation_resource'),
              'dataSource' => $ds,
              'value' => $data['resourceId'],
              'readonly' => $data['failed']||$data['cancelled'],
              'firstOption' => Application::get()->textStorage->getText('label.select_choose'),
              'userTextStorage' => false)), 'fi_resource');
    }
  }
  
  private function _insertEventSelect($data) {
    if ($data['eventPackStart']&&is_array($data['eventPackStart'])) {
      $this->insertTemplateVar('eventPackStyle', '');
      
      $html = '';
      foreach ($data['eventPackStart'] as $id=>$start) {
        $html .= sprintf('<div class="eventPackItem%s">', $data['eventPackFailed'][$id]?' eventPackItemFailed':'');
        if (!$data['failed']&&!$data['cancelled']&&!$data['eventPackFailed'][$id]) {
          $html .= sprintf('<input class="button" type="submit" name="action_eReservationCancelEventPackItem?eventPackItem=%s" value="%s" onclick="return confirm(\'%s\');"/>&nbsp;',
            $id, $this->_app->textStorage->getText('button.grid_cancel'),
            $this->_app->textStorage->getText('label.editReservation_confirmCancelPackItem'));
          $html .= sprintf('<input class="button" type="submit" name="action_eReservationFailEventPackItem?eventPackItem=%s" value="%s" onclick="return confirm(\'%s\');"/>&nbsp;',
            $id, $this->_app->textStorage->getText('button.grid_fail'),
            $this->_app->textStorage->getText('label.editReservation_confirmFailPackItem'));
        }
        $html .= sprintf('<div style="float:left;">%s</div>', $this->_app->regionalSettings->convertDateTimeToHuman($start));
        $html .= '</div>';
      }
      $this->insertTemplateVar('eventPackHtml', $html, false);
    } else {
      $this->insertTemplateVar('eventPackStyle', 'style="display: none;"', false);
    }
    
    $select = new SEvent;
    $select->setColumnsMask(array('event_id','name', 'free', 'start'));
    $select->addStatement(new SqlStatementMono($select->columns['active'], "%s='Y'"));
    if (!$this->_app->auth->isAdministrator()) {
      $select->addStatement(new SqlStatementBi($select->columns['provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
      $select->addStatement(new SqlStatementMono($select->columns['center'], sprintf('%%s IN (%s)', $this->_app->auth->getAllowedCenter('list'))));
      if ($this->_app->auth->getActualCenter()) $select->addStatement(new SqlStatementBi($select->columns['center'], $this->_app->auth->getActualCenter(), '%s=%s'));
    }
    $select->addOrder(new SqlStatementAsc($select->columns['start']));
    $res = $this->_app->db->doQuery($select->toString());
    $hash = array();
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $hash[$row['event_id']] = sprintf('%s - %s (%s)', $row['name'],
                    $this->_app->regionalSettings->convertDateTimeToHuman($row['start']), $row['free']);
    }
    
    if ($data['failed']||$data['cancelled']||$data['payed']||
        ($data['eventId']&&!in_array($data['eventId'],array_keys($hash)))||
        ($data['eventPack']=='Y')) { 
      $this->insert(new GuiFormInput(array(
            'id' => 'fi_event',
            'type' => 'hidden',
            'name' => 'eventId',
            'value' => $data['eventId'],
            'readonly' => true,
            'showDiv' => false, 
            'userTextStorage' => false)), 'fi_event');
      $this->insert(new GuiFormInput(array(
            'name' => 'eventName',
            'classLabel' => 'bold',
            'label' => $this->_app->textStorage->getText('label.editReservation_event'),
            'value' => $data['eventName'],
            'readonly' => true,
            'userTextStorage' => false)), 'fi_event');
    } else {
      $ds = new HashDataSource(new DataSourceSettings, $hash);
      $this->insert(new GuiFormSelect(array(
              'id' => 'fi_event',
              'name' => 'eventId',
              'classLabel' => 'bold',
              'label' => $this->_app->textStorage->getText('label.editReservation_event'),
              'dataSource' => $ds,
              'value' => $data['eventId'],
              'firstOption' => Application::get()->textStorage->getText('label.select_choose'),
              'userTextStorage' => false)), 'fi_event');
    }
  }
  
  private function _insertButton($data) {
    if (!$data['cancelled']&&!$data['failed']) {
      if (!$data['payed']) {
        $this->insertTemplateVar('fb_newUser',
          sprintf('<input class="button" class="fb_eSave" type="button" onclick="document.getElementById(\'fb_newUserHidden\').click();" name="action_eReservationUser" value="%s"/>',
                  $this->_app->textStorage->getText('button.editReservation_newUser')), false);

        $providerSettings = BCustomer::getProviderSettings($this->_app->auth->getActualProvider(),array('disableCredit','disableTicket','disableCash'));
        if (($providerSettings['disableCredit']=='N')||($providerSettings['disableTicket']=='N')||($providerSettings['disableCash']=='N')) {
          $this->insertTemplateVar('fb_savePay',
            sprintf('<input class="fb_eSave" id="fb_eReservationSavePay" onclick="return confirm(\'%s\');" type="submit" name="action_eReservationSave?pay=Y" value="%s" />',
              $this->_app->textStorage->getText('label.editReservation_confirmPay'), $this->_app->textStorage->getText('button.editReservation_savePay')), false);
        }

        $this->insertTemplateVar('fb_savePay', '<input class="fb_eHidden" id="fb_eReservationSavePayNoConfirm" type="submit" name="action_eReservationSave?pay=Y" value="save" />', false);
        $this->insertTemplateVar('fb_savePay', '<input class="fb_eHidden" id="fb_eReservationSavePayTicket" type="submit" name="action_eReservationSave?pay=Y&payType=ticket" value="save" />', false);
        $this->insertTemplateVar('fb_savePay', '<input class="fb_eHidden" id="fb_eReservationSavePayArrangeCredit" type="submit" name="action_eReservationSave?pay=Y&payType=credit&payArrangeCredit=Y" value="save" />', false);
        $this->insertTemplateVar('fb_savePay', '<input class="fb_eHidden" id="fb_eReservationSavePayCredit" type="submit" name="action_eReservationSave?pay=Y&payType=credit" value="save" />', false);
        $this->insertTemplateVar('fb_savePay', '<input class="fb_eHidden" id="fb_eReservationSavePayCash" type="submit" name="action_eReservationSave?pay=Y&payType=credit&payArrangeCredit=Y&payArrangeCreditAmount=Y" value="save" />', false);
      } else {
        $this->insertTemplateVar('fb_newUser', '');
        $this->insertTemplateVar('fb_savePay', '');
      }

      $this->insertTemplateVar('fb_save',
        sprintf('<input class="fb_eSave" id="fb_eReservationSave" type="submit" name="action_eReservationSave?pay=" value="%s" />',
          $this->_app->textStorage->getText('button.editReservation_save')), false);

      if ($data['id']) {
        $this->insertTemplateVar('fb_fail',
            sprintf('<input class="fb_eSave" id="fb_eReservationFail" onclick="return confirm(\'%s\');" type="submit" name="action_eReservationFail" value="%s" />',
                    $this->_app->textStorage->getText('label.editReservation_confirmFail'), $this->_app->textStorage->getText('button.grid_fail')), false);
        $this->insertTemplateVar('fb_cancel',
            sprintf('<input class="fb_eDelete" id="fb_eReservationCancel" onclick="return confirm(\'%s\');" type="submit" name="action_eReservationCancel" value="%s" />',
                    $this->_app->textStorage->getText('label.editReservation_confirmCancel'), $this->_app->textStorage->getText('button.grid_cancel')), false);
        $this->insertTemplateVar('fb_cancel', '<input class="fb_eHidden" id="fb_eReservationCancelNoConfirm" type="submit" name="action_eReservationCancel" value="save" />', false);
        if ($data['payed']) {
          $this->insertTemplateVar('fb_cancel', '<input class="fb_eHidden" id="fb_eReservationCancelNoRefundNoConfirm" type="submit" name="action_eReservationCancelRefund?refundTo=none" value="save" />', false);
          $this->insertTemplateVar('fb_cancel', '<input class="fb_eHidden" id="fb_eReservationCancelRefundCreditNoConfirm" type="submit" name="action_eReservationCancelRefund?refundTo=credit" value="save" />', false);
          $this->insertTemplateVar('fb_cancel', '<input class="fb_eHidden" id="fb_eReservationCancelRefundNoConfirm" type="submit" name="action_eReservationCancelRefund" value="save" />', false);
          $this->insertTemplateVar('fb_cancel',
            sprintf(' <input class="fb_eDelete" id="fb_eReservationCancelRefund" onclick="return confirm(\'%s\');" type="submit" name="action_eReservationCancelRefund?refundTo=refundFromEdit" value="%s" />',
                    $this->_app->textStorage->getText('label.editReservation_confirmCancelRefund'), $this->_app->textStorage->getText('button.listReservation_cancelRefund')), false);
        }
      } else {
        $this->insertTemplateVar('fb_fail', '');
        $this->insertTemplateVar('fb_cancel', '');
      } 
    } else {
      $this->insertTemplateVar('fb_newUser', '');
      $this->insertTemplateVar('fb_save', '');
      $this->insertTemplateVar('fb_savePay', '');
      $this->insertTemplateVar('fb_fail', '');
      $this->insertTemplateVar('fb_cancel', '');
    }
  }
  
  private function _insertEventAttendee($data) {
    $provider = ifsetor($data['providerId'],$this->_app->auth->getActualProvider());
    $providerSubAccount = BCustomer::getProviderSettings($provider, 'userSubaccount')=='Y';
    if ($providerSubAccount) {
      // v html je kod pro zobrazeni v GUI
      $html = sprintf('<table class="attendee" id="fi_eventAttendee">');
      // v template je sablona pro jednoho ucastnika
      // je pouzita pozdeji jak v $html, tak pro javascript (vlozeni noveho ucastnika)
      $template = '<tr meaning="attendee"%s">
              <input meaning="personId" type="hidden" name="eventAttendeePersonId[]" value="%s"/>
              <td><select meaning="user" name="eventAttendeeUser[]">%s</select>
              <input meaning="firstname" type="hidden" name="eventAttendeeFirstname[]" value="%s"/>
              <input meaning="lastname" type="hidden" name="eventAttendeeLastname[]" value="%s"/>
              <input meaning="email" type="hidden" name="eventAttendeeEmail[]" value="%s"/>&nbsp;%s</td>
            </tr>';
    } else {
      $html = sprintf('<table class="attendee" id="fi_eventAttendee">
              <tr>
                <th>{__label.editReservation_eventAttendee_firstname}</th>
                <th>{__label.editReservation_eventAttendee_lastname}</th>
                <th>{__label.editReservation_eventAttendee_email}</th>
              </tr>');
      $template = '<tr meaning="attendee"%s>
              <input meaning="personId" type="hidden" name="eventAttendeePersonId[]" value="%s"/>
              <input meaning="user" type="hidden" name="eventAttendeeUser[]" value="%s"/>
              <td><input meaning="firstname" class="mediumText" name="eventAttendeeFirstname[]" type="text" value="%s"/></td>
              <td><input meaning="lastname" class="mediumText" name="eventAttendeeLastname[]" type="text" value="%s"/></td>
              <td><input meaning="email" class="mediumText" name="eventAttendeeEmail[]" type="text" value="%s"/>&nbsp%s</td>
            </tr>';
    }

    // ziskam poducty uzivatele
    $subaccounts = array();
    if ($data['userId']) {
      $bUser = new BUser($data['userId']);
      $subaccounts = $bUser->getSubaccount();
    }

    // vlozim HTML ucastniku do formulare
    $aNum = $data['eventPlaces']>1?$data['eventPlaces']:1;
    $aNum = $aNum*($data['eventCoAttendees']?$data['eventCoAttendees']:1);
    for ($i=0;$i<$aNum;$i++) {
      $id = sprintf(' id="fi_attendee_%s"', $i+1);

      if ($providerSubAccount) {
        $eventAttendeeUser = '';
        foreach ($subaccounts as $account) {
          $eventAttendeeUser .= sprintf('<option value="%s"%s>%s</option>', $account['id'],
            isset($data['eventAttendeeUser'][$i])&&($data['eventAttendeeUser'][$i]==$account['id'])?'selected="selected"':'', $account['name']);
        }
      } else $eventAttendeeUser = ifsetor($data['eventAttendeeUser'][$i],'');

      $html .= sprintf($template, $id, ifsetor($data['eventAttendeePersonId'][$i],''), $eventAttendeeUser,
        ifsetor($data['eventAttendeeFirstname'][$i],''),ifsetor($data['eventAttendeeLastname'][$i],''),ifsetor($data['eventAttendeeEmail'][$i],''),
        ifsetor($data['eventAttendeeObsolete'][$i],''));
    }
    $html .= '</table>';
    $this->insert(new GuiElement(array('template'=>$html)), 'eventAttendeeTable');

    // vlozim promenne do JS pro vkladani novych ucastniku
    if ($eventAttendeeUser) $template = str_replace('%s</select>', $eventAttendeeUser.'</select>', $template);
    $javascript = sprintf("var useUserSubaccount=0;\nvar eventAttendeeTemplate='%s';\nvar eventAttendeeUserSelectOption = '';\n", str_replace(array('%s',"\n","\t"),'',$template));
    if ($providerSubAccount) $javascript .= sprintf("var useUserSubaccount=1;\nvar firstOption='%s';\n", $this->_app->textStorage->getText('label.select_choose'));
    $this->_app->document->addJavascript($javascript);
  }

  protected function _userRender() {
    $this->setTemplateFile(dirname(__FILE__).'/ReservationEdit.html');

    $validator = Validator::get('reservation', 'ReservationValidator');
    $data = $validator->getValues();
    #adump($data);
    
    $this->_readonly = $data['failed']||$data['cancelled'];
    $this->_readonlyPaid = $data['payed'];

    foreach ($data as $k => $v) {
      if (!is_array($v)) { $this->insertTemplateVar($k, $v); }
    }

    if (!$data['id']) {
      $this->insertTemplatevar('title', $this->_app->textStorage->getText('label.editReservation_titleNew'));
      
      $this->insertTemplateVar('commoditySelectStyle', '');
    } else {
      $this->insertTemplatevar('title', $this->_app->textStorage->getText('label.editReservation_titleExisting'));
      
      $this->insertTemplateVar('commoditySelectStyle', 'style="display: none;"', false);
    }
    
    $jsAttributeValues = '';
    foreach ($data['attribute'] as $id=>$value) {
      $jsAttributeValues .= sprintf("data.values['%d'] = \"%s\";\n", $id, htmlspecialchars(str_replace(array("\n","\r"),array('\n',''), $value)));
    }
    $readonlyAttributes = '';
    if ($this->_readonly) $readonlyAttributes = "data['readonly'] = 1;";
    
    global $AJAX;
    $javascript = sprintf("function showHideCommodity() {
                  if ($('#fi_resourceRadio').is(':checked')) {
                    $('#fi_resourceDiv').show();
                    $('#fi_eventDiv').hide();
                    
                    $('#fi_event').val('');
                  } else {
                    $('#fi_resourceDiv').hide();
                    $('#fi_eventDiv').show();
                    
                    $('#fi_resource').val('');
                  }
                  
                  resourceChange();
                  eventChange();
                }
                
                function resourceChange() {
                  if ($('#fi_resource').val()) {
                    $.ajax({
                        type: 'GET',
                        dataType: 'json',
                        data: { id : $('#fi_resource').val() },
                        url: '%s?action=getResource',
                        success: function(data) {
                          $('#fi_resourceDescription').html(data.description);
                          $('#fi_resourcePrice').html(data.priceHtml);
                        },
                        error: function(error) { alert('{__label.ajaxError}'); }
                    });
                    
                    getAttribute();
                    calculatePrice();
                  } else {
                    $('#fi_resourceDescription').html('');
                    $('#fi_resourcePrice').html('');
                    //$('#fi_price').val('');
                  }
                  
                  return false;
                };
                
                function eventChange() {
                  // u jiz ulozenych rezervaci na cyklus nejde dodatecne menit akce
                  //if (($('#fi_eventPack').val()=='Y')&&$('#fi_id').val()) return;
                
                  if ($('#fi_event').val()) {
                    $.ajax({
                        async: false,
                        type: 'GET',
                        dataType: 'json',
                        data: { id : $('#fi_event').val() },
                        url: '%s?action=getEvent',
                        success: function(data) {
                          if (data.reservationMaxAttendees==1) {
                            $('#fi_eventPlaces').val('1');
                            $('#fi_eventPlaces').attr('readonly', true);
                          } else {
                            if ($('#fi_payed').val()) $('#fi_eventPlaces').attr('readonly', true);
                            else $('#fi_eventPlaces').attr('readonly', false);
                          }
                          
                          var price = parseInt(data.price);
                          if ($('#fi_eventPack').val()=='Y') price = parseInt(data.repeatPrice);
                          
                          $('#fi_eventDescription').html(data.description);
                          $('#fi_eventPrice').html(data.priceHtml);
                          if ($('#fi_priceManual').val()!='1') {
                            price = price*parseInt($('#fi_eventPlaces').val());
                            
                            var voucherEl = $('#fi_voucherDiscount'); 
                            if (voucherEl.val()) {
                              if (voucherEl.attr('data-meaning')=='SUM') {
                                price -= voucherEl.val();
                                if (price<0) price = 0;
                              } else if (voucherEl.attr('data-meaning')=='PROPORTION') price -= price*voucherEl.val()/100;
                            } 
                            
                            $('#fi_price').val(price);
                          }
                          $('#fi_eventCoAttendees').val(data.coAttendees);
                        },
                        error: function(error) { alert('{__label.ajaxError}'); }
                    });
                    
                    getAttribute();                   
                  } else {
                    $('#fi_eventDescription').html('');
                    $('#fi_eventPrice').html('');
                    //$('#fi_price').val('');
                    $('#fi_eventCoAttendees').val('1');
                  }
                  
                  var person = parseInt($('#fi_eventPlaces').val())*parseInt($('#fi_eventCoAttendees').val());
                  if (person>0) {
                    var count = 0;
                    $('#fi_eventAttendee [meaning=attendee]').each(function() {
                      if (count>=person) {
                        $(this).remove();
                      }
                      count++;
                    });
                    var newEventAttendee = eventAttendeeTemplate.replace('</select>', eventAttendeeUserSelectOption+'</select>');
                    while (count<person) {
                      $('#fi_eventAttendee tbody').append(newEventAttendee);
                      count++;
                    }
                  }
                  
                  return false;
                };
                
                function getAttribute() {
                  if ($('#fi_resourceRadio').is(':checked')) {
                    data = { resourceId: $('#fi_resource').val(), reservationId: $('#fi_id').val(), values: {} }
                  } else {
                    data = { eventId: $('#fi_event').val(), reservationId: $('#fi_id').val(), values: {} }
                  }
                  %s
                  %s
                  
                  $.ajax({
                    type: 'GET',
                    url: '%s?action=guiReservationAttribute&sessid=%s&target=backoffice',
                    dataType: 'json',
                    data: data,
                    success: function(data) {
                      $('#fi_attribute').html(data.output);
                    },
                    error: function(error) { alert('{__label.ajaxError}'); }
                  });
                }
                
                function calculatePrice() {
                  if ($('#fi_priceManual').val()=='1') return;
                  if (parseInt($('#fi_voucherDiscount').val())>0) return;
                  
                  if ($('#fi_resource').val()&&$('#fi_resourceFrom').val()&&$('#fi_resourceTo').val()) {
                    $.ajax({
                        type: 'GET',
                        dataType: 'json',
                        data: {
                          resourceId : $('#fi_resource').val(),
                          from: moment($('#fi_resourceFrom').val(),'DD.MM.YYYYY hh:mm').format('YYYY-MM-DD HH:mm:ss'),
                          to: moment($('#fi_resourceTo').val(),'DD.MM.YYYYY hh:mm').format('YYYY-MM-DD HH:mm:ss'),
                        },
                        url: '%s?action=getResourcePrice',
                        success: function(data) {
                          $('#fi_price').val(data.price);
                        },
                        error: function(error) { alert('{__label.ajaxError}'); }
                    });
                  } else {
                    $('#fi_price').val('');
                  }
                }
                
                function fillSubaccountSelect(user) {
                  $.ajax({
                      type: 'GET',
                      dataType: 'json',
                      data: {
                        user : user,
                        sessid: '%s',
                      },
                      url: '%s?action=getUserSubaccount',
                      success: function(data) {
                        var selectHtml = '';
                        $.each(data, function(index,element) {
                          selectHtml += '<option value=\"'+element.id+'\">'+element.name+'</option>';
                        });
                        eventAttendeeUserSelectOption = selectHtml;
                        $('#fi_eventAttendee select').each(function() { 
                          var oldValue = $(this).val();
                          $(this).html(selectHtml);
                          if (oldValue) $(this).val(oldValue);
                        });
                      },
                      error: function(error) { alert('{__label.ajaxError}'); }
                  });
                }
                
                showHideCommodity();
                //resourceChange();
                //eventChange();
                
                $('#fi_resource').change(function() { resourceChange(); });
                ", $AJAX['adminUrl'], $AJAX['adminUrl'], $jsAttributeValues, $readonlyAttributes, $AJAX['adminUrl'], $this->_app->session->getId(),
                $AJAX['adminUrl'], $this->_app->session->getId(), $AJAX['adminUrl']);
    
    if ($this->_readonly) {
      $this->insertTemplateVar('readonlyState', 'readonly="yes"', false);
    } else {
      $javascript .= sprintf("
                $('#fi_resourceFrom').datetimepicker({format:'d.m.Y H:i',dayOfWeekStart:'1',lang:'cz'});
                $('#fi_resourceTo').datetimepicker({format:'d.m.Y H:i',dayOfWeekStart:'1',lang:'cz'});
                  
                $('#fi_resourceFrom').blur(function () {
                  if ($('#fi_resourceFrom').val()&&($('#fi_resourceTo').val()=='')) {
                    $('#fi_resourceTo').val($('#fi_resourceFrom').val());
                  }
                  calculatePrice();
                });
                $('#fi_resourceTo').blur(function() {
                  if (formatDateTime($('#fi_resourceTo').val(),'mysql')<formatDateTime($('#fi_resourceFrom').val(),'mysql')) {
                    alert('%s');
                    $('#fi_resourceTo').val($('#fi_resourceFrom').val());
                  }
                  calculatePrice(); 
                });", $this->_app->textStorage->getText('error.editReservation_invalidFromTo'));

      if ($this->_readonlyPaid) {
        $this->insertTemplateVar('readonlyState', 'readonly="yes"', false);
      } else {
        $this->insertTemplateVar('readonlyState', '');

        $javascript .= sprintf("
                $('.inputRadio').change(function() { showHideCommodity(); });
                $('#fi_event').change(function() { eventChange(); });
                $('#fi_eventPlaces').change(function() { eventChange(); });
              
                $('#fi_user').combogrid({
                  required: true,
                  url: '%s?action=getUser&provider=%s&sessid=%s',
                  debug: true,
                  //replaceNull: true,
                  colModel: [{'columnName':'id','width':'10','label':'id','hidden':'true'},
                             {'columnName':'name','width':'30','label':'Jméno','align':'left'},
                             {'columnName':'address','width':'40','label':'Adresa','align':'left'},
                             {'columnName':'email','width':'30','label':'Email','align':'left'}],
                  select: function(event,ui) {
                    $('#fi_user').val(ui.item.name);
                    $('#fi_userId').val(ui.item.id);
                    $('#fi_userName').val(ui.item.name);
                    
                    if (useUserSubaccount) fillSubaccountSelect(ui.item.id);
                    else {
                      $('#fi_attendee_1 [meaning=firstname]').each(function() { if (!$(this).val()) $(this).val(ui.item.firstname); });
                      $('#fi_attendee_1 [meaning=lastname]').each(function() { if (!$(this).val()) $(this).val(ui.item.lastname); });
                      $('#fi_attendee_1 [meaning=email]').each(function() { if (!$(this).val()) $(this).val(ui.item.email); });
                    }
                    
                    return false;
                  }
                });
                
                $('#fi_price').change(function() {
                  $('#fi_priceManual').val('1');
                  $('#fi_priceNew').val('1');
                  $('#fi_priceCommentDiv').show();
                  $('#fb_priceAuto').show();
                  $('#fi_priceComment').focus();
                });
                $('#fb_priceAuto').click(function() {
                  $('#fi_priceManual').val('0');
                  $('#fi_priceNew').val('0');
                  $('#fi_priceCommentDiv').hide();
                  $('#fb_priceAuto').hide();
                  
                  calculatePrice();
                  eventChange();
                });", $AJAX['adminUrl'], $this->_app->auth->getActualProvider(), $this->_app->session->getId());
      }
    }
    
    if ($data['commodity']=='event') {
      $this->insertTemplateVar('eventChecked', 'checked="yes"', false);
      $this->insertTemplateVar('resourceChecked', '');
    } else {
      $this->insertTemplateVar('resourceChecked', 'checked="yes"', false);
      $this->insertTemplateVar('eventChecked', '');
    }

    $this->_insertNumber($data);
    $this->_insertMandatorySelect($data);
    //$this->_insertCustomerSelect($data);
    $this->_insertResourceSelect($data);
    $this->_insertEventSelect($data);
    $this->_insertEventAttendee($data);
    $this->_insertPrice($data);
    $this->_insertState($data);
    $this->_insertButton($data);
    
    $this->_app->document->addJavascript(sprintf("$(document).ready(function() { %s });", $javascript));
  }
}

?>
