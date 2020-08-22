<?php

class GuiUserReport extends GuiReport {
  
  private function _insertTypeSelect() {
    if ($this->_data['providerId']&&(BCustomer::getProviderSettings($this->_data['providerId'],'userSubaccount')=='Y')) {
      $hash = array(
        'PRIMARY' => $this->_app->textStorage->getText('label.report_user_typePRIMARY'),
        'SUBACCOUNT' => $this->_app->textStorage->getText('label.report_user_typeSUBACCOUNT')
      );
      $ds = new HashDataSource(new DataSourceSettings, $hash);
      $this->insert(new GuiFormSelect(array(
        'id' => 'fi_type',
        'name' => 'type',
        'dataSource' => $ds,
        'value' => $this->_data['type'],
        'firstOption' => Application::get()->textStorage->getText('label.select_noMatter'),
        'label' => Application::get()->textStorage->getText('label.report_user_type'),
        'userTextStorage' => false
      )), 'fi_type');
    } else $this->insertTemplateVar('fi_type', '');
  }
  
  protected function _userRender() {
    $validator = Validator::get('userReport', 'UserReportValidator');
    $this->_data = $validator->getValues();
    $this->_section = 'user';
    
    parent::_userRender();
    
    $this->setTemplateFile(dirname(__FILE__).'/Customer.html');

    $this->insertTemplateVar('title', $this->_app->textStorage->getText('label.report_user_title'));
    
    $this->_insertTypeSelect();
    
    $this->_app->document->addJavascript("
              $(document).ready(function() {
                $('#fi_registrationFrom').datetimepicker({format:'d.m.Y',dayOfWeekStart:'1',timepicker:false,allowBlank:true,scrollInput:false});
                $('#fi_registrationTo').datetimepicker({format:'d.m.Y',dayOfWeekStart:'1',timepicker:false,allowBlank:true,scrollInput:false});
              });");
  }
}

?>
