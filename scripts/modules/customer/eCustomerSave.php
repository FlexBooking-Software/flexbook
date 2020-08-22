<?php

class ModuleCustomerSave extends ExecModule {
  
  private function _saveNewCenter($validator) {
    $newCenter = $this->_app->request->getParams('newCenter');
    
    // kdyz neni zadne stredisko a ma to byt provider, tak zalozim jedno stredisko s daty z karty zakaznika
    if ($validator->getVarValue('provider')=='Y') {
      if (!$newCenter||!count($newCenter)) {
        $newCenter[] = sprintf('centerId:;name:%s;street:%s;city:%s;region:;postalCode:%s;state:%s;paymentInfo:',
                               $validator->getVarValue('name'),$validator->getVarValue('street'),$validator->getVarValue('city'),
                               $validator->getVarValue('postalCode'),$validator->getVarValue('state'));
      }
    }
    
    $center = array();
    if (is_array($newCenter)) {
      foreach ($newCenter as $key=>$cLine) {
        $cParams = explode(';',$cLine);
        
        $params = array();
        foreach ($cParams as $par) {
          list($name,$value) = explode(':',$par);
          $params[$name] = $value;
        }
        $center[$key] = $params;
      }
    }
    
    $validator->setValues(array('center'=>$center));
  }
  
  private function _saveNewRegistration($validator) {
    $newReg = $this->_app->request->getParams('newRegistration');
    
    $reg = array();
    if (is_array($newReg)) {
      foreach ($newReg as $key=>$rLine) {
        $rParams = explode(';',$rLine);
        
        $params = array();
        foreach ($rParams as $par) {
          list($name,$value) = explode(':',$par);
          $params[$name] = $value;
        }
        $reg[$key] = $params;
      }
    }
    
    $validator->setValues(array('registration'=>$reg));
  }
  
  private function _saveNewEmployee($validator) {
    $newEmployee = $this->_app->request->getParams('newEmployee');
    
    $employee = array();
    if (is_array($newEmployee)) {
      foreach ($newEmployee as $key=>$eLine) {
        $eParams = explode(';',$eLine);
        
        $params = array();
        foreach ($eParams as $par) {
          list($name,$value) = explode(':',$par);
          $params[$name] = $value;
        }
        $employee[$key] = $params;
      }
    }
    
    $validator->setValues(array('employee'=>$employee));
  }
  
  private function _saveNewCoworker($validator) {
    $newCoworker = $this->_app->request->getParams('newCoworker');
    
    $coworker = array();
    if (is_array($newCoworker)) {
      foreach ($newCoworker as $key=>$cLine) {
        $cParams = explode(';',$cLine);
        
        $params = array();
        foreach ($cParams as $par) {
          list($name,$value) = explode(':',$par);
          $params[$name] = $value;
        }
        $coworker[$key] = $params;
      }
    }
    
    $validator->setValues(array('coworker'=>$coworker));
  }

  private function _attributeCalculateSequence($sequence, $attribute) {
    // posunu sekvenci, aby zohlednila kategoria (lze ovlivnit i razeni kategorii)
    $subs = 0;
    foreach ($sequence as $category=>$count) {
      $tmp = $sequence[$category];
      $sequence[$category] = $subs;
      $subs += $tmp;
    }
    foreach ($attribute as $key=>$attr) {
      $attribute[$key]['sequence'] += $sequence[$attr['category']];
    }

    return $attribute;
  }
  
