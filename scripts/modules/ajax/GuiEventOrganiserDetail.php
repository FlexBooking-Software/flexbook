<?php

class AjaxGuiEventOrganiserDetail extends AjaxGuiEventDetail {
  private $_availableTicket = array();

  protected function _createTemplate() {
    parent::_createTemplate();

    if (!in_array('attendees', $this->_params['renderText'])) {
      $this->_guiHtml = str_replace('{reservationInfo}', '{attendees}', $this->_guiHtml);
    } else {
      $this->_guiHtml = str_replace('{reservationInfo}', '', $this->_guiHtml);
    }
  }

  protected function _initDefaultParams() {
    parent::_initDefaultParams();

    if (!isset($this->_params['organiserShowReservationAttendee'])) $this->_params['organiserShowReservationAttendee'] = 1;
  }

  protected function _getJavascript() {
    $ret = parent::_getJavascript();

    $ret .= "function showReservationGui() {
                $('#{prefix}flb_event_user_name').val('');
                $('#{prefix}flb_event_user_id').val('');
                $('#{prefix}flb_ebent_user_selected').val('');
                $('#{prefix}flb_event_reserve_attendee_1 [meaning=firstname]').each(function() { $(this).val(''); });
                $('#{prefix}flb_event_reserve_attendee_1 [meaning=lastname]').each(function() { $(this).val(''); });
                $('#{prefix}flb_event_reserve_attendee_1 [meaning=email]').each(function() { $(this).val(''); });
                
                $('#{prefix}flb_event_places').val('1');
                
                eventInputAttendeesRefresh(null);
                calculatePrice();
                  
                $('#{prefix}flb_event_reserve_additional').show();
                
                $('#{prefix}flb_event_user_name').focus();
             }
             
             $('#{prefix}flb_event_{event_id}').on('focus','#{prefix}flb_event_user_name', function() {
                $('#{prefix}flb_event_user_name').trigger($.Event('keydown', {keyCode: 16}))
             });
    
             $('#{prefix}flb_event_{event_id}').on('click','#{prefix}flb_event_organiser_reserve_prepare', function() {
                $('#{prefix}flb_event_{event_id} .button').hide();
                
                $('#{prefix}flb_event_mandatory_line').show();
                $('#{prefix}flb_event_reserve_additional_button_reservation').show();
                $('#{prefix}flb_event_reserve_additional_button_substitute').hide();
                
                showReservationGui();
             });
             $('#{prefix}flb_event_{event_id}').on('click','#{prefix}flb_event_organiser_substitute_prepare', function() {
                $('#{prefix}flb_event_{event_id} .button').hide();
                
                $('#{prefix}flb_event_mandatory_line').hide();
                $('#{prefix}flb_event_reserve_additional_button_reservation').hide();
                $('#{prefix}flb_event_reserve_additional_button_substitute').show();
                
                showReservationGui();
             });
             $('#{prefix}flb_event_{event_id}').on('click','#{prefix}flb_event_organiser_prepare_back', function() {
                $('#{prefix}flb_event_{event_id} .button').show();
                
                $('#{prefix}flb_event_reserve_additional').hide();
             });
             
             $('#{prefix}flb_event_{event_id}').on('click','#{prefix}flb_event_organiser_reserve', function() {
                if ($('#{prefix}flb_event_user_name').val()!=$('#{prefix}flb_event_user_selected').val()) {
                  alert('{__label.calendar.editReservation_unknownUser}');
                } else {
                  saveReservation(null);
                }
             });
             
             $('#{prefix}flb_event_{event_id}').on('click','#{prefix}flb_event_organiser_reserve_substitute', function() {
                if ($('#{prefix}flb_event_user_name').val()!=$('#{prefix}flb_event_user_selected').val()) {
                  alert('{__label.calendar.editReservation_unknownUser}');
                } else {
                  saveSubstitute();
                }
             });
             
