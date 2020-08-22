<?php

class BCustomer extends BusinessObject {
  private $_invoiceSettings;

  private function _checkAccess($params=array()) {
    return true;
  
    $ret = $this->_app->auth->isAdministrator();
    
    // "normalniho" zakaznika muze zalozit kazdy provider
    if (!$ret) {
      if ($this->_app->auth->isProvider()&&!isset($params['providerParams'])) {
        $ret = true;
      }
    }
    
    return $ret;
  }
  
  private function _checkCreditAccess($provider,$type='CASH',$amount) {
    return true;
  
    $ret = $this->_app->auth->haveRight('credit_admin',$provider);
    
    if ($ret) {
      $s = new SCustomer;
      $s->addStatement(new SqlStatementBi($s->columns['customer_id'], $this->_id, '%s=%s'));
      $s->addStatement(new SqlStatementBi($s->columns['registration_provider'], $provider, '%s=%s'));
      $s->setColumnsMask(array('customer_id'));
      $res = $this->_app->db->doQuery($s->toString());
      $ret = $this->_app->db->getRowsNumber($res);
    } elseif ($c=$this->_app->auth->isCustomer()) {
      // pokud meni kredit zakaznik, muze jenom sobe strhavat za rezervaci
      $ret = ($c == $this->_id) && ($type == 'RESERVATION');
    }
    
    return $ret;
  }

  private function _checkBeforeSave($params) {
    // kdyz se zaklada novy zakaznik jsou tyto atributy povinne
    if (!$this->_id) {
      if (!isset($params['name'])) throw new ExceptionUserTextStorage('error.saveCustomer_emptyName');
      if (!isset($params['street'])) throw new ExceptionUserTextStorage('error.saveCustomer_emptyStreet');
      if (!isset($params['city'])) throw new ExceptionUserTextStorage('error.saveCustomer_emptyCity');
      if (!isset($params['postalCode'])) throw new ExceptionUserTextStorage('error.saveCustomer_emptyPostalCode');
      if (!isset($params['state'])) throw new ExceptionUserTextStorage('error.saveCustomer_emptyState');
      if (!isset($params['email'])) throw new ExceptionUserTextStorage('error.saveCustomer_emptyEmail');
      if (!isset($params['phone'])) throw new ExceptionUserTextStorage('error.saveCustomer_emptyPhone');
    }
    
    // tyto nesmi byt prazdny nikdy
    if (isset($params['name'])&&!$params['name']) throw new ExceptionUserTextStorage('error.saveCustomer_emptyName');
    if (isset($params['street'])&&!$params['street']) throw new ExceptionUserTextStorage('error.saveCustomer_emptyStreet');
    if (isset($params['city'])&&!$params['city']) throw new ExceptionUserTextStorage('error.saveCustomer_emptyCity');
    if (isset($params['postalCode'])&&!$params['postalCode']) throw new ExceptionUserTextStorage('error.saveCustomer_emptyPostalCode');
    if (isset($params['state'])&&!$params['state']) throw new ExceptionUserTextStorage('error.saveCustomer_emptyState');
    if (isset($params['email'])&&!$params['email']) throw new ExceptionUserTextStorage('error.saveCustomer_emptyEmail');
    if (isset($params['phone'])&&!$params['phone']) throw new ExceptionUserTextStorage('error.saveCustomer_emptyPhone');
    
    // test na unikatnost
    $s = new SCustomer;
    if (isset($params['ic'])&&$params['ic']) {
      $s->addStatement(new SqlStatementQuad($s->columns['email'], $params['email'], $s->columns['ic'], $params['ic'], '(%s=%s OR %s=%s)'));
    } else {
      $s->addStatement(new SqlStatementBi($s->columns['email'], $params['email'], '%s=%s'));
    }
    $s->addStatement(new SqlStatementBi($s->columns['customer_id'], $this->_id, '%s<>%s'));
    $s->setColumnsMask(array('customer_id'));
    $result = $this->_app->db->doQuery($s->toString());
    if ($this->_app->db->getRowsNumber($result) > 0) {
      throw new ExceptionUserTextStorage('error.saveCustomer_alreadyExists');
    }

    if (isset($params['providerParams']['vat'])&&($params['providerParams']['vat']=='Y')) {
      if (!isset($params['providerParams']['vatRate'])||($params['providerParams']['vatRate']<=0)) throw new ExceptionUserTextStorage('error.saveCustomer_emptyVatRate');
    }
  }
  
