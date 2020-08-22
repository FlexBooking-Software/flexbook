<?php

class ModuleInPageRegistrationSave extends ExecModule {
  private $_validator;

  private function _prepareReturnAction($type) {
    $ret = 'vInPageRegistration';
    if ($a = $this->_validator->getVarValue($type)) {
      parseNextActionFromString($module, $params, $a);
      if (is_array($params)&&count($params)) $this->_app->response->addParams($params);
      $ret = $module;
    }
    
    return $ret;
  }
  
  private function _saveAttributeValues() {
    $attribute = $this->_validator->getVarValue('attribute');
    foreach ($this->_validator->getVarValue('newAttributeValue') as $id=>$value) {
      $attribute[$id]['value'] = $value;
    }
    $this->_validator->setValues(array('attribute'=>$attribute));
  }

  protected function _userRun() {
    $iVal = Validator::get('inpage', 'InPageValidator');
    
    $this->_validator = Validator::get('registration', 'InPageRegistrationValidator');
    $this->_validator->initValues();
    
    $nextAction = $this->_app->request->getParams('nextAction');
    if ($nextAction!='goBack') $this->_validator->validateLastValues();
    
    $this->_saveAttributeValues();
    
    $data = $this->_validator->getValues();
    switch ($nextAction) {
      case 'goBack':
        $step = $this->_validator->getVarValue('step');
        $step--;
        if ($this->_validator->getVarValue('skipStep2')&&($step==2)) $step--;
        $this->_validator->setValues(array('step'=>$step));
        
        break;
      case 'validateEmail':
        // najdu zakaznika s danym emailem
        $s = new SUser;
        $s->addStatement(new SqlStatementBi($s->columns['email'], $data['email'], '%s=%s'));
        $s->setColumnsMask(array('user_id'));
        $res = $this->_app->db->doQuery($s->toString());
        if ($this->_app->db->getRowsNumber($res)) {
          $this->_validator->setValues(array('step'=>2,'username'=>$data['email']));
        } else {
          $bU = new BUser;
          $attribute = $bU->getAttribute($iVal->getVarValue('providerId'), $this->_app->language->getLanguage());
          $this->_validator->setValues(array('step'=>3,'skipStep2'=>1,'username'=>$data['email'],'attribute'=>$attribute));     
        }
        
        break;
      case 'sendPassword':
        $b = new BUser;
        $b->sendPassword($data['email'], $iVal->getVarValue('providerId'));
        $this->_app->messages->addMessage('userInfo', $this->_app->textStorage->getText('label.inpage_registration_passwordSent'));
        
        break;
      case 'validateAccount':
        if (!$this->_validator->getVarValue('validationPassword')) throw new ExceptionUserTextStorage('error.inpage_registration_passwordMissing');
        $this->_validator->getVar('validationPassword')->validateValue();
        
        // najdu uzivatele s danymi prihlasovacimi udaji
        $s = new SUser;
        $s->addStatement(new SqlStatementBi($s->columns['username'], $data['email'], '%s=%s'));
        $s->addStatement(new SqlStatementBi($s->columns['password'], $data['validationPassword'], '%s=%s'));
        $s->addStatement(new SqlStatementBi($s->columns['email'], $data['email'], '%s=%s'));
        $s->setColumnsMask(array('user_id','name','firstname','lastname','street','city','postal_code','state','username','email','phone'));
        $res = $this->_app->db->doQuery($s->toString());
        if (!$row = $this->_app->db->fetchAssoc($res)) throw new ExceptionUserTextStorage('label.inpage_registration_invalidPassword');
        
        // zkontroluju, jestli uz neni uzivatel u poskytovatele registrovan
        $s = new SUserRegistration;
        $s->addStatement(new SqlStatementBi($s->columns['user'], $row['user_id'], '%s=%s'));
        $s->addStatement(new SqlStatementBi($s->columns['provider'], $iVal->getVarValue('providerId'), '%s=%s'));
        $s->setColumnsMask(array('userregistration_id'));
        $res = $this->_app->db->doQuery($s->toString());
        if ($this->_app->db->getRowsNumber($res)) {
          $this->_app->messages->addMessage('userInfo', $this->_app->textStorage->getText('label.inpage_registration_alreadyExists'));
          
          $ret = $this->_app->auth->authenticate(array('provider'=>$iVal->getVarValue('providerId'),'username'=>$data['email'],'password'=>$data['validationPassword']));
          if ($ret) $this->_app->messages->addMessage('userInfo', $this->_app->textStorage->getText('label.inpage_registration_loginOk'));
          else $this->_app->messages->addMessage('userError', $this->_app->textStorage->getText('label.inpage_registration_loginFailed'));
          
          $action = $this->_prepareReturnAction('finishAction');
          return $action;
        }
        
        $bU = new BUser($row['user_id']);
        $attribute = $bU->getAttribute($iVal->getVarValue('providerId'), $this->_app->language->getLanguage());
        $this->_validator->setValues(array(
             'userId'         => $row['user_id'],
             'firstname'      => $row['firstname'],
             'lastname'       => $row['lastname'],
             'email'          => $row['email'],
             'phone'          => $row['phone'],
             'street'         => $row['street'],
             'city'           => $row['city'],
             'postalCode'     => $row['postal_code'],
             'state'          => $row['state'],
             'password'       => '',
             'retypePassword' => '',
             'step'           => 3,
             'attribute'      => $attribute,
             ));     
        
        break;
      default:
        if (!$data['userId']) {
          // kdyz se zaklada novy uzivatel, musi zadat heslo
          if (!$data['password']) throw new ExceptionUserTextstorage('error.inpage_registration_passwordMissing');
          if ($data['password']!=$data['retypePassword']) throw new ExceptionUserTextstorage('error.inpage_registration_passwordNoMatch');
        }
        
        $saveData = array(
            'firstname'             => $data['firstname'],
            'lastname'              => $data['lastname'],
            'street'                => $data['street'],
            'city'                  => $data['city'],
            'postalCode'            => $data['postalCode'],
            'state'                 => $data['state'],
            'email'                 => $data['email'],
            'phone'                 => $data['phone'],
            'disabled'              => 'N',
          );
        if ($pwd = $data['password']) {
          $saveData['password'] = $pwd;
        }
        
        // registrace u posytovatele podle stranek
        $saveData['registration'] = array(array(
            'providerId'    => $iVal->getVarValue('providerId'),
            'advertising'   => $data['advertising'],
            'organiser'     => 'N',
            'admin'         => 'N',
          ));
        
        // attributy
        $attribute = array();
        foreach ($data['attribute'] as $id=>$attr) {
          if (!strcmp($attr['type'],'FILE')&&(!isset($attr['changed']))) $attribute[$id] = '__no_change__';
          else $attribute[$id] = $attr['value'];
        }
        $saveData['attribute'] = $attribute;
        $saveData['attributeConverted'] = false;
        $saveData['attributeLanguage'] = $this->_app->language->getLanguage();
        
        //adump($saveData);die;
        $b = new BUser($data['userId']?$data['userId']:null);
        $b->registrate($saveData);
        
        if (!$data['userId']) {
          // kdyz se pouze rozsirovala registrace k novemu poskytovateli, neni potreba registraci validovat
          $this->_app->messages->addMessage('userInfo', $this->_app->textStorage->getText('label.inpage_registration_needsValidation'));
        } else {
          // kdyz si uzivatel ve formulari meni heslo
          if ($pwd) $data['validationPassword'] = $pwd;
          
          if ($ret = $this->_app->auth->authenticate(array('provider'=>$iVal->getVarValue('providerId'),'username'=>$data['email'],'password'=>$data['validationPassword']))) {
            $this->_app->messages->addMessage('userInfo', $this->_app->textStorage->getText('label.inpage_registration_ok'));
            
            $val = Validator::get('login','InPageLoginValidator');
            $val->setValues(array(
                  'firstname'             => $data['firstname'],
                  'lastname'              => $data['lastname'],
                  'street'                => $data['street'],
                  'city'                  => $data['city'],
                  'postalCode'            => $data['postalCode'],
                  'state'                 => $data['state'],
                  'email'                 => $data['email'],
                  'phone'                 => $data['phone'],
                  ));
          } else {
            $this->_app->messages->addMessage('userInfo', $this->_app->textStorage->getText('label.inpage_registration_authError'));
          }
        }
        
        $action = $this->_prepareReturnAction('finishAction');
        return $action;
    }
    
    $action = $this->_prepareReturnAction('progressAction');
    return $action;
  }
}

?>
