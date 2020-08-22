<?php

class GuiAttendeeReport extends GuiReport {
  
  private function _insertEventSelect() {
    $s = new SEvent;
    $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
    if (!$this->_app->auth->isAdministrator()) $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
    $s->addStatement(new SqlStatementMono($s->columns['center'], sprintf('%%s IN (%s)', $this->_app->auth->getAllowedCenter('list'))));
    if ($this->_data['providerId']) $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_data['providerId'], '%s=%s'));
    if ($this->_data['centerId']) $s->addStatement(new SqlStatementBi($s->columns['center'], $this->_data['centerId'], '%s=%s'));
    $s->addOrder(new SqlStatementAsc($s->columns['name']));
    $s->setColumnsMask(array('event_id','name','all_resource_name','start'));
    $res = $this->_app->db->doQuery($s->toString());
    $h = array();
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $h[$row['event_id']] = sprintf('%s %s- %s', $row['name'],
                    $row['all_resource_name']?'('.$row['all_resource_name'].') ':'',
                    $this->_app->regionalSettings->convertDateTimeToHuman($row['start']));
    }
    $ds = new HashDataSource(new DataSourceSettings, $h);
    $this->insert(new GuiFormSelect(array(
            'id' => 'fi_event',
            'name' => 'eventId',
            'dataSource' => $ds,
            'value' => $this->_data['eventId'],
            'firstOption' => Application::get()->textStorage->getText('label.select_noMatter'),
            'showDiv' => false,
            'userTextStorage' => false)), 'fi_event');
  }

  private function _insertPastEventSelect() {
    $s = new SEvent;
    $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='N'"));
    if (!$this->_app->auth->isAdministrator()) $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
    $s->addStatement(new SqlStatementMono($s->columns['center'], sprintf('%%s IN (%s)', $this->_app->auth->getAllowedCenter('list'))));
    if ($this->_data['providerId']) $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_data['providerId'], '%s=%s'));
    if ($this->_data['centerId']) $s->addStatement(new SqlStatementBi($s->columns['center'], $this->_data['centerId'], '%s=%s'));
    $s->addOrder(new SqlStatementAsc($s->columns['name']));
    $s->setColumnsMask(array('event_id','name','all_resource_name','start'));
    $res = $this->_app->db->doQuery($s->toString());
    $h = array();
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $h[$row['event_id']] = sprintf('%s %s- %s', $row['name'],
        $row['all_resource_name']?'('.$row['all_resource_name'].') ':'',
        $this->_app->regionalSettings->convertDateTimeToHuman($row['start']));
    }
    $ds = new HashDataSource(new DataSourceSettings, $h);
    $this->insert(new GuiFormSelect(array(
      'id' => 'fi_pastEvent',
      'name' => 'pastEventId',
      'dataSource' => $ds,
      'value' => $this->_data['pastEventId'],
      'firstOption' => Application::get()->textStorage->getText('label.select_noMatter'),
      'showDiv' => false,
      'userTextStorage' => false)), 'fi_pastEvent');
  }
  
  private function _insertOrganiserSelect() {
    $select = new SUserRegistration;
    $select->addStatement(new SqlStatementBi($select->columns['organiser'], $select->columns['power_organiser'], "(%s='Y' OR %s='Y')"));
    if ($this->_data['providerId']) $select->addStatement(new SqlStatementBi($select->columns['provider'], $this->_data['providerId'], '%s=%s'));
    elseif (!$this->_app->auth->isAdministrator()) $select->addStatement(new SqlStatementBi($select->columns['provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
    $select->setColumnsMask(array('user','fullname'));
    $ds = new SqlDataSource(new DataSourceSettings, $select);
    $this->insert(new GuiFormSelect(array(
            'id' => 'fi_organiser',
            'name' => 'organiserId',
            'dataSource' => $ds,
            'value' => $this->_data['organiserId'],
            'firstOption' => Application::get()->textStorage->getText('label.select_noMatter'),
            'showDiv' => false,
            'userTextStorage' => false)), 'fi_organiser');
  }

  private function _insertFailedSelect() {
    $hash = array(
      'Y'   => $this->_app->textStorage->getText('label.yes'),
      'N'   => $this->_app->textStorage->getText('label.no'),
    );
    $ds = new HashDataSource(new DataSourceSettings, $hash);
    $this->insert(new GuiFormSelect(array(
      'id' => 'fi_failed',
      'name' => 'failed',
      'showDiv' => false,
      'dataSource' => $ds,
      'value' => $this->_data['failed'],
      'firstOption' => Application::get()->textStorage->getText('label.select_noMatter'),
      'userTextStorage' => false)), 'fi_failed');
  }

  private function _getInsertedTag() {
    $ret = '';

    foreach (explode(',',$this->_data['tag']) as $tagId) {
      if (!$tagId) continue;

      $o = new OTag($tagId);
      $oData = $o->getData();
      $tagName = $oData['name'];

      $ret .= "$('#fi_tag').tokenInput('add', {id: $tagId, name: '$tagName'});";
    }

    return $ret;
  }
  
  protected function _userRender() {
    $validator = Validator::get('attendeeReport', 'AttendeeReportValidator');
    $this->_data = $validator->getValues();
    $this->_section = 'attendee';
    $this->_attributeApplicableType = 'SUBACCOUNT';
    
    parent::_userRender();
    
    $this->setTemplateFile(dirname(__FILE__).'/Attendee.html');

    $this->insertTemplateVar('title', $this->_app->textStorage->getText('label.report_attendee_title'));

    $this->_insertCenterSelect();
    $this->_insertEventSelect();
    $this->_insertPastEventSelect();
    $this->_insertOrganiserSelect();
    $this->_insertFailedSelect();
    
    global $AJAX;
    $this->_app->document->addJavascript(sprintf("
              $(document).ready(function() {
                $('#fi_from').datetimepicker({format:'d.m.Y',dayOfWeekStart:'1',timepicker:false,allowBlank:true,scrollInput:false});
                $('#fi_to').datetimepicker({format:'d.m.Y',dayOfWeekStart:'1',timepicker:false,allowBlank:true,scrollInput:false});
                
                $('#fi_tag').tokenInput('%s?action=getTag&provider=%s',{
                  minChars: 0,
                  showAllResults: true,
                  queryParam: 'term', theme: 'facebook',
                  preventDuplicates: true,
                  hintText: '{__label.searchTag_hint}',
                  searchingText: '{__label.searchTag_searching}',
                  noResultsText: '{__label.searchTag_noResult}',
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
                      url: '%s?action=getEvent',
                      success: function(data) {
                          var centerCombo = $('#fi_event').html('');
                          centerCombo.append('<option value=\"\">%s</option>');
                          
                          $.each(data, function(index,element) {
                            centerCombo.append('<option value=\"'+element.id+'\">'+element.info+'</option>');
                          });
                      },
                      error: function(error) { alert('{__label.ajaxError}'); }
                  });
                  
                  $.ajax({
                      type: 'GET',
                      dataType: 'json',
                      data: { provider : $(this).val(), inactive: 'Y', active: 'N' },
                      url: '%s?action=getEvent',
                      success: function(data) {
                          var centerCombo = $('#fi_pastEvent').html('');
                          centerCombo.append('<option value=\"\">%s</option>');
                          
                          $.each(data, function(index,element) {
                            centerCombo.append('<option value=\"'+element.id+'\">'+element.info+'</option>');
                          });
                      },
                      error: function(error) { alert('{__label.ajaxError}'); }
                  });
                });
                
                $('#fi_center').change(function() {
                  var data = {};
                  if ($('#fi_provider').val()) data.provider = $('#fi_provider').val();
                  if ($(this).val()) data.center = $(this).val();
                  
                  $.ajax({
                      type: 'GET',
                      dataType: 'json',
                      data: data,
                      url: '%s?action=getEvent',
                      success: function(data) {
                          var centerCombo = $('#fi_event').html('');
                          centerCombo.append('<option value=\"\">%s</option>');
                          
                          $.each(data, function(index,element) {
                            centerCombo.append('<option value=\"'+element.id+'\">'+element.info+'</option>');
                          });
                      },
                      error: function(error) { alert('{__label.ajaxError}'); }
                  });
                  
                  $.ajax({
                    type: 'GET',
                    dataType: 'json',
                    data: data,
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
                  
                  data.inactive = 'Y';
                  data.active = 'N';
                  $.ajax({
                      type: 'GET',
                      dataType: 'json',
                      data: data,
                      url: '%s?action=getEvent',
                      success: function(data) {
                          var centerCombo = $('#fi_pastEvent').html('');
                          centerCombo.append('<option value=\"\">%s</option>');
                          
                          $.each(data, function(index,element) {
                            centerCombo.append('<option value=\"'+element.id+'\">'+element.info+'</option>');
                          });
                      },
                      error: function(error) { alert('{__label.ajaxError}'); }
                  });
                });
                
                $('#fi_state').change(function() {
                  showHideInputs();
                });
                
                $('#fi_payed').change(function() {
                  showHideInputs();
                });
              });",
              $AJAX['adminUrl'], $this->_data['providerId'], $this->_getInsertedTag(),
              $AJAX['adminUrl'], $this->_app->textStorage->getText('label.select_noMatter'),
              $AJAX['adminUrl'], $this->_app->textStorage->getText('label.select_noMatter'),
              $AJAX['adminUrl'], $this->_app->textStorage->getText('label.select_noMatter'),
              $AJAX['adminUrl'], $this->_app->textStorage->getText('label.select_noMatter'),
              $AJAX['adminUrl'], $this->_app->textStorage->getText('label.select_noMatter'),
              $AJAX['adminUrl'], $this->_app->textStorage->getText('label.select_noMatter')));
  }
}

?>
