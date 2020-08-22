<?php

class GuiEditProviderAccountType extends GuiElement {

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
              'label' => $this->_app->textStorage->getText('label.editProviderAccountType_provider'),
              'dataSource' => $ds,
              'value' => $data['providerId'],
              'firstOption' => Application::get()->textStorage->getText('label.select_choose'),
              'userTextStorage' => false)), 'fi_provider');
    }
  }

  protected function _userRender() {
    $this->setTemplateFile(dirname(__FILE__).'/ProviderAccountTypeEdit.html');

    $validator = Validator::get('providerAccountType', 'ProviderAccountTypeValidator');
    $data = $validator->getValues();

    foreach ($data as $k => $v) {
      if (!is_array($v)) { $this->insertTemplateVar($k, $v); }
    }
    
    if (!$data['id']) {
      $this->insertTemplatevar('title', $this->_app->textStorage->getText('label.editProviderAccountType_titleNew'));
    } else {
      $this->insertTemplatevar('title', $this->_app->textStorage->getText('label.editProviderAccountType_titleExisting'));
    }
    
    $this->_insertProviderSelect($data);
    
    #$this->_app->document->addJavascriptTemplateFile(dirname(__FILE__).'/ProviderAccountTypeEdit.js');
  }
}

?>
