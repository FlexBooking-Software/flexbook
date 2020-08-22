<?php

class GuiEditAvailExProfile extends GuiElement {

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
              'label' => $this->_app->textStorage->getText('label.editAvailExProfile_provider'),
              'dataSource' => $ds,
              'value' => $data['providerId'],
              'firstOption' => Application::get()->textStorage->getText('label.select_choose'),
              'userTextStorage' => false)), 'fi_provider');
    }
  }
  
  private function _insertTerm($data) {
    if (count($data['term'])) {
      $template = sprintf('<tr><th>%s</th><th>%s</th><th>&nbsp;</th></tr>', $this->_app->textStorage->getText('label.editAvailExProfile_oneName'), $this->_app->textStorage->getText('label.editAvailExProfile_oneTerm'));
      $i=0;
      foreach ($data['term'] as $index=>$term) {
        if ($i++%2) $class = 'Even'; else $class = 'Odd';
        if ($term['type']=='Date') $htmlValue = $term['date'];
        elseif ($term['type']=='DateRange') $htmlValue = $term['dateFrom'].' - '.$term['dateTo'];
        elseif ($term['type']=='TimeRange') $htmlValue = $term['timeFrom'].' - '.$term['timeTo'];
        
        $formVariable = sprintf('<input type="hidden" name="newTerm[%d]" value="termId~%s;name~%s;type~%s;date~%s;dateFrom~%s;dateTo~%s;timeFrom~%s;timeTo~%s;repeated~%s;repeatCycle~%s;repeatUntil~%s;'.
                                'repeatWeekday_mon~%d;repeatWeekday_tue~%d;repeatWeekday_wed~%d;repeatWeekday_thu~%d;repeatWeekday_fri~%d;repeatWeekday_sat~%d;repeatWeekday_sun~%d"/>',
                                $index,$term['termId'],ifsetor($term['name']),$term['type'],ifsetor($term['date']),ifsetor($term['dateFrom']),ifsetor($term['dateTo']),ifsetor($term['timeFrom']),ifsetor($term['timeTo']),
                                ifsetor($term['repeated']),ifsetor($term['repeat_cycle']),ifsetor($term['repeat_until']),
                                ifsetor($term['repeat_weekday_mon']),ifsetor($term['repeat_weekday_tue']),ifsetor($term['repeat_weekday_wed']),ifsetor($term['repeat_weekday_thu']),ifsetor($term['repeat_weekday_fri']),
                                ifsetor($term['repeat_weekday_sat']),ifsetor($term['repeat_weekday_sun']));
        $template .= sprintf('<tr class="%s" id="%d" db_id="%d"><td>%s</td><td>%s</td><td>[<a href="#" id="fi_termEdit">%s</a>][<a href="#" id="fi_termRemove">%s</a>]</td>%s</tr>',
                             $class, $index, $term['termId'], $term['name'], $htmlValue,
                             $this->_app->textStorage->getText('button.grid_edit'),
                             $this->_app->textStorage->getText('button.grid_remove'),
                             $formVariable);
      }
      
      $this->insertTemplateVar('fi_term', $template, false);
    } else $this->insertTemplateVar('fi_term', '');
  }
  
  protected function _userRender() {
    $this->setTemplateFile(dirname(__FILE__).'/AvailExProfileEdit.html');

    $validator = Validator::get('availExProfile', 'AvailExProfileValidator');
    $data = $validator->getValues();

    foreach ($data as $k => $v) {
      if (!is_array($v)) { $this->insertTemplateVar($k, $v); }
    }
    
    if (!$data['id']) {
      $this->insertTemplatevar('title', $this->_app->textStorage->getText('label.editAvailExProfile_titleNew'));
    } else {
      $this->insertTemplatevar('title', $this->_app->textStorage->getText('label.editAvailExProfile_titleExisting'));
    }
    
    $this->_insertProviderSelect($data);
    $this->_insertTerm($data);
    
    $this->_app->document->addJavascriptTemplateFile(dirname(__FILE__).'/AvailExProfileEdit.js');
  }
}

?>