  private function _checkBeforeDelete() {
    $this->_load();
    
    $s = new SCustomerRegistration;
    $s->addStatement(new SqlStatementBi($s->columns['customer'], $this->_id, '%s=%s'));
    if (!$this->_app->auth->isAdministrator()) $s->addStatement(new SqlStatementMono($s->columns['provider'], sprintf('%%s IN (%s)', $this->_app->auth->getAllowedProvider('user_admin','list'))));
    $s->addStatement(new SqlStatementMono($s->columns['credit'], '%s>0'));
    $s->setColumnsMask(array('customerregistration_id'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($this->_app->db->getRowsNumber($res)) throw new ExceptionUserTextStorage('error.deleteCustomer_credit');
    
    $s = new SReservation;
    $s->addStatement(new SqlStatementBi($s->columns['customer'], $this->_id, '%s=%s'));
    $s->setColumnsMask(array('reservation_id'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($this->_app->db->getRowsNumber($res)) throw new ExceptionUserTextStorage('error.deleteCustomer_reservationExists');
    
    $s = new SUserRegistration;
    $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_data['providerId'], '%s=%s'));
    $s->setColumnsMask(array('user','fullname'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($row = $this->_app->db->fetchAssoc($res)) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.deleteCustomer_userRegistrationExists'), $row['fullname']));

  }

  protected function _load() {
    if ($this->_id&&!$this->_loaded) {
      $oCustomer = new OCustomer($this->_id);
      $data = $oCustomer->getData();
      $returnData['id'] = $data['customer_id'];
      $returnData['code'] = $data['code'];
      $returnData['name'] = $data['name'];
      $returnData['ic'] = $data['ic'];
      $returnData['dic'] = $data['dic'];
      $returnData['email'] = $data['email'];
      $returnData['phone'] = $data['phone'];
      
      $returnData['addressId'] = $data['address'];  
      if ($data['address']) {
        $oAddress = new OAddress($data['address']);
        $oAData = $oAddress->getData();
      
        $returnData['street'] = $oAData['street'];
        $returnData['city'] = $oAData['city'];
        $returnData['postalCode'] = $oAData['postal_code'];
        $returnData['state'] = $oAData['state'];
      }
      
      $returnData['providerId'] = $data['provider'];
      $returnData['provider'] = $data['provider']?'Y':'N';
      if ($data['provider']) {
        $oProvider = new OProvider($data['provider']);
        $oPData = $oProvider->getData();
        
        $returnData['shortName'] = $oPData['short_name'];
        $returnData['notificationTemplateId'] = $oPData['notificationtemplate'];
        $returnData['vat'] = $oPData['vat'];
        $returnData['vatRate'] = $oPData['vat_rate'];
        $returnData['bankAccount'] = $oPData['bank_account_number'];
        $returnData['bankAccountSuffix'] = $oPData['bank_account_suffix'];
        $returnData['phone1'] = $oPData['phone_1'];
        $returnData['phone2'] = $oPData['phone_2'];
        $returnData['www'] = $oPData['www'];

        $returnData['invoiceOther'] = $oPData['invoice_other'];
        $returnData['invoiceAddressId'] = $oPData['invoice_address'];
        if ($oPData['invoice_other']=='Y') {
          $returnData['invoiceName'] = $oPData['invoice_name'];
          if ($oPData['invoice_address']) {  
            $oAddress = new OAddress($oPData['invoice_address']);
            $oAData = $oAddress->getData();
      
            $returnData['invoiceStreet'] = $oAData['street'];
            $returnData['invoiceCity'] = $oAData['city'];
            $returnData['invoicePostalCode'] = $oAData['postal_code'];
            $returnData['invoiceState'] = $oAData['state'];
          }
        }
      }
      
      $returnData['center'] = array();
      $s = new SCenter;
      $s->addStatement(new SqlStatementBi($s->columns['customer_id'], $this->_id, '%s=%s'));
      $s->setColumnsMask(array('center_id','name','payment_info','address_id','street','city','region','postal_code','state'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $returnData['center'][$row['center_id']] = array(
              'centerId'    => $row['center_id'],
              'name'        => $row['name'],
              'paymentInfo' => $row['payment_info'],
              'addressId'   => $row['address_id'],
              'street'      => $row['street'],
              'city'        => $row['city'],
							'region'      => $row['region'],
              'postalCode'  => $row['postal_code'],
              'state'       => $row['state'],
              );
      }
      
      $returnData['registration'] = array();
      $s = new SCustomerRegistration;
      $s->addStatement(new SqlStatementBi($s->columns['customer'], $this->_id, '%s=%s'));
      if (!$this->_app->auth->isAdministrator()) $s->addStatement(new SqlStatementMono($s->columns['provider'], sprintf('%%s IN (%s)', $this->_app->auth->getAllowedProvider(null,'list'))));
      $s->addOrder(new SqlStatementAsc($s->columns['provider_name']));
      $s->setColumnsMask(array('customerregistration_id','provider','registration_timestamp','receive_advertising','provider_name','credit'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $returnData['registration'][$row['customerregistration_id']] = array(
              'registrationId'        => $row['customerregistration_id'],
              'providerId'            => $row['provider'],
              'providerName'          => $row['provider_name'],
              'timestamp'             => $row['registration_timestamp'],
              'advertising'           => $row['receive_advertising'],
              'credit'                => $row['credit'],
              );
      }
      
      $returnData['coworker'] = array();
      $s = new SUserRegistration;
      $s->addStatement(new SqlStatementBi($s->columns['provider'], $returnData['providerId'], '%s=%s'));
      $s->addStatement(new SqlStatementPenta($s->columns['admin'], $s->columns['supervisor'], $s->columns['reception'], $s->columns['organiser'], $s->columns['power_organiser'],
                                           "((%s='Y') OR (%s='Y') OR (%s='Y') OR (%s='Y') OR (%s='Y'))"));
      $s->addOrder(new SqlStatementAsc($s->columns['fullname']));
      $s->setColumnsMask(array('userregistration_id','user','fullname','email',
				'admin','supervisor','reception','organiser','power_organiser','role_center'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $returnData['coworker'][$row['userregistration_id']] = array(
					'coworkerId'        => $row['userregistration_id'],
					'userId'            => $row['user'],
					'fullname'          => $row['fullname'],
					'email'             => $row['email'],
					'admin'             => $row['admin'],
					'supervisor'        => $row['supervisor'],
					'reception'         => $row['reception'],
					'organiser'         => $row['organiser'],
					'powerOrganiser'    => $row['power_organiser'],
					'roleCenter'    		=> $row['role_center']
				);
      }
      
      $returnData['employee'] = array();
      $s = new SEmployee;
      $s->addStatement(new SqlStatementBi($s->columns['customer'], $returnData['id'], '%s=%s'));
      if (!$this->_app->auth->isAdministrator()) $s->addStatement(new SqlStatementMono($s->columns['registration_provider'], sprintf('%%s IN (%s)', $this->_app->auth->getAllowedProvider(null,'list'))));
      $s->addOrder(new SqlStatementAsc($s->columns['fullname']));
      $s->setColumnsMask(array('employee_id','user','fullname','email','credit_access'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $returnData['employee'][$row['employee_id']] = array(
              'employeeId'        => $row['employee_id'],
              'userId'            => $row['user'],
              'fullname'          => $row['fullname'],
              'email'             => $row['email'],
              'creditAccess'      => $row['credit_access'],
              );
      }
      
      $returnData['userAttribute'] = array();
			$returnData['commodityAttribute'] = array();
			$returnData['reservationAttribute'] = array();
      $s = new SAttribute;
      $s->addStatement(new SqlStatementBi($s->columns['provider'], $returnData['providerId'], '%s=%s'));
      #$s->addStatement(new SqlStatementMono($s->columns['applicable'], "%s='USER'"));
      #$s->addStatement(new SqlStatementMono($s->columns['disabled'], "%s<>'Y'"));
			$s->addOrder(new SqlStatementAsc($s->columns['applicable']));
      #$s->addOrder(new SqlStatementAsc($s->columns['category']));
      $s->addOrder(new SqlStatementAsc($s->columns['sequence']));
      $s->setColumnsMask(array('attribute_id','applicable','applicable_type','short_name','url','restricted','mandatory','category','sequence','type','allowed_values','disabled'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $s1 = new SAttributeName;
        $s1->addStatement(new SqlStatementBi($s1->columns['attribute'], $row['attribute_id'], '%s=%s'));
        $s1->setColumnsMask(array('lang','name'));
        $res1 = $this->_app->db->doQuery($s1->toString());
        $name = array(); while ($row1 = $this->_app->db->fetchAssoc($res1)) { $name[$row1['lang']] = $row1['name']; }

        switch ($row['applicable']) {
					case 'USER': $index = 'userAttribute'; break;
					case 'COMMODITY': $index = 'commodityAttribute'; break;
					case 'RESERVATION': $index = 'reservationAttribute'; break;
				}
        
        $returnData[$index][$row['attribute_id']] = array(
					'attributeId'       => $row['attribute_id'],
					'applicable'        => $row['applicable'],
					'applicableType'    => $row['applicable_type'],
					'name'              => $name,
					'short'             => $row['short_name'],
					'url'               => $row['url'],
					'restricted'        => $row['restricted'],
					'mandatory'         => $row['mandatory'],
					'category'          => $row['category'],
					'sequence'          => $row['sequence'],
					'type'              => $row['type'],
					'allowedValues'     => $row['allowed_values'],
					'disabled'          => $row['disabled'],
				);
      }

      $s = new SProviderFile;
      $s->addStatement(new SqlStatementBi($s->columns['provider'], $returnData['providerId'], '%s=%s'));
      $s->addOrder(new SqlStatementAsc($s->columns['name']));
      $s->setColumnsMask(array('providerfile_id','short_name','name','length','file','hash','file_name'));
      $res = $this->_app->db->doQuery($s->toString());
      $returnData['file'] = array();
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $returnData['file'][$row['providerfile_id']] = array(
          'id'          => $row['providerfile_id'],
          'short'       => $row['short_name'],
          'name'        => $row['name'],
          'length'      => $row['length'],
          'sourceId'    => $row['file'],
          'sourceHash'  => $row['hash'],
          'sourceName'  => $row['file_name']
        );
      }

      if ($returnData['providerId']) {
        $o = new OProviderSettings(array('provider'=>$returnData['providerId']));
        $oData = $o->getData();

        $returnData['invoice'] = array(
					'accountFrom'           => $oData['invoice_account_from'],
          'monthFee'              => $oData['invoice_month_fee'],
          'reservationFee'        => $oData['invoice_reservation_fee'],
          'reservationPriceFee'   => $oData['invoice_reservation_price_fee'],
          'reservationPricePaid'  => $oData['invoice_reservation_price_paid'],
          'dueLength'             => $oData['invoice_due_length'],
          'email'                 => $oData['invoice_email']
        );
        $returnData['smtpHost'] = $oData['smtp_host'];
        $returnData['smtpPort'] = $oData['smtp_port'];
        $returnData['smtpUser'] = $oData['smtp_user'];
        $returnData['smtpPassword'] = $oData['smtp_password'];
        $returnData['smtpSecure'] = $oData['smtp_secure'];
      }
      
      $this->_data = $returnData;
      
      $this->_loaded = true;
    }
  }

  public function getData() {
    if (!$this->_checkAccess()) throw new ExceptionUserTextStorage('error.accessDenied');

    $this->_load();

    return $this->_data;
  }

  private function _saveAddress($params) {
    $bA = new BAddress(ifsetor($this->_data['addressId']));
    
    $aData = array();
    if (isset($params['street'])) $aData['street'] = $params['street'];
    if (isset($params['city'])) $aData['city'] = $params['city'];
    if (isset($params['postalCode'])) $aData['postalCode'] = $params['postalCode'];
    if (isset($params['state'])) $aData['state'] = $params['state'];
    
    if (count($aData)) {
      $bA->save($aData);
      $o = new OCustomer($this->_id);
      $o->setData(array('address'=>$bA->getId()));
      $o->save();
    }
  }
  
  private function _saveProvider($params) {
    // je potreba kvuli ukladani dalsi dat zakaznika
    $this->_data['provider'] = 'N';
    
    if (isset($params['providerParams'])) {
      $provParams = $params['providerParams'];
      if ($provParams['provider']=='Y') {
        $oP = new OProvider(ifsetor($this->_data['providerId']));
        $oPData = array();
        $aData = array();
        if ($provParams['otherInvoiceAddress']=='Y') {
          if (isset($provParams['invoiceName'])) $oPData['invoice_name'] = $provParams['invoiceName'];
          if (isset($provParams['invoiceStreet'])) $aData['street'] = $provParams['invoiceStreet'];
          if (isset($provParams['invoiceCity'])) $aData['city'] = $provParams['invoiceCity'];
          if (isset($provParams['invoicePostalCode'])) $aData['postalCode'] = $provParams['invoicePostalCode'];
          if (isset($provParams['invoiceState'])) $aData['state'] = $provParams['invoiceState'];
        } else {
          if (isset($params['name'])) $oPData['invoice_name'] = $params['name'];
          if (isset($params['street'])) $aData['street'] = $params['street'];
          if (isset($params['city'])) $aData['city'] = $params['city'];
          if (isset($params['postalCode'])) $aData['postalCode'] = $params['postalCode'];
          if (isset($params['state'])) $aData['state'] = $params['state'];
        }
        
        if (count($aData)) {
          $bA = new BAddress(ifsetor($this->_data['invoiceAddressId']));
          $bA->save($aData);
          $oPData['invoice_address'] = $bA->getId();
        }
        
        if (isset($provParams['otherInvoiceAddress'])) $oPData['invoice_other'] = $provParams['otherInvoiceAddress'];
        if (isset($provParams['shortName'])) $oPData['short_name'] = $provParams['shortName'];
        if (isset($provParams['notificationTemplateId'])) $oPData['notificationtemplate'] = $provParams['notificationTemplateId']?$provParams['notificationTemplateId']:null;
        if (isset($provParams['vat'])) {
          $oPData['vat'] = $provParams['vat']?$provParams['vat']:null;
          if ($oPData['vat']=='Y') {
            if (isset($provParams['vatRate'])) $oPData['vat_rate'] = $provParams['vatRate']?$provParams['vatRate']:null;
          } else $oPData['vat_rate'] = 0;
        }
        if (isset($provParams['bankAccount'])) $oPData['bank_account_number'] = $provParams['bankAccount'];
        if (isset($provParams['bankAccountSuffix'])) $oPData['bank_account_suffix'] = $provParams['bankAccountSuffix'];
        if (isset($provParams['phone1'])) $oPData['phone_1'] = $provParams['phone1'];
        if (isset($provParams['phone2'])) $oPData['phone_2'] = $provParams['phone2'];
        if (isset($provParams['www'])) $oPData['www'] = $provParams['www'];
        if (count($oPData)) {
          $oP->setData($oPData);
          $oP->save();
          $oPData = $oP->getData();
          
          $o = new OCustomer($this->_id);
          $o->setData(array('provider'=>$oP->getId()));
          $o->save();
          
          // je potreba kvuli ukladani dalsi dat zakaznika
          $this->_data['providerId'] = $oP->getId();
          $this->_data['provider'] = 'Y';
          $this->_data['shortName'] = $oPData['short_name'];
        }
        
        $this->_saveCenter($provParams);

        $this->_saveDefaultSettings();
      } elseif (isset($this->_data['providerId'])&&$this->_data['providerId']) {
        $o = new OCustomer($this->_id);
        $o->setData(array('provider'=>null));
        $o->save();
          
        $oP = new OProvider($this->_data['providerId']);
        $oP->delete();
      }
    }
  }

  private function _saveDefaultSettings() {
    $s = new SProviderSettings;
    $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_data['providerId'], '%s=%s'));
    $s->setColumnsMask(array('provider'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($row = $this->_app->db->fetchAssoc($res)) {
      $o = new OProviderSettings(array('provider'=>$this->_data['providerId']));
      $oData = $o->getData();

      $newData = array();
    } else {
      $o = new OProviderSettings;

      $newData['provider'] = $this->_data['providerId'];
    }

    if (!isset($oData['show_company'])||!$oData['show_company']) $newData['show_company'] = 'N';
    if (!isset($oData['userregistration_validate'])||!$oData['userregistration_validate']) $newData['userregistration_validate'] = 'Y';

    if (count($newData)) {
      $o->setData($newData);
      $o->save();
    }
  }
  
  private function _saveOrganiser($params) {
    if (isset($params['organiser'])&&($this->_data['provider']=='Y')) {
      $idToSave = array();
      
      if (is_array($params['organiser'])&&count($params['organiser'])) {
        $s = new SUserRegistration;
        $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_data['providerId'], '%s=%s'));
        $s->addStatement(new SqlStatementMono($s->columns['userregistration_id'],
                                              sprintf('%%s IN (%s)', implode(',',array_keys($params['organiser'])))));
        $s->setColumnsMask(array('userregistration_id','user'));
        $res = $this->_app->db->doQuery($s->toString());
        while ($row = $this->_app->db->fetchAssoc($res)) {
          $o = new OUserRegistration($row['userregistration_id']);
          $o->setData(array('organiser'=>'Y'));
          $o->save();
          
          $o = new OUser($row['user']);
          $o->setData(array('organiser'=>'Y'));
          $o->save();
          
          $idToSave[] = $row['userregistration_id'];
        }
      }
      
      $s = new SUserRegistration;
      $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_data['providerId'], '%s=%s'));
      if (count($idToSave)) $s->addStatement(new SqlStatementMono($s->columns['userregistration_id'], sprintf('%%s NOT IN (%s)', implode(',',$idToSave))));
      $s->addStatement(new SqlStatementMono($s->columns['organiser'], "%s='Y'"));
      $s->setColumnsMask(array('userregistration_id','user'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $o = new OUserRegistration($row['userregistration_id']);
        $o->setData(array('organiser'=>'N'));
        $o->save();
        
        $o = new OUser($row['user']);
        $o->setData(array('organiser'=>'N'));
        $o->save();
      }
    }
  }

  private function _saveCoworker($params) {
    if (isset($params['coworker'])&&($this->_data['provider']=='Y')) {
      $idToSave = array();
      if (is_array($params['coworker'])) {
        foreach ($params['coworker'] as $coworker) {
          // kdyz nemam jako parametr userregistration_id, musim ho dohledat
          if (!$coworker['coworkerId']) {
            $s = new SUserRegistration;
            $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_data['providerId'], '%s=%s'));
            $s->addStatement(new SqlStatementBi($s->columns['user'], $coworker['userId'], '%s=%s'));
            $s->setColumnsMask(array('userregistration_id'));
            $res = $this->_app->db->doQuery($s->toString());
            if (!$row = $this->_app->db->fetchAssoc($res)) {
              if ($this->_app->auth->isAdministrator()) {
                // pokud registrace neexistuje a firmu uklada admin, tak registraci zalozim
                $o = new OUserRegistration;
                $o->setData(array(
                    'user'                    => $coworker['userId'],
                    'provider'                => $this->_data['providerId'],
                    'registration_timestamp'  => date('Y-m-d H:i:s'),
                    'receive_advertising'     => 'N',
                    'credit'                  => 0,
                    ));
                $o->save();
                $row['userregistration_id'] = $o->getId();
              } else throw new ExceptionUserTextStorage('error.saveCustomer_invalidCoworker');
            }
            $coworker['coworkerId'] = $row['userregistration_id'];
          }
          
          $idToSave[] = $coworker['coworkerId'];
          $o = new OUserRegistration($coworker['coworkerId']);
          $oData = array();
          if (isset($coworker['admin'])) $oData['admin'] = $coworker['admin'];
					if (isset($coworker['supervisor'])) $oData['supervisor'] = $coworker['supervisor'];
          if (isset($coworker['reception'])) $oData['reception'] = $coworker['reception'];
          if (isset($coworker['organiser'])) $oData['organiser'] = $coworker['organiser'];
          if (isset($coworker['powerOrganiser'])) $oData['power_organiser'] = $coworker['powerOrganiser'];
					if (isset($coworker['roleCenter'])) $oData['role_center'] = $coworker['roleCenter'];
          if (count($oData)) {  
            $o->setData($oData);
            $o->save();
          }
          
          #$b = new BUser($coworker['userId']);
          #$b->updateGlobalRole();
        }
      }
      
      $s = new SUserRegistration;
      $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_data['providerId'], '%s=%s'));
      if (count($idToSave)) $s->addStatement(new SqlStatementMono($s->columns['userregistration_id'], sprintf('%%s NOT IN (%s)', implode(',',$idToSave))));
      $s->setColumnsMask(array('userregistration_id','user'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $o = new OUserRegistration($row['userregistration_id']);
        $o->setData(array('admin'=>'N','supervisor'=>'N','reception'=>'N','organiser'=>'N','power_organiser'=>'N','role_center'=>''));
        $o->save();
                  
        #$b = new BUser($row['user']);
        #$b->updateGlobalRole();
      }
    }
  }
  
  private function _saveEmployee($params) {
    if (isset($params['employee'])) {
      $idToSave = array();
      if (is_array($params['employee'])) {
        foreach ($params['employee'] as $employee) {
          $o = new OEmployee($employee['employeeId']?$employee['employeeId']:null);
          
          $oData = array();
          if (isset($employee['userId'])) $oData['user'] = $employee['userId'];
          if (isset($employee['creditAccess'])) $oData['credit_access'] = $employee['creditAccess'];
          if (!$o->getId()) $oData['customer'] = $this->_id;
          $o->setData($oData);
          $o->save();
          
          $idToSave[] = $o->getId();
        }
      }
      
      $s = new SEmployee;
      $s->addStatement(new SqlStatementBi($s->columns['customer_id'], $this->_id, '%s=%s'));
      if (count($idToSave)) $s->addStatement(new SqlStatementMono($s->columns['employee_id'], sprintf('%%s NOT IN (%s)', implode(',',$idToSave))));
      if (!$this->_app->auth->isAdministrator()) $s->addStatement(new SqlStatementMono($s->columns['registration_provider'], sprintf('%%s IN (%s)', $this->_app->auth->getAllowedProvider('user_admin','list'))));
      $s->setColumnsMask(array('employee_id'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $o = new OEmployee($row['employee_id']);
        $o->delete();
      }
    }
  }
  
  private function _saveCenter($params) {
    if (isset($params['center'])&&$this->_data['providerId']) {
      $idToSave = array();
      if (is_array($params['center'])) {
        foreach ($params['center'] as $center) {
          $o = new OCenter($center['centerId']?$center['centerId']:null);
          
          $bAData = array();
          if (isset($center['street'])) $bAData['street']= $center['street'];
          if (isset($center['city'])) $bAData['city']= $center['city'];
					if (isset($center['region'])) $bAData['region']= $center['region'];
          if (isset($center['postalCode'])) $bAData['postalCode']= $center['postalCode'];
          if (isset($center['state'])) $bAData['state']= $center['state'];
          if (count($bAData)) {
            if (isset($center['centerId'])&&$center['centerId']) {
              $oData = $o->getData();
              $addressId = $oData['address'];
            } else $addressId = null;
            $bA = new BAddress($addressId);
            $bA->save($bAData);
          }
          
          $oData = array();
          if (isset($center['name'])) $oData['name'] = $center['name'];
          if (isset($center['paymentInfo'])) $oData['payment_info'] = $center['paymentInfo'];
          if (count($bAData)) $oData['address'] = $bA->getId();
          if (!$o->getId()) $oData['provider'] = $this->_data['providerId'];
          $o->setData($oData);
          $o->save();
          
          $idToSave[] = $o->getId();
        }
      }
      
      $s = new SCenter;
      $s->addStatement(new SqlStatementBi($s->columns['customer_id'], $this->_id, '%s=%s'));
      if (count($idToSave)) $s->addStatement(new SqlStatementMono($s->columns['center_id'], sprintf('%%s NOT IN (%s)', implode(',',$idToSave))));
      $s->setColumnsMask(array('center_id'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $o = new OCenter($row['center_id']);
        $o->delete();
      }
    }
  }

  private function _saveRegistration($params) {
    if (isset($params['registration'])) {
      $idToSave = array();
      if (is_array($params['registration'])) {
        // kontrola aby registrace byla unikatni pro poskytovatele
        $providerToCheck = array();
        foreach ($params['registration'] as $reg) {
          if (!isset($reg['registrationId'])||!$reg['registrationId']) {
            // uz jiz existujicich registraci nepujde menit poskytovatel, staci projit ty bez ID-cka
            if (isset($reg['providerId'])) $providerToCheck[] = $reg['providerId'];
          }
        }
        if (count($providerToCheck)) {
          $s = new SCustomerRegistration;
          $s->addStatement(new SqlStatementBi($s->columns['customer'], $this->_id, '%s=%s'));
          $s->addStatement(new SqlStatementMono($s->columns['provider'], sprintf('%%s IN (%s)', implode(',',$providerToCheck))));
          $s->setColumnsMask(array('customerregistration_id'));
          $res = $this->_app->db->doQuery($s->toString());
          if ($this->_app->db->getRowsNumber($res)) throw new ExceptionUserTextStorage('error.saveCustomer_registrationNotUnique');
        }
        
        foreach ($params['registration'] as $reg) {
          $regId = isset($reg['registrationId'])&&$reg['registrationId']?$reg['registrationId']:null;
          $o = new OCustomerRegistration($regId);
          $oData = array();
          if (isset($reg['providerId'])) $oData['provider'] = $reg['providerId'];
          if (isset($reg['advertising'])) $oData['receive_advertising'] = $reg['advertising'];
          if (!$o->getId()) {
            $oData['customer'] = $this->_id;
            $oData['credit'] = 0;
            $oData['registration_timestamp'] = date('Y-m-d H:i:s');
          }
          $o->setData($oData);
          $o->save();
          
          $idToSave[] = $o->getId();
        }
      }
      
      // kdyz uklada zakaznika prihlaseny uzivatel (muze se registrovat z webu)
      if ($this->_app->auth->getUserId()) {
        $s = new SCustomerRegistration;
        $s->addStatement(new SqlStatementBi($s->columns['customer'], $this->_id, '%s=%s'));
        // pokud prihlaseny uzivatel neni administrator, tak smazu registrace od povolenych poskytovatelu
        if (!$this->_app->auth->isAdministrator()) $s->addStatement(new SqlStatementMono($s->columns['provider'], sprintf('%%s IN (%s)', $this->_app->auth->getAllowedProvider('user_admin','list'))));
        if (count($idToSave)) $s->addStatement(new SqlStatementMono($s->columns['customerregistration_id'], sprintf('%%s NOT IN (%s)', implode(',',$idToSave))));
        $s->setColumnsMask(array('customerregistration_id'));
        $res = $this->_app->db->doQuery($s->toString());
        while ($row = $this->_app->db->fetchAssoc($res)) {
          $o = new OCustomerRegistration($row['customerregistration_id']);
          $o->delete();
        }
      }
    }
  }
  
  private function _saveUserAttribute($params) {
    if (isset($params['userAttribute'])&&isset($this->_data['providerId'])&&$this->_data['providerId']) {
      $idToSave = array();
      if (is_array($params['userAttribute'])) {
        foreach ($params['userAttribute'] as $key=>$attribute) {
          // to prijde pryc, az pujde v GUI zadat oznaceni v ruznych jazycich
          $params['userAttribute'][$key]['name'] = array('en'=>$attribute['name']);
          
          if (isset($attribute['attributeId'])&&$attribute['attributeId']) $idToSave[] = $attribute['attributeId'];
        }
      }
      
      $s = new SAttribute;
      $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_data['providerId'], '%s=%s'));
      $s->addStatement(new SqlStatementMono($s->columns['applicable'], "%s='USER'"));
      if (count($idToSave)) $s->addStatement(new SqlStatementMono($s->columns['attribute_id'], sprintf('%%s NOT IN (%s)', implode(',',$idToSave))));
      $s->setColumnsMask(array('attribute_id'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
      	// pokud je u uzivatele, nejde smazat
        $s1 = new SUserAttribute;
        $s1->addStatement(new SqlStatementBi($s1->columns['attribute'], $row['attribute_id'], '%s=%s'));
        $s1->setColumnsMask(array('user','attribute'));
        $res1 = $this->_app->db->doQuery($s1->toString());
        $userHave = $this->_app->db->getRowsNumber($res1)>0;
        $isUnique = false;
        if (!$userHave) {
        	// pokud je nastaven jako unique klic, taky nejde smazat
					$s1 = new SProviderSettings;
					$s1->addStatement(new SqlStatementBi($s1->columns['provider'], $this->_data['providerId'], '%s=%s'));
					$s1->setColumnsMask(array('user_unique','user_subaccount_unique'));
					$res1 = $this->_app->db->doQuery($s1->toString());
					if ($row1 = $this->_app->db->fetchAssoc($res1)) {
						if ((strpos($row1['user_unique'],'attr_'.$row['attribute_id'])!==false)||
							(strpos($row1['user_subaccount_unique'],'attr_'.$row['attribute_id'])!==false)) {
							$isUnique = true;
						}
					}
				}

        if ($userHave||$isUnique) {
          $s2 = new SAttributeName;
          $s2->addStatement(new SqlStatementBi($s2->columns['attribute'], $row['attribute_id'], '%s=%s'));
          $s2->setColumnsMask(array('lang','name'));
          $res2 = $this->_app->db->doQuery($s2->toString());
          $a = array();
          while ($row2 = $this->_app->db->fetchAssoc($res2)) {
            $a[$row2['lang']] = $row2['name'];
          }
          $name = ifsetor($a[$this->_app->language->getLanguage()], array_values($a)[0]);

          if ($userHave) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveCustomer_userAttributeUsed'), $name));
          else throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveCustomer_userAttributeUnique'), $name));
        }
        
        $o = new OAttribute($row['attribute_id']);
        $o->delete();
      }
      
      if (is_array($params['userAttribute'])) {
        // kontrola na unikatnost oznaceni
        $tmp = array();
        foreach ($params['userAttribute'] as $index=>$attribute) {
          if ($attribute['disabled']=='Y') continue;
          
          foreach ($attribute['name'] as $lang=>$name) {
            $key = sprintf('%s-%s', $lang, $name);
            if (isset($tmp[$key])) throw new ExceptionUserTextStorage('error.saveCustomer_attributeNotUnique');
            else $tmp[$key] = 1;
          }
        }
        
        foreach ($params['userAttribute'] as $attribute) {
          $o = new OAttribute($attribute['attributeId']?$attribute['attributeId']:null);
          
          if ($attribute['attributeId']) {
            $s = new SAttributeName;
            $s->addStatement(new SqlStatementBi($s->columns['attribute'], $attribute['attributeId'], '%s=%s'));
            $s->setColumnsMask(array('attributename_id'));
            $res = $this->_app->db->doQuery($s->toString());
            while ($row = $this->_app->db->fetchAssoc($res)) {
              $oo = new OAttributeName($row['attributename_id']);
              $oo->delete();
            }
          }

          if (isset($attribute['mandatory'])&&($attribute['mandatory']=='Y')) {
          	if (isset($attribute['restricted'])&&in_array($attribute['restricted'], array('INTERNAL','READONLY')))
          		throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveCustomer_attributeMandatoryConflict'), array_values($attribute['name'])[0]));
					}
      
          $oData = array();
          if (isset($attribute['short'])) $oData['short_name'] = $attribute['short'];
          if (isset($attribute['restricted'])) $oData['restricted'] = $attribute['restricted']?$attribute['restricted']:NULL;
          if (isset($attribute['mandatory'])) $oData['mandatory'] = $attribute['mandatory'];
          if (isset($attribute['category'])) $oData['category'] = $attribute['category'];
          if (isset($attribute['sequence'])) $oData['sequence'] = $attribute['sequence'];
          if (isset($attribute['disabled'])) $oData['disabled'] = $attribute['disabled']?$attribute['disabled']:'N';
          if (isset($attribute['type'])) {
            $oData['type'] = $attribute['type'];
            if (strcmp($attribute['type'],'LIST')) $attribute['allowedValues'] = '';
          }
          if (isset($attribute['allowedValues'])) $oData['allowed_values'] = preg_replace('/\s*,\s*/', ',', str_replace(array("\n","\t","\r"), '', $attribute['allowedValues']));
          if (isset($attribute['url'])) {
            if ($attribute['url']&&strpos($attribute['url'],'http')!==0) $attribute['url'] = 'http://'.$attribute['url'];
            $oData['url'] = $attribute['url'];
          }
          
          if (!$o->getId()) $oData['provider'] = $this->_data['providerId'];
					if (isset($attribute['applicableType'])) $oData['applicable_type'] = $attribute['applicableType']?$attribute['applicableType']:null;
          $oData['applicable'] = 'USER';
          $o->setData($oData);
          $o->save();
          
          foreach ($attribute['name'] as $lang=>$name) {
            $oo = new OAttributeName;
            $oo->setData(array('lang'=>$lang,'name'=>$name,'attribute'=>$o->getId()));
            $oo->save();
          }
        }
      }
    }
  }
  
  private function _saveCommodityAttribute($params) {
    if (isset($params['commodityAttribute'])&&isset($this->_data['providerId'])&&$this->_data['providerId']) {
      $idToSave = array();
      if (is_array($params['commodityAttribute'])) {
        foreach ($params['commodityAttribute'] as $key=>$attribute) {
          // to prijde pryc, az pujde v GUI zadat oznaceni v ruznych jazycich
          $params['commodityAttribute'][$key]['name'] = array('en'=>$attribute['name']);
          
          if (isset($attribute['attributeId'])&&$attribute['attributeId']) $idToSave[] = $attribute['attributeId'];
        }
      }
      
      $s = new SAttribute;
      $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_data['providerId'], '%s=%s'));
      $s->addStatement(new SqlStatementMono($s->columns['applicable'], "%s='COMMODITY'"));
      if (count($idToSave)) $s->addStatement(new SqlStatementMono($s->columns['attribute_id'], sprintf('%%s NOT IN (%s)', implode(',',$idToSave))));
      $s->setColumnsMask(array('attribute_id'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $s1 = new SResourceAttribute;
        $s1->addStatement(new SqlStatementBi($s1->columns['attribute'], $row['attribute_id'], '%s=%s'));
        $s1->setColumnsMask(array('resource','attribute'));
        $res1 = $this->_app->db->doQuery($s1->toString());
        $s2 = new SEventAttribute;
        $s2->addStatement(new SqlStatementBi($s2->columns['attribute'], $row['attribute_id'], '%s=%s'));
        $s2->setColumnsMask(array('event','attribute'));
        $res2 = $this->_app->db->doQuery($s2->toString());
        if ($this->_app->db->getRowsNumber($res1)||$this->_app->db->getRowsNumber($res2)) {
          $s2 = new SAttributeName;
          $s2->addStatement(new SqlStatementBi($s2->columns['attribute'], $row['attribute_id'], '%s=%s'));
          $s2->setColumnsMask(array('lang','name'));
          $res2 = $this->_app->db->doQuery($s2->toString());
          $a = array();
          while ($row2 = $this->_app->db->fetchAssoc($res2)) {
            $a[$row2['lang']] = $row2['name'];
          }
          $name = ifsetor($a[$this->_app->language->getLanguage()], array_values($a)[0]);
          throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveCustomer_commodityAttributeUsed'), $name));
        }
        
        $o = new OAttribute($row['attribute_id']);
        $o->delete();
      }
      
      if (is_array($params['commodityAttribute'])) {
        // kontrola na unikatnost oznaceni
        $tmp = array();
        foreach ($params['commodityAttribute'] as $index=>$attribute) {
          if ($attribute['disabled']=='Y') continue;
          
          foreach ($attribute['name'] as $lang=>$name) {
            $key = sprintf('%s-%s', $lang, $name);
            if (isset($tmp[$key])) throw new ExceptionUserTextStorage('error.saveCustomer_attributeNotUnique');
            else $tmp[$key] = 1;
          }
        }
        
        foreach ($params['commodityAttribute'] as $attribute) {
          $o = new OAttribute($attribute['attributeId']?$attribute['attributeId']:null);
          
          if ($attribute['attributeId']) {
            $s = new SAttributeName;
            $s->addStatement(new SqlStatementBi($s->columns['attribute'], $attribute['attributeId'], '%s=%s'));
            $s->setColumnsMask(array('attributename_id'));
            $res = $this->_app->db->doQuery($s->toString());
            while ($row = $this->_app->db->fetchAssoc($res)) {
              $oo = new OAttributeName($row['attributename_id']);
              $oo->delete();
            }
          }
      
          $oData = array();
          if (isset($attribute['short'])) $oData['short_name'] = $attribute['short'];
          if (isset($attribute['restricted'])) $oData['restricted'] = $attribute['restricted']?$attribute['restricted']:NULL;
          if (isset($attribute['category'])) $oData['category'] = $attribute['category'];
          if (isset($attribute['sequence'])) $oData['sequence'] = $attribute['sequence'];
          if (isset($attribute['disabled'])) $oData['disabled'] = $attribute['disabled']?$attribute['disabled']:'N';
          if (isset($attribute['type'])) {
            $oData['type'] = $attribute['type'];
            if (strcmp($attribute['type'],'LIST')) $attribute['allowedValues'] = '';
          }
          if (isset($attribute['allowedValues'])) $oData['allowed_values'] = preg_replace('/\s*,\s*/', ',', str_replace(array("\n","\t","\r"), '', $attribute['allowedValues']));
          if (isset($attribute['url'])) {
            if ($attribute['url']&&strpos($attribute['url'],'http')!==0) $attribute['url'] = 'http://'.$attribute['url'];
            $oData['url'] = $attribute['url'];
          }
          
          if (!$o->getId()) $oData['provider'] = $this->_data['providerId'];
          $oData['mandatory'] = null;
          $oData['applicable'] = 'COMMODITY';
          $o->setData($oData);
          $o->save();
          
          foreach ($attribute['name'] as $lang=>$name) {
            $oo = new OAttributeName;
            $oo->setData(array('lang'=>$lang,'name'=>$name,'attribute'=>$o->getId()));
            $oo->save();
          }
        }
      }
    }
  }
  
  private function _saveReservationAttribute($params) {
    if (isset($params['reservationAttribute'])&&isset($this->_data['providerId'])&&$this->_data['providerId']) {
      $idToSave = array();
      if (is_array($params['reservationAttribute'])) {
        foreach ($params['reservationAttribute'] as $key=>$attribute) {
          // to prijde pryc, az pujde v GUI zadat oznaceni v ruznych jazycich
          $params['reservationAttribute'][$key]['name'] = array('en'=>$attribute['name']);
          
          if (isset($attribute['attributeId'])&&$attribute['attributeId']) $idToSave[] = $attribute['attributeId'];
        }
      }
      
      $s = new SAttribute;
      $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_data['providerId'], '%s=%s'));
      $s->addStatement(new SqlStatementMono($s->columns['applicable'], "%s='RESERVATION'"));
      if (count($idToSave)) $s->addStatement(new SqlStatementMono($s->columns['attribute_id'], sprintf('%%s NOT IN (%s)', implode(',',$idToSave))));
      $s->setColumnsMask(array('attribute_id'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        // kdyz je atribut uz prirazen u rezervace, tak nejde smazat
        $s1 = new SReservationAttribute;
        $s1->addStatement(new SqlStatementBi($s1->columns['attribute'], $row['attribute_id'], '%s=%s'));
        $s1->setColumnsMask(array('reservation','attribute'));
        $res1 = $this->_app->db->doQuery($s1->toString());
        if ($this->_app->db->getRowsNumber($res1)) {
          $s2 = new SAttributeName;
          $s2->addStatement(new SqlStatementBi($s2->columns['attribute'], $row['attribute_id'], '%s=%s'));
          $s2->setColumnsMask(array('lang','name'));
          $res2 = $this->_app->db->doQuery($s2->toString());
          $a = array();
          while ($row2 = $this->_app->db->fetchAssoc($res2)) {
            $a[$row2['lang']] = $row2['name'];
          }
          $name = ifsetor($a[$this->_app->language->getLanguage()], array_values($a)[0]);
          throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveCustomer_reservationAttributeUsed'), $name));
        }
        
        // pokud jou rezervacni atributy pouze prirazeny akcim/zdroju, tak to smazat pujde
        $s2 = new SEventAttribute;
        $s2->addStatement(new SqlStatementBi($s2->columns['attribute'], $row['attribute_id'], '%s=%s'));
        $s2->setColumnsMask(array('event'));
        $res2 = $this->_app->db->doQuery($s2->toString());
        while ($row2 = $this->_app->db->fetchAssoc($res2)) {
          $o = new OEventAttribute(array('event'=>$row2['event'],'attribute'=>$row['attribute_id']));
          $o->delete();
        }
        $s2 = new SResourceAttribute;
        $s2->addStatement(new SqlStatementBi($s2->columns['attribute'], $row['attribute_id'], '%s=%s'));
        $s2->setColumnsMask(array('resource'));
        $res2 = $this->_app->db->doQuery($s2->toString());
        while ($row2 = $this->_app->db->fetchAssoc($res2)) {
          $o = new OResourceAttribute(array('resource'=>$row2['resource'],'attribute'=>$row['attribute_id']));
          $o->delete();
        }
        
        $o = new OAttribute($row['attribute_id']);
        $o->delete();
      }
      
      if (is_array($params['reservationAttribute'])) {
        // kontrola na unikatnost oznaceni
        $tmp = array();
        foreach ($params['reservationAttribute'] as $index=>$attribute) {
          if ($attribute['disabled']=='Y') continue;
          
          foreach ($attribute['name'] as $lang=>$name) {
            $key = sprintf('%s-%s', $lang, $name);
            if (isset($tmp[$key])) throw new ExceptionUserTextStorage('error.saveCustomer_attributeNotUnique');
            else $tmp[$key] = 1;
          }
        }
        
        foreach ($params['reservationAttribute'] as $attribute) {
          $o = new OAttribute($attribute['attributeId']?$attribute['attributeId']:null);
          
          if ($attribute['attributeId']) {
            $s = new SAttributeName;
            $s->addStatement(new SqlStatementBi($s->columns['attribute'], $attribute['attributeId'], '%s=%s'));
            $s->setColumnsMask(array('attributename_id'));
            $res = $this->_app->db->doQuery($s->toString());
            while ($row = $this->_app->db->fetchAssoc($res)) {
              $oo = new OAttributeName($row['attributename_id']);
              $oo->delete();
            }
          }
      
          $oData = array();
          if (isset($attribute['short'])) $oData['short_name'] = $attribute['short'];
          if (isset($attribute['restricted'])) $oData['restricted'] = $attribute['restricted']?$attribute['restricted']:NULL;
          if (isset($attribute['mandatory'])) $oData['mandatory'] = $attribute['mandatory'];
          if (isset($attribute['category'])) $oData['category'] = $attribute['category'];
          if (isset($attribute['sequence'])) $oData['sequence'] = $attribute['sequence'];
          if (isset($attribute['disabled'])) $oData['disabled'] = $attribute['disabled']?$attribute['disabled']:'N';
          if (isset($attribute['type'])) {
            $oData['type'] = $attribute['type'];
            if (strcmp($attribute['type'],'LIST')) $attribute['allowedValues'] = '';
          }
          if (isset($attribute['allowedValues'])) $oData['allowed_values'] = preg_replace('/\s*,\s*/', ',', str_replace(array("\n","\t","\r"), '', $attribute['allowedValues']));
          if (isset($attribute['url'])) {
            if ($attribute['url']&&strpos($attribute['url'],'http')!==0) $attribute['url'] = 'http://'.$attribute['url'];
            $oData['url'] = $attribute['url'];
          }
          
          if (!$o->getId()) $oData['provider'] = $this->_data['providerId'];
          $oData['applicable'] = 'RESERVATION';
          $o->setData($oData);
          $o->save();
          
          foreach ($attribute['name'] as $lang=>$name) {
            $oo = new OAttributeName;
            $oo->setData(array('lang'=>$lang,'name'=>$name,'attribute'=>$o->getId()));
            $oo->save();
          }
        }
      }
    }
  }
  
  private function _saveFile($params) {
    if (isset($params['file'])&&isset($this->_data['providerId'])&&$this->_data['providerId']) {
      $idToSave = array();
      
      foreach ($params['file'] as $file) {
        $oData = array(
                'provider'            => $this->_data['providerId'],
                'short_name'          => $file['short'],
                'name'                => $file['name'],
            );
        
        // ulozim soubor
        if (isset($file['newSource'])&&$file['newSource']) {
          $bF = new BFile(isset($file['sourceId'])&&$file['sourceId']?$file['sourceId']:null);
          $fileParams = $bF->getFileFromLink($file['sourceLink'], true);
          $fileId = $bF->save($fileParams);
          
          $oData['file'] = $fileId;
        }
        
        $o = new OProviderFile(isset($file['id'])&&$file['id']?$file['id']:null);
        $o->setData($oData);
        $o->save();
        
        $idToSave[] = $o->getId();
      }
      
      $s = new SProviderFile;
      $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_data['providerId'], '%s=%s'));
      if (count($idToSave)) $s->addStatement(new SqlStatementMono($s->columns['providerfile_id'], sprintf('%%s NOT IN (%s)', implode(',',$idToSave))));
      $s->setColumnsMask(array('providerfile_id','file'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $o = new OProviderFile($row['providerfile_id']);
        $o->delete();
        
        if ($row['file']) {
          $bF = new BFile($row['file']);
          $bF->delete();
        }
      }
    }
  }