  private function _saveNewAttribute($validator) {
    $newAttribute = $this->_app->request->getParams('newUserAttribute');
    //adump($newAttribute);
    
    $sequence = array();
    $attribute = array();
    if (is_array($newAttribute)) {
      foreach ($newAttribute as $key=>$cLine) {
        $cParams = explode('_;_',$cLine);
        
        $params = array();
        foreach ($cParams as $par) {
          list($name,$value) = explode('_:_',$par);
          $params[$name] = $value;
        }
        
        if (!isset($sequence[$params['category']])) $sequence[$params['category']] = 0;
        $params['sequence'] = $sequence[$params['category']]++;
        $attribute[$key] = $params;
      }
    }
    $attribute = $this->_attributeCalculateSequence($sequence, $attribute);
    
    $validator->setValues(array('userAttribute'=>$attribute));
    
    $newAttribute = $this->_app->request->getParams('newCommodityAttribute');
    //adump($newAttribute);
    
    $sequence = array();
    $attribute = array();
    if (is_array($newAttribute)) {
      foreach ($newAttribute as $key=>$cLine) {
        $cParams = explode('_;_',$cLine);
        
        $params = array();
        foreach ($cParams as $par) {
          list($name,$value) = explode('_:_',$par);
          $params[$name] = $value;
        }
        
        if (!isset($sequence[$params['category']])) $sequence[$params['category']] = 0;
        $params['sequence'] = $sequence[$params['category']]++;
        $attribute[$key] = $params;
      }
    }
    $attribute = $this->_attributeCalculateSequence($sequence, $attribute);
    
    $validator->setValues(array('commodityAttribute'=>$attribute));
    
    $newAttribute = $this->_app->request->getParams('newReservationAttribute');
    //adump($newAttribute);
    
    $sequence = array();
    $attribute = array();
    if (is_array($newAttribute)) {
      foreach ($newAttribute as $key=>$cLine) {
        $cParams = explode('_;_',$cLine);
        
        $params = array();
        foreach ($cParams as $par) {
          list($name,$value) = explode('_:_',$par);
          $params[$name] = $value;
        }
        
        if (!isset($sequence[$params['category']])) $sequence[$params['category']] = 0;
        $params['sequence'] = $sequence[$params['category']]++;
        $attribute[$key] = $params;
      }
    }
    $attribute = $this->_attributeCalculateSequence($sequence, $attribute);
    
    $validator->setValues(array('reservationAttribute'=>$attribute));
  }
  
  private function _saveNewFile($validator) {
    $newFile = $this->_app->request->getParams('newFile');
    
    $file = array();
    if (is_array($newFile)) {
      foreach ($newFile as $key=>$eLine) {
        $eParams = explode(';',$eLine);
        
        $params = array();
        foreach ($eParams as $par) {
          list($name,$value) = explode(':',$par);
          $params[$name] = $value;
        }
        $file[$key] = $params;
      }
    }
    
    $validator->setValues(array('file'=>$file));
  }

  private function _prepareInvoiceData($validator, & $data) {
    global $NOTIFICATION;
    if (in_array($this->_app->auth->getUsername(),explode(',',$NOTIFICATION['adminEmail']))) {
      $data['invoice'] = array(
        'accountFrom' => $this->_app->regionalSettings->convertHumanToDate($validator->getVarValue('invoiceAccountFrom')),
        'monthFee' => $this->_app->regionalSettings->convertHumanToNumber($validator->getVarValue('invoiceMonthFee'),5,1),
        'reservationFee' => $this->_app->regionalSettings->convertHumanToNumber($validator->getVarValue('invoiceReservationFee'),10,1),
        'reservationPriceFee' => $this->_app->regionalSettings->convertHumanToNumber($validator->getVarValue('invoiceReservationPriceFee'),3,1),
        'reservationPricePaid' => $validator->getVarValue('invoiceReservationPricePaid'),
        'dueLength' => $validator->getVarValue('invoiceDueLength'),
        'email' => $validator->getVarValue('invoiceEmail'),
      );
    }
  }

