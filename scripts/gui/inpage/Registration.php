<?php

class GuiInPageRegistration extends GuiElement {
  private $_js = '';

  private function _insertState($data) {
    $select = new SState;
    $select->setColumnsMask(array('code','name'));
    $ds = new SqlDataSource(new DataSourceSettings, $select);
    $this->insert(new GuiFormSelect(array(
            'id' => 'fi_state',
            'name' => 'state',
            'dataSource' => $ds,
            'value' => $data['state'],
            'firstOption' => Application::get()->textStorage->getText('label.select_choose'),
            'showDiv' => false,
            'userTextStorage' => false)), 'fi_state');
  }
  
  private function _insertAttribute($data) {
    foreach ($data['attribute'] as $id=>$attribute) {
      switch ($attribute['type']) {
        case 'NUMBER':
        case 'TEXT': $inputHtml = sprintf('<input class="text" type="text" id="%d" name="newAttributeValue[%d]" value="%s" />', $id, $id, $attribute['value']);
                     break;
        case 'TIME': $inputHtml = sprintf('<input class="text" type="text" id="%d" name="newAttributeValue[%d]" value="%s" />', $id, $id, $attribute['value']);
                     $this->_js .= sprintf("$(function() { $('#%d').datetimepicker({format:'H:i',datepicker:false,timepicker:true}); });", $id);
                     break;
        case 'DATETIME':
                     $inputHtml = sprintf('<input class="text" type="text" id="%d" name="newAttributeValue[%d]" value="%s" />', $id, $id, $attribute['value']);
                     $this->_js .= sprintf("$(function() { $('#%d').datetimepicker({format:'d.m.Y H:i',datepicker:true,timepicker:true}); });", $id);
                     break;
        case 'DATE': $inputHtml = sprintf('<input class="text" type="text" id="%d" name="newAttributeValue[%d]" value="%s" />', $id, $id, $attribute['value']);
                     $this->_js .= sprintf("$(function() { $('#%d').datetimepicker({format:'d.m.Y',datepicker:true,timepicker:false}); });", $id);
                     break;
        case 'LIST': $inputHtml = sprintf('<select name="newAttributeValue[%d]"><option value="">%s</option>', $id, $this->_app->textStorage->getText('label.select_choose'));
                     foreach (explode(',',$attribute['allowedValues']) as $value) {
                      $inputHtml .= sprintf('<option value="%s"%s>%s</option>', $value, !strcmp($value,$attribute['value'])?' selected="selected"':'', $value);
                     }
                     $inputHtml .= '</select>';
                     break;
        default: $inputHtml = 'Unknown type!';
      }
      if ($attribute['mandatory']=='Y') {
        $class = ' class="bold"';
        $labelPrefix = '* ';
      } else {
        $class = '';
        $labelPrefix = '&nbsp; ';
      }
      $html = sprintf('<label%s>%s%s:</label>%s<br/>', $class, $labelPrefix, $attribute['name'], $inputHtml);
      
      $this->insertTemplateVar('fi_attribute', $html, false);
    }
  }

  protected function _userRender() {
    $validator = Validator::get('registration', 'InPageRegistrationValidator');
    $data = $validator->getValues();
    
    foreach ($data as $key=>$val) {
      if (!is_array($val)) $this->insertTemplateVar($key, $val);
    }
    
    $this->setTemplateFile(dirname(__FILE__).'/Registration_'.$data['step'].'.html');
    
    if ($data['step']==3) {
      $this->_insertState($data);
      $this->_insertAttribute($data);
    }
    
    if ($this->_js) $this->_app->document->addJavascript($this->_js);
  }
}

?>