  private function _saveSettings($params) {
    if (isset($this->_data['providerId'])&&$this->_data['providerId']) {
      $oData = array();
      if (isset($params['providerParams']['smtpHost'])) $oData['smtp_host'] = $params['providerParams']['smtpHost'];
      if (isset($params['providerParams']['smtpPort'])) $oData['smtp_port'] = $params['providerParams']['smtpPort'];
      if (isset($params['providerParams']['smtpUser'])) $oData['smtp_user'] = $params['providerParams']['smtpUser'];
      if (isset($params['providerParams']['smtpPassword'])) $oData['smtp_password'] = $params['providerParams']['smtpPassword'];
      if (isset($params['providerParams']['smtpSecure'])) $oData['smtp_secure'] = $params['providerParams']['smtpSecure'];
			if (isset($params['invoice']['accountFrom'])) $oData['invoice_account_from'] = $params['invoice']['accountFrom']!==''?$params['invoice']['accountFrom']:null;
      if (isset($params['invoice']['monthFee'])) $oData['invoice_month_fee'] = $params['invoice']['monthFee']!==''?$params['invoice']['monthFee']:null;
      if (isset($params['invoice']['reservationFee'])) $oData['invoice_reservation_fee'] = $params['invoice']['reservationFee']!==''?$params['invoice']['reservationFee']:null;
      if (isset($params['invoice']['reservationPricePaid'])) $oData['invoice_reservation_price_paid'] = $params['invoice']['reservationPricePaid'];
      if (isset($params['invoice']['reservationPriceFee'])) $oData['invoice_reservation_price_fee'] = $params['invoice']['reservationPriceFee']!==''?$params['invoice']['reservationPriceFee']:null;
      if (isset($params['invoice']['dueLength'])) $oData['invoice_due_length'] = $params['invoice']['dueLength']!==''?$params['invoice']['dueLength']:null;
      if (isset($params['invoice']['email'])) $oData['invoice_email'] = $params['invoice']['email'];

      if (count($oData)) {
        $o = new OProviderSettings(array('provider'=>$this->_data['providerId']));
        $o->setData($oData);
        $o->save();
      }
    }
  }

