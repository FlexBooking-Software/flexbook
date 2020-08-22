<?php

class GuiEditEventSubstitute extends GuiElement {
  
  private function _insertEventSelect($data) {
    $select = new SEvent;
    $select->setColumnsMask(array('event_id','name_with_free_substitute'));
    $select->addStatement(new SqlStatementMono($select->columns['active'], "%s='Y'"));
    if ($id = $this->_app->auth->isProvider()) $select->addStatement(new SqlStatementBi($select->columns['provider'], $id, '%s=%s'));
    $ds = new SqlDataSource(new DataSourceSettings, $select);
    $this->insert(new GuiFormSelect(array(
            'id' => 'fi_event',
            'name' => 'eventId',
            'classLabel' => 'bold',
            'label' => $this->_app->textStorage->getText('label.editEventSubstitute_event'),
            'dataSource' => $ds,
            'value' => $data['eventId'],
            'firstOption' => Application::get()->textStorage->getText('label.select_choose'),
            'userTextStorage' => false)), 'fi_event');
  }
  
  private function _insertButton($data) {
    $this->insertTemplateVar('fb_save',
        sprintf('<input class="fb_eSave" id="fb_eEventSubstituteSave" type="submit" name="action_eEventSubstituteSave" value="%s" />',
                $this->_app->textStorage->getText('button.editEventSubstitute_save')), false);
  }

  private function _insertMandatorySelect($data) {
    if (BCustomer::getProviderSettings($data['providerId'], 'allowMandatoryReservation')=='Y') {
      $ds = new HashDataSource(new DataSourceSettings, array('N'=>$this->_app->textStorage->getText('label.no'),'Y'=>$this->_app->textStorage->getText('label.yes')));
      $this->insert(new GuiFormSelect(array(
        'id' => 'fi_mandatory',
        'name' => 'mandatory',
        'label' => $this->_app->textStorage->getText('label.editEventSubstitute_mandatory'),
        'dataSource' => $ds,
        'value' => $data['mandatory'],
        'firstOption' => Application::get()->textStorage->getText('label.select_choose'),
        'userTextStorage' => false)), 'fi_mandatory');
    } else $this->insertTemplateVar('fi_mandatory', '<input type="hidden" name="mandatory" value="N" />', false);
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
              <td><select meaning="user" name="eventAttendeeUser[]">%s</select></td>
              <input meaning="firstname" type="hidden" name="eventAttendeeFirstname[]" value="%s"/>
              <input meaning="lastname" type="hidden" name="eventAttendeeLastname[]" value="%s"/>
              <input meaning="email" type="hidden" name="eventAttendeeEmail[]" value="%s"/>
            </tr>';
    } else {
      $html = sprintf('<table class="attendee" id="fi_eventAttendee">
              <tr>
                <th>{__label.editEventSubstitute_eventAttendee_firstname}</th>
                <th>{__label.editEventSubstitute_eventAttendee_lastname}</th>
                <th>{__label.editEventSubstitute_eventAttendee_email}</th>
              </tr>');
      $template = '<tr meaning="attendee"%s>
              <input meaning="user" type="hidden" name="eventAttendeeUser[]" value="%s"/>
              <td><input meaning="firstname" class="mediumText" name="eventAttendeeFirstname[]" type="text" value="%s"/></td>
              <td><input meaning="lastname" class="mediumText" name="eventAttendeeLastname[]" type="text" value="%s"/></td>
              <td><input meaning="email" class="mediumText" name="eventAttendeeEmail[]" type="text" value="%s"/></td>
            </tr>';
    }

    // ziskam poducty uzivatele
    $subaccounts = array();
    if ($data['userId']) {
      $bUser = new BUser($data['userId']);
      $subaccounts = $bUser->getSubaccount();
    }

    // vlozim HTML ucastniku do formulare
    $aNum = $data['places']>1?$data['places']:1;
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

      $html .= sprintf($template, $id, $eventAttendeeUser,
        ifsetor($data['eventAttendeeFirstname'][$i],''),ifsetor($data['eventAttendeeLastname'][$i],''),ifsetor($data['eventAttendeeEmail'][$i],''));
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
    $this->setTemplateFile(dirname(__FILE__).'/EventSubstituteEdit.html');

    $validator = Validator::get('eventSubstitute', 'EventSubstituteValidator');
    $data = $validator->getValues();
    #adump($data);

    foreach ($data as $k => $v) {
      if (!is_array($v)) { $this->insertTemplateVar($k, $v); }
    }

    $this->insertTemplatevar('title', $this->_app->textStorage->getText('label.editEventSubstitute_title'));
    
    if ($data['eventReservationMaxAttendees']>1) $this->insertTemplateVar('readonlyPlaces', '');
    else $this->insertTemplateVar('readonlyPlaces', ' readonly="readonly"', false);
    
    $jsAttributeValues = '';
    foreach ($data['attribute'] as $id=>$value) {
      $jsAttributeValues .= sprintf("data.values['%d'] = '%s';\n", $id, $value);
    }
    
    global $AJAX;
    $this->_app->document->addJavascript(sprintf("
            $(document).ready(function() {
              $('#fi_user').combogrid({
                url: '%s?action=getUser&sessid={%%sessid%%}',
                debug: true,
                //replaceNull: true,
                colModel: [{'columnName':'id','width':'10','label':'id','hidden':'true'},
                           {'columnName':'name','width':'30','label':'Jméno','align':'left'},
                           {'columnName':'address','width':'40','label':'Adresa','align':'left'},
                           {'columnName':'email','width':'30','label':'Email','align':'left'}],
                select: function(event,ui) {
                  $('#fi_user').val(ui.item.name);
                  $('#fi_userId').val(ui.item.id);
                  
                  if (useUserSubaccount) fillSubaccountSelect(ui.item.id);
                  else {
                    $('#fi_attendee_1 [meaning=firstname]').each(function() { if (!$(this).val()) $(this).val(ui.item.firstname); });
                    $('#fi_attendee_1 [meaning=lastname]').each(function() { if (!$(this).val()) $(this).val(ui.item.lastname); });
                    $('#fi_attendee_1 [meaning=email]').each(function() { if (!$(this).val()) $(this).val(ui.item.email); });
                  }
                    
                  return false;
                }
              });
              
              $('#fi_places').change(function() {
                var person = parseInt($('#fi_places').val())*parseInt($('#fi_eventCoAttendees').val());
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
              })
              
              function getAttribute() {
                data = { eventId: $('#fi_eventId').val(), values: {} }
                %s
                
                $.ajax({
                  type: 'GET',
                  url: '%s?action=guiReservationAttribute&sessid={%%sessid%%}&target=backoffice',
                  dataType: 'json',
                  data: data,
                  success: function(data) {
                    $('#fi_attribute').html(data.output);
                  },
                  error: function(error) { alert('{__label.ajaxError}'); }
                });
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
              
              getAttribute();
            });", $AJAX['adminUrl'], $jsAttributeValues, $AJAX['adminUrl'],
            $this->_app->session->getId(), $AJAX['adminUrl']));
    
    #$this->_insertEventSelect($data);
    $this->_insertMandatorySelect($data);
    $this->_insertEventAttendee($data);
    $this->_insertButton($data);
  }
}

?>
