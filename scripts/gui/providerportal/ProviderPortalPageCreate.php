<?php

class GuiCreateProviderPortalPage extends GuiElement {

  private function _insertTemplateSelect($data) {
    $s = new SPageTemplate;
    $s->addOrder(new SqlStatementAsc($s->columns['name']));
    $s->setColumnsMask(array('pagetemplate_id', 'name'));
    $ds = new SqlDataSource(new DataSourceSettings, $s);
    $this->insert(new GuiFormSelect(array(
                'id'          => 'fi_pageFromTemplate',
                'name'        => 'pageFromTemplate',
                'showDiv'     => false,
                'dataSource'  => $ds,
                'value'       => $data['pageFromTemplate'],
                'firstOption' => $this->_app->textStorage->getText('label.editProviderPortalPage_empty'),
                )), 'fi_template');
  }

  protected function _userRender() {
    $this->setTemplateFile(dirname(__FILE__).'/ProviderPortalPageCreate.html');

    $validator = Validator::get('providerPortal', 'ProviderPortalValidator');
    $data = $validator->getValues();

    foreach ($data as $k => $v) {
      if (!is_array($v)) { $this->insertTemplateVar($k, $v); }
    }

    $this->insertTemplatevar('title', sprintf($this->_app->textStorage->getText('label.editProviderPortalPage_titleNew'), $data['name']));
    
    $this->_insertTemplateSelect($data);
  }
}

?>