  public function saveInvoiceSettings($params) {
    global $NOTIFICATION;
    if (!in_array($this->_app->auth->getUsername(),$NOTIFICATION['adminEmail'])) throw new ExceptionUserTextStorage('error.accessDenied');

    $this->_load();

    $this->_app->db->beginTransaction();

    $this->_saveSettings($params);

    $this->_app->db->commitTransaction();
  }

  private function _save($params) {
    $this->_load();
    
    $this->_app->db->beginTransaction();

    $o = new OCustomer($this->_id?$this->_id:null);
    $oData = array();
    if (isset($params['name'])) $oData['name'] = $params['name'];
    if (isset($params['ic'])) $oData['ic'] = $params['ic'];
    if (isset($params['dic'])) $oData['dic'] = $params['dic'];
    if (isset($params['email'])) $oData['email'] = $params['email'];
    if (isset($params['phone'])) $oData['phone'] = $params['phone'];
    if (count($oData)) {
      $o->setData($oData);
      $o->save();
    }
    $this->_id = $o->getId();
    
    $this->_saveAddress($params);
    $this->_saveProvider($params);
    $this->_saveRegistration($params);
    $this->_saveEmployee($params);
    $this->_saveCoworker($params);
    $this->_saveUserAttribute($params);
    $this->_saveCommodityAttribute($params);
    $this->_saveReservationAttribute($params);
    $this->_saveFile($params);
    $this->_saveSettings($params);
    
    $this->_app->db->commitTransaction();
  }

