<?php

class NotificationTemplateValidator extends Validator {

  protected function _insert() {
    $app = Application::get();

    $this->addValidatorVar(new ValidatorVar('id'));
    $this->addValidatorVar(new ValidatorVar('providerId', true));
    $this->addValidatorVar(new ValidatorVar('name', true, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('target', true));
    $this->addValidatorVar(new ValidatorVar('description'));
    $this->addValidatorVar(new ValidatorVar('item'));

    $this->addValidatorVar(new ValidatorVar('fromEvent'));
    $this->addValidatorVar(new ValidatorVar('fromResource'));

    $this->getVar('providerId')->setLabel($app->textStorage->getText('label.editNotificationTemplate_provider'));
    $this->getVar('name')->setLabel($app->textStorage->getText('label.editNotificationTemplate_name'));
    $this->getVar('target')->setLabel($app->textStorage->getText('label.editNotificationTemplate_target'));
  }

  public function loadData($id) {
    $app = Application::get();

    $bNotificationTemplate = new BNotificationTemplate($id);
    $data = $bNotificationTemplate->getData();
    
    foreach ($data['item'] as $key=>$value) {  
      $offsetUnit = 'min'; $offsetCount = $value['offset'];
      if ($offsetCount&&($offsetCount%1440 === 0)) { $offsetCount = $offsetCount/1440; $offsetUnit = 'day'; }
      elseif ($offsetCount&&($offsetCount%60 === 0)) { $offsetCount = $offsetCount/60; $offsetUnit = 'hour'; }
      
      $data['item'][$key]['offsetUnit'] = $offsetUnit;
      $data['item'][$key]['offsetCount'] = $offsetCount;
    }
    
    $this->setValues($data);
  }
}

?>
