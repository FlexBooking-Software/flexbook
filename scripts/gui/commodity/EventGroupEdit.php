<?php

class GuiEditEventGroup extends GuiElement {
  
  private function _insertActive($data) {
    $ds = new HashDataSource(new DataSourceSettings, array('Y'=>$this->_app->textStorage->getText('label.yes'),'N'=>$this->_app->textStorage->getText('label.no')));
    $this->insert(new GuiFormSelect(array(
            'id' => 'fi_active',
            'name' => 'active',
            'showDiv' => false, 
            'dataSource' => $ds,
            'value' => $data['active'],
            'userTextStorage' => false)), 'fi_active');
  }
  
  private function _insertBadge($data) {
    $ds = new HashDataSource(new DataSourceSettings, array('Y'=>$this->_app->textStorage->getText('label.yes'),'N'=>$this->_app->textStorage->getText('label.no')));
    $this->insert(new GuiFormSelect(array(
            'id' => 'fi_badge',
            'name' => 'badge',
            'showDiv' => false, 
            'dataSource' => $ds,
            'value' => $data['badge'],
            'userTextStorage' => false)), 'fi_badge');
  }
  
  private function _insertCenterSelect($data) {
    if ($this->_app->auth->isAdministrator()) {
      $select = new SCenter;
      $select->setColumnsMask(array('center_id','description'));
      if ($data['providerId']) $select->addStatement(new SqlStatementBi($select->columns['provider'], $data['providerId'], '%s=%s'));
      elseif (!$this->_app->auth->isAdministrator()) $select->addStatement(new SqlStatementBi($select->columns['provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
      $ds = new SqlDataSource(new DataSourceSettings, $select);
      $this->insert(new GuiFormSelect(array(
              'id' => 'fi_center',
              'name' => 'centerId',
              'classLabel' => 'bold',
              'label' => $this->_app->textStorage->getText('label.editEvent_center'),
              'dataSource' => $ds,
              'value' => $data['centerId'],
              'firstOption' => Application::get()->textStorage->getText('label.select_choose'),
              'userTextStorage' => false)), 'fi_center');
    } else {
      $this->insertTemplateVar('fi_center', sprintf('<input type="hidden" id="fi_center" name="centerId" value="%s" />', $this->_app->auth->getActualCenter()), false);
    } 
  }
  
  private function _insertProviderSelect($data) {
    if ($this->_app->auth->isAdministrator()) {
      $select = new SProvider;
      $select->setColumnsMask(array('provider_id','name'));
      $ds = new SqlDataSource(new DataSourceSettings, $select);
      $this->insert(new GuiFormSelect(array(
              'id' => 'fi_provider',
              'name' => 'providerId',
              'classLabel' => 'bold',
              'label' => $this->_app->textStorage->getText('label.editEvent_provider'),
              'dataSource' => $ds,
              'value' => $data['providerId'],
              'firstOption' => Application::get()->textStorage->getText('label.select_choose'),
              'userTextStorage' => false)), 'fi_provider');
    } else {
      $this->insertTemplateVar('fi_provider', sprintf('<input type="hidden" id="fi_provider" name="providerId" value="%s" />', $this->_app->auth->getActualProvider()), false);
    }
  }
  
  private function _insertOrganiserSelect($data) {
    $select = new SUserRegistration;
    $select->addStatement(new SqlStatementBi($select->columns['organiser'], $select->columns['power_organiser'], "(%s='Y' OR %s='Y')"));
    if ($data['providerId']) $select->addStatement(new SqlStatementBi($select->columns['provider'], $data['providerId'], '%s=%s'));
    elseif (!$this->_app->auth->isAdministrator()) $select->addStatement(new SqlStatementBi($select->columns['provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
    $select->setColumnsMask(array('user','fullname'));
    $ds = new SqlDataSource(new DataSourceSettings, $select);
    $this->insert(new GuiFormSelect(array(
            'id' => 'fi_organiser',
            'name' => 'organiserId',
            'dataSource' => $ds,
            'value' => $data['organiserId'],
            'firstOption' => Application::get()->textStorage->getText('label.select_choose'),
            'showDiv' => false,
            'userTextStorage' => false)), 'fi_organiser');
    
    $this->insert($g = new GuiElement(array('template'=>'
      <div id="fi_organiserDiv">
        &nbsp;
        <input class="button" type="button" onclick="return mySubmit(\'fb_eEventSave\',\'fi_nextAction\',\'newOrganiser\');" name="action_eNewOrganiser" value="{__button.editEvent_newOrganiser}" />
      </div>
      ')), 'fi_organiser');
  }
  
  private function _insertReservationConditionSelect($data) {
    $select = new SReservationCondition;
    if ($data['providerId']) $select->addStatement(new SqlStatementBi($select->columns['provider'], $data['providerId'], '%s=%s'));
    elseif (!$this->_app->auth->isAdministrator()) $select->addStatement(new SqlStatementBi($select->columns['provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
    $select->setColumnsMask(array('reservationcondition_id','name'));
    $ds = new SqlDataSource(new DataSourceSettings, $select);
    $this->insert(new GuiFormSelect(array(
            'id' => 'fi_reservationCondition',
            'name' => 'reservationConditionId',
            'dataSource' => $ds,
            'value' => $data['reservationConditionId'],
            'firstOption' => Application::get()->textStorage->getText('label.select_choose'),
            'showDiv' => false,
            'userTextStorage' => false)), 'fi_reservationCondition');
  }
  
  private function _insertNotificationTemplateSelect($data) {
    $select = new SNotificationTemplate;
    if ($data['providerId']) $select->addStatement(new SqlStatementBi($select->columns['provider'], $data['providerId'], '%s=%s'));
    elseif (!$this->_app->auth->isAdministrator()) $select->addStatement(new SqlStatementBi($select->columns['provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
    $select->setColumnsMask(array('notificationtemplate_id','name'));
    $ds = new SqlDataSource(new DataSourceSettings, $select);
    $this->insert(new GuiFormSelect(array(
            'id' => 'fi_notificationTemplate',
            'name' => 'notificationTemplateId',
            'dataSource' => $ds,
            'value' => $data['notificationTemplateId'],
            'firstOption' => Application::get()->textStorage->getText('label.select_choose'),
            'showDiv' => false,
            'userTextStorage' => false)), 'fi_notificationTemplate');
  }
  
  private function _insertCalendarInput($data) {
    $this->insert(new GuiFormInputDate(array(
        'classInput' => 'mediumText',
        'classDiv' => 'formItem bold',
        'name' => 'startDate',
        'value' => $data['startDate'],
        'readonly' => $this->_attendee,
        'label' => $this->_app->textStorage->getText('label.editEvent_startDate'),
        'jsVarName' => 'startDate',
        'calendarDivName' => 'startDate',
        'calendarIcon' => 'img/cal.gif',
        'dateFormat' => 'dd.MM.yyyy',
        'weekStartDay' => 1,
        'todayLabel' => $this->_app->textStorage->getText('label.calendar_today'),
        'dayLabels' => $this->_app->textStorage->getText('label.calendar_dayLabels'),
        'monthLabels' => $this->_app->textStorage->getText('label.calendar_monthLabels'),
        'otherCalendars' => array('endDate'),
        'useTextStorage' => false)), 'fi_startDate');

    $this->insert(new GuiFormInputDate(array(
        'classInput' => 'mediumText',
        'classDiv' => 'formItem bold',
        'name' => 'endDate',
        'value' => $data['endDate'],
        'readonly' => $this->_attendee,
        'label' => $this->_app->textStorage->getText('label.editEvent_endDate'),
        'jsVarName' => 'endDate',
        'calendarDivName' => 'endDate',
        'calendarIcon' => 'img/cal.gif',
        'dateFormat' => 'dd.MM.yyyy',
        'weekStartDay' => 1,
        'todayLabel' => $this->_app->textStorage->getText('label.calendar_today'),
        'dayLabels' => $this->_app->textStorage->getText('label.calendar_dayLabels'),
        'monthLabels' => $this->_app->textStorage->getText('label.calendar_monthLabels'),
        'otherCalendars' => array('startDate'),
        'useTextStorage' => false)), 'fi_endDate');
  }

  private function _insertResourceSelect($data) {
    if ($data['centerId']) {
      $s = new SResource;
      $s->addStatement(new SqlStatementBi($s->columns['center'], $data['centerId'], '%s=%s'));
      $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
      $s->addOrder(new SqlStatementAsc($s->columns['name']));
      $s->setColumnsMask(array('resource_id', 'name'));

      $ds = new SqlDataSource(new DataSourceSettings, $s);
      $this->insert(new GuiFormSelect(array(
        'id' => 'fi_resourceSelect',
        'name' => 'resourceSelectId',
        'dataSource' => $ds,
        'firstOption' => Application::get()->textStorage->getText('label.select_choose'),
        'showDiv' => false,
        'userTextStorage' => false)), 'fi_resourceSelect');
    }
  }
  
  private function _insertResourceCheckbox($data) {
    if ($data['centerId']) {
      $s = new SResource;
      $s->addStatement(new SqlStatementBi($s->columns['center'], $data['centerId'], '%s=%s'));
      $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
      $s->addOrder(new SqlStatementAsc($s->columns['name']));
      $s->setColumnsMask(array('resource_id','name'));
      $res = $this->_app->db->doQuery($s->toString());
      if ($this->_app->db->getRowsNumber($res)) {
        while ($row = $this->_app->db->fetchAssoc($res)) {
          $checked = in_array($row['resource_id'], $data['resource'])?'checked="yes"':'';
          $this->insertTemplateVar('fi_resource', sprintf('<div><input type="checkbox" id="fi_resource_%s" class="inputCheckbox" name="resource[]" value="%s" %s/>&nbsp;%s</div>',
            $row['resource_id'], $row['resource_id'], $checked, $row['name']), false);
        }
      } else $this->insertTemplateVar('fi_resource','');
    } else $this->insertTemplateVar('fi_resource','');
  }
  
  private function _insertPortalSelect($data) {
    $select = new SPortal;
    $select->setColumnsMask(array('portal_id','name'));
    $ds = new SqlDataSource(new DataSourceSettings, $select);
    $this->insert(new GuiFormSelect(array(
            'id' => 'fi_portal',
            'name' => 'portal[]',
            'multiple' => true,
            'dataSource' => $ds,
            'value' => $data['portal'],
            'showDiv' => false,
            'userTextStorage' => false)), 'fi_portal');
  }
  
  private function _insertException($data) {
    $template = '';
    if (is_array($data['groupException'])&&count($data['groupException'])) {
      foreach ($data['groupException'] as $key=>$reg) {
        $template .= sprintf('<tr id="%d"><td id="exceptionFrom">%s</td><td id="exceptionTo">%s</td>
                              <td class="tdAction">[<a href="#" id="fi_editException">%s</a>][<a href="#" id="fi_removeException">%s</a>]</td>
                              <input type="hidden" name="newException[%d]" value="from#%s;to#%s"/>
                              </tr>',
                             $key, $reg['from'], $reg['to'],
                             $this->_app->textStorage->getText('button.edit'),
                             $this->_app->textStorage->getText('button.grid_remove'),
                             $key, $reg['from'], $reg['to']);
      }
    }
    $this->insertTemplateVar('fi_eventGroup_exceptionData', $template, false);
  }
  
  protected function _userRender() {
    $this->setTemplateFile(dirname(__FILE__).'/EventGroupEdit.html');

    $validator = Validator::get('eventGroup', 'EventGroupValidator');
    $data = $validator->getValues();

    foreach ($data as $k => $v) {
      if (!is_array($v)) { $this->insertTemplateVar($k, $v); }
    }

    $this->insertTemplatevar('title', $this->_app->textStorage->getText('label.editEvent_titleNewGroup'));

    $tokenValues = '';
    foreach (explode(',',$data['tag']) as $tag) {
      if (!$tag) continue;

      $s = new STag;
      $s->addStatement(new SqlStatementBi($s->columns['name'], $tag, '%s=%s'));
      $s->setColumnsMask(array('tag_id', 'name'));
      $res = $this->_app->db->doQuery($s->toString());
      $row = $this->_app->db->fetchAssoc($res);
      $id = ifsetor($row['tag_id']);

      $tokenValues .= "$('#fi_tag').tokenInput('add', {id: '$id', name: '$tag'});";
    }
    
    global $AJAX;
    $this->_app->document->addJavascript(sprintf("
                $(document).ready(function() {    
                  var tabCookieName = 'ui-eventgroup-tab';
                  var tab = $('#tab').tabs({
                          active : ($.cookie(tabCookieName) || 0),
                          activate : function( event, ui ) {
                            var newIndex = ui.newTab.parent().children().index(ui.newTab);
                            // my setup requires the custom path, yours may not
                            $.cookie(tabCookieName, newIndex);
                          }
                      });
                
                  $('#fi_start').datetimepicker({format:'d.m.Y H:i',dayOfWeekStart:'1',allowBlank:true});
                  $('#fi_end').datetimepicker({format:'d.m.Y H:i',dayOfWeekStart:'1',allowBlank:true});
                  
                  $('#fi_start').blur(function () {
                    if ($('#fi_start').val()) {
                      if ($('#fi_end').val()=='') $('#fi_end').val($('#fi_start').val());
                      else if ($('#fi_end').val()<$('#fi_start').val()) $('#fi_end').val($('#fi_start').val());
                    }
                  });
                  $('#fi_end').blur(function () {
                    if ($('#fi_end').val()) {
                      if ($('#fi_start').val()=='') $('#fi_start').val($('#fi_end').val());
                      else if ($('#fi_end').val()<$('#fi_start').val()) $('#fi_end').val($('#fi_start').val());
                    }
                  });
                  
                  $('#fi_tag').tokenInput('%s/ajax.php?action=getTag',{
                    minChars: 3, queryParam: 'term', theme: 'facebook',
                    tokenValue: 'name',
                    preventDuplicates: true,
                    hintText: '{__label.searchTag_hint}',
                    searchingText: '{__label.searchTag_searching}',
                    noResultsText: '{__label.searchTag_noResult}',
                    onResult: function (item) {
                      if ($.isEmptyObject(item)) {
                        return [ { id: $('tester').text(),name: $('tester').text() } ];
                      } else {
                        return item;
                      }
                    },
                  });
                  %s
      
                  $('#fi_provider').change(function() {
                    $.ajax({
                        type: 'GET',
                        dataType: 'json',
                        data: { provider : $(this).val() },
                        url: '%s?action=getCenter',
                        success: function(data) {
                            var centerCombo = $('#fi_center').html('');
                            centerCombo.append('<option value=\"\">%s</option>');
                            
                            $.each(data, function(index,element) {
                              centerCombo.append('<option value=\"'+element.id+'\">'+element.name+'</option>');
                            });
                        },
                        error: function(error) { alert('{__label.ajaxError}'); }
                    });
                    
                    $.ajax({
                        type: 'GET',
                        dataType: 'json',
                        data: { provider : $(this).val() },
                        url: '%s?action=getOrganiser',
                        success: function(data) {
                            var combo = $('#fi_organiser').html('');
                            combo.append('<option value=\"\">%s</option>');
                            
                            $.each(data, function(index,element) {
                              combo.append('<option value=\"'+element.id+'\">'+element.name+'</option>');
                            });
                        },
                        error: function(error) { alert('{__label.ajaxError}'); }
                    });
                    
                    providerChange();
                    
                    return false;
                  });
                  
                  $('#fi_resourceSelect').change(function() {
                    $('#fi_name').val($('#fi_resourceSelect option:selected').html());
                    
                    $('#fi_resource .inputCheckbox').prop('checked', false);
                    $('#fi_resource_'+$('#fi_resourceSelect').val()).prop('checked', true);
                    
                    $.ajax({
                        type: 'GET',
                        dataType: 'json',
                        data: { id : $('#fi_resourceSelect').val() },
                        url: '%s?action=getResource',
                        success: function(data) {
                          if (data.description) $('#fi_description').html(data.description);
                          if (data.organiser) $('#fi_organiser').val(data.organiser);
                          else $('#fi_organiser').val('');
                        }
                    });
                  });
                  
                  $('#fi_center').change(function() {
                    $.ajax({
                        type: 'GET',
                        dataType: 'json',
                        data: { center : $(this).val() },
                        url: '%s?action=getResource',
                        success: function(data) {
                            var resourceCheck = $('#fi_resource').html('');
                            
                            $.each(data, function(index,element) {
                              resourceCheck.append('<div><input type=\"checkbox\" class=\"inputCheckbox\" name=\"resource[]\" value=\"'+element.id+'\"/>&nbsp;'+element.name+'</div>');
                            });
                        },
                        error: function(error) { alert('{__label.ajaxError}'); }
                    });
                    
                    return false;
                  });
                  
                  function providerChange() {
                    if ($('#fi_provider').val()) $('#fi_organiserDiv').show();
                    else $('#fi_organiserDiv').hide();
                  };
                                
                  $('#fi_newException').click(function() {
                    $('#fi_newException_form').dialog('open');
                  });
                  
                  $('#form').on('click','#fi_editException', function() {
                      var tr = $(this).closest('tr');
                      $('#editException_index').val(tr.attr('id'));
                      $('#editException_from').val(tr.find('#exceptionFrom').html());
                      $('#editException_to').val(tr.find('#exceptionTo').html());
                      
                      $('#fi_newException_form').dialog('open');
                      
                      return false;
                  });
                  $('#form').on('click','#fi_removeException', function() {
                      $(this).closest('tr').remove();
                      return false;
                  });
                  
                  $('#editException_from').datetimepicker({format:'d.m.Y H:i',dayOfWeekStart:'1'});
                  $('#editException_to').datetimepicker({format:'d.m.Y H:i',dayOfWeekStart:'1'});
                  $('#editException_from').blur(function () {
                    if ($('#editException_from').val()&&($('#editException_to').val()=='')) {
                      $('#editException_to').val($('#editException_from').val());
                    }
                  });
                  
                  $('#fi_newException_form').dialog({
                      autoOpen: false,
                      height: 220,
                      width: 450,
                      modal: true,
                      buttons: {
                          '{__button.editEvent_groupExceptionOk}': function() {
                              bValid = true;
                              if (bValid) {      
                                  var html =
                                          '<td id=\"exceptionFrom\">' + $('#editException_from').val() + '</td>' +
                                          '<td id=\"exceptionTo\">' + $('#editException_to').val() + '</td>' +
                                          '<td class=\"tdAction\">[<a href=\"#\" id=\"fi_editException\">{__button.edit}</a>][<a href=\"#\" id=\"fi_removeException\">{__button.grid_remove}]</a></td>';
                                  
                                  var tmp;
                                  if ($('#editException_index').val()) { tmp = $('#editException_index').val(); }
                                  else { tmp = Math.floor(Math.random()*10000); };
                                  
                                  html += '<input type=\"hidden\" name=\"newException['+tmp+']\" value=\"from#'+$('#editException_from').val()+';to#'+$('#editException_to').val()+'\"/>';
              
                                  if ($('#editException_index').val()) {
                                      $('#fi_eventGroup_exception tbody').find('tr#'+tmp).html(html);
                                  } else {
                                      $('#fi_eventGroup_exception tbody').append('<tr id=\"'+tmp+'\">'+html+'</tr>');
                                  }
                                  
                                  $(this).dialog('close');
                              }
                          },
                          '{__button.editEvent_groupExceptionCancel}': function() {
                              $(this).dialog('close');
                          }
                      },
                      close: function() {
                          $('#editException_index').val('');
                          $('#editException_from').val('');
                          $('#editException_to').val('');
                      }
                  });
                });",
                dirname($AJAX['adminUrl']), $tokenValues,
                $AJAX['adminUrl'], $this->_app->textStorage->getText('label.select_choose'),
                $AJAX['adminUrl'], $this->_app->textStorage->getText('label.select_choose'),
                $AJAX['adminUrl'], $AJAX['adminUrl']));
    
    $this->_insertCenterSelect($data);
    $this->_insertProviderSelect($data);
    $this->_insertOrganiserSelect($data);
    $this->_insertReservationConditionSelect($data);
    $this->_insertNotificationTemplateSelect($data);
    $this->_insertResourceSelect($data);
    $this->_insertResourceCheckbox($data);
    $this->_insertActive($data);
    $this->_insertBadge($data);
    $this->_insertPortalSelect($data);
    $this->_insertException($data);
  }
}

?>