  public function save($params) {
    if (!$this->_checkAccess($params)) throw new ExceptionUserTextStorage('error.accessDenied');
    
    $this->_checkBeforeSave($params);
    
    $this->_save($params);

    $this->_loaded = false;
    
    return $this->_id;
  }
  
  private function _delete() {
    $this->_checkBeforeDelete();
    
    $this->_app->db->beginTransaction();
    
    $o = new OCustomer($this->_id);
    $o->delete();
    
    $this->_app->db->commitTransaction();
  }
  
  public function delete() {
    if (!$this->_checkAccess()) throw new ExceptionUserTextStorage('error.accessDenied');
    
    $this->_load();
    
    $ret = $this->_data['name'];
  
    $this->_delete();
    
    return $ret;
  }
  
  public function changeCredit($provider, $change, $type='CASH', $description=null) {
    if (!$this->_checkCreditAccess($provider,$type,$change)) throw new ExceptionUserTextStorage('error.accessDenied');
    
    $s = new SCustomerRegistration;
    $s->addStatement(new SqlStatementBi($s->columns['customer'], $this->_id, '%s=%s'));
    $s->addStatement(new SqlStatementBi($s->columns['provider'], $provider, '%s=%s'));
    $s->setColumnsMask(array('customerregistration_id','credit'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($row = $this->_app->db->fetchAssoc($res)) {
      if ($row['credit']+$change<0) throw new ExceptionUserTextStorage('error.editCustomerCredit_notEnoughResources');
      
      $this->_app->db->beginTransaction();
      
      $o = new OCustomerRegistration($row['customerregistration_id']);
      $o->setData(array('credit'=>$row['credit']+$change));
      $o->save();
      $crId = $o->getId();
      
      $o = new OCreditJournal;
      $o->setData(array(
        'provider'                => $provider,
        'customerregistration'    => $crId,
        'amount'                  => $change,
        'description'             => $description,
        'change_timestamp'        => date('Y-m-d H:i:s'),
        'change_user'             => $this->_app->auth->getUserId(),
        'flag'                    => $change>0?'C':'D',
        'type'                    => $type,
        ));
      $o->save();
    
      $this->_app->db->commitTransaction();
    } else {
      throw new ExceptionUserTextStorage('error.editCustomerCredit_notEnoughResources');
    }
  }

  public static function getProviderPaymentGateway($provider, $price=null) {
		$app = Application::get();

		$gateway = array();

		global $PAYMENT_GATEWAY;
		$s = new SProviderPaymentGateway;
		$s->addStatement(new SqlStatementBi($s->columns['provider'], $provider, '%s=%s'));
		$s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
		$s->setColumnsMask(array('providerpaymentgateway_id','gateway_name'));
		$res = $app->db->doQuery($s->toString());
		while ($row = $app->db->fetchAssoc($res)) {
			// platebni brana muze mit definovanou minimalni castku, ktere lze zaplatit
			if ($price&&isset($PAYMENT_GATEWAY['source'][$row['gateway_name']]['minimalAmount'])&&$PAYMENT_GATEWAY['source'][$row['gateway_name']]['minimalAmount']>$price) continue;

			$gw = array(
				'name'					=> $row['gateway_name'],
				'minimalAmount' => ifsetor($PAYMENT_GATEWAY['source'][$row['gateway_name']]['minimalAmount'])
			);

			$gateway[] = $gw;
		}

		return $gateway;
	}
  
  public static function getProviderSettings($provider, $key=null) {
    $app = Application::get();
    
    $ret = array('userConfirm'=>'Y','userSubaccount'=>'N','badgePhoto'=>null,'badgeTemplate'=>'','ticketTemplate'=>'',
      'generateAccounting'=>'N',
      'prepaymentInvoiceTemplate'=>'','prepaymentInvoiceNumber'=>'','receiptTemplate'=>'','receiptNumber'=>'',
      'invoiceTemplate'=>'','invoiceNumber'=>'','creditnoteTemplate'=>'','creditnoteNumber'=>'',
      'showCompany'=>'N','reservationCancelMessage'=>'',
      'allowSkipReservationCondition'=>'N','userReservationCondition'=>null,'userUnique'=>array(),'subaccountUnique'=>array(),
      'allowMandatoryReservation'=>'N','organiserMandatoryReservation'=>'N','organiserMandatorySubstitute'=>'N',
			'disableCredit'=>'N','disableTicket'=>'N','disableOnline'=>'N');
    
    $s = new SProviderSettings;
    $s->addStatement(new SqlStatementBi($s->columns['provider'], $provider, '%s=%s'));
    $s->setColumnsMask(array('userregistration_validate','allow_user_subaccount','badge_photo','badge_template','ticket_template',
      'generate_accounting','vat_rate',
      'prepayment_invoice_template','prepayment_invoice_number','receipt_template','receipt_number',
      'invoice_template','invoice_number','creditnote_template','creditnote_number',
      'show_company','reservation_cancel_message',
      'allow_skip_reservation_condition','user_reservationcondition','documenttemplate',
			'user_unique','user_subaccount_unique',
      'allow_mandatory_reservation','organiser_mandatory_reservation','organiser_mandatory_substitute',
			'disable_credit','disable_ticket','disable_cash','disable_online'));
    $res = $app->db->doQuery($s->toString());
    if ($row = $app->db->fetchAssoc($res)) {
      $ret['userConfirm'] = $row['userregistration_validate'];
      $ret['userSubaccount'] = $row['allow_user_subaccount'];
      $ret['badgePhoto'] = $row['badge_photo'];
      $ret['badgeTemplate'] = $row['badge_template'];
      $ret['ticketTemplate'] = $row['ticket_template'];
      $ret['generateAccounting'] = $row['generate_accounting'];
      $ret['prepaymentInvoiceTemplate'] = $row['prepayment_invoice_template'];
      $ret['prepaymentInvoiceNumber'] = $row['prepayment_invoice_number'];
      $ret['receiptTemplate'] = $row['receipt_template'];
      $ret['receiptNumber'] = $row['receipt_number'];
      $ret['invoiceTemplate'] = $row['invoice_template'];
      $ret['invoiceNumber'] = $row['invoice_number'];
      $ret['creditnoteTemplate'] = $row['creditnote_template'];
      $ret['creditnoteNumber'] = $row['creditnote_number'];
      $ret['showCompany'] = $row['show_company'];
      $ret['reservationCancelMessage'] = $row['reservation_cancel_message'];
      $ret['allowSkipReservationCondition'] = $row['allow_skip_reservation_condition'];
      $ret['userReservationCondition'] = $row['user_reservationcondition'];
			$ret['documenttemplate'] = $row['documenttemplate'];
			$ret['userUnique'] = $row['user_unique']?explode(',',$row['user_unique']):array();
			$ret['subaccountUnique'] = $row['user_subaccount_unique']?explode(',',$row['user_subaccount_unique']):array();
      $ret['allowMandatoryReservation'] = $row['allow_mandatory_reservation'];
      $ret['organiserMandatoryReservation'] = $row['organiser_mandatory_reservation'];
      $ret['organiserMandatorySubstitute'] = $row['organiser_mandatory_substitute'];
			$ret['disableCredit'] = $row['disable_credit'];
			$ret['disableTicket'] = $row['disable_ticket'];
			$ret['disableCash'] = $row['disable_cash'];
			$ret['disableOnline'] = $row['disable_online'];
    }

    if ($key&&in_array($key,array_keys($ret))) return $ret[$key];
    else return $ret;
  }
  
  public static function saveProviderSettings($provider, $params) {
  	if (isset($params['generateAccounting'])&&($params['generateAccounting']=='Y')) {
  		if (!isset($params['prepaymentInvoiceNumber'])||!$params['prepaymentInvoiceNumber']) throw new ExceptionUserTextStorage('error.settingsGeneral_missingPrepaymentInvoiceNumber');
			if (!isset($params['receiptNumber'])||!$params['receiptNumber']) throw new ExceptionUserTextStorage('error.settingsGeneral_missingReceiptNumber');
			if (!isset($params['invoiceNumber'])||!$params['invoiceNumber']) throw new ExceptionUserTextStorage('error.settingsGeneral_missingInvoiceNumber');
			if (!isset($params['creditnoteNumber'])||!$params['creditnoteNumber']) throw new ExceptionUserTextStorage('error.settingsGeneral_missingCreditnoteNumber');
		}

  	$app = Application::get();

    $app->db->beginTransaction();
    
    $s = new SProviderSettings;
    $s->addStatement(new SqlStatementBi($s->columns['provider'], $provider, '%s=%s'));
    $s->setColumnsMask(array('provider'));
    $res = $app->db->doQuery($s->toString());
    if ($row = $app->db->fetchAssoc($res)) {
      $o = new OProviderSettings(array('provider'=>$provider));
      $oData = array();
    } else {
      $o = new OProviderSettings;
      $oData = array('provider'=>$provider);
    }
    
    if (isset($params['userConfirm'])) $oData['userregistration_validate'] = $params['userConfirm'];
    if (isset($params['userSubaccount'])) $oData['allow_user_subaccount'] = $params['userSubaccount'];
    if (isset($params['badgePhoto'])) $oData['badge_photo'] = $params['badgePhoto']?$params['badgePhoto']:null;
    if (isset($params['badgeTemplate'])) $oData['badge_template'] = $params['badgeTemplate'];
    if (isset($params['ticketTemplate'])) $oData['ticket_template'] = $params['ticketTemplate'];
    if (isset($params['generateAccounting'])) $oData['generate_accounting'] = $params['generateAccounting'];
    if (isset($params['prepaymentInvoiceTemplate'])) $oData['prepayment_invoice_template'] = $params['prepaymentInvoiceTemplate'];
    if (isset($params['prepaymentInvoiceNumber'])) $oData['prepayment_invoice_number'] = $params['prepaymentInvoiceNumber'];
    if (isset($params['receiptTemplate'])) $oData['receipt_template'] = $params['receiptTemplate'];
    if (isset($params['receiptNumber'])) $oData['receipt_number'] = $params['receiptNumber'];
    if (isset($params['invoiceTemplate'])) $oData['invoice_template'] = $params['invoiceTemplate'];
    if (isset($params['invoiceNumber'])) $oData['invoice_number'] = $params['invoiceNumber'];
    if (isset($params['creditnoteTemplate'])) $oData['creditnote_template'] = $params['creditnoteTemplate'];
    if (isset($params['creditnoteNumber'])) $oData['creditnote_number'] = $params['creditnoteNumber'];
    if (isset($params['showCompany'])) $oData['show_company'] = $params['showCompany'];
    if (isset($params['reservationCancelMessage'])) $oData['reservation_cancel_message'] = $params['reservationCancelMessage']?$params['reservationCancelMessage']:null;
    if (isset($params['allowSkipReservationCondition'])) $oData['allow_skip_reservation_condition'] = $params['allowSkipReservationCondition'];
    if (isset($params['userReservationCondition'])) $oData['user_reservationcondition'] = $params['userReservationCondition']?$params['userReservationCondition']:null;
		if (isset($params['documenttemplate'])) $oData['documenttemplate'] = $params['documenttemplate']?$params['documenttemplate']:null;
    if (isset($params['allowMandatoryReservation'])) $oData['allow_mandatory_reservation'] = $params['allowMandatoryReservation'];
    if (isset($params['organiserMandatoryReservation'])) $oData['organiser_mandatory_reservation'] = $params['organiserMandatoryReservation'];
    if (isset($params['organiserMandatorySubstitute'])) $oData['organiser_mandatory_substitute'] = $params['organiserMandatorySubstitute'];
		if (isset($params['disableCredit'])) $oData['disable_credit'] = $params['disableCredit'];
		if (isset($params['disableTicket'])) $oData['disable_ticket'] = $params['disableTicket'];
		if (isset($params['disableCash'])) $oData['disable_cash'] = $params['disableCash'];
		if (isset($params['disableOnline'])) $oData['disable_online'] = $params['disableOnline'];

		if (isset($params['userUnique'])&&is_array($params['userUnique'])) $oData['user_unique'] = implode(',', array_filter($params['userUnique']));
		if (isset($params['subaccountUnique'])&&is_array($params['subaccountUnique'])) $oData['user_subaccount_unique'] = implode(',', array_filter($params['subaccountUnique']));
    
    $o->setData($oData);
    $o->save();
    
    $app->db->commitTransaction();
  }

  private function _calculateMonthFee(& $invoiceParams) {
    // mesicni staly poplatek
    if ($this->_invoiceSettings['invoice_month_fee']>0) {
			// mesicni poplatek muze byt pomerne ponizen, pokud se neuctuje poskytovateli od zacatku mesice
			$monthFee = $this->_invoiceSettings['invoice_month_fee'];
			list($accountYear,$accountMonth,$accountDay) = explode('-', $this->_invoiceSettings['dateFrom']);
			if (strcmp($accountDay,'01')) {
				$daysInMonth = date('t',strtotime($this->_invoiceSettings['dateFrom']));
				$discountRatio = 1 - (($accountDay - 1) / $daysInMonth);
				$monthFee = round($monthFee*$discountRatio);
			}

      $invoiceParams['totalAmount'] += $monthFee;
      $invoiceParams['item']['monthFee'] = array(
        'label' => sprintf($this->_invoiceSettings['itemLabel_monthFee'],
          $this->_app->regionalSettings->convertDateToHuman($this->_invoiceSettings['dateFrom']), $this->_app->regionalSettings->convertDateToHuman($this->_invoiceSettings['dateTo'])),
        'count' => 1,
        'price' => $monthFee,
      );
    }
  }

  private function _calculateReservationFee(& $invoiceParams) {
    // poplatek za vytvorene rezervace
    if ($this->_invoiceSettings['invoice_reservation_fee']>0) {
      $s1 = new SReservation;
      $s1->addStatement(new SqlStatementBi($s1->columns['provider'], $this->_data['providerId'], '%s=%s'));
      #$s1->addStatement(new SqlStatementMono($s1->columns['cancelled'], '%s IS NULL'));
      $s1->addStatement(new SqlStatementTri($s1->columns['created'], $this->_invoiceSettings['dateTimeFrom'], $this->_invoiceSettings['dateTimeTo'], '%s BETWEEN %s AND %s'));
      $s1->setColumnsMask(array('reservation_id'));
      #echo $s1->toString();
      $res1 = $this->_app->db->doQuery($s1->toString());
      if ($count = $this->_app->db->getRowsNumber($res1)) {
        $invoiceParams['totalAmount'] += $this->_invoiceSettings['invoice_reservation_fee']*$count;

        $invoiceParams['item']['reservationFee'] = array(
          'label' => sprintf($this->_invoiceSettings['itemLabel_reservationFee'],
            $this->_app->regionalSettings->convertDateToHuman($this->_invoiceSettings['dateFrom']), $this->_app->regionalSettings->convertDateToHuman($this->_invoiceSettings['dateTo'])),
          'count' => $count,
          'price' => $this->_invoiceSettings['invoice_reservation_fee'],
        );
      }
    }
  }

  private function _calculatePaidReservationPriceFee(& $invoiceParams) {
    // poplatek za zaplacene rezervace
    if ($this->_invoiceSettings['invoice_reservation_price_fee']>0) {
      $reservationFee = $this->_invoiceSettings['invoice_reservation_price_fee']/100;

      $s1 = new SReservation;
      $s1->addStatement(new SqlStatementBi($s1->columns['provider'], $this->_data['providerId'], '%s=%s'));
      $s1->addStatement(new SqlStatementTri($s1->columns['payed'], $this->_invoiceSettings['dateTimeFrom'], $this->_invoiceSettings['dateTimeTo'], '%s BETWEEN %s AND %s'));
      $s1->addColumn(new SqlColumn(false, new SqlStatementMono($s1->columns['total_price'],'SUM(%s)'), 'sum_price', true));
      $s1->setColumnsMask(array('sum_price'));
      #echo $s1->toString();
      $res1 = $this->_app->db->doQuery($s1->toString());
      $row1 = $this->_app->db->fetchAssoc($res1);
      if ($row1['sum_price']>0) {
        $invoiceParams['totalAmount'] += $row1['sum_price']*$reservationFee;

        $invoiceParams['item']['reservationPriceFee'] = array(
          'label' => sprintf($this->_invoiceSettings['itemLabel_paidReservationPriceFee'],
            $this->_app->regionalSettings->convertDateToHuman($this->_invoiceSettings['dateFrom']), $this->_app->regionalSettings->convertDateToHuman($this->_invoiceSettings['dateTo'])),
          'count' => $row1['sum_price'],
          'price' => $reservationFee,
        );
      }
      // musim odecist refundovane v danem obdobi
      $s1 = new SReservationJournal;
      $s1->addStatement(new SqlStatementBi($s1->columns['provider'], $this->_data['providerId'], '%s=%s'));
      $s1->addStatement(new SqlStatementMono($s1->columns['action'], "%s='REFUND'"));
      $s1->addStatement(new SqlStatementTri($s1->columns['change_timestamp'], $this->_invoiceSettings['dateTimeFrom'], $this->_invoiceSettings['dateTimeTo'], '%s BETWEEN %s AND %s'));
      $s1->setColumnsMask(array('reservation','note_2'));
      #echo $s1->toString();
      $res1 = $this->_app->db->doQuery($s1->toString());
      while ($row1 = $this->_app->db->fetchAssoc($res1)) {
        if ($row1['note_2']) {//&&isset($invoiceParams['item']['reservationPriceFee'])) {
          $invoiceParams['totalAmount'] -= $reservationFee*$row1['note_2'];

          if (!isset($invoiceParams['item']['reservationPriceFeeBack'])) {
            $invoiceParams['item']['reservationPriceFeeBack'] = array(
              'label' => sprintf($this->_invoiceSettings['itemLabel_paidReservationPriceFeeBack'],
                $this->_app->regionalSettings->convertDateToHuman($this->_invoiceSettings['dateFrom']), $this->_app->regionalSettings->convertDateToHuman($this->_invoiceSettings['dateTo'])),
              'count' => $row1['note_2'],
              'price' => -$reservationFee,
            );
          } else {
            $invoiceParams['item']['reservationPriceFeeBack']['count'] += $row1['note_2'];
          }
        }
      }
    }
  }

  private function _calculateRealisedReservationPriceFee(& $invoiceParams) {
    // poplatek za realizovane rezervace
    if ($this->_invoiceSettings['invoice_reservation_price_fee']>0) {
      $reservationFee = $this->_invoiceSettings['invoice_reservation_price_fee'] / 100;

      $s1 = new SReservation;
      $s1->addStatement(new SqlStatementBi($s1->columns['provider'], $this->_data['providerId'], '%s=%s'));
      $s1->addStatement(new SqlStatementMono($s1->columns['cancelled'], '%s IS NULL'));
      $s1->addStatement(new SqlStatementTri($s1->columns['start'], $this->_invoiceSettings['dateTimeFrom'], $this->_invoiceSettings['dateTimeTo'], '%s BETWEEN %s AND %s'));
      $s1->addColumn(new SqlColumn(false, new SqlStatementMono($s1->columns['total_price'], 'SUM(%s)'), 'sum_price', true));
      $s1->setColumnsMask(array('sum_price'));
      #echo $s1->toString();
      $res1 = $this->_app->db->doQuery($s1->toString());
      $row1 = $this->_app->db->fetchAssoc($res1);
      if ($row1['sum_price'] > 0) {
        $invoiceParams['totalAmount'] += $row1['sum_price'] * $reservationFee;

        $invoiceParams['item']['reservationPriceFee'] = array(
          'label' => sprintf($this->_invoiceSettings['itemLabel_realisedReservationPriceFee'],
            $this->_app->regionalSettings->convertDateToHuman($this->_invoiceSettings['dateFrom']), $this->_app->regionalSettings->convertDateToHuman($this->_invoiceSettings['dateTo'])),
          'count' => $row1['sum_price'],
          'price' => $reservationFee,
        );
      }
      // musim odecist starsi stornovane v danem obdobi
      $s1 = new SReservation;
      $s1->addStatement(new SqlStatementBi($s1->columns['provider'], $this->_data['providerId'], '%s=%s'));
      $s1->addStatement(new SqlStatementBi($s1->columns['start'], $this->_invoiceSettings['dateTimeFrom'], '%s<%s'));
      $s1->addStatement(new SqlStatementTri($s1->columns['cancelled'], $this->_invoiceSettings['dateTimeFrom'], $this->_invoiceSettings['dateTimeTo'], '%s BETWEEN %s AND %s'));
      $s1->addColumn(new SqlColumn(false, new SqlStatementMono($s1->columns['total_price'], 'SUM(%s)'), 'sum_price', true));
      $s1->setColumnsMask(array('sum_price'));
      #error_log($s1->toString());
      $res1 = $this->_app->db->doQuery($s1->toString());
      $row1 = $this->_app->db->fetchAssoc($res1);
      if ($row1['sum_price']) {//&&isset($invoiceParams['item']['reservationPriceFee'])) {
        $invoiceParams['totalAmount'] -= $row1['sum_price'] * $reservationFee;

        $invoiceParams['item']['reservationPriceFeeBack'] = array(
          'label' => sprintf($this->_invoiceSettings['itemLabel_realisedReservationPriceFeeBack'],
            $this->_app->regionalSettings->convertDateToHuman($this->_invoiceSettings['dateFrom']), $this->_app->regionalSettings->convertDateToHuman($this->_invoiceSettings['dateTo'])),
          'count' => $row1['sum_price'],
          'price' => -$reservationFee,
        );
      }
    }
  }

  private function _addOverPayment(& $params) {
    if ($params['totalAmount']>0) {
      $s = new SProviderInvoice;
      $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_data['providerId'], '%s=%s'));
      $s->addStatement(new SqlStatementBi($s->columns['vs'], $params['vs'], '%s<>%s'));
      $s->addStatement(new SqlStatementMono($s->columns['total_amount'], '%s<0'));
      $s->addStatement(new SqlStatementMono($s->columns['accounted'], '%s IS NULL'));
      $s->setColumnsMask(array('providerinvoice_id', 'vs', 'total_amount'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $params['totalAmount'] += $row['total_amount'];

        if (!isset($params['overPayment'])) $params['overPayment'] = array();
        $params['overPayment'][] = $row['providerinvoice_id'];

        $params['item'][] = array(
          'label' => sprintf($this->_invoiceSettings['itemLabel_overPayment'], $row['vs']),
          'count' => 1,
          'price' => $row['total_amount'],
        );
      }
    }
  }

  private function _prepareInvoice($period, $createDate=null) {
    global $INVOICE;
    $this->_invoiceSettings = $INVOICE;

    $invoiceParams = null;
    if ($this->_data['providerId']&&$this->_data['invoice']['accountFrom']&&($this->_data['invoice']['monthFee']||$this->_data['invoice']['reservationFee']||$this->_data['invoice']['reservationPriceFee'])) {
      $this->_invoiceSettings['dateFrom'] = $period.'-01';
			$this->_invoiceSettings['dateTo'] = $this->_app->regionalSettings->decreaseDate($this->_app->regionalSettings->increaseDate($this->_invoiceSettings['dateFrom'],0, 1));
			// kdyz se jeste nema poskytovateli nic uctovat, preskocim ho
			if ($this->_data['invoice']['accountFrom']<$this->_invoiceSettings['dateTo']) {
				if ($this->_data['invoice']['accountFrom']>$this->_invoiceSettings['dateFrom']) $this->_invoiceSettings['dateFrom'] = $this->_data['invoice']['accountFrom'];
				$this->_invoiceSettings['dateTimeFrom'] = $this->_invoiceSettings['dateFrom'] . ' 00:00:00';
				$this->_invoiceSettings['dateTimeTo'] = $this->_invoiceSettings['dateTo'] . ' 23:59:59';
				$this->_invoiceSettings['invoice_month_fee'] = $this->_data['invoice']['monthFee'];
				$this->_invoiceSettings['invoice_reservation_fee'] = $this->_data['invoice']['reservationFee'];
				$this->_invoiceSettings['invoice_reservation_price_fee'] = $this->_data['invoice']['reservationPriceFee'];

				$createDate = $createDate?$createDate:date('Y-m-d');
				$accountDate = $this->_invoiceSettings['dateTo'];

				$invoiceParams = array(
					'provider' => $this->_data['providerId'],
					'period' => $period,
					'createDate' => $createDate,
					'accountDate' => $accountDate,
					'dueDate' => $this->_app->regionalSettings->increaseDate($createDate, $this->_data['invoice']['dueLength'] ? $this->_data['invoice']['dueLength'] : $this->_invoiceSettings['dueLength']),
					'numberPrefix' => $this->_invoiceSettings['number_prefix'],
					'vs' => sprintf('%s%04d', str_replace('-', '', $period), $this->_data['providerId']),
					'item' => array(),
					'totalAmount' => 0,
				);

				$this->_calculateMonthFee($invoiceParams);
				$this->_calculateReservationFee($invoiceParams);
				if ($this->_data['invoice']['reservationPricePaid']=='Y') $this->_calculatePaidReservationPriceFee($invoiceParams);
				else $this->_calculateRealisedReservationPriceFee($invoiceParams);
				$this->_addOverPayment($invoiceParams);
			}
    }

    return $invoiceParams;
  }

  private function _generateInvoicePdf($params) {
    $s = new SProvider;
    $s->addStatement(new SqlStatementBi($s->columns['provider_id'], $this->_data['providerId'], '%s=%s'));
    $s->setColumnsMask(array('name','ic','dic','invoice_name','invoice_street','invoice_city','invoice_postal_code','invoice_state'));
    $res = $this->_app->db->doQuery($s->toString());
    $row = $this->_app->db->fetchAssoc($res);

    $params['ic'] = $row['ic'];
    $params['dic'] = $row['dic'];
    $params['name'] = $row['invoice_name']?$row['invoice_name']:$row['name'];
    $params['street'] = $row['invoice_street'];
    $params['city'] = $row['invoice_city'];
    $params['postalCode'] = $row['invoice_postal_code'];
    $params['state'] = strtoupper($row['invoice_state']);

    global $TMP_DIR;
    global $INVOICE;

    $fileName = sprintf('%s/%s.pdf', $TMP_DIR, $params['vs']);
    $gui = new GuiCustomerInvoice($params);

    #file_put_contents($fileName, $gui->render());
    include_once($INVOICE['pdf_creator']);
    $mpdf = new mPDF;
    $mpdf->WriteHTML($gui->render());
    $mpdf->Output($fileName, 'F');

    return $fileName;
  }

  private function _saveInvoicePdf($params) {
    $file = $this->_generateInvoicePdf($params);

    $b = new BFile;
    $id = $b->save(array('file'=>basename($file),'name'=>basename($file)));

    return $id;
  }

  private function _getInvoiceNumber($params) {
    if ($params['totalAmount']<=0) $ret =  '-';
    else {
      $s = new SProviderInvoice;
      $s->addStatement(new SqlStatementBi($s->columns['account_date'], $params['accountDate'], '%s=%s'));
      $s->setColumnsMask(array('providerinvoice_id'));
      $res = $this->_app->db->doQuery($s->toString());
      $count = $this->_app->db->getRowsNumber($res);

      $ret = sprintf('%s%s%s%04d', $params['numberPrefix'], substr($params['accountDate'],0,4), substr($params['accountDate'],5,2), $count+1);
    }

    return $ret;
  }

  private function _markOverPayment($params) {
    if (isset($params['overPayment'])) {
      foreach ($params['overPayment'] as $payment) {
        $o = new OProviderInvoice($payment);
        $o->setData(array('accounted'=>date('Y-m-d H:i:s')));
        $o->save();
      }
    }
  }

  public function createInvoice($period, $createDate=null) {
    $this->_load();

    $params = $this->_prepareInvoice($period, $createDate);

    if (!in_array($this->_app->auth->getUsername(),$this->_invoiceSettings['admin'])) throw new ExceptionUserTextStorage('error.accessDenied');

    // kdyz nejsou polozky faktury, neni co fakturovat
    if (!isset($params['item'])||!count($params['item'])) return null;

    $s = new SProviderInvoice;
    $s->addStatement(new SqlStatementBi($s->columns['vs'], $params['vs'], '%s=%s'));
    $s->setColumnsMask(array('number'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($row = $this->_app->db->fetchAssoc($res)) {
      throw new ExceptionUser(sprintf("Invoice with VS '%s' already exists.", $params['vs']));
    }

    $isInvoice = $params['totalAmount']>0;
    $params['number'] = $this->_getInvoiceNumber($params);

    $this->_app->db->beginTransaction();

    $file = $this->_saveInvoicePdf($params);

    $o = new OProviderInvoice;
    $oData = array(
      'provider'        => $this->_data['providerId'],
      'number'          => $params['number'],
      'create_date'     => $params['createDate'],
      'account_date'    => $isInvoice?$params['accountDate']:null,
      'due_date'        => $isInvoice?$params['dueDate']:null,
      'vs'              => $params['vs'],
      'total_amount'    => $params['totalAmount'],
      'file'            => $file,
      'created'         => date('Y-m-d H:i:s'),
    );
    $o->setData($oData);
    $o->save();

    $this->_markOverPayment($params);

    $this->_app->db->commitTransaction();

    return $params['number'];
  }

	public static function generateAccountingDocumentNumber($providerId, $documentType) {
  	$app = Application::get();
		$ret = array('counter'=>null,'year'=>null,'number'=>null);

		$query = sprintf('SELECT 
        providersettings.generate_accounting AS settings_generate_accounting, 
        providersettings.%s_number AS settings_number, 
        provider.document_year, provider.%s_counter AS settings_counter 
      FROM provider LEFT JOIN providersettings ON provider_id=provider WHERE provider_id=%s FOR UPDATE', $documentType, $documentType, $providerId);
		$res = $app->db->doQuery($query);
		$row = $app->db->fetchAssoc($res);
		if ($row['settings_generate_accounting']=='N') return $ret;
		if (!$row['settings_number']) throw new ExceptionUserTextStorage('error.accounting_noNumber_'.$documentType);

		// novy counter
		$ret['counter'] = $row['settings_counter']+1;
		$lastYear = $row['document_year'];
		$ret['year'] = date('Y');
		if ($lastYear != $ret['year']) {
			$ret['counter'] = 1;
		}
		$ret['number'] = $row['settings_number'];

		$yearStart = strpos($ret['number'], '[Y');
		if ($yearStart !== false) {
			$yearEnd = strpos(substr($ret['number'], $yearStart+2), ']');
			$yearLength = $yearEnd+1;
		} else $yearLength = 0;
		if ($yearStart !== false) { $ret['number'] = substr_replace($ret['number'], substr($ret['year'],-$yearLength), $yearStart, $yearLength+2); }

		$counterStart = strpos($ret['number'], '[C');
		if ($counterStart !== false) {
			$counterEnd = strpos(substr($ret['number'], $counterStart+2), ']');
			$counterLength = $counterEnd+1;
		}
		if ($counterStart !== false) { $ret['number'] = substr_replace($ret['number'], sprintf("%0${counterLength}u", $ret['counter']), $counterStart, $counterLength+2); }

		return $ret;
	}

	public static function generateDocumentNumber($providerId, $documentTemplateItemId) {
		$app = Application::get();
		$ret = array('globalCounter'=>null,'documentCounter'=>null,'year'=>null,'number'=>null);

		$s = new SProvider;
		$s->addStatement(new SqlStatementBi($s->columns['provider_id'], $providerId, '%s=%s'));
		$s->setColumnsMask(array('document_year','document_counter'));
		$s->setForUpdate(true);
		$res = $app->db->doQuery($s->toString());
		$row = $app->db->fetchAssoc($res);

		$s = new SDocumentTemplateItem;
		$s->addStatement(new SqlStatementBi($s->columns['documenttemplateitem_id'], $documentTemplateItemId, '%s=%s'));
		$s->addStatement(new SqlStatementBi($s->columns['provider'], $providerId, '%s=%s'));
		$s->setColumnsMask(array('code','number','counter'));
		$s->setForUpdate(true);
		$res = $app->db->doQuery($s->toString());
		$row1 = $app->db->fetchAssoc($res);

		// novy counter
		$ret['globalCounter'] = $row['document_counter']+1;
		$ret['documentCounter'] = $row1['counter']+1;
		$lastYear = $row['document_year'];
		$ret['year'] = date('Y');
		if ($lastYear != $ret['year']) {
			$ret['globalCounter'] = 1;
			$ret['documentCounter'] = 1;
		}
		$ret['number'] = $row1['number'];

		$codeStart = strpos($ret['number'], '[ID]');
		if ($codeStart !== false) { $ret['number'] = str_replace('[ID]', $row1['code'], $ret['number']); }

		$yearStart = strpos($ret['number'], '[Y');
		if ($yearStart !== false) {
			$yearEnd = strpos(substr($ret['number'], $yearStart+2), ']');
			$yearLength = $yearEnd+1;
		} else $yearLength = 0;
		if ($yearStart !== false) { $ret['number'] = substr_replace($ret['number'], substr(date('Y'),-$yearLength), $yearStart, $yearLength+2); }

		$counterStart = strpos($ret['number'], '[C');
		if ($counterStart !== false) {
			$counterEnd = strpos(substr($ret['number'], $counterStart+2), ']');
			$counterLength = $counterEnd+1;
		}
		if ($counterStart !== false) { $ret['number'] = substr_replace($ret['number'], sprintf("%0${counterLength}u", $ret['globalCounter']), $counterStart, $counterLength+2); }

		$codeCounterStart = strpos($ret['number'], '[c');
		if ($codeCounterStart !== false) {
			$codeCounterEnd = strpos(substr($ret['number'], $codeCounterStart+2), ']');
			$codeCounterLength = $codeCounterEnd+1;
		}
		if ($codeCounterStart !== false) { $ret['number'] = substr_replace($ret['number'], sprintf("%0${codeCounterLength}u", $ret['documentCounter']), $codeCounterStart, $codeCounterLength+2); }

		// kontrola, jestli je cislo dokumentu unikatni
		$s = new SDocument;
		$s->addStatement(new SqlStatementBi($s->columns['provider'], $providerId, '%s=%s'));
		$s->addStatement(new SqlStatementBi($s->columns['number'], $ret['number'], '%s=%s'));
		$s->setColumnsMask(array('number'));
		$res = $app->db->doQuery($s->toString());
		if ($app->db->getRowsNumber($res)) throw new ExceptionUser(sprintf($app->textStorage->getText('error.saveDocument_numberNotUnique'), $ret['number']));

		return $ret;
	}

	public static function deleteTextStorage($providerId) {
    $app = Application::get();

    $s = new SProviderTextStorage;
    $s->addStatement(new SqlStatementBi($s->columns['provider'], $providerId, '%s=%s'));
    $s->setColumnsMask(array('providertextstorage_id'));
    $res = $app->db->doQuery($s->toString());
    while ($row = $app->db->fetchAssoc($res)) {
      $o = new OProviderTextStorage($row['providertextstorage_id']);
      $o->delete();
    }
  }

	public static function initTextStorage($providerId) {
    $app = Application::get();

    $app->db->beginTransaction();

    self::deleteTextStorage($providerId);

    // nactu a ulozim zaznamy textstorage
    foreach ($app->language->getAccept() as $lang) {
      $resource = $app->textStorage->getResource($lang, array('ajax','calendar'));
      foreach ($resource as $key=>$value) {
        $o = new OProviderTextStorage;
        $o->setData(array(
          'provider'        => $providerId,
          'language'        => $lang,
          'ts_key'          => $key,
          'original_value'  => $value,
        ));
        $o->save();
      }
    }

    $app->db->commitTransaction();
  }

	public static function addTextStorage($providerId) {
		$app = Application::get();

		$app->db->beginTransaction();

		$count = 0;
		// nactu a ulozim zaznamy textstorage
		foreach ($app->language->getAccept() as $lang) {
			$resource = $app->textStorage->getResource($lang, array('ajax','calendar'));
			foreach ($resource as $key=>$value) {
				$s = new SProviderTextStorage;
				$s->addStatement(new SqlStatementBi($s->columns['provider'], $providerId, '%s=%s'));
				$s->addStatement(new SqlStatementBi($s->columns['language'], $lang, '%s=%s'));
				$s->addStatement(new SqlStatementBi($s->columns['ts_key'], $key, '%s=%s'));
				$s->setColumnsMask(array('providertextstorage_id'));
				$res = $app->db->doQuery($s->toString());
				if (!$app->db->getRowsNumber($res)) {
					$o = new OProviderTextStorage;
					$o->setData(array(
						'provider'        => $providerId,
						'language'        => $lang,
						'ts_key'          => $key,
						'original_value'  => $value,
					));
					$o->save();

					$count++;
				}
			}
		}

		$app->db->commitTransaction();

		return $count;
	}

  public static function updateTextStorage($providerId, $newValues) {
    $app = Application::get();

    $app->db->beginTransaction();

    foreach ($newValues as $id=>$newValue) {
      $s = new SProviderTextStorage;
      $s->addStatement(new SqlStatementBi($s->columns['provider'], $providerId, '%s=%s'));
      $s->addStatement(new SqlStatementBi($s->columns['providertextstorage_id'], $id, '%s=%s'));
      $s->setColumnsMask(array('providertextstorage_id','language','ts_key','new_value'));
      $res = $app->db->doQuery($s->toString());
      if ($row = $app->db->fetchAssoc($res)) {
        $o = new OProviderTextStorage($id);
        $o->setData(array(
          'new_value' => $newValue?$newValue:null
        ));
        $o->save();
      }
    }

    $app->db->commitTransaction();
  }
}

?>