<?php

class ResourceValidator extends Validator {

  protected function _insert() {
    $app = Application::get();

    $this->addValidatorVar(new ValidatorVar('id'));
    $this->addValidatorVar(new ValidatorVar('externalId', false, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('name', true, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('centerId', true));
    $this->addValidatorVar(new ValidatorVar('centerName'));
    $this->addValidatorVar(new ValidatorVar('organiserId', false));
    $this->addValidatorVar(new ValidatorVar('description', true));
    $this->addValidatorVar(new ValidatorVar('tag'));
    $this->addValidatorVar(new ValidatorVar('availProfile', true));
    $this->addValidatorVar(new ValidatorVar('availExProfile'));
    $this->addValidatorVar(new ValidatorVar('unitProfile', true));
    $this->addValidatorVar(new ValidatorVar('price', true, new ValidatorTypeNumber(100,2)));
    $this->addValidatorVar(new ValidatorVar('priceList'));
    $this->addValidatorVar(new ValidatorVar('accountTypeId'));
    $this->addValidatorVar(new ValidatorVar('reservationConditionId'));
    $this->addValidatorVar(new ValidatorVar('notificationTemplateId'));
    $this->addValidatorVar(new ValidatorVar('documentTemplateId'));
    $this->addValidatorVar(new ValidatorVar('active'));
    $this->addValidatorVar(new ValidatorVarArray('portal'));
    $this->addValidatorVar(new ValidatorVar('providerId', true));
    $this->addValidatorVar(new ValidatorVar('providerName'));
    $this->addValidatorVar(new ValidatorVar('providerEmail'));
    $this->addValidatorVar(new ValidatorVar('providerPhone1'));
    $this->addValidatorVar(new ValidatorVar('providerPhone2'));
    $this->addValidatorVar(new ValidatorVar('providerWww'));

    $this->addValidatorVar(new ValidatorVarArray('feAllowedPayment'));
    
    $this->addValidatorVar(new ValidatorVar('urlDescription'));
    $this->addValidatorVar(new ValidatorVar('urlPrice'));
    $this->addValidatorVar(new ValidatorVar('urlOpening'));
    $this->addValidatorVar(new ValidatorVar('urlPhoto'));
    
    $this->addValidatorVar(new ValidatorVar('attribute'));
    $this->addValidatorVar(new ValidatorVar('reservationAttribute'));
    
    $this->addValidatorVar(new ValidatorVar('reservation'));
    
    $this->addValidatorVar(new ValidatorVar('groupSave'));
    $this->addValidatorVar(new ValidatorVarArray('groupSaveItem'));
    
    $this->getVar('externalId')->setLabel($app->textStorage->getText('label.editResource_externalId'));
    $this->getVar('name')->setLabel($app->textStorage->getText('label.editResource_name'));
    $this->getVar('description')->setLabel($app->textStorage->getText('label.editResource_description'));
    $this->getVar('price')->setLabel($app->textStorage->getText('label.editResource_price'));
    $this->getVar('centerId')->setLabel($app->textStorage->getText('label.editResource_center'));
    $this->getVar('providerId')->setLabel($app->textStorage->getText('label.editResource_provider'));
    $this->getVar('availProfile')->setLabel($app->textStorage->getText('label.editResource_availProfile'));
    $this->getVar('unitProfile')->setLabel($app->textStorage->getText('label.editResource_unitProfile'));
  }

  public function loadData($id) {
    $app = Application::get();
    
    $bResource = new BResource($id);
    $data = $bResource->getData();
    
    foreach ($data['attribute'] as $key=>$val) {
      // zobrazeni nazvu atributu v jazyku portalu
      $data['attribute'][$key]['name'] = ifsetor($data['attribute'][$key]['name'][$app->language->getLanguage()], array_values($data['attribute'][$key]['name'])[0]);
      
      if (!strcmp($val['type'],'DATE')) $data['attribute'][$key]['value'] = $app->regionalSettings->convertDateToHuman($val['value']);
      if (!strcmp($val['type'],'TIME')) $data['attribute'][$key]['value'] = $app->regionalSettings->convertTimeToHuman($val['value'],'h:m');
      if (!strcmp($val['type'],'DATETIME')) $data['attribute'][$key]['value'] = $app->regionalSettings->convertDatetimeToHuman($val['value']);
      if (!strcmp($val['type'],'NUMBER')) $data['attribute'][$key]['value'] = $app->regionalSettings->convertNumberToHuman($val['value']);
      if (!strcmp($val['type'],'DECIMALNUMBER')) $data['attribute'][$key]['value'] = $app->regionalSettings->convertNumberToHuman($val['value'],2);
    }
    foreach ($data['reservationAttribute'] as $key=>$val) {
      // zobrazeni nazvu atributu v jazyku portalu
      $data['reservationAttribute'][$key]['name'] = ifsetor($data['reservationAttribute'][$key]['name'][$app->language->getLanguage()], array_values($data['reservationAttribute'][$key]['name'])[0]);
      
      if (!strcmp($val['type'],'DATE')&&isset($val['val'])) $data['reservationAttribute'][$key]['value'] = $app->regionalSettings->convertDateToHuman($val['value']);
      if (!strcmp($val['type'],'TIME')&&isset($val['val'])) $data['reservationAttribute'][$key]['value'] = $app->regionalSettings->convertTimeToHuman($val['value'],'h:m');
      if (!strcmp($val['type'],'DATETIME')&&isset($val['val'])) $data['reservationAttribute'][$key]['value'] = $app->regionalSettings->convertDatetimeToHuman($val['value']);
      if (!strcmp($val['type'],'NUMBER')&&isset($val['val'])) $data['reservationAttribute'][$key]['value'] = $app->regionalSettings->convertNumberToHuman($val['value']);
      if (!strcmp($val['type'],'DECIMALNUMBER')&&isset($val['val'])) $data['reservationAttribute'][$key]['value'] = $app->regionalSettings->convertNumberToHuman($val['value'],2);
    }
    
    $this->setValues($data);
  }
}

?>