             $('#{prefix}flb_event_{event_id}').on('click','.btn_attendeeFail', function() {
                if (confirm('{__label.ajax_event_attendeeFailConfirm}')) {
                  var idExt = $(this).attr('id');
                  idExt = idExt.replace('{prefix}attendeeFail_','');
                  $.ajax({
                    type: 'GET',
                    url: $('#flb_core_url').val()+'action=failReservationEventPackItem',
                    data: 'event={event_id}&reservation='+idExt+'&sessid='+$('#flb_core_sessionid').val(),
                    dataType: 'json',
                    success: function(data) {
                      if (data.error) alert(data.message);
                      if (data.popup) alert(data.popup)
                
                      if (typeof flbRefresh == 'function') {
                        flbRefresh('.flb_event_detail');
                      }
                    },
                    error: function(data) { alert('{__label.calendar_ajaxError}'); return; }
                  });
                }
             });
             
             $('#{prefix}flb_event_{event_id}').on('click','.btn_attendeeCancel', function() {
                if (confirm('{__label.ajax_event_attendeeCancelConfirm}')) {
                  var idExt = $(this).attr('id');
                  idExt = idExt.replace('{prefix}attendeeCancel_','');
                  $.ajax({
                    type: 'GET',
                    url: $('#flb_core_url').val()+'action=cancelReservationEventPackItem',
                    data: 'event={event_id}&reservation='+idExt+'&sessid='+$('#flb_core_sessionid').val(),
                    dataType: 'json',
                    success: function(data) {
                      if (data.error) alert(data.message);
                      if (data.popup) alert(data.popup)
            
                      if (typeof flbRefresh == 'function') {
                        flbRefresh('.flb_event_detail');
                      }
                    },
                    error: function(data) { alert('{__label.calendar_ajaxError}'); return; }
                  });
                }
              });
             
             $('#{prefix}flb_event_{event_id}').on('click','.btn_swapAttendee', function() {
                if (confirm('{__label.ajax_event_swapAttendeeConfirm}')) {
                  var idExt = $(this).attr('id');
                  idExt = idExt.replace('{prefix}swapAttendee_','');
                  $.ajax({
                    type: 'GET',
                    url: $('#flb_core_url').val()+'action=swapSubstitute',
                    data: 'event={event_id}&reservation='+idExt+'&sessid='+$('#flb_core_sessionid').val(),
                    dataType: 'json',
                    success: function(data) {
                      if (data.error) alert(data.message);
                      if (data.popup) alert(data.popup);
                        
                      if (typeof flbRefresh == 'function') {
                        flbRefresh('.flb_event_detail');
                      }
                    },
                    error: function(data) { alert('{__label.calendar_ajaxError}'); return; }
                  });
                }
             });
             
             $('#{prefix}flb_event_{event_id}').on('click','.btn_swapSubstitute', function() {
                if (confirm('{__label.ajax_event_swapSubstituteConfirm}')) {
                  var idExt = $(this).attr('id');
                  idExt = idExt.replace('{prefix}swapSubstitute_','');
                  $.ajax({
                    type: 'GET',
                    url: $('#flb_core_url').val()+'action=swapSubstitute',
                    data: 'event={event_id}&substitute='+idExt+'&sessid='+$('#flb_core_sessionid').val(),
                    dataType: 'json',
                    success: function(data) {
                      if (data.error) alert(data.message);
                      if (data.popup) alert(data.popup);
                        
                      if (typeof flbRefresh == 'function') {
                        flbRefresh('.flb_event_detail');
                      }
                    },
                    error: function(data) { alert('{__label.calendar_ajaxError}'); return; }
                  });
                }
             });
             
             $('#{prefix}flb_event_{event_id}').on('click','.btn_substituteCancel', function() {
                if (confirm('{__label.ajax_event_substituteCancelConfirm}')) {
                  var idExt = $(this).attr('id');
                  idExt = idExt.replace('{prefix}substituteCancel_','');
                  $.ajax({
                    type: 'GET',
                    url: $('#flb_core_url').val()+'action=cancelSubstitute',
                    data: 'id='+idExt+'&sessid='+$('#flb_core_sessionid').val(),
                    dataType: 'json',
                    success: function(data) {
                      if (data.error) alert(data.message);
                      if (data.popup) alert(data.popup)
            
                      if (typeof flbRefresh == 'function') {
                        flbRefresh('.flb_event_detail');
                      }
                    },
                    error: function(data) { alert('{__label.calendar_ajaxError}'); return; }
                  });
                }
              });
              
