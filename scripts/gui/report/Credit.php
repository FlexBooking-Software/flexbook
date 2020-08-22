<?php

class GuiCreditReport extends GuiReport {

  private function _insertUserSelect() {
    $select = new SUser;
    $select->setColumnsMask(array('user_id','fullname'));
    if ($this->_data['providerId']) $select->addStatement(new SqlStatementBi($select->columns['registration_provider'], $this->_data['providerId'], '%s=%s'));
    elseif (!$this->_app->auth->isAdministrator()) $select->addStatement(new SqlStatementBi($select->columns['registration_provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
    if (!$this->_app->auth->haveRight('report_admin', $this->_app->auth->getActualProvider())) $select->addStatement(new SqlStatementBi($select->columns['user_id'], $this->_app->auth->getUserId(), '%s=%s'));
    $select->addStatement(new SqlStatementTri($select->columns['registration_admin'], $select->columns['registration_supervisor'], $select->columns['registration_reception'], "((%s='Y') OR (%s='Y') OR (%s='Y'))"));
    $ds = new SqlDataSource(new DataSourceSettings, $select);
    $this->insert(new GuiFormSelect(array(
            'id' => 'fi_user',
            'name' => 'userId',
            'showDiv' => false,
            'dataSource' => $ds,
            'value' => $this->_data['userId'],
            'firstOption' => Application::get()->textStorage->getText('label.select_noMatter'),
            'userTextStorage' => false)), 'fi_user');
  }

  protected function _userRender() {
    $validator = Validator::get('creditReport', 'CreditReportValidator');
    $this->_data = $validator->getValues();
    $this->_section = 'credit';
    
    parent::_userRender();
    
    $this->setTemplateFile(dirname(__FILE__).'/Credit.html');

    $this->insertTemplateVar('title', $this->_app->textStorage->getText('label.report_credit_title'));
    
    $this->_insertUserSelect($this->_data);
    
    global $AJAX;
    $this->_app->document->addJavascript(sprintf("
              $(document).ready(function() {
                $('#fi_from').datetimepicker({format:'d.m.Y H:i',dayOfWeekStart:'1',allowBlank:true,scrollInput:false});
                $('#fi_to').datetimepicker({format:'d.m.Y H:i',dayOfWeekStart:'1',allowBlank:true,scrollInput:false});
                
                $('#fi_provider').change(function() {
                    $.ajax({
                        type: 'GET',
                        dataType: 'json',
                        data: { provider : $(this).val() },
                        url: '%s?action=getCoworker',
                        success: function(data) {
                            var combo = $('#fi_user').html('');
                            combo.append('<option value=\"\">%s</option>');
                            
                            $.each(data, function(index,element) {
                              combo.append('<option value=\"'+element.id+'\">'+element.name+'</option>');
                            });
                        },
                        error: function(error) { alert('{__label.ajaxError}'); }
                    });
                });
              });", $AJAX['adminUrl'], $this->_app->textStorage->getText('label.select_choose')));
  }
}

?>
