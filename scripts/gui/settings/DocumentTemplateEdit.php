<?php

class GuiEditDocumentTemplate extends GuiElement {

  private function _insertProviderSelect($data) {
    if (!$this->_app->auth->isAdministrator()) {
      $this->insertTemplateVar('fi_provider', sprintf('<input type="hidden" id="fi_provider" name="providerId" value="%s" />', $this->_app->auth->getActualProvider()), false);
    } else {
      $select = new SProvider;
      $select->setColumnsMask(array('provider_id','name'));
      $ds = new SqlDataSource(new DataSourceSettings, $select);
      $this->insert(new GuiFormSelect(array(
              'id' => 'fi_provider',
              'classLabel' => 'bold',
              'name' => 'providerId',
              'label' => $this->_app->textStorage->getText('label.editDocumentTemplate_provider'),
              'dataSource' => $ds,
              'value' => $data['providerId'],
              'firstOption' => Application::get()->textStorage->getText('label.select_choose'),
              'userTextStorage' => false)), 'fi_provider');
    }
  }

  private function _insertTargetSelect($data) {
    $this->insert(new GuiFormSelect(array(
      'id' => 'fi_target',
      'name' => 'target',
      'dataSource' => new HashDataSource(new DataSourceSettings, array(
        'GENERAL'   => $this->_app->textStorage->getText('label.listDocumentTemplate_target_GENERAL'),
        'COMMODITY' => $this->_app->textStorage->getText('label.listDocumentTemplate_target_COMMODITY'),
      )),
      'value' => $data['target'],
      'firstOption' => Application::get()->textStorage->getText('label.select_choose'),
      'showDiv' => false)), 'fi_target');
  }
  
  private function _insertItem($data) {
    if (count($data['item'])) {
      $template = sprintf('<tr><th>%s</th><th>%s</th><th>&nbsp;</th></tr>', $this->_app->textStorage->getText('label.editDocumentTemplate_itemName'), $this->_app->textStorage->getText('label.editDocumentTemplate_itemCode'));
      $i=0;
      foreach ($data['item'] as $index=>$item) {
        if ($i++%2) $class = 'Even'; else $class = 'Odd';

        if ($content = ifsetor($item['content'])) $content = str_replace('"', '&quot;', $content);
        $formVariable = sprintf('<input type="hidden" data-meaing="general" name="newItem[%d]" value="itemId~%s;name~%s;code~%s;type~%s;number~%s"/>',
                                $index,$item['itemId'],ifsetor($item['name']),ifsetor($item['code']),ifsetor($item['type']),ifsetor($item['number']));
        $formVariable .= sprintf('<input type="hidden" data-meaning="content" name="newItemContent[%d]" value="%s"', $index, $content);
        $template .= sprintf('<tr class="%s" id="%d" db_id="%d"><td>%s</td><td>%s</td><td>[<a href="#" id="fi_itemEdit">%s</a>][<a href="#" id="fi_itemRemove">%s</a>]</td>%s</tr>',
                             $class, $index, $item['itemId'], $item['name'], $item['code'],
                             $this->_app->textStorage->getText('button.grid_edit'),
                             $this->_app->textStorage->getText('button.grid_remove'),
                             $formVariable);
      }
      
      $this->insertTemplateVar('fi_item', $template, false);
    } else $this->insertTemplateVar('fi_item', '');
  }
  
  protected function _userRender() {
    $this->setTemplateFile(dirname(__FILE__).'/DocumentTemplateEdit.html');

    $validator = Validator::get('documentTemplate', 'DocumentTemplateValidator');
    $data = $validator->getValues();
    #adump($data);

    foreach ($data as $k => $v) {
      if (!is_array($v)) { $this->insertTemplateVar($k, $v); }
    }
    
    if (!$data['id']) {
      $this->insertTemplatevar('title', $this->_app->textStorage->getText('label.editDocumentTemplate_titleNew'));
    } else {
      $this->insertTemplatevar('title', $this->_app->textStorage->getText('label.editDocumentTemplate_titleExisting'));
    }
    
    $this->_insertProviderSelect($data);
    $this->_insertTargetSelect($data);
    $this->_insertItem($data);

    $this->_app->document->addJavascriptFile('tinymce/tinymce.min.js?version=5.2.2');
    $this->_app->document->addJavascript('
      $(document).on("focusin", function(e) {
        if ($(e.target).closest(".tox-tinymce-aux, .moxman-window, .tam-assetmanager-root").length) {
          e.stopImmediatePropagation();
        }
      });');


    global $AJAX;
    $this->_app->document->addJavascriptTemplateFile(dirname(__FILE__).'/DocumentTemplateEdit.js',
                                                     array('url'=>$AJAX['adminUrl']));
  }
}

?>
