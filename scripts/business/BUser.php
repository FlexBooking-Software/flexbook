<?php

class BUser extends BusinessObject {

  private function _checkAccess($params=array()) {
    $ret = $this->_app->auth->haveRight('user_admin', $this->_app->auth->getActualProvider());
    
    if (!$ret) {
      $this->_load();

      if (($userId=$this->_app->auth->isUser())||$this->_app->auth->haveRight('power_organiser', $this->_app->auth->getActualProvider())) {
        // normalni uzivatel muze menit jenom svoje udaje a svoje poducty
        // a powerorganisator muze sebe menit z backoffice
        if ($this->_id) {
          $ret = ($userId==$this->_id)||($this->_data['parent']==$userId);
        } else {
          $ret = isset($params['parent'])&&($params['parent']==$userId);
        }
      }
    }

    if (isset($params['admin'])&&($params['admin']=='Y')&&!$this->_app->auth->isAdministrator())  $ret = false;
    
    return $ret;
  }

  private function _checkDeleteAccess() {
    $ret = $this->_app->auth->haveRight('delete_record', $this->_app->auth->getActualProvider());

    return $ret;
  }
  
  private function _checkCreditAccess($provider,$type='CASH') {
    $ret = $this->_app->auth->haveRight('credit_admin',$provider);
    
    if (!$ret) {
      // pokud meni kredit normalni uzivatel, muze jenom sobe strhavat za rezervaci nebo kupovat permanentku nebo dobijet online kredit
      if ($userId = $this->_app->auth->isUser()) {
        $ret = ($userId==$this->_id)&&(in_array($type,array('RESERVATION','TICKET','ONLINE')));
      }
    }
    
    return $ret;
  }
  