  protected function _userRun() {  
    $validator = Validator::get('customer','CustomerValidator');
    $validator->initValues();

    //adump($validator->getValues());die;
    
    $this->_saveNewEmployee($validator);
    $this->_saveNewCenter($validator);
    $this->_saveNewRegistration($validator);
    $this->_saveNewCoworker($validator);
    $this->_saveNewAttribute($validator);
    $this->_saveNewFile($validator);

    $customerId = $validator->getVarValue('id');

    if ($this->_app->request->getParams('newEmployeeUser')) {
      $this->_app->response->addParams(array('fromCustomerEmployee'=>1));
      return 'eUserEdit';
    }
    if ($this->_app->request->getParams('newCoworkerUser')) {
      $this->_app->response->addParams(array('fromCustomerCoworker'=>1));
      return 'eUserEdit';
    }
    if ($this->_app->request->getParams('generateInvoice')) {
      if ($invoicePeriod = $validator->getVarValue('invoicePeriod')) {
        $data = array();
        $this->_prepareInvoiceData($validator, $data);

        $bCustomer = new BCustomer($customerId ? $customerId : null);
        $bCustomer->saveInvoiceSettings($data);
        $number = $bCustomer->createInvoice($invoicePeriod);

        $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.editCustomer_createInvoice'), $number));
      }

      $this->_app->response->addParams(array('backwards'=>1));
      return 'eBack';
    }
    if ($this->_app->request->getParams('sendTestEmail')) {
      $bNot = new BNotification;
      $ret = $bNot->sendTestEmail($validator->getVarValue('providerId'),$validator->getVarValue('smtpHost'),$validator->getVarValue('smtpPort'),
        $validator->getVarValue('smtpUser'),$validator->getVarValue('smtpPassword'),$validator->getVarValue('smtpSecure'));

      if (!$ret) $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.editCustomer_sentTestEmail_ok'), $this->_app->auth->getEmail()));
      else $this->_app->messages->addMessage('userError', sprintf($this->_app->textStorage->getText('info.editCustomer_sentTestEmail_fail'), $ret));

      $this->_app->response->addParams(array('backwards'=>1));
      return 'eBack';
    }

    $validator->validateValues();
    
    if (($validator->getVarValue('provider')=='Y')&&!$validator->getVarValue('shortName')) {
      throw new ExceptionUserTextstorage('error.editCustomer_shortNameMissing');
    }

    $bCustomer = new BCustomer($customerId?$customerId:null);
    $data = array(
        'name'                  => $validator->getVarValue('name'),
        'street'                => $validator->getVarValue('street'),
        'city'                  => $validator->getVarValue('city'),
        'postalCode'            => $validator->getVarValue('postalCode'),
        'state'                 => $validator->getVarValue('state'),
        'ic'                    => $validator->getVarValue('ic'),
        'dic'                   => $validator->getVarValue('dic'),
        'email'                 => $validator->getVarValue('email'),
        'phone'                 => $validator->getVarValue('phone'),
        'employee'              => $validator->getVarValue('employee'),
        'registration'          => $validator->getVarValue('registration'),
        );
    if (!$validator->getVarValue('fromReservation')&&
        ($validator->getVarValue('myData')||$this->_app->auth->isAdministrator())) {
      $data['providerParams'] = array(
        'provider'              => $validator->getVarValue('provider'),
        'otherInvoiceAddress'   => $validator->getVarValue('invoiceOther'),
        'invoiceName'           => $validator->getVarValue('invoiceName'),
        'invoiceStreet'         => $validator->getVarValue('invoiceStreet'),
        'invoiceCity'           => $validator->getVarValue('invoiceCity'),
        'invoicePostalCode'     => $validator->getVarValue('invoicePostalCode'),
        'invoiceState'          => $validator->getVarValue('invoiceState'),
        'notificationTemplateId'=> $validator->getVarValue('notificationTemplateId'),
        'bankAccount'           => $validator->getVarValue('bankAccount'),
        'vat'                   => $validator->getVarValue('vat'),
        'vatRate'               => $this->_app->regionalSettings->convertHumanToNumber($validator->getVarValue('vatRate'),2,1),
        'bankAccountSuffix'     => $validator->getVarValue('bankAccountSuffix'),
        'phone1'                => $validator->getVarValue('phone1'),
        'phone2'                => $validator->getVarValue('phone2'),
        'www'                   => $validator->getVarValue('www'),
        'center'                => $validator->getVarValue('center'),
        'smtpHost'              => $validator->getVarValue('smtpHost'),
        'smtpPort'              => $validator->getVarValue('smtpPort'),
        'smtpUser'              => $validator->getVarValue('smtpUser'),
        'smtpPassword'          => $validator->getVarValue('smtpPassword'),
        'smtpSecure'            => $validator->getVarValue('smtpSecure'),
      );
      $data['coworker'] = $validator->getVarValue('coworker');
      $data['userAttribute'] = $validator->getVarValue('userAttribute');
      $data['commodityAttribute'] = $validator->getVarValue('commodityAttribute');
      $data['reservationAttribute'] = $validator->getVarValue('reservationAttribute');
      $data['file'] = $validator->getVarValue('file');

      if ($this->_app->auth->isAdministrator()) $data['providerParams']['shortName'] = $validator->getVarValue('shortName');
    }

    $this->_prepareInvoiceData($validator, $data);
    
    #adump($data);die;
    $bCustomer->save($data);

    // pokud se uklada provider prihlaseneho uzivatele (a to muze byt pouze nekdo s roli "spravce")
    // pridam mu nova strediska do AUTHu
    // zmenim aktualni stredisko, pokud je smazane
    if ($validator->getVarValue('myData')) {
      $allowedCenter = $this->_app->auth->getAllowedCenter('array');
      $actualCenter = $this->_app->auth->getActualCenter();

      $actualCenterOk = false;
      $customerData = $bCustomer->getData();
      foreach ($customerData['center'] as $center) {
        if (!in_array($center['centerId'], $allowedCenter)) $this->_app->auth->addAllowedCenter($center['centerId']);
        if ($center['centerId']==$actualCenter) $actualCenterOk = true;
      }
      if (!$actualCenterOk) $this->_app->auth->setActualCenter(null);
    }
    
    $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.editCustomer_saveOk'), $validator->getVarValue('name')));
    
    return 'eBack';
  }
}

?>
