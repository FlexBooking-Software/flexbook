<?php

class CustomerValidator extends Validator {

  protected function _insert() {
    $app = Application::get();

    $this->addValidatorVar(new ValidatorVar('id'));
    $this->addValidatorVar(new ValidatorVar('code'));
    $this->addValidatorVar(new ValidatorVar('name', true, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('addressId'));
    $this->addValidatorVar(new ValidatorVar('street', true, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('city', true, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('postalCode', true, new ValidatorTypeString(5)));
    $this->addValidatorVar(new ValidatorVar('state', true, new ValidatorTypeString(2)));
    $this->addValidatorVar(new ValidatorVar('ic', false, new ValidatorTypeString(8)));
    $this->addValidatorVar(new ValidatorVar('dic', false, new ValidatorTypeString(12)));
    $this->addValidatorVar(new ValidatorVar('email', true, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('phone', true, new ValidatorTypeString(15)));
    $this->addValidatorVar(new ValidatorVar('provider', false));
    $this->addValidatorVar(new ValidatorVar('providerId', false));
    $this->addValidatorVar(new ValidatorVar('invoiceOther', false));
    $this->addValidatorVar(new ValidatorVar('invoiceName', false, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('invoiceAddressId'));
    $this->addValidatorVar(new ValidatorVar('invoiceStreet', false, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('invoiceCity', false, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('invoicePostalCode', false, new ValidatorTypeString(5)));
    $this->addValidatorVar(new ValidatorVar('invoiceState', false, new ValidatorTypeString(2)));
    $this->addValidatorVar(new ValidatorVar('shortName', false, new ValidatorTypeString(20)));
    $this->addValidatorVar(new ValidatorVar('notificationTemplateId'));
    $this->addValidatorVar(new ValidatorVar('vat'));
    $this->addValidatorVar(new ValidatorVar('vatRate', false, new ValidatorTypeNumber(2,1)));
    $this->addValidatorVar(new ValidatorVar('bankAccount', false, new ValidatorTypeString(16)));
    $this->addValidatorVar(new ValidatorVar('bankAccountSuffix', false, new ValidatorTypeString(4)));
    $this->addValidatorVar(new ValidatorVar('phone1', false, new ValidatorTypeString(15)));
    $this->addValidatorVar(new ValidatorVar('phone2', false, new ValidatorTypeString(15)));
    $this->addValidatorVar(new ValidatorVar('www', false, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('center'));
    $this->addValidatorVar(new ValidatorVar('registration'));
    $this->addValidatorVar(new ValidatorVar('coworker'));
    $this->addValidatorVar(new ValidatorVar('employee'));
    $this->addValidatorVar(new ValidatorVar('userAttribute'));
    $this->addValidatorVar(new ValidatorVar('commodityAttribute'));
    $this->addValidatorVar(new ValidatorVar('reservationAttribute'));
    $this->addValidatorVar(new ValidatorVar('file'));
    $this->addValidatorVar(new ValidatorVar('invoiceAccountFrom', false, new ValidatorTypeDate));
    $this->addValidatorVar(new ValidatorVar('invoiceMonthFee', false, new ValidatorTypeNumber(5,1)));
    $this->addValidatorVar(new ValidatorVar('invoiceReservationFee', false, new ValidatorTypeNumber(10,1)));
    $this->addValidatorVar(new ValidatorVar('invoiceReservationPriceFee', false, new ValidatorTypeNumber(3,1)));
    $this->addValidatorVar(new ValidatorVar('invoiceReservationPricePaid'));
    $this->addValidatorVar(new ValidatorVar('invoiceDueLength', false, new ValidatorTypeInteger));
    $this->addValidatorVar(new ValidatorVar('invoiceEmail', false, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('invoicePeriod'));
    $this->addValidatorVar(new ValidatorVar('smtpHost'));
    $this->addValidatorVar(new ValidatorVar('smtpPort'));
    $this->addValidatorVar(new ValidatorVar('smtpUser'));
    $this->addValidatorVar(new ValidatorVar('smtpPassword'));
    $this->addValidatorVar(new ValidatorVar('smtpSecure'));
    
    $this->addValidatorVar(new ValidatorVar('myData'));
    $this->addValidatorVar(new ValidatorVar('fromReservation'));
    $this->addValidatorVar(new ValidatorVar('fromEventSubstitute'));
    
    $this->getVar('name')->setLabel($app->textStorage->getText('label.editCustomer_name'));
    $this->getVar('street')->setLabel($app->textStorage->getText('label.editCustomer_street'));
    $this->getVar('city')->setLabel($app->textStorage->getText('label.editCustomer_city'));
    $this->getVar('postalCode')->setLabel($app->textStorage->getText('label.editCustomer_postalCode'));
    $this->getVar('state')->setLabel($app->textStorage->getText('label.editCustomer_state'));
    $this->getVar('ic')->setLabel($app->textStorage->getText('label.editCustomer_ic'));
    $this->getVar('dic')->setLabel($app->textStorage->getText('label.editCustomer_dic'));
    $this->getVar('email')->setLabel($app->textStorage->getText('label.editCustomer_email'));
    $this->getVar('phone')->setLabel($app->textStorage->getText('label.editCustomer_phone'));
    $this->getVar('invoiceName')->setLabel($app->textStorage->getText('label.editCustomer_invoiceName'));
    $this->getVar('invoiceStreet')->setLabel($app->textStorage->getText('label.editCustomer_invoiceStreet'));
    $this->getVar('invoiceCity')->setLabel($app->textStorage->getText('label.editCustomer_invoiceCity'));
    $this->getVar('invoicePostalCode')->setLabel($app->textStorage->getText('label.editCustomer_invoicePostalCode'));
    $this->getVar('invoiceState')->setLabel($app->textStorage->getText('label.editCustomer_invoiceState'));
    $this->getVar('shortName')->setLabel($app->textStorage->getText('label.editCustomer_shortName'));
    $this->getVar('notificationTemplateId')->setLabel($app->textStorage->getText('label.editCustomer_notification'));
    $this->getVar('bankAccount')->setLabel($app->textStorage->getText('label.editCustomer_bankAccount'));
    $this->getVar('bankAccountSuffix')->setLabel($app->textStorage->getText('label.editCustomer_bankAccountSuffix'));
    $this->getVar('phone1')->setLabel($app->textStorage->getText('label.editCustomer_phone1'));
    $this->getVar('phone2')->setLabel($app->textStorage->getText('label.editCustomer_phone2'));
    $this->getVar('www')->setLabel($app->textStorage->getText('label.editCustomer_www'));
    $this->getVar('invoiceAccountFrom')->setLabel($app->textStorage->getText('label.editCustomer_invoiceAccountFrom'));
    $this->getVar('invoiceMonthFee')->setLabel($app->textStorage->getText('label.editCustomer_invoiceMonthFee'));
    $this->getVar('invoiceReservationFee')->setLabel($app->textStorage->getText('label.editCustomer_invoiceReservationFee'));
    $this->getVar('invoiceReservationPriceFee')->setLabel($app->textStorage->getText('label.editCustomer_invoiceReservationPriceFee'));
    $this->getVar('invoiceDueLength')->setLabel($app->textStorage->getText('label.editCustomer_invoiceDueLength'));
    $this->getVar('invoiceEmail')->setLabel($app->textStorage->getText('label.editCustomer_invoiceEmail'));
  }

  public function loadData($id) {
    $app = Application::get();
  
    $data = array();
    if ($id) {
      $bCustomer = new BCustomer($id);
      $data = $bCustomer->getData();
      
      foreach ($data['registration'] as $key=>$val) {
        $data['registration'][$key]['timestamp'] = $app->regionalSettings->convertDateToHuman(
                                    substr($data['registration'][$key]['timestamp'],0,10));
      }
      foreach ($data['userAttribute'] as $key=>$val) {
        // zobrazeni nazvu atributu v jazyku portalu
        $data['userAttribute'][$key]['name'] = ifsetor($data['userAttribute'][$key]['name'][$app->language->getLanguage()], array_values($data['userAttribute'][$key]['name'])[0]);
      }
      foreach ($data['commodityAttribute'] as $key=>$val) {
        // zobrazeni nazvu atributu v jazyku portalu
        $data['commodityAttribute'][$key]['name'] = ifsetor($data['commodityAttribute'][$key]['name'][$app->language->getLanguage()], array_values($data['commodityAttribute'][$key]['name'])[0]);
      }
      foreach ($data['reservationAttribute'] as $key=>$val) {
        // zobrazeni nazvu atributu v jazyku portalu
        $data['reservationAttribute'][$key]['name'] = ifsetor($data['reservationAttribute'][$key]['name'][$app->language->getLanguage()], array_values($data['reservationAttribute'][$key]['name'])[0]);
      }
      if (isset($data['vatRate'])&&$data['vatRate']) $data['vatRate'] = $app->regionalSettings->convertNumberToHuman($data['vatRate'],1);

      if (isset($data['invoice'])) {
        $data['invoiceAccountFrom'] = $app->regionalSettings->convertDateToHuman($data['invoice']['accountFrom']);
        $data['invoiceMonthFee'] = $app->regionalSettings->convertNumberToHuman($data['invoice']['monthFee'],1);
        $data['invoiceReservationFee'] = $app->regionalSettings->convertNumberToHuman($data['invoice']['reservationFee'],1);
        $data['invoiceReservationPriceFee'] = $app->regionalSettings->convertNumberToHuman($data['invoice']['reservationPriceFee'],1);
        $data['invoiceReservationPricePaid'] = $data['invoice']['reservationPricePaid'];
        $data['invoiceDueLength'] = $data['invoice']['dueLength'];
        $data['invoiceEmail'] = $data['invoice']['email'];
      }
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
            );
      }
    }

    $this->setValues($data);
  }
}

?>
