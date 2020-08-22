<?php

class GuiReservationReport extends GuiReport {
  
  private function _insertStateSelect() {
    $hash = array(
                'ACTIVE'        => $this->_app->textStorage->getText('label.listReservation_stateACTIVE'),
                'REALISED'      => $this->_app->textStorage->getText('label.listReservation_stateREALISED'),
                'VALID'         => $this->_app->textStorage->getText('label.listReservation_stateVALID'),
                'FAILED'        => $this->_app->textStorage->getText('label.listReservation_stateFAILED'),
                'CANCELLED'     => $this->_app->textStorage->getText('label.listReservation_stateCANCELLED'),
                'ONLINEPAYMENT' => $this->_app->textStorage->getText('label.listReservation_stateONLINEPAYMENT'),
                );
    $ds = new HashDataSource(new DataSourceSettings, $hash);
    $this->insert(new GuiFormSelect(array(
            'id' => 'fi_state',
            'name' => 'state',
            'showDiv' => false, 
            'dataSource' => $ds,
            'value' => $this->_data['state'],
            'firstOption' => Application::get()->textStorage->getText('label.select_noMatter'),
            'userTextStorage' => false)), 'fi_state');
  }
  
  private function _insertPayedSelect() {
    $hash = array(
                'Y'   => $this->_app->textStorage->getText('label.yes'),
                'N'   => $this->_app->textStorage->getText('label.no'),
                );
    $ds = new HashDataSource(new DataSourceSettings, $hash);
    $this->insert(new GuiFormSelect(array(
            'id' => 'fi_payed',
            'name' => 'payed',
            'showDiv' => false, 
            'dataSource' => $ds,
            'value' => $this->_data['payed'],
            'firstOption' => Application::get()->textStorage->getText('label.select_noMatter'),
            'userTextStorage' => false)), 'fi_payed');
  }

  private function _insertMandatorySelect() {
    if (BCustomer::getProviderSettings($this->_data['providerId'], 'allowMandatoryReservation')=='Y') {
      $hash = array(
        'Y'   => $this->_app->textStorage->getText('label.yes'),
        'N'   => $this->_app->textStorage->getText('label.no'),
      );
      $ds = new HashDataSource(new DataSourceSettings, $hash);
      $this->insert(new GuiFormSelect(array(
        'id' => 'fi_mandatory',
        'name' => 'mandatory',
        'label' => $this->_app->textStorage->getText('label.report_reservation_mandatory'),
        'dataSource' => $ds,
        'value' => $this->_data['mandatory'],
        'firstOption' => Application::get()->textStorage->getText('label.select_noMatter'),
        'userTextStorage' => false)), 'fi_mandatory');
    } else $this->insertTemplateVar('fi_mandatory', '');
  }

  private function _insertPaymentTypeSelect() {
    $hash = array(
      'credit'      => $this->_app->textStorage->getText('label.report_reservation_paymentType_credit'),
      'ticket'      => $this->_app->textStorage->getText('label.report_reservation_paymentType_ticket'),
      'csob'        => $this->_app->textStorage->getText('label.report_reservation_paymentType_csob'),
      'comgate'     => $this->_app->textStorage->getText('label.report_reservation_paymentType_comgate'),
      'gpwebpay'    => $this->_app->textStorage->getText('label.report_reservation_paymentType_gpwebpay'),
    );
    $ds = new HashDataSource(new DataSourceSettings, $hash);
    $this->insert(new GuiFormSelect(array(
      'id' => 'fi_paymentType',
      'name' => 'paymentType',
      'showDiv' => false,
      'dataSource' => $ds,
      'value' => $this->_data['paymentType'],
      'firstOption' => Application::get()->textStorage->getText('label.select_noMatter'),
      'userTextStorage' => false)), 'fi_paymentType');
  }
  
  private function _insertPriceManualSelect() {
    $hash = array(
      'Y'   => $this->_app->textStorage->getText('label.yes'),
      'N'   => $this->_app->textStorage->getText('label.no'),
    );
    $ds = new HashDataSource(new DataSourceSettings, $hash);
    $this->insert(new GuiFormSelect(array(
            'id' => 'fi_priceManual',
            'name' => 'priceManual',
            'showDiv' => false, 
            'dataSource' => $ds,
            'value' => $this->_data['priceManual'],
            'firstOption' => Application::get()->textStorage->getText('label.select_noMatter'),
            'userTextStorage' => false)), 'fi_priceManual');
  }

