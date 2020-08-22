<?php

class GuiCreateProviderPortal extends GuiElement {

  private function _insertProviderSelect($data) {
    if ($this->_app->auth->isAdministrator()) {
      $select = new SProvider;
      $select->setColumnsMask(array('provider_id','name'));
      $ds = new SqlDataSource(new DataSourceSettings, $select);
      $this->insert(new GuiFormSelect(array(
              'id' => 'fi_provider',
              'name' => 'providerId',
              'classLabel' => 'bold',
              'label' => $this->_app->textStorage->getText('label.editProviderPortal_provider'),
              'dataSource' => $ds,
              'value' => $data['providerId'],
              'firstOption' => Application::get()->textStorage->getText('label.select_choose'),
              'userTextStorage' => false)), 'fi_provider');
    } else {
      $this->insertTemplateVar('fi_provider', sprintf('<input type="hidden" id="fi_provider" name="providerId" value="%s" />', $this->_app->auth->getActualProvider()), false);
    }
  }
  
  private function _insertTemplateSelect($data) {
    $s = new SPortalTemplate;
    $s->addOrder(new SqlStatementAsc($s->columns['name']));
    $s->setColumnsMask(array('portaltemplate_id', 'name', 'page_count', 'preview', 'preview_hash'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      global $AJAX;
      $preview = $row['preview']?sprintf('<a target="_blank" href="%s/getfile.php?id=%s"><img src="%s/getfile.php?id=%s"/></a>', dirname($AJAX['adminUrl']), $row['preview_hash'], dirname($AJAX['adminUrl']), $row['preview_hash']):'';
      
      $html = sprintf('<div class="template"><input type="radio" name="fromTemplate" value="%d" %s/><div class="detail"><div>%s (%d)</div>%s</div></div>',
                      $row['portaltemplate_id'], $row['portaltemplate_id']==$data['fromTemplate']?'checked="yes"':'', $row['name'],
                      $row['page_count'], $preview);
      
      $this->insertTemplateVar('fi_template', $html, false);
    }
    $this->insertTemplateVar('fi_template', '');
  }

  protected function _userRender() {
    $this->setTemplateFile(dirname(__FILE__).'/ProviderPortalCreate.html');

    $validator = Validator::get('providerPortal', 'ProviderPortalValidator');
    $data = $validator->getValues();

    foreach ($data as $k => $v) {
      if (!is_array($v)) { $this->insertTemplateVar($k, $v); }
    }

    $this->insertTemplatevar('title', $this->_app->textStorage->getText('label.editProviderPortal_titleNew'));
    
    $this->_insertProviderSelect($data);
    $this->_insertTemplateSelect($data);
  }
}

?>
