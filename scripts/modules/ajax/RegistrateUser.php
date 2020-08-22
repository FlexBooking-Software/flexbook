<?php

class AjaxRegistrateUser extends AjaxAction {

  protected function _userRun() {
    switch ($this->_params['step']) {
      case 1:
        // vyhledani uzivatele podle emailu
        if (!isset($this->_params['email'])||!$this->_params['email']) throw new ExceptionUserTextStorage('error.ajax_profile_emailMissing');
        if (!$this->_app->regionalSettings->checkEmail($this->_params['email'])) throw new ExceptionUserTextStorage('error.ajax_profile_emailInvalid');
      
        $s = new SUser;
        $s->addStatement(new SqlStatementBi($s->columns['registration_provider'], $this->_params['provider'], '%s=%s'));
        $s->addStatement(new SqlStatementBi($s->columns['email'], $this->_params['email'], '%s=%s'));
        $s->setColumnsMask(array('user_id','validated','disabled'));
        $res = $this->_app->db->doQuery($s->toString());
        if ($row = $this->_app->db->fetchAssoc($res)) {
          $this->_result = array('step'=>2);
          if ($row['disabled']=='Y') $this->_result['userDisabled'] = true;
          elseif (!$row['validated']) $this->_result['userNotValidated'] = true;
        } else {
          $this->_result = array('step'=>3);     
        }
        
        break;
      case 'sendPassword':
        if (!isset($this->_params['email'])||!$this->_params['email']) throw new ExceptionUserTextStorage('error.ajax_profile_emailMissing');
        
        $b = new BUser;
        $b->sendPassword($this->_params['email'], $this->_params['provider']);
        
        $this->_result = array();
        
        break;
      case 2:
        // kontrola uzivatele podle emailu a hesla
        if (!isset($this->_params['email'])||!$this->_params['email']) throw new ExceptionUserTextStorage('error.ajax_profile_emailMissing');
        if (!isset($this->_params['password'])||!$this->_params['password']) throw new ExceptionUserTextStorage('error.ajax_profile_passwordMissing');

        // najdu uzivatele s danymi prihlasovacimi udaji
        $s = new SUser;
        $s->addStatement(new SqlStatementBi($s->columns['registration_provider'], $this->_params['provider'], '%s=%s'));
        $s->addStatement(new SqlStatementBi($s->columns['username'], strtoupper($this->_params['email']), 'UPPER(%s)=%s'));
        $s->addStatement(new SqlStatementBi($s->columns['password'], $this->_app->auth->getMd5Password()?md5(addslashes($this->_params['password'])):$this->_params['password'], '%s=%s'));
        $s->setColumnsMask(array('user_id','firstname','lastname','street','city','postal_code','state','email','phone',
                                 'facebook_id','google_id','twitter_id'));
        $res = $this->_app->db->doQuery($s->toString());
        if (!$row = $this->_app->db->fetchAssoc($res)) throw new ExceptionUserTextStorage('error.ajax_profile_invalidPassword');
        
        $this->_result = array(
              'userid'          => $row['user_id'],
              'email'           => $row['email'],
              'firstname'       => $row['firstname'],
              'lastname'        => $row['lastname'],
              'street'          => $row['street'],
              'city'            => $row['city'],
              'postalCode'      => $row['postal_code'],
              'state'           => $row['state'],
              'phone'           => $row['phone'],
              'facebookId'      => $row['facebook_id'],
              'googleId'        => $row['google_id'],
              'twitterId'       => $row['twitter_id'],
              'popup'           => null,
              'step'            => 3,
              );
        
        // zkontroluju, jestli uz neni uzivatel u poskytovatele registrovan
        $s = new SUserRegistration;
        $s->addStatement(new SqlStatementBi($s->columns['user'], $row['user_id'], '%s=%s'));
        $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_params['provider'], '%s=%s'));
        $s->setColumnsMask(array('userregistration_id'));
        $res = $this->_app->db->doQuery($s->toString());
        if ($this->_app->db->getRowsNumber($res)) {
          $this->_result['step'] = 'done';
          $this->_result['popup'] .= $this->_app->textStorage->getText('label.ajax_profile_alreadyExists');
          
          $ret = $this->_app->auth->authenticate(array('username'=>$this->_params['email'],
                                                       'password'=>$this->_params['password'],
                                                       'provider'=>$this->_params['provider']));
          if ($ret) {
            $this->_result['username'] = $this->_app->auth->getFullname();
            $this->_result['useremail'] = $this->_app->auth->getEmail();
            $this->_result['sessionid'] = $this->_app->session->getId();
            
            $this->_result['popup'] .= $this->_app->textStorage->getText('label.ajax_profile_loginOk');
          } else {
            // prihlaseni se nepovedlo, zrusim id uzivatele
            $this->_result['userid'] = null;
          
            $this->_result['popup'] .= $this->_app->textStorage->getText('label.ajax_profile_loginFailed');
          }
        }
        
