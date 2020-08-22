<?php

class GuiEditPriceList extends GuiElement {

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
              'label' => $this->_app->textStorage->getText('label.editPriceList_provider'),
              'dataSource' => $ds,
              'value' => $data['providerId'],
              'firstOption' => Application::get()->textStorage->getText('label.select_choose'),
              'userTextStorage' => false)), 'fi_provider');
    }
  }
  
  private function _insertSeason($data) {
    if (count($data['season'])) {
      $template = sprintf('<tr><th>%s</th><th>%s</th><th>&nbsp;</th></tr>',
                          $this->_app->textStorage->getText('label.editPriceList_seasonName'),
                          $this->_app->textStorage->getText('label.editPriceList_seasonTerm'));
      $i=0;
      foreach ($data['season'] as $index=>$season) {
        if ($i++%2) $class = 'Even'; else $class = 'Odd';
        
        $formVariable = sprintf('<input type="hidden" name="newSeason[%d]" value="seasonId~%s;name~%s;start~%s;end~%s;basePrice~%s;monPrice~%s;tuePrice~%s;wedPrice~%s;thuPrice~%s;friPrice~%s;satPrice~%s;sunPrice~%s"/>',
                                $index,$season['seasonId'],$season['name'],$season['start'],$season['end'],$season['basePrice'],
                                $season['monPrice'],$season['tuePrice'],$season['wedPrice'],$season['thuPrice'],$season['friPrice'],$season['satPrice'],$season['sunPrice']);
        $template .= sprintf('<tr class="%s" id="%d" db_id="%d"><td>%s</td><td>%s - %s</td><td>[<a href="#" id="fi_seasonEdit">%s</a>][<a href="#" id="fi_seasonRemove">%s</a>]</td>%s</tr>',
                             $class, $index, $season['seasonId'], $season['name'], $season['start'], $season['end'],
                             $this->_app->textStorage->getText('button.grid_edit'),
                             $this->_app->textStorage->getText('button.grid_remove'),
                             $formVariable);
      }
      
      $this->insertTemplateVar('fi_season', $template, false);
    } else $this->insertTemplateVar('fi_season', '');
  }

  protected function _userRender() {
    $this->setTemplateFile(dirname(__FILE__).'/PriceListEdit.html');

    $validator = Validator::get('priceList', 'PriceListValidator');
    $data = $validator->getValues();

    foreach ($data as $k => $v) {
      if (!is_array($v)) { $this->insertTemplateVar($k, $v); }
    }
    
    if (!$data['id']) {
      $this->insertTemplatevar('title', $this->_app->textStorage->getText('label.editPriceList_titleNew'));
    } else {
      $this->insertTemplatevar('title', $this->_app->textStorage->getText('label.editPriceList_titleExisting'));
    }
    
    $this->_insertProviderSelect($data);
    $this->_insertSeason($data);
    
    $this->_app->document->addJavascriptTemplateFile(dirname(__FILE__).'/PriceListEdit.js');
  }
}

?>
