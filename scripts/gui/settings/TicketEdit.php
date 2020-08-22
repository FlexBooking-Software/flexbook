<?php

class GuiEditTicket extends GuiElement {

  private function _insertProviderSelect($data) {
    if (!$this->_app->auth->isAdministrator()) {
      $this->insertTemplateVar('fi_provider', sprintf('<input type="hidden" name="providerId" value="%s" />', $this->_app->auth->getActualProvider()), false);
    } else {
      $select = new SProvider;
      $select->setColumnsMask(array('provider_id','name'));
      $ds = new SqlDataSource(new DataSourceSettings, $select);
      $this->insert(new GuiFormSelect(array(
              'id' => 'fi_provider',
              'classLabel' => 'bold',
              'name' => 'providerId',
              'label' => $this->_app->textStorage->getText('label.editTicket_provider'),
              'dataSource' => $ds,
              'value' => $data['providerId'],
              'firstOption' => Application::get()->textStorage->getText('label.select_choose'),
              'userTextStorage' => false)), 'fi_provider');
    }
  }
  
  private function _insertValidity($data) {
    $this->insert(new GuiFormSelect(array(
            'id' => 'fi_validtyUnit',
            'name' => 'validityUnit',
            'showDiv' => false,
            'dataSource' => new HashDataSource(new DataSourceSettings, array(
                                        'DAY'     => $this->_app->textStorage->getText('label.day_l'),
                                        'WEEK'     => $this->_app->textStorage->getText('label.week_l'),
                                        'MONTH'     => $this->_app->textStorage->getText('label.month_l'),
                                        'YEAR'     => $this->_app->textStorage->getText('label.year_l'),
                                        )),
            'value' => $data['validityUnit'],
            'firstOption' => Application::get()->textStorage->getText('label.select_choose'),
            'userTextStorage' => false)), 'fi_validityUnit');

    if ($data['validityType']=='LENGTH') {
      $this->insertTemplateVar('noLimitChecked', '', false);
      $this->insertTemplateVar('lengthChecked', 'checked="checked"', false);
      $this->insertTemplateVar('periodChecked', '', false);
    } elseif ($data['validityType']=='PERIOD') {
      $this->insertTemplateVar('noLimitChecked', '', false);
      $this->insertTemplateVar('lengthChecked', '', false);
      $this->insertTemplateVar('periodChecked', 'checked="checked"', false);
    } else {
      $this->insertTemplateVar('noLimitChecked', 'checked="checked"', false);
      $this->insertTemplateVar('lengthChecked', '', false);
      $this->insertTemplateVar('periodChecked', '', false);
    }
  }
  
  private function _insertCenterSelect($data) {
    $select = new SCenter;
    $select->setColumnsMask(array('center_id','description'));
    if ($data['providerId']) $select->addStatement(new SqlStatementBi($select->columns['provider'], $data['providerId'], '%s=%s'));
    elseif (!$this->_app->auth->isAdministrator()) $select->addStatement(new SqlStatementBi($select->columns['provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
    $ds = new SqlDataSource(new DataSourceSettings, $select);
    
    $gui = new GuiElement(array('template'=>'<div class="formItem">
        <label>{__label.editTicket_center}:</label>
        {fi_center}
      </div>'));
    $gui->insert(new GuiFormSelect(array(
            'id' => 'fi_center',
            'name' => 'center',
            'showDiv' => false,
            'dataSource' => $ds,
            'value' => $data['center'],
            'firstOption' => Application::get()->textStorage->getText('label.select_all'),
            'userTextStorage' => false)), 'fi_center');
    
    $this->insert($gui, 'fi_center');
  }
  
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

  protected function _userRender() {
    $this->setTemplateFile(dirname(__FILE__).'/TicketEdit.html');

    $validator = Validator::get('ticket', 'TicketValidator');
    $data = $validator->getValues();

    foreach ($data as $k => $v) {
      if (!is_array($v)) { $this->insertTemplateVar($k, $v); }
    }
    
    if (!$data['id']) {
      $this->insertTemplatevar('title', $this->_app->textStorage->getText('label.editTicket_titleNew'));
    } else {
      $this->insertTemplatevar('title', $this->_app->textStorage->getText('label.editTicket_titleExisting'));
    }
    
    $this->_insertProviderSelect($data);
    $this->_insertValidity($data);
    $this->_insertCenterSelect($data);
    $this->_insertActive($data);
    
    $tokenValues = '';
    foreach (explode(',',$data['subjectTag']) as $tag) {
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
                  $('#fi_tag').tokenInput('%s/ajax.php?action=getTag&provider=%s',{
                    minChars: 3, queryParam: 'term', theme: 'facebook',
                    tokenValue: 'name',
                    preventDuplicates: true,
                    hintText: '%s',
                    searchingText: '%s',
                    noResultsText: '%s',
                  });
                  %s
                  
                  $('#fi_from').datetimepicker({format:'d.m.Y',dayOfWeekStart:'1',datepicker:true,timepicker:false});
                  $('#fi_to').datetimepicker({format:'d.m.Y',dayOfWeekStart:'1',datepicker:true,timepicker:false});
                  
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
                  });
                });", dirname($AJAX['adminUrl']), $this->_app->auth->getActualProvider(), $this->_app->textStorage->getText('label.searchTag_hint'),
                $this->_app->textStorage->getText('label.searchTag_searching'), $this->_app->textStorage->getText('label.searchTag_noResult'),
                $tokenValues, 
                $AJAX['adminUrl'], $this->_app->textStorage->getText('label.select_all')));
  }
}

?>