              $('#{prefix}flb_event_user_name').click(function () { $(this).select(); })
              $('#{prefix}flb_event_user_name').combogrid({
                url: $('#flb_core_url').val()+'action=getUser&sessid={%sessid%}{organiserCanReserveOnBehalf}',
                debug: true,
                //replaceNull: true,
                colModel: [{'columnName':'id','width':'10','label':'id','hidden':'true'},
                  {'columnName':'firstname','width':'30','label':'{__label.calendar_editUser_firstname}','align':'left'},
                  {'columnName':'lastname','width':'30','label':'{__label.calendar_editUser_lastname}','align':'left'},
                  {'columnName':'{organiserCanReserveOnBehalfCustomColumnValue}','width':'40','label':'{organiserCanReserveOnBehalfCustomColumnTitle}','align':'left'}],
                select: function(event,ui) {
                  $('#{prefix}flb_event_user_name').val(ui.item.name);
                  $('#{prefix}flb_event_user_id').val(ui.item.id);
                  $('#{prefix}flb_event_user_selected').val(ui.item.name);
                  
                  $('#{prefix}flb_event_user_name').blur();
                  
                  $('#{prefix}flb_event_reserve_attendee_1 [meaning=firstname]').each(function() { $(this).val(ui.item.firstname); });
                  $('#{prefix}flb_event_reserve_attendee_1 [meaning=lastname]').each(function() { $(this).val(ui.item.lastname); });
                  $('#{prefix}flb_event_reserve_attendee_1 [meaning=email]').each(function() { $(this).val(ui.item.email); });
        
                  var userData = getUserDetail({ user: $('#{prefix}flb_event_user_id').val(), event: $('#{prefix}flb_event_id').val(), price: $('#{prefix}flb_event_total_price_amount').html() });
                  if (!userData) $('#{prefix}flb_event_user_name').val('');
                  
                  eventInputAttendeesRefresh(userData);
                  return false;
                }
              });
              $('#{prefix}flb_event_user_name').change(function () {
                if ($(this).val()=='') {
                  $('#{prefix}flb_event_user_name').val('');
                  $('#{prefix}flb_event_user_id').val('');
                  $('#{prefix}flb_event_user_selected').val('');
                  
                  var userData = getUserDetail({ user: $('#{prefix}flb_event_user_id').val(), event: $('#{prefix}flb_event_id').val(), price: $('#{prefix}flb_event_total_price_amount').html() });
                  
                  eventInputAttendeesRefresh(userData);
                }
              });
              
              {showReservationGui}";

