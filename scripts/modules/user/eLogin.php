<?php

class LoginModule extends ExecModule {
  
  protected function _clearExpired() {
    $ret = null;
    
    // login akci se rusi priznak vyprsela session
    // a zkusim zavolat akci pred vyprsenim
    if ($this->_app->session->getExpired()) {
      $nextAction = $this->_app->session->getExpiredAction();
      $params = $this->_app->session->getExpiredActionParams();

      $this->_app->session->removeExpired();

      if ($nextAction) {
        if ($params) $this->_app->response->addParams($params);
        $ret = $nextAction;
      }
    }
    
    return $ret;
  }

  protected function _linkExternalUser($externalType, $userInfo) {
    // pokud neexistuje, zkusim uzivatele najit podle emailu a slinkovat, pripadne zaregistrovat
    $s = new SUser;
    $s->addStatement(new SqlStatementBi($s->columns['registration_provider'], $userInfo['provider'], '%s=%s'));
    $s->addStatement(new SqlStatementBi($s->columns[$externalType.'_id'], $userInfo['id'], '%s=%s'));
    $s->addStatement(new SqlStatementMono($s->columns['disabled'], "%s='N'"));
    $s->addStatement(new SqlStatementMono($s->columns['validated'], '%s IS NOT NULL'));
    $s->setColumnsMask(array('user_id'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($row = $this->_app->db->fetchAssoc($res)) return;

    if (isset($userInfo['provider'])&&$userInfo['provider']&&
      isset($userInfo['id'])&&$userInfo['id']&&
      isset($userInfo['email'])&&$userInfo['email']&&
      isset($userInfo['firstname'])&&$userInfo['firstname']&&
      isset($userInfo['lastname'])&&$userInfo['lastname']) {
      // nejdriv ho zkusim najit podle emailu
      $s = new SUser;
      $s->addStatement(new SqlStatementBi($s->columns['registration_provider'], $userInfo['provider'], '%s=%s'));
      $s->addStatement(new SqlStatementBi($s->columns['email'], $userInfo['email'], '%s=%s'));
      $s->setColumnsMask(array('user_id','username','password','disabled','validated'));
      $res = $this->_app->db->doQuery($s->toString());
      if ($row = $this->_app->db->fetchAssoc($res)) {
        if ($row['disabled']=='N') {
          $o = new OUser($row['user_id']);
          $oData = array($externalType.'_id'=>$userInfo['id']);
          if (!$row['validated']) $oData['validated'] = date('Y-m-d H:i:s');
          $o->setData($oData);
          $o->save();

          // pokud existuje, zkontroluju registraci u poskytovatele
          $s = new SUserRegistration;
          $s->addStatement(new SqlStatementBi($s->columns['user'], $row['user_id'], '%s=%s'));
          $s->addStatement(new SqlStatementBi($s->columns['provider'], $userInfo['provider'], '%s=%s'));
          $s->setColumnsMask(array('userregistration_id'));
          $res = $this->_app->db->doQuery($s->toString());
          if (!$this->_app->db->getRowsNumber($res)) {
            $o = new OUserRegistration;
            $o->setData(array('user'=>$row['user_id'],'provider'=>$userInfo['provider'],'registration_timestamp'=>date('Y-m-d H:i:s'),'credit'=>0));
            $o->save();
          }
        }
      } else {
        // pokud neexistuje, budu delat "neuplnou" registraci
        $regParams = array(
          'email'             => $userInfo['email'],
          'firstname'         => $userInfo['firstname'],
          'lastname'          => $userInfo['lastname'],
          'state'             => 'CZ',
          $externalType.'Id'  => $userInfo['id'],
          'registration'      => array(
            array('providerId' => $userInfo['provider']),
          )
        );
        $bUser = new BUser;
        $bUser->registrate($regParams, true);
      }
    }
  }

  protected function _getExternalUserAuthParams($externalAccountType, $user, $frontendLogin) {
    $authParams = array();

    switch ($externalAccountType) {
      case 'facebook':
        $id = $user['id'];
        list($firstname,$lastname) = explode(' ', $user['name'], 2);
        $email = $user['email'];
        break;
      case 'google':
        $id = $user['id'];
        $firstname = $user['givenName'];
        $lastname = $user['familyName'];
        $email = $user['email'];
        break;
      case 'twitter':
        $id = $user->id;
        list($firstname,$lastname) = explode(' ', $user->name, 2);
        $email = $user->email;
        break;
    }
    if (!$email) throw new ExceptionUser($this->_app->textStorage->getText('error.externalAccount_noEmail'));

    $authParams[$externalAccountType] = $id;
    if ($frontendLogin) {
      $this->_app->auth = new InPageAuth;
      $authParams['provider'] = $this->_app->session->get('account_provider');
    } else {
      $validator = Validator::get('login','LoginValidator');
      if ($provider=$validator->getVarValue('login_provider')) $authParams['provider'] = $provider;
    }

    // pokud se prihlasuje z frontendu a neexistuje, zkusim uzivatele najit podle emailu a slinkovat, pripadne zaregistrovat
    if ($frontendLogin) {
      $this->_linkExternalUser($externalAccountType, array('provider'=>$authParams['provider'],'id'=>$id,'email'=>$email,'firstname'=>$firstname,'lastname'=>$lastname));
    }

    return $authParams;
  }
}

class ModuleLogin extends LoginModule {

  protected function _userRun() {
    $validator = Validator::get('login','LoginValidator');
    $validator->initValues();
    $validator->validateValues();

    if (!$validator->getVarValue('login_username')&&!$validator->getVarValue('login_facebook')&&!$validator->getVarValue('login_google')&&!$validator->getVarValue('login_twitter')) {
      throw new ExceptionUser($this->_app->textStorage->getText('error.login_missingCredentials'));
    }

    $ret = $this->_app->auth->authenticate(array(
          'username'    => $validator->getVarValue('login_username'),
          'password'    => $validator->getVarValue('login_password'),
          'provider'    => $validator->getVarValue('login_provider'),
          'google'      => $validator->getVarValue('login_google'),
          'twitter'     => $validator->getVarValue('login_twitter'),
          'facebook'    => $validator->getVarValue('login_facebook')), $accounts);
    // kdyz se prihlaseni nepovede, vratim se na vLogin
    // nemuzu pouzit std. mechanismus webcore 'eBack' kvuli expirovani session
    // vLogin se neuklada do historie a kazde eBack po spatnem loginu vymaze akci 
    if (!$ret&&!$accounts) {
      $this->_app->messages->addMessage('userError', $this->_app->textStorage->getText('error.authFailed'));
      return 'vLogin';
    } elseif (!$ret) {
      #error_log(sprintf('Interactive login of user %s from %s.', $validator->getVarValue('login_username'), $this->_app->getRemoteAddress()));

      $validator->setValues(array('login_accounts'=>$accounts));
      return 'vLogin';
    }
    
    if ($action = $this->_clearExpired()) return $action;

    if ($backwards = $this->_app->request->getParams('backwards')) $this->_app->response->addParams(array('backwards'=>$backwards));
    return 'eBack';
  }
}

class ModuleReLogin extends LoginModule {

  protected function _userRun() {
    $validator = Validator::get('login','LoginValidator');
    $validator->initValues();
    $validator->setValues(array('login_accounts'=>null));

    return 'vLogin';
  }
}

class ModuleFacebookLogin extends LoginModule {
  
  protected function _userRun() {
    global $FACEBOOK;

    if (!$this->_app->request->getParams('sessid','get')) {
      $state = $this->_app->request->getParams('state');
      $url = sprintf('%s&code=%s&sessid=%s&state=%s', $FACEBOOK['backLoginUrl'], $this->_app->request->getParams('code'), $state, $state);
      header('Location: '. $url);
      die;
    }
    
    require_once $FACEBOOK['apiSrc'];

    $facebook = new Facebook\Facebook(array(
      'app_id'                => $FACEBOOK['appId'],
      'app_secret'            => $FACEBOOK['appSecret'],
      'default_graph_version' => 'v2.7',
    ));
    $helper = $facebook->getRedirectLoginHelper();
    
    try {
      $accessToken = $helper->getAccessToken($FACEBOOK['backLoginUrl']);
    } catch(Facebook\Exceptions\FacebookResponseException $e) {
      // When Graph returns an error
      echo 'Graph returned an error: ' . $e->getMessage();
      exit;
    } catch(Facebook\Exceptions\FacebookSDKException $e) {
      // When validation fails or other local issues
      echo 'Facebook SDK returned an error: ' . $e->getMessage();
      exit;
    }

    if (!isset($accessToken)) {
      if ($helper->getError()) {
        header('HTTP/1.0 401 Unauthorized');
        echo "Error: " . $helper->getError() . "\n";
        echo "Error Code: " . $helper->getErrorCode() . "\n";
        echo "Error Reason: " . $helper->getErrorReason() . "\n";
        echo "Error Description: " . $helper->getErrorDescription() . "\n";
      } else {
        header('HTTP/1.0 400 Bad Request');
        echo 'Bad request';
      }
      exit;
    }

    try {
      // Returns a `Facebook\FacebookResponse` object
      $response = $facebook->get('/me?fields=id,name,email', $accessToken);
    } catch(Facebook\Exceptions\FacebookResponseException $e) {
      echo 'Graph returned an error: ' . $e->getMessage();
      exit;
    } catch(Facebook\Exceptions\FacebookSDKException $e) {
      echo 'Facebook SDK returned an error: ' . $e->getMessage();
      exit;
    }
    
    try {
      $user = $response->getGraphUser();
      #error_log(var_export($user,true));
      if (isset($user['id'])&&$user['id']) {
        $authParams = $this->_getExternalUserAuthParams('facebook', $user, $this->_app->session->get('account_jsReturn'));
        $ret = $this->_app->auth->authenticate($authParams, $accounts);
        if (!$ret) {
          if (!$accounts||$this->_app->session->get('account_jsReturn')) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.facebook_authFailed'), $user['email']));

          // kdyz mam ucet k prihlaseni, zobrazim vyber poskytovatele
          #error_log(sprintf('Facebook login of user %s from %s.', $user['email'], $this->_app->getRemoteAddress()));
          $validator = Validator::get('login','LoginValidator');
          $validator->setValues(array('login_facebook'=>$authParams['facebook'],'login_accounts'=>$accounts));

          return 'vLogin';
        }
        #if (!$this->_app->session->get('account_jsReturn')) error_log(sprintf('Facebook login of user %s from %s.', $user['email'], $this->_app->getRemoteAddress()));

        if ($action = $this->_clearExpired()) return $action;
      }
    } catch (Exception $e) {
      if ($this->_app->session->get('account_jsReturn')) {
        echo sprintf('<body onload="window.onunload = window.opener.postMessage({status:-1,message:\'%s\'},\'*\'); window.close();"/>', str_replace("'","\'",$e->getMessage()));
        die;
      } else throw new ExceptionUser($e->getMessage());
    }
   
    if ($this->_app->session->get('account_jsReturn')) {
      echo sprintf('<body onload="window.onunload = window.opener.postMessage({status:0,type:\'login\',userId:\'%s\',userName:\'%s\',userEmail:\'%s\',sessId:\'%s\'},\'*\'); window.close();"/>',
                   $this->_app->auth->getUserId(), $this->_app->auth->getFullname(), $this->_app->auth->getEmail(), $this->_app->session->getId());
    } else {
      if ($backwards = $this->_app->request->getParams('backwards')) $this->_app->response->addParams(array('backwards'=>$backwards));
      return 'eBack';
    }
  }
}

class ModuleGoogleLogin extends LoginModule {
  
  protected function _userRun() {
    global $GOOGLE;
    
    if ($state = $this->_app->request->getParams('state')) {
      #error_log('redirect_session: '.$_GET['state']);
      $url = sprintf('%s&code=%s&sessid=%s', $GOOGLE['backLoginUrl'], $this->_app->request->getParams('code'), $state);
      header('Location: '. $url);
      die;
    }
    #error_log('redirected_session: '.$_GET['sessid']);
    
    require_once $GOOGLE['apiSrc'];

    $client = new Google_Client();
    $client->setClientId($GOOGLE['clientId']);
    $client->setClientSecret($GOOGLE['clientSecret']);
    $client->setRedirectUri(sprintf($GOOGLE['backLoginUrl'],$this->_app->session->getId()));
    $client->addScope('https://www.googleapis.com/auth/userinfo.email');
    $client->addScope('https://www.googleapis.com/auth/userinfo.profile');

    $oAuth = new Google_Service_Oauth2($client);
    
    try {
      if ($code = $this->_app->request->getParams('code')) {
      
        $client->authenticate($code);
        $token = $client->getAccessToken();
        
        $user = $oAuth->userinfo->get();
        #error_log(var_export($user,true));
        $userId = isset($user['id'])&&$user['id']?$user['id']:'';
        if ($userId) {
          $authParams = $this->_getExternalUserAuthParams('google', $user, $this->_app->session->get('account_jsReturn'));
          $ret = $this->_app->auth->authenticate($authParams, $accounts);
          if (!$ret) {
            if (!$accounts||$this->_app->session->get('account_jsReturn')) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.google_authFailed'), $user['email']));

            // kdyz mam ucet k prihlaseni, zobrazim vyber poskytovatele
            #error_log(sprintf('Google login of user %s from %s.', $user['email'], $this->_app->getRemoteAddress()));
            $validator = Validator::get('login','LoginValidator');
            $validator->setValues(array('login_google'=>$authParams['google'],'login_accounts'=>$accounts));

            return 'vLogin';
          }
          #if (!$this->_app->session->get('account_jsReturn')) error_log(sprintf('Google login of user %s from %s.', $user['email'], $this->_app->getRemoteAddress()));
          
          if ($action = $this->_clearExpired()) return $action;
        }
      }
    } catch (Exception $e) {
      if ($this->_app->session->get('account_jsReturn')) {
        echo sprintf('<body onload="window.onunload = window.opener.postMessage({status:-1,message:\'%s\'},\'*\'); window.close();"/>', str_replace("'","\'",$e->getMessage()));
        die;
      } else throw new ExceptionUser($e->getMessage());
    }
   
    if ($this->_app->session->get('account_jsReturn')) {
      echo sprintf('<body onload="window.onunload = window.opener.postMessage({status:0,type:\'login\',userId:\'%s\',userName:\'%s\',userEmail:\'%s\',sessId:\'%s\'},\'*\'); window.close();"/>',
                   $this->_app->auth->getUserId(), $this->_app->auth->getFullname(), $this->_app->auth->getEmail(), $this->_app->session->getId());
    } else {
      if ($backwards = $this->_app->request->getParams('backwards')) $this->_app->response->addParams(array('backwards'=>$backwards));
      return 'eBack';
    }
  }
}

class ModuleTwitterLogin extends LoginModule {
  
  protected function _userRun() {
    global $TWITTER;
    
    require_once $TWITTER['apiSrc'];
    
    try {
      if (($token=$this->_app->request->getParams('oauth_token'))&&($token==$this->_app->session->get('twitterToken'))) {
        $connection = new TwitterOAuth($TWITTER['consumerKey'], $TWITTER['consumerSecret'], $token , $this->_app->session->get('twitterTokenSecret'));
        $token = $connection->getAccessToken($this->_app->request->getParams('oauth_verifier'));
        if ($connection->http_code == '200') {
          $user = $connection->get('account/verify_credentials', array('include_email'=>'true'));
          #error_log(var_export($user,true));

          $authParams = $this->_getExternalUserAuthParams('twitter', $user, $this->_app->session->get('account_jsReturn'));
          $ret = $this->_app->auth->authenticate($authParams, $accounts);
          if (!$ret) {
            if (!$accounts||$this->_app->session->get('account_jsReturn')) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.twitter_authFailed'), $user->email));

            // kdyz mam ucet k prihlaseni, zobrazim vyber poskytovatele
            #error_log(sprintf('Twitter login of user %s from %s.', $user->email, $this->_app->getRemoteAddress()));
            $validator = Validator::get('login','LoginValidator');
            $validator->setValues(array('login_twitter'=>$authParams['twitter'],'login_accounts'=>$accounts));

            return 'vLogin';
          }
          #if (!$this->_app->session->get('account_jsReturn')) error_log(sprintf('Twitter login of user %s from %s.', $user->email, $this->_app->getRemoteAddress()));
      
          if ($action = $this->_clearExpired()) return $action;
        }
      }
    } catch (Exception $e) {
      if ($this->_app->session->get('account_jsReturn')) {
        echo sprintf('<body onload="window.onunload = window.opener.postMessage({status:-1,message:\'%s\'},\'*\'); window.close();"/>', str_replace("'","\'",$e->getMessage()));
        die;
      } else throw new ExceptionUser($e->getMessage());
    }
   
    if ($this->_app->session->get('account_jsReturn')) {
      echo sprintf('<body onload="window.onunload = window.opener.postMessage({status:0,type:\'login\',userId:\'%s\',userName:\'%s\',userEmail:\'%s\',sessId:\'%s\'},\'*\'); window.close();"/>',
                   $this->_app->auth->getUserId(), $this->_app->auth->getFullname(), $this->_app->auth->getEmail(), $this->_app->session->getId());
    } else {
      if ($backwards = $this->_app->request->getParams('backwards')) $this->_app->response->addParams(array('backwards'=>$backwards));
      return 'eBack';
    }
  }
}

?>
