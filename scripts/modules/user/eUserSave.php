<?php

class ModuleUserSave extends ExecModule {

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
  
  private function _saveNewAttribute($validator) {
    $newAttr = $this->_app->request->getParams('newAttribute');
    //adump($newReg);die;
    
    $attr = array();
    if (is_array($newAttr)) {
      foreach ($newAttr as $key=>$aLine) {
        $aParams = explode(';',$aLine);
        
        $params = array();
        foreach ($aParams as $par) {
          list($name,$value) = explode(':~',$par);
          $params[$name] = $value;
        }
        $attr[$key] = $params;
      }
    }
    
    $validator->setValues(array('attribute'=>$attr));
  }
  
  private function _getAttribute($validator) {
    $attribute = array();
    foreach ($validator->getVarValue('attribute') as $key=>$a) {
      // kdyz je atribut soubor a nezmenil se, poslu spec. hodnotu, aby se na nic s timto atributem nedelalo
      if (!strcmp($a['type'],'FILE')) {
        if (!isset($a['changed'])) $attribute[$a['attributeId']] = '__no_change__';
        else $attribute[$a['attributeId']] = $a['fileId'];
      } else $attribute[$a['attributeId']] = $a['value'];
    }
    
    return $attribute;
  }

  protected function _userRun() {
    $subaccountEdit = $this->_app->request->getParams('subaccount');

    $validator = Validator::get($subaccountEdit?'userSubaccount':'user','UserValidator');
    $validator->initValues();

    $this->_saveNewRegistration($validator);
    $this->_saveNewAttribute($validator);
    
    $validator->validateValues();

    //if (!$validator->getVarValue('id')&&!$validator->getVarValue('password')) {
    //  throw new ExceptionUserTextstorage('error.saveUser_emptyPassword');
    //}
    if ($validator->getVarValue('passwordEdit') != $validator->getVarValue('retypePasswordEdit')) {
      throw new ExceptionUserTextstorage('error.saveUser_passwordNoMatch');
    }
    if (($validator->getVarValue('subaccountUser')=='Y')&&!$validator->getVarValue('parent')) {
      throw new ExceptionUserTextstorage('error.saveUser_noParent');
    }

    $attribute = $this->_getAttribute($validator);

    $userId = $validator->getVarValue('id');    
    $bUser = new BUser($userId?$userId:null);
    $data = array(
        'parent'                => $validator->getVarValue('parent'),
        'firstname'             => $validator->getVarValue('firstname'),
        'lastname'              => $validator->getVarValue('lastname'),
        'street'                => $validator->getVarValue('street'),
        'city'                  => $validator->getVarValue('city'),
        'postalCode'            => $validator->getVarValue('postalCode'),
        'state'                 => $validator->getVarValue('state'),
        'phone'                 => $validator->getVarValue('phone'),
        'registration'          => $validator->getVarValue('registration'),
        'attribute'             => $attribute,
        'attributeConverted'    => false,
        );
    if (!$validator->getVarValue('myProfile')) {
      $data['email'] = $validator->getVarValue('email');
      $data['reservationConditionId'] = $validator->getVarValue('reservationConditionId');
    }
    if ($this->_app->auth->isAdministrator()) {
      $data['username'] = $validator->getVarValue('username')?$validator->getVarValue('username'):$validator->getVarValue('email');
      if ($pwd = $validator->getVarValue('passwordEdit')) $data['password'] = $pwd;
      $data['admin'] = $validator->getVarValue('admin');
    }
    if ($validator->getVarValue('myProfile')||$this->_app->auth->isAdministrator()) {
      $data['facebookId'] = $validator->getVarValue('facebookId');
      $data['googleId'] = $validator->getVarValue('googleId');
      $data['twitterId'] = $validator->getVarValue('twitterId');
    }
    
    #adump($validator->getVarValue('fromEvent'));
    #adump($data);die;
    $id = $bUser->save($data);

    $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.editUser_saveOk'),
                                                          $validator->getVarValue('firstname').' '.$validator->getVarValue('lastname')));
    
    if ($validator->getVarValue('fromReservation')) {
      $rValidator = Validator::get('reservation', 'ReservationValidator');
      $rValidator->setValues(array('userId'=>$id,'userName'=>$validator->getVarValue('firstname').' '.$validator->getVarValue('lastname')));
      
      $this->_app->response->addParams(array('backwards'=>2));
    } elseif ($validator->getVarValue('fromEvent')) {
      $validator = Validator::get('event', 'EventValidator');
      $bUser->saveOrganiser($validator->getVarValue('providerId'));
      $validator->setValues(array('organiserId'=>$id));
      
      $this->_app->response->addParams(array('backwards'=>2));
    } elseif ($validator->getVarValue('fromEventGroup')) {
      $validator = Validator::get('eventGroup', 'EventGroupValidator');
      $bUser->saveOrganiser($validator->getVarValue('providerId'));
      $validator->setValues(array('organiserId'=>$id));
      
      $this->_app->response->addParams(array('backwards'=>2));
    } elseif ($validator->getVarValue('fromEventSubstitute')) {
      $esValidator = Validator::get('eventSubstitute', 'EventSubstituteValidator');
      $esValidator->setValues(array('userId'=>$id,'userName'=>$validator->getVarValue('firstname').' '.$validator->getVarValue('lastname')));
      
      $this->_app->response->addParams(array('backwards'=>2));
    } elseif ($validator->getVarValue('fromCustomerEmployee')) {
      $cValidator = Validator::get('customer', 'CustomerValidator');
      $employee = $cValidator->getVarValue('employee');
      if (!is_array($employee)) $employee = array();
      $employee[] = array(
              'employeeId'    => null,
              'userId'        => $id,
              'fullname'      => $validator->getVarValue('firstname').' '.$validator->getVarValue('lastname'),
              'email'         => $validator->getVarValue('email'),
              'creditAccess'  => 'N',
              );
      $cValidator->setValues(array('employee'=>$employee));
      
      $this->_app->response->addParams(array('backwards'=>2));
    } elseif ($validator->getVarValue('fromCustomerCoworker')) {
      $cValidator = Validator::get('customer', 'CustomerValidator');
      $coworker = $cValidator->getVarValue('coworker');
      if (!is_array($coworker)) $coworker = array();
      $coworker[] = array(
              'coworkerId'    => null,
              'userId'        => $id,
              'fullname'      => $validator->getVarValue('firstname').' '.$validator->getVarValue('lastname'),
              'email'         => $validator->getVarValue('email'),
              'admin'         => 'N',
              'reception'     => 'N',
              'organiser'     => 'N',
              );
      $cValidator->setValues(array('coworker'=>$coworker));
      
      $this->_app->response->addParams(array('backwards'=>2));
    }

    // aby se po ulozeni poductu z hlavniho uctu zobrazil seznam poductu
    if ($subaccountEdit) setcookie('ui-user-tab', 3);
                                                          
    return 'eBack';
  }
}

?>