        break;
      case 3:
        // ulozeni registrace uzivatele
        if (!$this->_params['userid']) {
          // kdyz se zaklada novy uzivatel, musi zadat heslo nebo mit externi ucet
          if (!$this->_params['password']&&!$this->_params['facebookId']&&
              !$this->_params['googleId']&&!$this->_params['twitterId']) throw new ExceptionUserTextstorage('error.ajax_profile_passwordMissing');
          if ($this->_params['password']!=$this->_params['retype_password']) throw new ExceptionUserTextstorage('error.ajax_profile_passwordNoMatch');
        }
        
        $saveData = array(
            'firstname'             => $this->_params['firstname'],
            'lastname'              => $this->_params['lastname'],
            'street'                => $this->_params['street'],
            'city'                  => $this->_params['city'],
            'postalCode'            => $this->_params['postalCode'],
            'state'                 => $this->_params['state'],
            'email'                 => $this->_params['email'],
            'phone'                 => $this->_params['phone'],
            'disabled'              => 'N',
            'facebookId'            => $this->_params['facebookId'],
            'googleId'              => $this->_params['googleId'],
            'twitterId'             => $this->_params['twitterId'],
          );
        if ($pwd = ifsetor($this->_params['password'])) {
          $saveData['password'] = $pwd;
        }
        
        // registrace u posytovatele podle stranek
        $saveData['registration'] = array(array(
            'providerId'    => $this->_params['provider'],
            'advertising'   => $this->_params['advertising'],
          ));
        
        // atributy zakaznika
        $saveData['attributeLanguage'] = $this->_app->language->getLanguage();
        $saveData['attributeConverted'] = false;
        $saveData['attribute'] = array();
        if (isset($this->_params['attribute'])&&is_array($this->_params['attribute'])) {
          $saveData['attributeValidation'] = (isset($this->_params['checkAttributeMandatory'])&&$this->_params['checkAttributeMandatory'])?'exact':true;
          foreach ($this->_params['attribute'] as $attr) {
            $saveData['attribute'][$attr['id']] = $attr['value'];
          }
        }
        
        //adump($saveData);die;
        $b = new BUser($this->_params['userid']?$this->_params['userid']:null);
        $validated = $b->registrate($saveData);
        
        $this->_result['popup'] = '';
        
        if ($validated==2) {
          // kdyz se registroval uplne novy uzivatel, je potreba registraci validovat
          $this->_result['popup'] .= $this->_app->textStorage->getText('label.ajax_profile_needsValidation');
        } else {
          if ($ret = $this->_app->auth->authenticate(array('username'=>$this->_params['username'],'password'=>$this->_params['password'],'provider'=>$this->_params['provider'],
            'facebook'=>$this->_params['facebookId'],'google'=>$this->_params['googleId'],'twitter'=>$this->_params['twitterId']))) {
            $this->_result['userid'] = $this->_app->auth->getUserId();
            $this->_result['username'] = $this->_app->auth->getFullname();
            $this->_result['useremail'] = $this->_app->auth->getEmail();
            $this->_result['sessionid'] = $this->_app->session->getId();
            
            $this->_result['popup'] .= $this->_app->textStorage->getText('label.ajax_profile_registrationOk');
          } else {
            $this->_result['popup'] .= $this->_app->textStorage->getText('label.ajax_profile_registrationAuthError');
          }
        }
        
        break;
      default: throw new ExceptionUserTextStorage('error.ajax_profile_registrationError');
    }
  }
}

?>