  private function _checkBeforeSave(& $params, $externalAccount=false) {
    $subAccount = (isset($params['parent'])&&$params['parent'])||(isset($this->_data['parent'])&&$this->_data['parent']);

    if (isset($params['email'])) $params['email'] = strtolower($params['email']);

    // kdyz neni zadano ani ulozeno username a je zadan email, budou stejny
    if (!isset($params['username'])&&(!isset($this->_data['username'])||!$this->_data['username'])&&isset($params['email'])) $params['username'] = $params['email'];
    // kdyz neni zadano ani ulozeno heslo, vygeneruju nahodne
    if (!isset($params['password'])&&(!isset($this->_data['password'])||!$this->_data['password'])) $params['password'] = randomString(10);

    if (!$externalAccount) {
      // kdyz se zaklada novy uzivatel jsou tyto atributy povinne
      if (!$this->_id) {
        if (!isset($params['firstname'])) throw new ExceptionUserTextStorage('error.saveUser_emptyFirstname');
        if (!isset($params['lastname'])) throw new ExceptionUserTextStorage('error.saveUser_emptyLastname');

        if (!$subAccount) {
          if (!isset($params['username'])) throw new ExceptionUserTextStorage('error.saveUser_emptyUsername');
          if (!isset($params['password'])) throw new ExceptionUserTextStorage('error.saveUser_emptyPassword');
          if (!isset($params['email'])) throw new ExceptionUserTextStorage('error.saveUser_emptyEmail');
          if (!isset($params['phone'])) throw new ExceptionUserTextStorage('error.saveUser_emptyPhone');
        }
      }

      // tyto nesmi byt prazdny nikdy
      if (isset($params['firstname']) && !$params['firstname']) throw new ExceptionUserTextStorage('error.saveUser_emptyFirstname');
      if (isset($params['lastname']) && !$params['lastname']) throw new ExceptionUserTextStorage('error.saveUser_emptyLastname');

      if (!$subAccount) {
        if (isset($params['username']) && !$params['username']) throw new ExceptionUserTextStorage('error.saveUser_emptyUsername');
        if (isset($params['password']) && !$params['password']) throw new ExceptionUserTextStorage('error.saveUser_emptyPassword');
        if (isset($params['email']) && !$params['email']) throw new ExceptionUserTextStorage('error.saveUser_emptyEmail');
        if (isset($params['phone']) && !$params['phone']) throw new ExceptionUserTextStorage('error.saveUser_emptyPhone');
      }
    }

    // nektere hodnoty muze nastavit pouze admin
    if (!$this->_app->auth->isAdministrator()) {
      // menit roli admin
      if (isset($params['admin'])) throw new ExceptionUserTextStorage('error.saveUser_notAllowed');
      // nastavit jiny username nez email
      $username = ifsetor($params['username']);
      $email = ifsetor($params['email']);
      if ($username&&$email&&strcmp($username,$email)) throw new ExceptionUserTextStorage('error.saveUser_notAllowed');
    }
    
    // testy na unikatnost
    if (isset($params['username'])&&$params['username']) {
      $s = new SUser;
      $s->addStatement(new SqlStatementBi($s->columns['username'], strtolower($params['username']), '%s=%s'));
      $s->addStatement(new SqlStatementBi($s->columns['registration_provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
      $s->addStatement(new SqlStatementBi($s->columns['user_id'], $this->_id, '%s<>%s'));
      $s->setColumnsMask(array('user_id'));
      $result = $this->_app->db->doQuery($s->toString());
      if ($this->_app->db->getRowsNumber($result) > 0) throw new ExceptionUserTextStorage('error.saveUser_alreadyExists');
    }
    if (isset($params['email'])&&$params['email']) {
      if (!$this->_app->regionalSettings->checkEmail($params['email'])) throw new ExceptionUserTextStorage('error.saveUser_invalidEmail');

      $s = new SUser;
      $s->addStatement(new SqlStatementBi($s->columns['email'], strtolower($params['email']), '%s=%s'));
      $s->addStatement(new SqlStatementBi($s->columns['registration_provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
      $s->addStatement(new SqlStatementBi($s->columns['user_id'], $this->_id, '%s<>%s'));
      $s->setColumnsMask(array('user_id'));
      $result = $this->_app->db->doQuery($s->toString());
      if ($this->_app->db->getRowsNumber($result) > 0) throw new ExceptionUserTextStorage('error.saveUser_alreadyExists');
    }
    if (isset($params['facebookId'])&&$params['facebookId']) {
      $s = new SUser;
      $s->addStatement(new SqlStatementBi($s->columns['facebook_id'], $params['facebookId'], '%s=%s'));
      $s->addStatement(new SqlStatementBi($s->columns['registration_provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
      $s->addStatement(new SqlStatementBi($s->columns['user_id'], $this->_id, '%s<>%s'));
      $s->setColumnsMask(array('user_id'));
      $result = $this->_app->db->doQuery($s->toString());
      if ($this->_app->db->getRowsNumber($result) > 0) throw new ExceptionUserTextStorage('error.saveUser_facebookAlreadyExists');
    }
    if (isset($params['googleId'])&&$params['googleId']) {
      $s = new SUser;
      $s->addStatement(new SqlStatementBi($s->columns['google_id'], $params['googleId'], '%s=%s'));
      $s->addStatement(new SqlStatementBi($s->columns['registration_provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
      $s->addStatement(new SqlStatementBi($s->columns['user_id'], $this->_id, '%s<>%s'));
      $s->setColumnsMask(array('user_id'));
      $result = $this->_app->db->doQuery($s->toString());
      if ($this->_app->db->getRowsNumber($result) > 0) throw new ExceptionUserTextStorage('error.saveUser_googleAlreadyExists');
    }
    if (isset($params['twitterId'])&&$params['twitterId']) {
      $s = new SUser;
      $s->addStatement(new SqlStatementBi($s->columns['twitter_id'], $params['twitterId'], '%s=%s'));
      $s->addStatement(new SqlStatementBi($s->columns['registration_provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
      $s->addStatement(new SqlStatementBi($s->columns['user_id'], $this->_id, '%s<>%s'));
      $s->setColumnsMask(array('user_id'));
      $result = $this->_app->db->doQuery($s->toString());
      if ($this->_app->db->getRowsNumber($result) > 0) throw new ExceptionUserTextStorage('error.saveUser_twitterAlreadyExists');
    }

    $this->_checkProviderUnique($params, $subAccount);
  }

  private function _checkProviderUnique($params, $subaccount) {
    $mandatoryFields = $subaccount?BCustomer::getProviderSettings($this->_app->auth->getActualProvider(), 'subaccountUnique'):BCustomer::getProviderSettings($this->_app->auth->getActualProvider(), 'userUnique');
    if (count($mandatoryFields)) {
      $user = array();

      $userAttribute = $attributeIdString = array();
      foreach ($mandatoryFields as $key=>$value) {
        if (isset($params[$value])) $user[$value] = $params[$value];

        if (strpos($value, 'attr_')===0) {
          $id = substr($value,5);
          $attributeIdString[] = sprintf("'%s'", $id);
          if (isset($params['attribute'][$id])) $userAttribute[$id] = $params['attribute'][$id];

          unset($mandatoryFields[$key]);
        }
      }
      if (count($userAttribute)) {
        ksort($userAttribute);
        $user['all_attribute_value_json'] = '';
        foreach ($userAttribute as $key=>$value) {
          if ($user['all_attribute_value_json']) $user['all_attribute_value_json'] .= ',';
          $user['all_attribute_value_json'] .= sprintf('%s:%s', $key, $value);
        }
      }
      $s = new SUser;
      $s->addStatement(new SqlStatementBi($s->columns['registration_provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
      if ($subaccount) $s->addStatement(new SqlStatementMono($s->columns['parent_user'], '%s IS NOT NULL'));
      $s->addStatement(new SqlStatementBi($s->columns['user_id'], $this->_id, '%s<>%s'));
      if (count($attributeIdString)) {
        $s->addStatement(new SqlStatementMono($s->columns['attribute_id'], sprintf('%%s IN (%s)', implode(',',$attributeIdString))));
        $mandatoryFields[] = 'all_attribute_value_json';
      }
      $s->setColumnsMask($mandatoryFields);
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        if ($user == $row) throw new ExceptionUserTextStorage('error.ajax_saveUser_providerUnique');
      }
    }
  }
  
  private function _checkBeforeDelete() {
    $s = new SUserRegistration;
    $s->addStatement(new SqlStatementBi($s->columns['user'], $this->_id, '%s=%s'));
    if (!$this->_app->auth->isAdministrator()) $s->addStatement(new SqlStatementMono($s->columns['provider'], sprintf('%%s IN (%s)', $this->_app->auth->getAllowedProvider('user_admin','list'))));
    $s->addStatement(new SqlStatementMono($s->columns['credit'], '%s>0'));
    $s->setColumnsMask(array('userregistration_id'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($this->_app->db->getRowsNumber($res)) throw new ExceptionUserTextStorage('error.deleteUser_credit');
    
    $s = new SReservation;
    $s->addStatement(new SqlStatementBi($s->columns['user'], $this->_id, '%s=%s'));
    $s->setColumnsMask(array('reservation_id'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($this->_app->db->getRowsNumber($res)) throw new ExceptionUserTextStorage('error.deleteUser_reservationExists');

    $s = new SEvent;
    $s->addStatement(new SqlStatementBi($s->columns['organiser'], $this->_id, '%s=%s'));
    $s->setColumnsMask(array('event_id'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($this->_app->db->getRowsNumber($res)) throw new ExceptionUserTextStorage('error.deleteUser_organiserExists');

    $s = new SEventAttendeePerson;
    $s->addStatement(new SqlStatementBi($s->columns['subaccount'], $this->_id, '%s=%s'));
    $s->setColumnsMask(array('eventattendeeperson_id'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($this->_app->db->getRowsNumber($res)) throw new ExceptionUserTextStorage('error.deleteUser_attendeeExists');

    $s = new SCreditJournal;
    $s->addStatement(new SqlStatementBi($s->columns['change_user'], $this->_id, '%s=%s'));
    $s->setColumnsMask(array('change_user'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($this->_app->db->getRowsNumber($res)) throw new ExceptionUserTextStorage('error.deleteUser_journalExists');
    $s = new SReservationJournal;
    $s->addStatement(new SqlStatementBi($s->columns['change_user'], $this->_id, '%s=%s'));
    $s->setColumnsMask(array('change_user'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($this->_app->db->getRowsNumber($res)) throw new ExceptionUserTextStorage('error.deleteUser_journalExists');
    $s = new SUserTicketJournal;
    $s->addStatement(new SqlStatementBi($s->columns['change_user'], $this->_id, '%s=%s'));
    $s->setColumnsMask(array('change_user'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($this->_app->db->getRowsNumber($res)) throw new ExceptionUserTextStorage('error.deleteUser_journalExists');
  }
  
  private function _checkBeforeRegistrationDelete() {
    $s = new SUserRegistration;
    $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
    $s->addStatement(new SqlStatementBi($s->columns['user'], $this->_id, '%s=%s'));
    $s->setColumnsMask(array('userregistration_id','credit'));
    $res = $this->_app->db->doQuery($s->toString());
    if (!$this->_app->db->getRowsNumber($res)) throw new ExceptionUserTextStorage('error.deleteUserRegistration_invalidRegistration');
    $row = $this->_app->db->fetchAssoc($res);
    if ($row['credit']>0) throw new ExceptionUserTextStorage('error.deleteUserRegistration_credit');
    $ret = $row['userregistration_id'];
    
    $s = new SReservation;
    $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
    $s->addStatement(new SqlStatementBi($s->columns['user'], $this->_id, '%s=%s'));
    $s->setColumnsMask(array('reservation_id'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($this->_app->db->getRowsNumber($res)) throw new ExceptionUserTextStorage('error.deleteUserRegistration_reservationExists');
    
    return $ret;
  }

  public function changePassword($oldPassword, $newPassword) {
    $this->_load();

    $s = new SUser;
    $s->addStatement(new SqlStatementBi($s->columns['user_id'], $this->_app->auth->getUserId(), '%s=%s'));
    $s->addStatement(new SqlStatementBi($s->columns['password'], $this->_app->auth->getMd5Password()?md5(addslashes($oldPassword)):addslashes($oldPassword), '%s=%s'));
    $s->setColumnsMask(array('user_id'));
    $result = $this->_app->db->doQuery($s->toString());
    if (!$this->_app->db->getRowsNumber($result)) {
      throw new ExceptionUserTextStorage('error.changePassword_invalidPassword');
    }
    if (!$newPassword) throw new ExceptionUserTextStorage('error.changePassword_noPassword');

    $this->_app->db->beginTransaction();

    $oUser = new OUser($this->_id);
    $dUser['password'] = $this->_app->auth->getMd5Password()?md5($newPassword):$newPassword;
    $oUser->setData($dUser);
    $oUser->save();
      
    $this->_app->db->commitTransaction();
  }

  protected function _load() {
    if ($this->_id&&!$this->_loaded) {
      $s = new SUser;
      $s->addStatement(new SqlStatementBi($s->columns['user_id'], $this->_id, '%s=%s'));
      $s->setColumnsMask(array('user_id','parent_user','username','password','facebook_id','google_id','twitter_id',
                               'firstname','lastname','email','phone',
                               'address','street','city','postal_code','state','admin',
                               'disabled','validated','reservationcondition'
                               ));
      $res = $this->_app->db->doQuery($s->toString());
      $data = $this->_app->db->fetchAssoc($res);
      $returnData['id'] = $data['user_id'];
      $returnData['parent'] = $data['parent_user'];
      $returnData['username'] = $data['username'];
      $returnData['password'] = $data['password'];
      $returnData['facebookId'] = $data['facebook_id'];
      $returnData['googleId'] = $data['google_id'];
      $returnData['twitterId'] = $data['twitter_id'];
      $returnData['firstname'] = $data['firstname'];
      $returnData['lastname'] = $data['lastname'];
      $returnData['fullname'] = sprintf('%s %s', $data['firstname'], $data['lastname']);
      $returnData['email'] = $data['email'];
      $returnData['phone'] = $data['phone'];
      $returnData['addressId'] = $data['address'];  
      $returnData['street'] = $data['street'];
      $returnData['city'] = $data['city'];
      $returnData['postalCode'] = $data['postal_code'];
      $returnData['state'] = $data['state'];
      $returnData['admin'] = $data['admin'];
      $returnData['validated'] = $data['validated'];
      $returnData['disabled'] = $data['disabled'];
      $returnData['reservationConditionId'] = $data['reservationcondition'];

      if (!$data['validated']) {
        $s = new SUserValidation;
        $s->addStatement(new SqlStatementBi($s->columns['user'], $this->_id, '%s=%s'));
        $s->setColumnsMask(array('validation_string'));
        $res = $this->_app->db->doQuery($s->toString());
        if ($row = $this->_app->db->fetchAssoc($res)) {
          global $URL;

          $returnData['validationUrl'] = $URL['user_validation'].$row['validation_string'];
        }
      }
      
      $returnData['registration'] = array();
      $s = new SUserRegistration;
      $s->addStatement(new SqlStatementBi($s->columns['user'], $this->_id, '%s=%s'));
      if (($this->_id!=$this->_app->auth->getUserId())&&!$this->_app->auth->isAdministrator()) $s->addStatement(new SqlStatementMono($s->columns['provider'], sprintf('%%s IN (%s)', $this->_app->auth->getAllowedProvider('user_admin','list'))));
      $s->addOrder(new SqlStatementAsc($s->columns['provider_name']));
      $s->setColumnsMask(array('userregistration_id','provider','registration_timestamp','receive_advertising','provider_name','credit','organiser','admin','reception'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $returnData['registration'][$row['userregistration_id']] = array(
              'registrationId'        => $row['userregistration_id'],
              'providerId'            => $row['provider'],
              'providerName'          => $row['provider_name'],
              'timestamp'             => $row['registration_timestamp'],
              'advertising'           => $row['receive_advertising'],
              'credit'                => $row['credit'],
              'organiser'             => $row['organiser'],
              'admin'                 => $row['admin'],
              'reception'             => $row['reception'],
              );
      }

      $provider = !$this->_app->auth->isAdministrator()?$this->_app->auth->getActualProvider():false;
      $returnData['attribute'] = $this->getAttribute($provider, $this->_app->language->getLanguage(), null, array('INTERNAL','READONLY','CREATEONLY'), true);
      foreach ($returnData['attribute'] as $id=>$attr) {
        if (is_null($attr['value'])) unset($returnData['attribute'][$id]);
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
      $o = new OUser($this->_id);
      $o->setData(array('address'=>$bA->getId()));
      $o->save();
    }
  }
  
  private function _saveRegistration($params) {
    // pokud se zaklada poducet bez registrace, tak udelam stejnou registraci, jako ma parent ucet
    if (isset($params['parent'])&&$params['parent']&&!isset($params['registration'])) {
      $s = new SUserRegistration;
      $s->addStatement(new SqlStatementBi($s->columns['user'], $params['parent'], '%s=%s'));
      $s->setColumnsMask(array('provider'));
      $res = $this->_app->db->doQuery($s->toString());
      if ($row = $this->_app->db->fetchAssoc($res)) {
        $params['registration'] = array(array('providerId'=>$row['provider']));
      }
    }

    if (isset($params['registration'])) {
      $idToSave = array();
      if (is_array($params['registration'])) {
        // kontrola aby registrace byla unikatni pro poskytovatele
        $providerToCheck = array();
        foreach ($params['registration'] as $reg) {
          if (!isset($reg['registrationId'])||!$reg['registrationId']) {
            // u jiz existujicich registraci nepujde menit poskytovatel, staci projit ty bez ID-cka
            if (isset($reg['providerId'])) $providerToCheck[] = $reg['providerId'];
          }
        }
        if (count($providerToCheck)) {
          $s = new SUserRegistration;
          $s->addStatement(new SqlStatementBi($s->columns['user'], $this->_id, '%s=%s'));
          $s->addStatement(new SqlStatementMono($s->columns['provider'], sprintf('%%s IN (%s)', implode(',',$providerToCheck))));
          $s->setColumnsMask(array('userregistration_id'));
          $res = $this->_app->db->doQuery($s->toString());
          if ($this->_app->db->getRowsNumber($res)) throw new ExceptionUserTextStorage('error.saveUser_registrationNotUnique');
        }
        
        foreach ($params['registration'] as $reg) {
          $regId = isset($reg['registrationId'])&&$reg['registrationId']?$reg['registrationId']:null;
          $o = new OUserRegistration($regId);
          $oData = array();
          if (isset($reg['providerId'])) $oData['provider'] = $reg['providerId'];
          if (isset($reg['advertising'])) $oData['receive_advertising'] = $reg['advertising'];
          
          if (!$o->getId()) {
            $oData['user'] = $this->_id;
            $oData['credit'] = 0;
            $oData['registration_timestamp'] = date('Y-m-d H:i:s');
          }
          $o->setData($oData);
          $o->save();
          
          $idToSave[] = $o->getId();
        }
      }
      
      // kdyz uklada administrator z administrace smazou se neuvedene registrace
      if ($this->_app->auth->isAdministrator()) {
        $s = new SUserRegistration;
        $s->addStatement(new SqlStatementBi($s->columns['user'], $this->_id, '%s=%s'));
        if (count($idToSave)) $s->addStatement(new SqlStatementMono($s->columns['userregistration_id'], sprintf('%%s NOT IN (%s)', implode(',',$idToSave))));
        $s->setColumnsMask(array('userregistration_id','credit','reservation_count','provider'));
        $res = $this->_app->db->doQuery($s->toString());
        while ($row = $this->_app->db->fetchAssoc($res)) {
          if ($row['credit']) throw new ExceptionUserTextStorage('error.saveUser_deleteRegistrationWithCredit');
          if ($row['reservation_count']) throw new ExceptionUserTextStorage('error.saveUser_deleteRegistrationWithReservation');
          $o = new OUserRegistration($row['userregistration_id']);
          $o->delete();
          
          // jeste smazu rozsirene atributy pro daneho poskytovatele
          $s = new SUserAttribute;
          $s->addStatement(new SqlStatementBi($s->columns['user'], $this->_id, '%s=%s'));
          $s->addStatement(new SqlStatementBi($s->columns['provider'], $row['provider'], '%s=%s'));
          $s->setColumnsMask(array('attribute'));
          $res1 = $this->_app->db->doQuery($s->toString());
          while ($row1 = $this->_app->db->fetchAssoc($res1)) {
            $o = new OUserAttribute(array('user'=>$this->_id,'attribute'=>$row1['attribute']));
            $o->delete();
          }
        }
      }
      
      #$this->updateGlobalRole();
    }
  }
  
  private function _save($params) {
    $this->_app->db->beginTransaction();

    $o = new OUser($this->_id?$this->_id:null);

    if (isset($params['parent'])) $oData['parent_user'] = $params['parent']?$params['parent']:null;
    if (isset($params['firstname'])) $oData['firstname'] = $params['firstname'];
    if (isset($params['lastname'])) $oData['lastname'] = $params['lastname'];
    if (isset($params['username'])) $oData['username'] = $params['username'];
    if (isset($params['password'])) $oData['password'] = $this->_app->auth->getMd5Password()?md5(addslashes($params['password'])):$params['password'];
    if (isset($params['email'])) $oData['email'] = $params['email'];
    if (isset($params['phone'])) $oData['phone'] = $params['phone'];
    if (isset($params['disabled'])) $oData['disabled'] = $params['disabled'];
    if (isset($params['facebookId'])) $oData['facebook_id'] = $params['facebookId'];
    if (isset($params['googleId'])) $oData['google_id'] = $params['googleId'];
    if (isset($params['twitterId'])) $oData['twitter_id'] = $params['twitterId'];
    if (isset($params['reservationConditionId'])) $oData['reservationcondition'] = $params['reservationConditionId']?$params['reservationConditionId']:null;
    if (isset($params['admin'])) $oData['admin'] = $params['admin'];

    if (count($oData)) {
      $o->setData($oData);
      $o->save();
    }
    $this->_id = $o->getId();
    
    $this->_saveAddress($params);
    if (isset($params['attribute'])) $this->saveAttribute($params);
    $this->_saveRegistration($params);
    
    $this->_app->db->commitTransaction();
  }

  public function save($params) {
    $newUser = !$this->_id;
    
    $this->_load();
    
    if (!$this->_checkAccess($params)) throw new ExceptionUserTextStorage('error.accessDenied');
    
    $this->_checkBeforeSave($params);

    if (!isset($params['attributeValidation'])) $params['attributeValidation'] = true;
    $this->_save($params);
    
    if ($newUser) $this->validate();
    else {
      // notifikace pri zmene rez. podminek
      $provider = null;
      if (isset($this->_data['registration'])) {
        foreach ($this->_data['registration'] as $reg) {
          if (isset($reg['providerId'])) {
            $provider = $reg['providerId'];
            break;
          }
        }
      }
      $oldResCond = ifsetor($this->_data['reservationConditionId']);
      $newResCond = ifsetor($params['reservationConditionId']);
      if ($provider&&$newResCond&&($newResCond!=$oldResCond)) {
        $notParams = array('type'=>'U_RESERVATION_COND','providerId'=>$provider,'userId'=>$this->_id,
          'reservationConditionId'=>$newResCond,'data'=>array(),'forceSend'=>true);
        BNotificationTemplate::generate($notParams);
      }
    }
    
    return $this->_id;
  }
  
  public function delete() {
    $this->_load();

    if ($this->_data['parent']) {
      if (!$this->_checkAccess()) throw new ExceptionUserTextStorage('error.accessDenied');
    } else {
      if (!$this->_checkDeleteAccess()) throw new ExceptionUserTextStorage('error.accessDenied');
    }
  
    $this->_delete();
  }

  private function _deleteSubaccount() {
    $s = new SUser;
    $s->addStatement(new SqlStatementBi($s->columns['parent_user'], $this->_id, '%s=%s'));
    $s->setColumnsMask(array('user_id'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $b = new BUser($row['user_id']);
      $b->delete();
    }
  }

  private function _delete() {
    if ($this->_id == $this->_app->auth->getUserId()) { throw new ExceptionUserTextStorage('error.deleteUser_notMyself'); }

    $this->_checkBeforeDelete();
    
    $this->_app->db->beginTransaction();

    $this->_deleteSubaccount();

    $user = new OUser($this->_id);
    $user->delete();

    $this->_app->db->commitTransaction();
  }
  
  public function disable() {
    if ($this->_id == $this->_app->auth->getUserId()) { throw new ExceptionUserTextStorage('error.disableUser_notMyself'); }
    
    if (!$this->_checkAccess()) throw new ExceptionUserTextStorage('error.accessDenied');

    $this->_app->db->beginTransaction();

    $user = new OUser($this->_id);
    $user->setData(array('disabled'=>'Y'));
    $user->save();

    $this->_app->db->commitTransaction();
  }
  
  public function enable() { 
    if (!$this->_checkAccess()) throw new ExceptionUserTextStorage('error.accessDenied');

    $this->_app->db->beginTransaction();

    $user = new OUser($this->_id);
    $user->setData(array('disabled'=>'N'));
    $user->save();

    $this->_app->db->commitTransaction();
  }
  
  public function sendPassword($email, $provider) {
    // todle je kvuli moznosti odeslat heslo v editaci uzivatele, kdyz je prihlasenej admin bez aktualniho poskytovatele
    if (!$provider&&$this->_id&&$this->_app->auth->isAdministrator()) {
      $s = new SUserRegistration;
      $s->addStatement(new SqlStatementBi($s->columns['user'], $this->_id, '%s=%s'));
      $s->setColumnsMask(array('provider'));
      $res = $this->_app->db->doQuery($s->toString());
      if ($row = $this->_app->db->fetchAssoc($res)) {
        $provider = $row['provider'];
      }
    }

    $s = new SUser;
    $s->addStatement(new SqlStatementBi($s->columns['registration_provider'], $provider, '%s=%s'));
    $s->addStatement(new SqlStatementBi($s->columns['email'], $email, '%s=%s'));
    $s->setColumnsMask(array('user_id','username','password','validated','disabled'));
    $res = $this->_app->db->doQuery($s->toString());
    if (!$user = $this->_app->db->fetchAssoc($res)) throw new ExceptionUserTextStorage('error.userSendPassword_unknownEmail');
    if ($user['disabled'] == 'Y') throw new ExceptionUserTextStorage('error.userSendPassword_disabled');
    if (!$user['validated']) throw new ExceptionUserTextStorage('error.userSendPassword_notConfirmed');

    // @todo kdyz bude MD5 je nutne heslo vygenerovant a nejak predat do notifikace
    $newPassword = randomString(10);
    $o = new OUser($user['user_id']);
    $o->setData(array('password'=>$this->_app->auth->getMd5Password()?md5(addslashes($newPassword)):$newPassword));
    $o->save();

    $params = array('type'=>'U_PASSWORD','providerId'=>$provider,'userId'=>$user['user_id'],'forceSend'=>true);
    // kdyz je heslo MD5, musim ho poslat do notifikace "zvenku"
    if ($this->_app->auth->getMd5Password()) $params['newPassword'] = $newPassword;
    BNotificationTemplate::generate($params);
  }
  
  public function registrate($params, $externalAccount=false) {
    $ret = 1;
    
    $newUser  = !$this->_id;
    
    $this->_app->db->beginTransaction();

    // musi se zabranit dvojitym registracim v ten samy cas
    $s = new SProvider;
    $s->addStatement(new SqlStatementBi($s->columns['provider_id'], $this->_app->auth->getActualProvider(), '%s=%s'));
    $s->setColumnsMask(array('short_name'));
    $s->setForUpdate(true);
    $res = $this->_app->db->doQuery($s->toString());

    $this->_checkBeforeSave($params, $externalAccount);
    
    if (!isset($params['attributeValidation'])) $params['attributeValidation'] = 'exact';

    if ($rc=BCustomer::getProviderSettings(ifsetor($params['registration'][0]['providerId']),'userReservationCondition')) $params['reservationConditionId'] = $rc;

    $this->_save($params);
    
    $notParams = array('type'=>'U_CREATE','providerId'=>ifsetor($params['registration'][0]['providerId']),'userId'=>$this->_id,'data'=>array(),'forceSend'=>true);
    
    if ($newUser) {
      $settings = BCustomer::getProviderSettings(ifsetor($params['registration'][0]['providerId']));
      if (($settings['userConfirm']=='Y')&&!$externalAccount) {
        $string = randomString(20);
        $o = new OUserValidation;
        $o->setData(array('user'=>$this->_id,'validation_string'=>$string));
        $o->save();
        
        $ret = 2;

        $notParams['type'] .= '_VALIDATION';
      } else {
        $o = new OUser($this->_id);
        $o->setData(array('validated'=>date('Y-m-d H:i:s')));
        $o->save();
      }

      BDocumentTemplate::generate(array('type'=>'U_CREATE','providerId'=>ifsetor($params['registration'][0]['providerId']),'userId'=>$this->_id));
    }

    $this->_app->db->commitTransaction();

    BNotificationTemplate::generate($notParams);

    return $ret;
  }

  public function sendRegistrationEmail($params) {
    $s = new SUser;
    $s->addStatement(new SqlStatementBi($s->columns['registration_provider'], $params['provider'], '%s=%s'));
    $s->addStatement(new SqlStatementBi($s->columns['email'], $params['userEmail'], '%s=%s'));
    $s->setColumnsMask(array('user_id','validation_string'));
    $res = $this->_app->db->doQuery($s->toString());
    if (($row = $this->_app->db->fetchAssoc($res))&&$row['validation_string']) {
      $notParams = array('type'=>'U_CREATE_VALIDATION','skipReceiver'=>array('PROVIDER'),'providerId'=>$params['provider'],'userId'=>$row['user_id'],'data'=>array(),'forceSend'=>true);

      global $URL;
      $notParams['data']['user_validation_url'] = $URL['user_validation'].$row['validation_string'];

      BNotificationTemplate::generate($notParams);
    }
  }
  
  public function validate() {
    $this->_load();
    
    if ($this->_data['validated']) throw new ExceptionUserTextStorage('error.validateUser_alreadyValidated');
    
    $this->_app->db->beginTransaction();
    
    $o = new OUser($this->_id);
    $o->setData(array('validated'=>date('Y-m-d H:i:s')));
    $o->save();
    
    $s = new SUserValidation;
    $s->addStatement(new SqlStatementBi($s->columns['user'], $this->_id, '%s=%s'));
    $s->setColumnsMask(array('user'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($row = $this->_app->db->fetchAssoc($res)) {
      $o = new OUserValidation(array('user'=>$row['user']));
      $o->delete();
    }
    
    $this->_app->db->commitTransaction();
    
    $ret = $this->_data['fullname'];
    
    return $ret;
  }
  
  public function deleteRegistration() {
    $registration = $this->_checkBeforeRegistrationDelete();
    
    $this->_load();
    
    $this->_app->db->beginTransaction();
    
    $o = new OUserRegistration($registration);
    $o->delete();
    
    $ret = $this->_data['fullname'];
    
    $this->_app->db->commitTransaction();
    
    return $ret;
  }
  
  public function changeCredit($provider, $change, $type='CASH', $description=null) {
    $this->_load();

    if ($this->_data['parent']) throw new ExceptionUserTextStorage('error.saveUserCredit_subaccount');
    if (!$this->_checkCreditAccess($provider,$type)) throw new ExceptionUserTextStorage('error.accessDenied');

    if (BCustomer::getProviderSettings($provider,'disableCredit')=='Y') throw new ExceptionUserTextStorage('error.accessDenied');
    
    if ($this->_id) {
      $s = new SUserRegistration;
      $s->addStatement(new SqlStatementBi($s->columns['user'], $this->_id, '%s=%s'));
      $s->addStatement(new SqlStatementBi($s->columns['provider'], $provider, '%s=%s'));
      $s->setColumnsMask(array('userregistration_id','credit'));
      $res = $this->_app->db->doQuery($s->toString());
      if ($row = $this->_app->db->fetchAssoc($res)) {
        if ($row['credit']+$change<0) throw new ExceptionUserTextStorage('error.saveUserCredit_notEnoughResources');
        
        $this->_app->db->beginTransaction();
        
        $o = new OUserRegistration($row['userregistration_id']);
        $o->setData(array('credit'=>$row['credit']+$change));
        $o->save();
        $urId = $o->getId();
        
        $o = new OCreditJournal;
        $o->setData(array(
          'provider'                => $provider,
          'userregistration'        => $urId,
          'amount'                  => $change,
          'change_timestamp'        => date('Y-m-d H:i:s'),
          'change_user'             => $this->_app->auth->getUserId(),
          'flag'                    => $change>0?'C':'D',
          'type'                    => $type,
          'note'                    => $description,
          ));
        $o->save();

        // vygenerovani zalohove faktury, kdyz jsou to nove penize na kredit uzivatele
        if (($change>0)&&in_array($type,array('CASH','CREDITCARD','BANK','ONLINE'))) {
          $prepaymentInvoiceId = $this->_generatePrepaymentInvoice($provider, $urId, $o->getId());

          // vygenerovani notifikace
          BNotificationTemplate::generate(array('type'=>'U_CHARGE_CREDIT','providerId'=>$provider,
            'userId'=>$this->_id,'prepaymentInvoiceId'=>$prepaymentInvoiceId));
        }
      
        $this->_app->db->commitTransaction();
      } else {
        throw new ExceptionUserTextStorage('error.saveUserCredit_notEnoughResources');
      }
    } else {
      // kdyz neni uzivatel, jde pouze platit rezervace CASH nebo vracet platbu CASH
      if (strcmp($type,'RESERVATION')) throw new ExceptionUserTextStorage('error.saveUserCredit_invalidAction');
      
      $o = new OCreditJournal;
      $o->setData(array(
        'provider'                => $provider,
        'amount'                  => -$change,
        'change_timestamp'        => date('Y-m-d H:i:s'),
        'change_user'             => $this->_app->auth->getUserId(),
        'flag'                    => $change>0?'D':'C',
        'type'                    => 'RESERVATION',
        'note'                    => $description,
        ));
      $o->save();
    }
  }

  public function saveCreditFromOnlinePayment($paymentId) {
    $s = new SOnlinePayment;
    $s->addStatement(new SqlStatementMono($s->columns['target'], "%s='CREDIT'"));
    $s->addStatement(new SqlStatementBi($s->columns['paymentid'], $paymentId, '%s=%s'));
    $s->setColumnsMask(array('onlinepayment_id','amount','target_id','target_params','start_timestamp'));
    $res = $this->_app->db->doQuery($s->toString());
    $row = $this->_app->db->fetchAssoc($res);

    $row['target_params'] = json_decode($row['target_params']);
    $this->_id = $row['target_id'];
    if (!$row['target_params']->provider) throw new ExceptionUser('Credit charge from online payment failed: missing provider');

    $this->changeCredit($row['target_params']->provider, $row['amount'], 'ONLINE');
  }

  public function saveTicket($ticketId, $chargeCredit=true, $validityFrom=null) {
    if (BCustomer::getProviderSettings($provider,'disableTicket')=='Y') throw new ExceptionUserTextStorage('error.accessDenied');

    // muze se pridavat pouze aktivni ticket
    $s = new STicket;
    $s->addStatement(new SqlStatementBi($s->columns['ticket_id'], $ticketId, '%s=%s'));
    $s->setColumnsMask(array('ticket_id','name','provider','active','price','value','validity_type','validity_count','validity_unit','validity_from','validity_to'));
    $res = $this->_app->db->doQuery($s->toString());
    if (!$row=$this->_app->db->fetchAssoc($res)) throw new ExceptionUserTextStorage('error.saveUserTicket_invalidTicket');
    if ($row['active']!='Y') throw new ExceptionUserTextStorage('error.saveUserTicket_pastTicket');

    $this->_app->db->beginTransaction();

    if ($chargeCredit) $this->changeCredit($row['provider'], -$row['price'], 'TICKET', $row['name']);

    $from = $to = null;
    if ($row['validity_type']=='LENGTH') {
      $from = $to = $validityFrom?$validityFrom:date('Y-m-d H:i:s');
      switch ($row['validity_unit']) {
        case 'DAY': $to = $this->_app->regionalSettings->increaseDateTime($to, $row['validity_count']); break;
        case 'WEEK': $to = $this->_app->regionalSettings->increaseDateTime($to, $row['validity_count']*7); break;
        case 'MONTH': $to = $this->_app->regionalSettings->increaseDateTime($to, 0, $row['validity_count']); break;
        case 'YEAR': $to = $this->_app->regionalSettings->increaseDateTime($to, 0, 0, $row['validity_count']); break;
      }
    } elseif ($row['validity_type']=='PERIOD') {
      $from = $row['validity_from'];
      $to = $row['validity_to'];
    }

    $oData = array(
      'user'            => $this->_id,
      'ticket'          => $row['ticket_id'],
      'from_timestamp'  => $from,
      'to_timestamp'    => $to,
      'name'            => $row['name'],
      'original_value'  => $row['value'],
      'value'           => $row['value'],
      'created'         => date('Y-m-d H:i:s'),
    );

    $o = new OUserTicket;
    $o->setData($oData);
    $o->save();

    $oJ = new OUserTicketJournal;
    $oJ->setData(array(
      'userticket'              => $o->getId(),
      'amount'                  => $row['value'],
      'change_timestamp'        => date('Y-m-d H:i:s'),
      'change_user'             => $this->_app->auth->getUserId(),
      'flag'                    => 'C',
      'type'                    => 'CREATE',
    ));
    $oJ->save();

    $this->_app->db->commitTransaction();

    return $row['name'];
  }

  public function saveTicketFromOnlinePayment($paymentId) {
    $s = new SOnlinePayment;
    $s->addStatement(new SqlStatementMono($s->columns['target'], "%s='TICKET'"));
    $s->addStatement(new SqlStatementBi($s->columns['paymentid'], $paymentId, '%s=%s'));
    $s->setColumnsMask(array('onlinepayment_id','target_id','target_params','start_timestamp'));
    $res = $this->_app->db->doQuery($s->toString());
    $row = $this->_app->db->fetchAssoc($res);

    $row['target_params'] = json_decode($row['target_params']);
    $this->_id = $row['target_params']->user;
    if (!$this->_id) throw new ExceptionUser('Create ticket from online payment failed: missing user');

    $this->saveTicket($row['target_id'], false, $row['start_timestamp']);
  }

  public function refundTicket($ticketId) {
    $s = new SUserTicket;
    $s->addStatement(new SqlStatementBi($s->columns['userticket_id'], $ticketId, '%s=%s'));
    $s->setColumnsMask(array('name','provider','price','original_value','value'));
    $res = $this->_app->db->doQuery($s->toString());
    if (!$row=$this->_app->db->fetchAssoc($res)) throw new ExceptionUserTextStorage('error.saveUserTicket_invalidTicket');

    // muze se delat i castecny refund
    //if ($row['value']!=$row['original_value']) throw new ExceptionUserTextStorage('error.saveUserTicket_alreadyUsed');
    $refundAmount = round($row['price']*($row['value']/$row['original_value']));
    
    $this->_app->db->beginTransaction();
    
    $this->changeCredit($row['provider'], $refundAmount, 'TICKET', $row['name']);
    
    $o = new OUserTicket($ticketId);
    $o->setData(array('value'=>0));
    $o->save();
    
    $o = new OUserTicketJournal;
    $o->setData(array(
      'userticket'              => $ticketId,
      'amount'                  => -$row['value'],
      'change_timestamp'        => date('Y-m-d H:i:s'),
      'change_user'             => $this->_app->auth->getUserId(),
      'flag'                    => 'C',
      'type'                    => 'REFUND',
      ));
    $o->save();
    
    $this->_app->db->commitTransaction();
    
    return $row['name'];
  }
  
  public function getAvailableTicket($provider, $timeValidation=false, $center=null, $tag=null, $price=null) {
    $ret = array();
    
    $s = new SUserTicket;
    $s->addStatement(new SqlStatementBi($s->columns['user'], $this->_id, '%s=%s'));
    $s->addStatement(new SqlStatementBi($s->columns['provider'], $provider, '%s=%s'));
    if ($timeValidation) $s->addStatement(new SqlStatementQuad($s->columns['from_timestamp'], $s->columns['from_timestamp'],
                            $s->columns['to_timestamp'], $s->columns['to_timestamp'], '((%s IS NULL OR %s<=NOW()) AND (%s IS NULL OR NOW()<=%s))'));
    if ($center) $s->addStatement(new SqlStatementTri($s->columns['center'], $s->columns['center'], $center, '(%s IS NULL OR %s=%s)'));
    if ($price!==null) $s->addStatement(new SqlStatementBi($s->columns['value'], $price, '%s>=%s'));
    $s->setColumnsMask(array('userticket_id','name','value','subject_tag','center','from_timestamp','to_timestamp'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      if ($row['subject_tag']&&is_array($tag)&&!count(array_intersect($tag,explode(',',$row['subject_tag'])))) continue;
    
      $ret[] = array(
        'id'              => $row['userticket_id'],
        'name'            => $row['name'],
        'validFrom'       => $row['from_timestamp'],
        'validTo'         => $row['to_timestamp'],
        'validForTag'     => $row['subject_tag'],
        'validForCenter'  => $row['center'],
        'value'           => $this->_app->regionalSettings->convertNumberToHuman($row['value'],2),
        'valueRaw'        => $row['value'],
        'currency'        => $this->_app->textStorage->getText('label.currency_CZK'),
      );
    }
    
    return $ret;
  }
  
  public function changeTicket($provider, $ticket, $change, $type='RESERVATION', $description=null) {
    if (!$this->_checkCreditAccess($provider,$type)) throw new ExceptionUserTextStorage('error.accessDenied');
    
    $s = new SUserTicket;
    $s->addStatement(new SqlStatementBi($s->columns['user'], $this->_id, '%s=%s'));
    $s->addStatement(new SqlStatementBi($s->columns['userticket_id'], $ticket, '%s=%s'));
    $s->setColumnsMask(array('userticket_id','value','from_timestamp','to_timestamp'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($row = $this->_app->db->fetchAssoc($res)) {
      $now = date('Y-m-d H:i:s');
      if (($row['from_timestamp']&&($now<$row['from_timestamp']))||($row['to_timestamp']&&($row['to_timestamp']<$now))) throw new ExceptionUserTextStorage('error.saveUserTicket_notValid');
      if ($row['value']+$change<0) throw new ExceptionUserTextStorage('error.saveUserTicket_notEnoughResources');
      
      $this->_app->db->beginTransaction();
      
      $o = new OUserTicket($row['userticket_id']);
      $o->setData(array('value'=>$row['value']+$change));
      $o->save();
      
      $o = new OUserTicketJournal;
      $o->setData(array(
        'userticket'              => $row['userticket_id'],
        'amount'                  => $change,
        'change_timestamp'        => date('Y-m-d H:i:s'),
        'change_user'             => $this->_app->auth->getUserId(),
        'flag'                    => $change>0?'C':'D',
        'type'                    => $type,
        'note'                    => $description,
        ));
      $o->save();
    
      $this->_app->db->commitTransaction();
    } else {
      throw new ExceptionUserTextStorage('error.saveUserTicket_invalidTicket');
    }
  }

  public function getAvailableVoucher($provider, $price=null, $center=null, $tag=null, $timeValidation=true, $applicationValidation=true) {
    $ret = array();

    $s = new SVoucher;
    $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
    $s->addStatement(new SqlStatementBi($s->columns['provider'], $provider, '%s=%s'));
    if ($center) $s->addStatement(new SqlStatementTri($s->columns['center'], $s->columns['center'], $center, '(%s IS NULL OR %s=%s)'));
    if ($timeValidation) {
      $s->addStatement(new SqlStatementQuad($s->columns['validity_from'], $s->columns['validity_from'],
        $s->columns['validity_to'], $s->columns['validity_to'], '((%s IS NULL OR %s<=NOW()) AND (%s IS NULL OR NOW()<=%s))'));
    }
    if ($applicationValidation) {
      $s->addStatement(new SqlStatementTri($s->columns['application_total'], $s->columns['application_total'], $s->columns['reservation_count'], '(%s IS NULL OR %s>%s)'));
    }
    $s->setColumnsMask(array('voucher_id','name','code','subject_tag','discount_amount','discount_proportion',
      'validity_from','validity_to','application_user'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      if ($row['subject_tag']&&is_array($tag)&&!count(array_intersect($tag,explode(',',$row['subject_tag'])))) continue;
      if ($applicationValidation&&$row['application_user']) {
        $s = new SReservation;
        $s->addStatement(new SqlStatementBi($s->columns['user'], $this->_id, '%s=%s'));
        $s->addStatement(new SqlStatementBi($s->columns['voucher'], $row['voucher_id'], '%s=%s'));
        $s->addStatement(new SqlStatementMono($s->columns['cancelled'], "%s IS NULL"));
        $s->setColumnsMask(array('reservation_id'));
        $res1 = $this->_app->db->doQuery($s->toString());
        if ($this->_app->db->getRowsNumber($res1)>=$row['application_user']) continue;
      }

      $b = new BVoucher($row['voucher_id']);
      $discount = $b->getDiscount($price);

      $ret[$row['voucher_id']] = array(
        'id'                      => $row['voucher_id'],
        'name'                    => $row['name'],
        'code'                    => $row['code'],
        'validFrom'               => $row['validity_from'],
        'validTo'                 => $row['validity_to'],
        'discountType'            => $row['discount_amount']?'AMOUNT':'PROPORTION',
        'discountValue'           => $row['discount_amount']?$row['discount_amount']:$row['discount_proportion'],
        'calculatedDiscountRaw'   => $discount,
        'calculatedDiscount'      => $this->_app->regionalSettings->convertNumberToHuman($discount,2),
        'currency'                => $this->_app->textStorage->getText('label.currency_CZK'),
      );
    }

    return $ret;
  }
  
  public function saveOrganiser($providerId) {
    $this->_app->db->beginTransaction();
    
    $s = new SUserRegistration;
    $s->addStatement(new SqlStatementBi($s->columns['user'], $this->_id, '%s=%s'));
    $s->addStatement(new SqlStatementBi($s->columns['provider'], $providerId, '%s=%s'));
    $s->setColumnsMask(array('userregistration_id'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($row = $this->_app->db->fetchAssoc($res)) {
      $o = new OUserRegistration($row['userregistration_id']);
      $o->setData(array('organiser'=>'Y'));
      $o->save();
     
      #$this->updateGlobalRole();
    }
    
    $this->_app->db->commitTransaction();
  }
  
  /*public function updateGlobalRole() {
    $this->_app->db->beginTransaction();
    
    $o = new OUser($this->_id);
    $oData = array('organiser'=>'N','provider'=>'N');
    
    $s = new SUserRegistration;
    $s->addStatement(new SqlStatementBi($s->columns['user'], $this->_id, '%s=%s'));
    $s->setColumnsMask(array('admin','reception','organiser','power_organiser'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      if ($row['admin']=='Y') $oData['provider'] = 'Y';
      if ($row['reception']=='Y') $oData['provider'] = 'Y';
      if ($row['organiser']=='Y') $oData['organiser'] = 'Y';
      if ($row['power_organiser']=='Y') {
        $oData['provider'] = 'Y';
        $oData['organiser'] = 'Y';
      }
    
      if (($oData['provider']=='Y')&&($oData['organiser']=='Y')) break;
    }
    
    $o->setData($oData);
    $o->save();
    
    $this->_app->db->commitTransaction();
  }*/
  
  public function getAttribute($provider, $language=null, $accountType=null, $includeRestricted=array('READONLY','CREATEONLY'), $includeDisabled=false) {
    $attribute = array();

    // nactu vsechny vyplnene atributy uzivatele
    $ua = array();
    $s = new SUserAttribute;
    $s->addStatement(new SqlStatementBi($s->columns['user'], $this->_id, '%s=%s'));
    if ($provider!==false) $s->addStatement(new SqlStatementBi($s->columns['provider'], $provider, '%s=%s'));
    $s->setColumnsMask(array('attribute','value'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $ua[$row['attribute']] = $row['value'];
    }

    if ($includeRestricted) {
      if (!is_array($includeRestricted)) $includeRestricted = array($includeRestricted);
      $includeRestrictedString = '';
      foreach ($includeRestricted as $item) {
        if ($includeRestrictedString) $includeRestrictedString .= ',';
        $includeRestrictedString .= "'".$this->_app->db->escapeString($item)."'";
      }
    }
    
    // nactu vsechny atributy evidovane u poskytovatele
    $s = new SAttribute;
    if ($provider!==false) $s->addStatement(new SqlStatementBi($s->columns['provider'], $provider, '%s=%s'));
    $s->addStatement(new SqlStatementMono($s->columns['applicable'], "%s='USER'"));
    if ($accountType) $s->addStatement(new SqlStatementTri($s->columns['applicable_type'], $s->columns['applicable_type'], $accountType, '(%s IS NULL OR %s=%s)'));
    if (!$includeDisabled) $s->addStatement(new SqlStatementMono($s->columns['disabled'], "%s<>'Y'"));
    if (!$includeRestricted) $s->addStatement(new SqlStatementMono($s->columns['restricted'], '%s IS NULL'));
    else $s->addStatement(new SqlStatementBi($s->columns['restricted'], $s->columns['restricted'], sprintf('(%%s IS NULL OR %%s IN (%s))', $includeRestrictedString)));
    #$s->addOrder(new SqlStatementAsc($s->columns['category']));
    $s->addOrder(new SqlStatementAsc($s->columns['sequence']));
    $s->setColumnsMask(array('provider','customer_name','attribute_id','short_name','url','restricted','disabled','mandatory','category','sequence','type','allowed_values'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $s1 = new SAttributeName;
      $s1->addStatement(new SqlStatementBi($s1->columns['attribute'], $row['attribute_id'], '%s=%s'));
      $s1->setColumnsMask(array('lang','name'));
      $res1 = $this->_app->db->doQuery($s1->toString());
      $name = array(); while ($row1 = $this->_app->db->fetchAssoc($res1)) { $name[$row1['lang']] = $row1['name']; }
      $name = ifsetor($name[$language], array_values($name)[0]);
      
      $attribute[$row['attribute_id']] = array(
        'attributeId'       => $row['attribute_id'],
        'providerId'        => $row['provider'],
        'providerName'      => $row['customer_name'],
        'name'              => $name,
        'shortName'         => $row['short_name'],
        'url'               => $row['url'],
        'restricted'        => $row['restricted'],
        'disabled'          => $row['disabled'],
        'mandatory'         => $row['mandatory'],
        'category'          => $row['category'],
        'sequence'          => $row['sequence'],
        'type'              => $row['type'],
        'allowedValues'     => !strcmp($row['type'],'LIST')?$row['allowed_values']:null,
        'value'             => ifsetor($ua[$row['attribute_id']]),
      );
      
      if (!strcmp($row['type'],'FILE')&&isset($ua[$row['attribute_id']])) {
        $s2 = new SFile;
        $s2->addStatement(new SqlStatementBi($s2->columns['file_id'], $ua[$row['attribute_id']], '%s=%s'));
        $s2->setColumnsMask(array('hash','name'));
        $res2 = $this->_app->db->doQuery($s2->toString());
        $row2 = $this->_app->db->fetchAssoc($res2);
        
        $attribute[$row['attribute_id']]['valueId'] = $row2['hash'];
        $attribute[$row['attribute_id']]['value'] = $row2['name'];
      }
    }
    
    return $attribute;
  }
  
  private function _validateAttribute($params) {
    global $TMP_DIR;
    
    $extendedAttributes = array();
    
    foreach ($params['attribute'] as $id=>$value) {
      $s = new SAttribute;
      $s->addStatement(new SqlStatementBi($s->columns['attribute_id'], $id, '%s=%s'));
      $s->setColumnsMask(array('attribute_id','type','mandatory','allowed_values'));
      $res = $this->_app->db->doQuery($s->toString());
      $row = $this->_app->db->fetchAssoc($res);
      
      $attribute = array('id'=>$id,'type'=>$row['type'],'value'=>$value);
      
      if (isset($params['attributeValidation'])&&$params['attributeValidation']) {      
        $s1 = new SAttributeName;
        $s1->addStatement(new SqlStatementBi($s1->columns['attribute'], $row['attribute_id'], '%s=%s'));
        $s1->setColumnsMask(array('lang','name'));
        $res1 = $this->_app->db->doQuery($s1->toString());
        $name = array(); while ($row1 = $this->_app->db->fetchAssoc($res1)) { $name[$row1['lang']] = $row1['name']; }
        $name = ifsetor($name[ifsetor($params['attributeLanguage'])], array_values($name)[0]);
        if (($row['mandatory']=='Y')&&!strcmp($params['attributeValidation'],'exact')&&!$value)
          throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveUser_attributeMissingValue'), $name));
        
        if ($value) {
          $notConverted = isset($params['attributeConverted'])&&!$params['attributeConverted'];
          switch ($row['type']) {
            case 'DATE': if ($notConverted) {
                            if (!$this->_app->regionalSettings->checkHumanDate($value)) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveUser_attributeInvalidDate'), $name));
                            $attribute['value'] = $this->_app->regionalSettings->convertHumanToDate($value);
                         } elseif (!$this->_app->regionalSettings->checkDate($value)) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveUser_attributeInvalidDate'), $name));
                         break;
            case 'TIME': if ($notConverted) {
                            if (!$this->_app->regionalSettings->checkHumanTime($value,'h:m')) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveUser_attributeInvalidTime'), $name));
                            $attribute['value'] = $this->_app->regionalSettings->convertHumanToTime($value,'h:m');
                         } elseif (!$this->_app->regionalSettings->checkTime($value)) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveUser_attributeInvalidTime'), $name));
                         break;
            case 'DATETIME':
                         if ($notConverted) {
                            if (!$this->_app->regionalSettings->checkHumanDateTime($value)) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveUser_attributeInvalidDatetime'), $name));
                            $attribute['value'] = $this->_app->regionalSettings->convertHumanToDateTime($value);
                         } elseif (!$this->_app->regionalSettings->checkDateTime($value)) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveUser_attributeInvalidDatetime'), $name));
                         break;
            case 'NUMBER':
                         if ($notConverted) {
                            if (!$this->_app->regionalSettings->checkHumanNumber($value,20)) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveUser_attributeInvalidNumber'), $name));
                            $attribute['value'] = $this->_app->regionalSettings->convertHumanToNumber($value,20);
                         } elseif (!$this->_app->regionalSettings->checkNumber($value)) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveUser_attributeInvalidNumber'), $name));
                         break;
            case 'DECIMALNUMBER':
                         if ($notConverted) {
                           if (!$this->_app->regionalSettings->checkHumanNumber($value,20,2)) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveUser_attributeInvalidDecimalNumber'), $name));
                           $attribute['value'] = $this->_app->regionalSettings->convertHumanToNumber($value,20,2);
                         } elseif (!$this->_app->regionalSettings->checkNumber($value)) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveUser_attributeInvalidDecimalNumber'), $name));
                         break;
            case 'LIST': if ($value&&!in_array($value,explode(',',$row['allowed_values'])))
                            throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveUser_attributeInvalidList'), $name));
                         break;
            case 'FILE': if ($value&&strcmp($value,'__no_change__')&&!file_exists($TMP_DIR.$value))
                            throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveUser_attributeInvalidFile'), $name));
                         break;
            default: break;
          }
        }
      }
      
      $extendedAttributes[$id] = $attribute;
    }
    
    return $extendedAttributes;
  }
  
  public function saveAttribute($params) {
    $attributes = $this->_validateAttribute($params);
    #adump($params);
    #adump($params['attribute']);
    #adump($attributes);
    #throw new ExceptionUser('FAKE');
    
    $this->_app->db->beginTransaction();
    
    $idsToSave = array();
    foreach ($attributes as $id=>$attribute) {
      $s = new SUserAttribute;
      $s->addStatement(new SqlStatementBi($s->columns['attribute'], $id, '%s=%s'));
      $s->addStatement(new SqlStatementBi($s->columns['user'], $this->_id, '%s=%s'));
      $s->setColumnsMask(array('attribute','value'));
      $res = $this->_app->db->doQuery($s->toString());
      $row = $this->_app->db->fetchAssoc($res);
      
      // pokud je atribut typu soubor
      if (!strcmp($attribute['type'],'FILE')) {
        if (!$attribute['value']) {
          $attribute['value'] = null;
        } elseif (!strcmp($attribute['value'],'__no_change__')) {
          // kdyz se nic nemeni
          $attribute['value'] = $row['value'];
        } else {
          // jinak ulozim nejdriv soubor
          $bF = new BFile(isset($row['value'])?$row['value']:null);
          $fileParams = $bF->getFileFromLink($attribute['value'], true);
          $fileId = $bF->save($fileParams);
          
          $attribute[ 'value'] = $fileId;
        }
      }
      
      if ($row) {
        $o = new OUserAttribute(array('user'=>$this->_id,'attribute'=>$id));
        $o->setData(array('value'=>$attribute['value']));
        $o->save();
      } else {
        $o = new OUserAttribute;
        $o->setData(array('user'=>$this->_id,'attribute'=>$id,'value'=>$attribute['value']));
        $o->save();
      }
      $idsToSave[] = $id;
    }
    
    if (($this->_app->auth->isProvider()||$this->_app->auth->isAdministrator())&&count($this->_app->auth->getAllowedProvider(null,'array'))) {
      $s = new SUserAttribute;
      $s->addStatement(new SqlStatementMono($s->columns['provider'], sprintf('%%s IN (%s)', $this->_app->auth->getAllowedProvider(null,'list'))));
      $s->addStatement(new SqlStatementBi($s->columns['user'], $this->_id, '%s=%s'));
      if (count($idsToSave)) $s->addStatement(new SqlStatementMono($s->columns['attribute'], sprintf('%%s NOT IN (%s)', implode(',', $idsToSave))));
      $s->setColumnsMask(array('attribute','value','type'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $o = new OUserAttribute(array('user'=>$this->_id, 'attribute'=>$row['attribute']));
        $o->delete();
        if (!strcmp($row['type'],'FILE')) {
          $o = new OFile($row['value']);
          $o->delete();
        }
      }
    }
    
    $this->_app->db->commitTransaction();
  }

  private function _generatePrepaymentInvoice($providerId, $userRegistrationId, $creditJournalId) {
    $documentStruct = BCustomer::generateAccountingDocumentNumber($providerId, 'prepayment_invoice');

    if ($documentStruct['counter']) {
      $o = new OProvider($providerId);
      $o->setData(array('document_year' => $documentStruct['year'], 'prepayment_invoice_counter' => $documentStruct['counter']));
      $o->save();

      $documentGui = new GuiUserPrepaymentInvoice(array('creditJournal'=>$creditJournalId,'number'=>$documentStruct['number']));
      $file = new BFile;
      $invoiceFile = $file->saveFromString(array('content'=>$documentGui->render()));

      $o = new OPrepaymentInvoice();
      $o->setData(array(
        'number'            => $documentStruct['number'],
        'userregistration'  => $userRegistrationId,
        'creditjournal'     => $creditJournalId,
        'content'           => $invoiceFile,
      ));
      $o->save();

      $ret = $o->getId();
    } else $ret = null;

    return $ret;
  }

  public function getSubaccount() {
    if (($this->_app->auth->getUserId()!=$this->_id)&&
        !$this->_app->auth->haveRight('user_admin', $this->_app->auth->getActualProvider())&&
        !$this->_app->auth->haveRight('power_organiser', $this->_app->auth->getActualProvider())&&
        !$this->_app->auth->haveRight('organiser', $this->_app->auth->getActualProvider())) {
      throw new ExceptionUserTextStorage('error.accessDenied');
    }

    $includeParent = false;

    $ret = array();

    $s = new SUser;
    if ($includeParent) $s->addStatement(new SqlStatementQuad($s->columns['parent_user'], $this->_id, $s->columns['user_id'], $this->_id, '(%s=%s OR %s=%s)'));
    else $s->addStatement(new SqlStatementBi($s->columns['parent_user'], $this->_id, '%s=%s'));
    if (!$this->_app->auth->isAdministrator()) $s->addStatement(new SqlStatementBi($s->columns['registration_provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
    $s->addOrder(new SqlStatementAsc($s->columns['lastname']));
    $s->addOrder(new SqlStatementAsc($s->columns['firstname']));
    $s->addOrder(new SqlStatementAsc($s->columns['email']));
    $s->setColumnsMask(array('user_id','fullname_reversed','email'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $name = $row['fullname_reversed'];
      if ($row['email']) $name .= sprintf(' (%s)', $row['email']);
      $ret[$row['user_id']] = array('id'=>$row['user_id'],'name'=>$name);
    }

    return $ret;
  }
}

?>