  private function _insertResourceSelect() {
    $s = new SResource;
    $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
    if (!$this->_app->auth->isAdministrator()) $this->_data['providerId'] = $this->_app->auth->getActualProvider();
    $s->addStatement(new SqlStatementMono($s->columns['center'], sprintf('%%s IN (%s)', $this->_app->auth->getAllowedCenter('list'))));
    if ($this->_data['providerId']) $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_data['providerId'], '%s=%s'));
    if ($this->_data['centerId']) $s->addStatement(new SqlStatementBi($s->columns['center'], $this->_data['centerId'], '%s=%s'));
    $s->addOrder(new SqlStatementAsc($s->columns['name']));
    $s->setColumnsMask(array('resource_id','name'));
      
    $ds = new SqlDataSource(new DataSourceSettings, $s);
    $this->insert(new GuiFormSelect(array(
            'id' => 'fi_resource',
            'name' => 'resourceId',
            'label' => $this->_app->textStorage->getText('label.report_resource'),
            'dataSource' => $ds,
            'value' => $this->_data['resourceId'],
            'firstOption' => Application::get()->textStorage->getText('label.select_noMatter'),
            'userTextStorage' => false)), 'fi_resource');
  }
  
  private function _insertEventSelect() {
    $s = new SEvent;
    $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
    if (!$this->_app->auth->isAdministrator()) $this->_data['providerId'] = $this->_app->auth->getActualProvider();
    $s->addStatement(new SqlStatementMono($s->columns['center'], sprintf('%%s IN (%s)', $this->_app->auth->getAllowedCenter('list'))));
    if ($this->_data['providerId']) $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_data['providerId'], '%s=%s'));
    if ($this->_data['centerId']) $s->addStatement(new SqlStatementBi($s->columns['center'], $this->_data['centerId'], '%s=%s'));
    if ($this->_data['resourceId']) $s->addStatement(new SqlStatementBi($s->columns['resource'], $this->_data['resourceId'], '%s=%s'));
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
            'label' => $this->_app->textStorage->getText('label.report_event'),
            'dataSource' => $ds,
            'value' => $this->_data['eventId'],
            'firstOption' => Application::get()->textStorage->getText('label.select_noMatter'),
            'userTextStorage' => false)), 'fi_event');
  }

  private function _insertPastEventSelect() {
    $s = new SEvent;
    $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='N'"));
    if (!$this->_app->auth->isAdministrator()) $this->_data['providerId'] = $this->_app->auth->getActualProvider();
    $s->addStatement(new SqlStatementMono($s->columns['center'], sprintf('%%s IN (%s)', $this->_app->auth->getAllowedCenter('list'))));
    if ($this->_data['providerId']) $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_data['providerId'], '%s=%s'));
    if ($this->_data['centerId']) $s->addStatement(new SqlStatementBi($s->columns['center'], $this->_data['centerId'], '%s=%s'));
    if ($this->_data['resourceId']) $s->addStatement(new SqlStatementBi($s->columns['resource'], $this->_data['resourceId'], '%s=%s'));
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
      'label' => $this->_app->textStorage->getText('label.report_pastEvent'),
      'dataSource' => $ds,
      'value' => $this->_data['pastEventId'],
      'firstOption' => Application::get()->textStorage->getText('label.select_noMatter'),
      'userTextStorage' => false)), 'fi_pastEvent');
  }
  
  private function _insertAccountTypeSelect() {
    $select = new SProviderAccountType;
    if ($this->_data['providerId']) $select->addStatement(new SqlStatementBi($select->columns['provider'], $this->_data['providerId'], '%s=%s'));
    elseif (!$this->_app->auth->isAdministrator()) $select->addStatement(new SqlStatementBi($select->columns['provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
    $select->setColumnsMask(array('provideraccounttype_id','name'));
    $ds = new SqlDataSource(new DataSourceSettings, $select);
    $this->insert(new GuiFormSelect(array(
            'id' => 'fi_accountType',
            'name' => 'accountTypeId',
            'dataSource' => $ds,
            'value' => $this->_data['accountTypeId'],
            'firstOption' => Application::get()->textStorage->getText('label.select_noMatter'),
            'showDiv' => false,
            'userTextStorage' => false)), 'fi_accountType');
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
    $validator = Validator::get('reservationReport', 'ReservationReportValidator');
    $this->_data = $validator->getValues();
    $this->_section = 'reservation';
    
    parent::_userRender();
    
    $this->setTemplateFile(dirname(__FILE__).'/Reservation.html');

    $this->insertTemplateVar('title', $this->_app->textStorage->getText('label.report_reservation_title'));

    $this->_insertMandatorySelect();
    $this->_insertStateSelect();
    $this->_insertPayedSelect();
    $this->_insertPaymentTypeSelect();
    $this->_insertAccountTypeSelect();
    $this->_insertPriceManualSelect();
    $this->_insertCenterSelect();
    $this->_insertResourceSelect();
    $this->_insertEventSelect();
    $this->_insertPastEventSelect();
    
    global $AJAX;
    $this->_app->document->addJavascript(sprintf("
              function showHideInputs() {
                if ($('#fi_state').val()=='CANCELLED') {
                  $('#fi_cancelledRange').show();
                } else {
                  $('#fi_cancelledRange').hide();
                  $('#fi_cancelledFrom').val('');
                  $('#fi_cancelledTo').val('');
                }   
                
                if ($('#fi_payed').val()=='Y') {
                  $('#fi_paymentTypeDiv').show();
                } else {
                  $('#fi_paymentTypeDiv').hide();
                }                            
              }
              
              $(document).ready(function() {
                showHideInputs();
                
                $('#fi_from').datetimepicker({format:'d.m.Y',dayOfWeekStart:'1',timepicker:false,allowBlank:true,scrollInput:false});
                $('#fi_to').datetimepicker({format:'d.m.Y',dayOfWeekStart:'1',timepicker:false,allowBlank:true,scrollInput:false});
                $('#fi_realiseFrom').datetimepicker({format:'d.m.Y',dayOfWeekStart:'1',timepicker:false,allowBlank:true,scrollInput:false});
                $('#fi_realiseTo').datetimepicker({format:'d.m.Y',dayOfWeekStart:'1',timepicker:false,allowBlank:true,scrollInput:false});
                $('#fi_cancelledFrom').datetimepicker({format:'d.m.Y',dayOfWeekStart:'1',timepicker:false,allowBlank:true,scrollInput:false});
                $('#fi_cancelledTo').datetimepicker({format:'d.m.Y',dayOfWeekStart:'1',timepicker:false,allowBlank:true,scrollInput:false});
                
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
                      url: '%s?action=getResource',
                      success: function(data) {
                          var centerCombo = $('#fi_resource').html('');
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
                  
                  $.ajax({
                      type: 'GET',
                      dataType: 'json',
                      data: { provider : $(this).val() },
                      url: '%s?action=getAccountType',
                      success: function(data) {
                          var combo = $('#fi_accountType').html('');
                          combo.append('<option value=\"\">%s</option>');
                          
                          $.each(data, function(index,element) {
                            combo.append('<option value=\"'+element.id+'\">'+element.name+'</option>');
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
                      url: '%s?action=getResource',
                      success: function(data) {
                          var centerCombo = $('#fi_resource').html('');
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
                
                $('#fi_resource').change(function() {
                  var data = {};
                  if ($('#fi_provider').val()) data.provider = $('#fi_provider').val();
                  if ($('#fi_center').val()) data.center = $('#fi_center').val();
                  if ($(this).val()) data.resource = $(this).val();
                  
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
              $AJAX['adminUrl'], $this->_app->textStorage->getText('label.select_noMatter'),
              $AJAX['adminUrl'], $this->_app->textStorage->getText('label.select_noMatter'),
              $AJAX['adminUrl'], $this->_app->textStorage->getText('label.select_noMatter'),
              $AJAX['adminUrl'], $this->_app->textStorage->getText('label.select_noMatter')));
  }
}

?>
