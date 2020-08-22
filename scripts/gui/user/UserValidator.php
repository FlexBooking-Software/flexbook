<?php

class UserValidator extends Validator {

  protected function _insert() {
    $app = Application::get();

    $this->addValidatorVar(new ValidatorVar('id'));
    $this->addValidatorVar(new ValidatorVar('email', false, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('firstname', true, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('lastname', true, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('phone', false, new ValidatorTypeString(15)));
    $this->addValidatorVar(new ValidatorVar('addressId'));
    $this->addValidatorVar(new ValidatorVar('street', false, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('city', false, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('postalCode', false, new ValidatorTypeString(5)));
    $this->addValidatorVar(new ValidatorVar('state', false, new ValidatorTypeString(2)));
    $this->addValidatorVar(new ValidatorVar('username', false, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('passwordEdit', false, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('retypePasswordEdit', false, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('facebookId', false, new ValidatorTypeString(100)));
    $this->addValidatorVar(new ValidatorVar('googleId', false, new ValidatorTypeString(100)));
    $this->addValidatorVar(new ValidatorVar('twitterId', false, new ValidatorTypeString(100)));
    $this->addValidatorVar(new ValidatorVar('reservationConditionId'));
    $this->addValidatorVar(new ValidatorVar('admin'));
    $this->addValidatorVar(new ValidatorVar('registration'));
    $this->addValidatorVar(new ValidatorVar('attribute'));
    $this->addValidatorVar(new ValidatorVar('validationUrl'));

    $this->addValidatorVar(new ValidatorVar('subaccountUser'));
    $this->addValidatorVar(new ValidatorVar('parent'));
    
    $this->addValidatorVar(new ValidatorVar('myProfile'));
    
    $this->addValidatorVar(new ValidatorVar('fromReservation'));
    $this->addValidatorVar(new ValidatorVar('fromEvent'));
    $this->addValidatorVar(new ValidatorVar('fromEventGroup'));
    $this->addValidatorVar(new ValidatorVar('fromEventSubstitute'));
    $this->addValidatorVar(new ValidatorVar('fromCustomerEmployee'));
    $this->addValidatorVar(new ValidatorVar('fromCustomerCoworker'));
    
    $this->getVar('firstname')->setLabel($app->textStorage->getText('label.editUser_firstname'));
    $this->getVar('lastname')->setLabel($app->textStorage->getText('label.editUser_lastname'));
    $this->getVar('username')->setLabel($app->textStorage->getText('label.editUser_username'));
    $this->getVar('passwordEdit')->setLabel($app->textStorage->getText('label.editUser_password'));
    $this->getVar('retypePasswordEdit')->setLabel($app->textStorage->getText('label.editUser_retypePassword'));
    $this->getVar('street')->setLabel($app->textStorage->getText('label.editUser_street'));
    $this->getVar('city')->setLabel($app->textStorage->getText('label.editUser_city'));
    $this->getVar('postalCode')->setLabel($app->textStorage->getText('label.editUser_postalCode'));
    $this->getVar('state')->setLabel($app->textStorage->getText('label.editUser_state'));
    $this->getVar('email')->setLabel($app->textStorage->getText('label.editUser_email'));
    $this->getVar('phone')->setLabel($app->textStorage->getText('label.editUser_phone'));
  }

  public function loadData($id, $organiserFor=null) {
    $app = Application::get();
    
    $data = array();
    if ($id) {
      $bUser = new BUser($id);
      $data = $bUser->getData();

      if ($data['parent']) $data['subaccountUser'] = 'Y';
      
      foreach ($data['registration'] as $key=>$val) {
        $data['registration'][$key]['timestamp'] = $app->regionalSettings->convertDateToHuman(substr($data['registration'][$key]['timestamp'],0,10));
      }
      foreach ($data['attribute'] as $key=>$val) {
        // zobrazeni nazvu atributu v jazyku portalu
        # uz business vraci nazev v aktualnim jazyku
        #$data['attribute'][$key]['name'] = ifsetor($data['attribute'][$key]['name'][$app->language->getLanguage()], array_values($data['attribute'][$key]['name'])[0]);
        
        if (!strcmp($val['type'],'DATE')&&$app->regionalSettings->checkDate($val['value'])) $data['attribute'][$key]['value'] = $app->regionalSettings->convertDateToHuman($val['value']);
        if (!strcmp($val['type'],'TIME')&&$app->regionalSettings->checkTime($val['value'])) $data['attribute'][$key]['value'] = $app->regionalSettings->convertTimeToHuman($val['value'],'h:m');
        if (!strcmp($val['type'],'DATETIME')&&$app->regionalSettings->checkDateTime($val['value'])) $data['attribute'][$key]['value'] = $app->regionalSettings->convertDatetimeToHuman($val['value']);
        if (!strcmp($val['type'],'NUMBER')&&$app->regionalSettings->checkNumber($val['value'])) $data['attribute'][$key]['value'] = $app->regionalSettings->convertNumberToHuman($val['value']);
        if (!strcmp($val['type'],'DECIMALNUMBER')&&$app->regionalSettings->checkNumber($val['value'])) $data['attribute'][$key]['value'] = $app->regionalSettings->convertNumberToHuman($val['value'],2);
      }
    } elseif ($organiserFor) {
      $s = new SProvider;
      $s->addStatement(new SqlStatementBi($s->columns['provider_id'], $organiserFor, '%s=%s'));
      $s->setColumnsMask(array('name'));
      $res = $app->db->doQuery($s->toString());
      $row = $app->db->fetchAssoc($res);
      $data['registration'][] = array(
          'registrationId'        => null,
          'providerId'            => $organiserFor,
          'providerName'          => $row['name'],
          'timestamp'             => date('d.m.Y'),
          'advertising'           => 'Y',
          'credit'                => 0,
          'organiser'             => 'Y',
          'admin'                 => 'N',
          'reception'             => 'N',
          );
    } else {
      if (!$app->auth->isAdministrator()) {
        $s = new SProvider;
        $s->addStatement(new SqlStatementBi($s->columns['provider_id'], $app->auth->getActualProvider(), '%s=%s'));
        $s->setColumnsMask(array('provider_id','name'));
        $res = $app->db->doQuery($s->toString());
        $row = $app->db->fetchAssoc($res);
        $data['registration'][] = array(
            'registrationId'        => null,
            'providerId'            => $row['provider_id'],
            'providerName'          => $row['name'],
            'timestamp'             => date('d.m.Y'),
            'advertising'           => 'Y',
            'credit'                => 0,
            'organiser'             => 'N',
            'admin'                 => 'N',
            'reception'             => 'N',
            );
        if ($c=BCustomer::getProviderSettings($app->auth->getActualProvider(), 'userReservationCondition')) {
          $data['reservationConditionId'] = $c;
        }
      }
      if ($app->request->getParams('subaccount')) {
        $validator = Validator::get('user', 'UserValidator');
        $origData = $validator->getValues();
        $data['subaccountUser'] = 'Y';
        $data['parent'] = $origData['id'];
      }
    }

    $this->setValues($data);
  }
}

?>
