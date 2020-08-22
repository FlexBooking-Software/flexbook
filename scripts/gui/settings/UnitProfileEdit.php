<?php

class GuiEditUnitProfile extends GuiElement {

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
              'label' => $this->_app->textStorage->getText('label.editUnitProfile_provider'),
              'dataSource' => $ds,
              'value' => $data['providerId'],
              'firstOption' => Application::get()->textStorage->getText('label.select_choose'),
              'userTextStorage' => false)), 'fi_provider');
    }
  }
  
  private function _insertSelected($data) {
    if (!strcmp($data['unitBase'],'min')) $this->insertTemplateVar('minCheckedUnit', 'selected="yes"', false);
    elseif (!strcmp($data['unitBase'],'hour')) $this->insertTemplateVar('hourCheckedUnit', 'selected="yes"', false);
    elseif (!strcmp($data['unitBase'],'day')) $this->insertTemplateVar('dayCheckedUnit', 'selected="yes"', false);
    elseif (!strcmp($data['unitBase'],'night')) $this->insertTemplateVar('nightCheckedUnit', 'selected="yes"', false);
    
    if (!strcmp($data['alignmentTimeGridBase'],'min')) $this->insertTemplateVar('minCheckedAlignment', 'selected="yes"', false);
    elseif (!strcmp($data['alignmentTimeGridBase'],'hour')) $this->insertTemplateVar('hourCheckedAlignment', 'selected="yes"', false);
    elseif (!strcmp($data['alignmentTimeGridBase'],'day')) $this->insertTemplateVar('dayCheckedAlignment', 'selected="yes"', false); 
  }

  protected function _userRender() {
    $this->setTemplateFile(dirname(__FILE__).'/UnitProfileEdit.html');

    $validator = Validator::get('unitProfile', 'UnitProfileValidator');
    $data = $validator->getValues();

    foreach ($data as $k => $v) {
      if (!is_array($v)) { $this->insertTemplateVar($k, $v); }
    }
    
    if (!$data['id']) {
      $this->insertTemplatevar('title', $this->_app->textStorage->getText('label.editUnitProfile_titleNew'));
    } else {
      $this->insertTemplatevar('title', $this->_app->textStorage->getText('label.editUnitProfile_titleExisting'));
    }
    
    $this->_insertProviderSelect($data);
    $this->_insertSelected($data);
    
    $this->_app->document->addJavascript("
              $(function() {
                $('#fi_alignmentTimeFrom').datetimepicker({format:'H:i',dayOfWeekStart:'1',datepicker:false,step:30,allowBlank:true});
                $('#fi_alignmentTimeTo').datetimepicker({format:'H:i',dayOfWeekStart:'1',datepicker:false,step:30,allowBlank:true});
                $('#fi_endTimeFrom').datetimepicker({format:'H:i',dayOfWeekStart:'1',datepicker:false,step:30,allowBlank:true});
                $('#fi_endTimeTo').datetimepicker({format:'H:i',dayOfWeekStart:'1',datepicker:false,step:30,allowBlank:true});
              });
              ");
  }
}

?>