    return $ret;
  }

  protected function _getAttendeeInputGui() {
    $ret = parent::_getAttendeeInputGui();

    // pokud neni moznost rezervovat vice mist a neni vice ucastniku na jednu rezervaci,
    // muze byt input na ucastniky schovanej
    if (($this->_data['reservation_max_attendees']==1)&&($this->_data['max_coattendees']==1)) {
      if (!$this->_params['organiserShowReservationAttendee']) {
        $ret = sprintf('<div style="display:none;">%s</div>', $ret);
      }
    }

    return $ret;
  }
  
  protected function _getReservationGui() {
    if (!isset($this->_params['organiserCanReserveOnBehalf'])) parent::_getReservationGui();
    else {
      $reservationButton = '';
      $showReservationGui = '';

      if (in_array('reservation',$this->_params['renderText'])) {
        if ($user=$this->_app->auth->getUserId()) {
          if ($this->_data['free']) $reservationButton .= sprintf('<input type="button" id="%sflb_event_organiser_reserve_prepare" class="flb_primaryButton" value="%s" />', $this->_params['prefix'], $this->_app->textStorage->getText('button.ajax_event_organiser_reserve'));
          if ($this->_data['free_substitute']) $reservationButton .= sprintf('<input type="button" id="%sflb_event_organiser_substitute_prepare" class="flb_primaryButton" value="%s" />', $this->_params['prefix'], $this->_app->textStorage->getText('button.ajax_event_organiser_reserve_substitute'));

          if (isset($this->_params['reserve'])&&$this->_params['reserve']) $showReservationGui = sprintf("$('#%sflb_event_organiser_reserve_prepare').click();", $this->_params['prefix']);
        } else {
          $reservationButton = sprintf('<input type="button" id="%sflb_event_reserve_notlogged" value="%s" />', $this->_params['prefix'], $this->_app->textStorage->getText('button.ajax_event_reserve'));
        }
      }

      $this->_guiParams['reservationButton'] = $reservationButton;
      $this->_guiParams['showReservationGui'] = $showReservationGui;
      $this->_guiParams['reservationGui'] = $this->_getReservationInput();
    }
  }

  private function _getReservationInput() {
    $template = '';

    // vyber uzivatele
    $template .= sprintf('<div class="label flb_event_user_label"><span>%s:</span></div>
                          <input type="text" id="%sflb_event_user_name" class="text ui-widget-content ui-corner-all"/>
                          <input type="hidden" id="%sflb_event_user_selected"/>',
      $this->_app->textStorage->getText('label.ajax_event_user'), $this->_params['prefix'], $this->_params['prefix']);

    if (is_array($this->_params['organiserCanReserveOnBehalf'])) {
      if (isset($this->_params['organiserCanReserveOnBehalf']['usersHavingReservationsOnEventWithTag'])) $this->_guiParams['organiserCanReserveOnBehalf'] = '&scope=event&scopeEvent='.$this->_params['organiserCanReserveOnBehalf']['usersHavingReservationsOnEventWithTag'];
      if (isset($this->_params['organiserCanReserveOnBehalf']['usersHavingPaidReservationsOnEventWithTag'])) $this->_guiParams['organiserCanReserveOnBehalf'] = '&scope=paidEvent&scopeEvent='.$this->_params['organiserCanReserveOnBehalf']['usersHavingPaidReservationsOnEventWithTag'];
    } elseif (!strcmp($this->_params['organiserCanReserveOnBehalf'], 'allUsers')) $this->_guiParams['organiserCanReserveOnBehalf'] = '&scope=all';
    else $this->_guiParams['organiserCanReserveOnBehalf'] = '&scope=unknown';

    if (isset($this->_params['organiserCanReserveOnBehalfCustomColumn']['value'])) $this->_guiParams['organiserCanReserveOnBehalf'] .= '&customColumn='.$this->_params['organiserCanReserveOnBehalfCustomColumn']['value'];
    $this->_guiParams['organiserCanReserveOnBehalfCustomColumnTitle'] = ifsetor($this->_params['organiserCanReserveOnBehalfCustomColumn']['title'], $this->_app->textStorage->getText('label.calendar_editUser_email'));
    $this->_guiParams['organiserCanReserveOnBehalfCustomColumnValue'] = ifsetor($this->_params['organiserCanReserveOnBehalfCustomColumn']['value'], 'email');

    // moznost vybrat cele opakovani nebo jednotliva akce
    if ($this->_data['free']&&!strcmp($this->_data['repeat_reservation'],'BOTH')) {
      $template .= sprintf('<div class="label flb_event_pack_label"><span>%s:</span></div>
                             <input type="radio" id="%sflb_event_pack_N" name="pack" value="N" checked="yes"/>%s
                             <input type="radio" id="%sflb_event_pack_Y" name="pack" value="Y"/>%s',
                             $this->_app->textStorage->getText('label.ajax_event_reservePack'),
                             $this->_params['prefix'], $this->_app->textStorage->getText('label.ajax_event_reservePack_single'),
                             $this->_params['prefix'], $this->_app->textStorage->getText('label.ajax_event_reservePack_pack'));
      $template .= sprintf('<input type="hidden" id="%sflb_event_pack" value="N" />', $this->_params['prefix']);

    } else {
      $template .= sprintf('<input type="hidden" id="%sflb_event_pack" value="%s" />', $this->_params['prefix'], !strcmp($this->_data['repeat_reservation'],'PACK')?'Y':'N');
    }

    // moznost vice mist na rezervaci
    $template .= sprintf('<input type="hidden" id="%sflb_event_coAttendees" value="%s" />', $this->_params['prefix'], $this->_data['max_coattendees']);
    if ($this->_data['reservation_max_attendees']>1) {
      $input = sprintf('<select class="flb_event_places" id="%sflb_event_places">', $this->_params['prefix']);
      $capacity = $this->_data['free']>0?$this->_data['free']:$this->_data['free_substitute'];
      for ($i=1;($i<=$this->_data['reservation_max_attendees'])&&($i<=$capacity);$i++) $input .= sprintf('<option value="%s">%s</option>', $i, $i);
      $input .= '</select>';
      $template .= sprintf('<div class="label flb_event_reserve_places_label"><span>%s:</span></div>%s', $this->_app->textStorage->getText('label.ajax_event_reservePlaces'), $input);
    } else {
      $template .= sprintf('<input type="hidden" id="%sflb_event_places" value="1" />', $this->_params['prefix']);
    }

    // mandatory rezervace pro organizatory
    if ((BCustomer::getProviderSettings($this->_params['provider'],'allowMandatoryReservation')=='Y')&&($this->_app->auth->getUserId()==$this->_data['organiser'])) {
      if (BCustomer::getProviderSettings($this->_params['provider'],'organiserMandatoryReservation')=='Y') {
        $template .= sprintf('<input type="checkbox" id="%sflb_event_mandatory" checked="checked" style="display: none;"/>', $this->_params['prefix']);
      } else {
        $template .= sprintf('<div class="label" id="%sflb_event_mandatory_line"><div class="label flb_event_reserve_mandatory_label"><span>%s:</span></div>
                                <input type="checkbox" class="checkboxMandatory" id="%sflb_event_mandatory" /></div>',
          $this->_params['prefix'], $this->_app->textStorage->getText('label.ajax_event_reserveMandatory'), $this->_params['prefix']);
      }
    } else {
      $template .= sprintf('<input type="checkbox" id="%sflb_event_mandatory" style="display:none;"/>', $this->_params['prefix']);
    }

    // inputy pro ucastniky
    $template .= $this->_getAttendeeInputGui();

    // inputy pro dodatecne atributy rezervace
    $template .= $this->_getReservationAttributeInputGui();

    $template .= sprintf('
       <input type="hidden" id="%sflb_event_single_price" value="%s"/>
       <input type="hidden" id="%sflb_event_pack_price" value="%s"/>
       <div class="flb_event_reserve_price">
         <div class="label flb_event_total_price_label"><span>%s:</span></div>
         <div class="value flb_event_total_price"><span id="%sflb_event_total_price_amount">%s</span> %s</div>
       </div>',
       $this->_params['prefix'], $this->_data['price'],
       $this->_params['prefix'], $this->_data['repeat_price'],
       $this->_app->textStorage->getText('label.ajax_event_totalPrice'),
       $this->_params['prefix'],
       !strcmp($this->_data['repeat_reservation'],'PACK')?$this->_data['repeat_price']:$this->_data['price'],
       $this->_app->textStorage->getText('label.currency_CZK'));

    if (count($this->_availableTicket)) {
      $ticketSelect = sprintf('<select id="%sflb_event_pay_ticket"><option value="">%s</option>',
                              $this->_params['prefix'], $this->_app->textStorage->getText('label.select_choose'));
      foreach ($this->_availableTicket as $t) { $ticketSelect .= sprintf('<option value="%s">%s / %s %s</option>', $t['id'], $t['name'], $t['value'], $t['currency']); }
      $ticketSelect .= '</select>';

      $template .= sprintf('<div id="%sflb_event_reserve_additional_input_ticket" class="flb_event_reserve_additional_input_ticket label">
                             <div class="label flb_event_ticket_label"><span>%s:</span></div>
                             %s
                             </div>',
                             $this->_params['prefix'],
                             $this->_app->textStorage->getText('label.ajax_event_reserveTicket'),
                             $ticketSelect);
    }

    $template = sprintf('<div class="input">%s</div>', $template);

    // tlacitka
    $template .= sprintf('<div class="flb_event_reserve_additional_button">
                            <div><input type="button" id="%sflb_event_organiser_prepare_back" value="%s" /></div>
                            <div id="%sflb_event_reserve_additional_button_substitute">
                              <input type="button" id="%sflb_event_organiser_reserve_substitute" class="flb_primaryButton" value="%s" />
                            </div>
                            <div id="%sflb_event_reserve_additional_button_reservation">
                              <input type="button" id="%sflb_event_organiser_reserve" class="flb_primaryButton" value="%s" />
                            </div>
                          </div>',
      $this->_params['prefix'], $this->_app->textStorage->getText('button.back'),
      $this->_params['prefix'], $this->_params['prefix'], $this->_app->textStorage->getText('button.ajax_event_organiser_save_substitute'),
      $this->_params['prefix'], $this->_params['prefix'], $this->_app->textStorage->getText('button.ajax_event_organiser_save'));

    $template = sprintf('<div id="%sflb_event_reserve_additional" class="flb_event_attendee" style="display: none;">%s</div>', $this->_params['prefix'], $template);

    return $template;
  }

  protected function _getAttendeeGui() {
    $ret = '';

    if (($this->_data['fe_attendee_visible']=='Y')||$this->_app->auth->getUserId()) {
      $functionButtons = ifsetor($this->_params['organiserCanReserveOnBehalfFunctionButtons']);
      if (!$functionButtons||!is_array($functionButtons)) $functionButtons = array('cancelAttendee','failAttendee','attendeeToSubstitute','cancelSubstitute','substituteToAttendee');

      $s = new SEventAttendee;
      $s->addStatement(new SqlStatementBi($s->columns['event'], $this->_params['eventId'], '%s=%s'));
      $s->addStatement(new SqlStatementMono($s->columns['substitute'], "%s='N'"));
      $s->addOrder(new SqlStatementAsc($s->columns['fullname']));
      $s->setColumnsMask(array('reservation_id','reservation_event_pack','reservation_payed','failed','user','places','fullname','email','phone',
        'person_user','person_user_firstname','person_user_lastname','person_user_email','person_firstname','person_lastname','person_email'));
      $res = $this->_app->db->doQuery($s->toString());
      if ($this->_app->db->getRowsNumber($res)) {
        $template = sprintf('<div class="label flb_event_attendees_label"><span>%s:</span></div><div class="value flb_event_attendees">',
          $this->_app->textStorage->getText('label.ajax_event_attendees'));
        $reservation = null;

        while ($row = $this->_app->db->fetchAssoc($res)) {
          $line = array(
            'user'            => $row['user'],
            'user_fullname'   => $row['fullname'],
            'user_email'      => $row['email'],
            'user_phone'      => $row['phone'],
            'firstname'       => $row['person_user']?$row['person_user_firstname']:$row['person_firstname'],
            'lastname'        => $row['person_user']?$row['person_user_lastname']:$row['person_lastname'],
            'email'           => $row['person_user']?$row['person_user_email']:$row['person_email'],
            'places'          => $row['places']
          );

          if (strcmp($reservation,$row['reservation_id'])) {
            if ($reservation) $template .= '</div>';
            $template .= '<div class="flb_event_attendee_reservation">';
            if ($this->_data['organiser']==$this->_app->auth->getUserId()) {
              $buttons = '';

              if (!$row['failed']) {
                if (in_array('attendeeToSubstitute',$functionButtons)&&!$row['reservation_payed']&&($row['reservation_event_pack']!='Y')&&$row['user']&&($row['places']<=$this->_data['free_substitute'])) {
                  $buttons .= sprintf('<input type="button" id="%sswapAttendee_%d" class="btn_swapAttendee" title="%s" value="v"/>', $this->_params['prefix'], $row['reservation_id'],
                    $this->_app->textStorage->getText('label.ajax_event_swapAttendeeTitle'));
                }
                if (in_array('failAttendee',$functionButtons)) {
                  $buttons .= sprintf('<input type="button" id="%sattendeeFail_%d" class="btn_attendeeFail" title="%s" value="-"/>', $this->_params['prefix'], $row['reservation_id'],
                    $this->_app->textStorage->getText('label.ajax_event_attendeeFailTitle'));
                }
                if (in_array('cancelAttendee',$functionButtons)&&!$row['reservation_payed']) {
                  $buttons .= sprintf('<input type="button" id="%sattendeeCancel_%d" class="btn_attendeeCancel" title="%s" value="x"/>', $this->_params['prefix'], $row['reservation_id'],
                    $this->_app->textStorage->getText('label.ajax_event_attendeeCancelTitle'));
                }
              } else {
                $line['failed'] = $row['failed'];
              }

              if ($buttons) $template .= sprintf('<div class="flb_event_attendee_action">%s</div>', $buttons);
            }
          }

          $template .= $this->_parseAttendeeLine($line, $this->_params['showAttendeePayment']&&$row['reservation_payed']?'flb_event_attendee_payed':null);

          $reservation = $row['reservation_id'];
        }
        if ($reservation) $template .= '</div>';

        $ret = $template;
      }
        
      // jeste nahradniky
      $s = new SEventAttendee;
      $s->addStatement(new SqlStatementBi($s->columns['event'], $this->_params['eventId'], '%s=%s'));
      $s->addStatement(new SqlStatementMono($s->columns['substitute'], "%s='Y'"));
      $s->addOrder(new SqlStatementAsc($s->columns['fullname']));
      $s->setColumnsMask(array('eventattendee_id','user','places','fullname','email','phone',
        'person_user','person_user_firstname','person_user_lastname','person_user_email','person_firstname','person_lastname','person_email'));
      $res = $this->_app->db->doQuery($s->toString());
      if ($this->_app->db->getRowsNumber($res)) {
        $template = sprintf('<div class="label flb_event_attendees_label flb_event_substitutes_label"><span>%s:</span></div><div class="value flb_event_attendees flb_event_substitutes">',
          $this->_app->textStorage->getText('label.ajax_event_substitutes'));
        $attendee = null;

        while ($row = $this->_app->db->fetchAssoc($res)) {
          $line = array(
            'user'            => $row['user'],
            'user_fullname'   => $row['fullname'],
            'user_email'      => $row['email'],
            'user_phone'      => $row['phone'],
            'firstname'       => $row['person_user']?$row['person_user_firstname']:$row['person_firstname'],
            'lastname'        => $row['person_user']?$row['person_user_lastname']:$row['person_lastname'],
            'email'           => $row['person_user']?$row['person_user_email']:$row['person_email'],
            'places'          => $row['places'],
          );

          if (strcmp($attendee,$row['eventattendee_id'])) {
            if ($attendee) $template .= '</div>';
            $template .= '<div class="flb_event_attendee_reservation">';
            if ($this->_data['organiser']==$this->_app->auth->getUserId()) {
              $buttons = '';

              if (in_array('substituteToAttendee',$functionButtons)&&($row['places']<=$this->_data['free'])) {
                $buttons .= sprintf('<input type="button" id="%sswapSubstitute_%d" class="btn_swapSubstitute" title="%s" value="^"/>', $this->_params['prefix'], $row['eventattendee_id'],
                  $this->_app->textStorage->getText('label.ajax_event_swapSubstituteTitle'));
              }
              if (in_array('cancelSubstitute',$functionButtons)) {
                $buttons .= sprintf('<input type="button" id="%ssubstituteCancel_%d" class="btn_substituteCancel" title="%s" value="x"/>', $this->_params['prefix'], $row['eventattendee_id'],
                  $this->_app->textStorage->getText('label.ajax_event_substituteCancelTitle'));
              }

              if ($buttons) $template .= sprintf('<div class="flb_event_attendee_action">%s</div>', $buttons);
            }
          }

          $template .= $this->_parseAttendeeLine($line, 'flb_event_substitute');

          $attendee = $row['eventattendee_id'];
        }
        if ($attendee) $template .= '</div>';

        $ret .= $template;
      }
    }
    
    $this->_guiParams['attendees'] = $ret;
  }

  protected function _getData() {
  	parent::_getData();

  	// kdyz je organizatorovi umozneno rezervovat pro jine uzivatele, nebude se predvyplnovat prihlaseny organizator
    if (isset($this->_params['organiserCanReserveOnBehalf'])) $this->_guiParams['user_id'] = '';
	}
}

?>
