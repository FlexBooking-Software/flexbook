<?php

class GuiEditAvailProfile extends GuiElement {

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
              'label' => $this->_app->textStorage->getText('label.editAvailProfile_provider'),
              'dataSource' => $ds,
              'value' => $data['providerId'],
              'firstOption' => Application::get()->textStorage->getText('label.select_choose'),
              'userTextStorage' => false)), 'fi_provider');
    }
  }
  
  private function _insertWeekdayTime($data) {
    foreach (array('mon','tue','wed','thu','fri','sat','sun') as $day) {
      $template = sprintf('
            <div class="formItem">
              <label>%s:</label>
              <input class="shortText" type="text" name="%s" value="%s" />
              -
              <input class="shortText" type="text" name="%s" value="%s" />
            </div>', $this->_app->textStorage->getText('label.day_'.$day),
            'weekdayFrom['.$day.']',ifsetor($data['weekdayFrom'][$day]),
            'weekdayTo['.$day.']',ifsetor($data['weekdayTo'][$day])
            );
      
      $gui = new GuiElement(array('template'=>$template));
      $this->insert($gui, 'fi_weekday');
    }
  }

  protected function _userRender() {
    $this->setTemplateFile(dirname(__FILE__).'/AvailProfileEdit.html');

    $validator = Validator::get('availProfile', 'AvailProfileValidator');
    $data = $validator->getValues();

    foreach ($data as $k => $v) {
      if (!is_array($v)) { $this->insertTemplateVar($k, $v); }
    }
    
    if (!$data['id']) {
      $this->insertTemplatevar('title', $this->_app->textStorage->getText('label.editAvailProfile_titleNew'));
    } else {
      $this->insertTemplatevar('title', $this->_app->textStorage->getText('label.editAvailProfile_titleExisting'));
    }
    
    $this->_insertProviderSelect($data);
    $this->_insertWeekdayTime($data);
  }
}

?>
