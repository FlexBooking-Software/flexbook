<?php

class ModuleUserGoogleAssign extends ExecModule {
  
  protected function _userRun() {
    global $GOOGLE;
    
    // session se musi predavat ve specialnim paramtru
    if ($state = $this->_app->request->getParams('state')) {
      #error_log('redirect_session: '.$_GET['state']);
      $url = sprintf('%s&code=%s&sessid=%s', $GOOGLE['backAssignUrl'], $this->_app->request->getParams('code'), $state);
      header('Location: '. $url);
      die;
    }
    
    require_once $GOOGLE['apiSrc'];
    
    $client = new Google_Client();
    $client->setClientId($GOOGLE['clientId']);
    $client->setClientSecret($GOOGLE['clientSecret']);
    $client->setRedirectUri(sprintf($GOOGLE['backAssignUrl'],$this->_app->session->getId()));
    $client->addScope('https://www.googleapis.com/auth/userinfo.email');
    $oAuth = new Google_Service_Oauth2($client);
    
    $userId = '';
    if ($code = $this->_app->request->getParams('code')) {
    
      $client->authenticate($code);
      $token = $client->getAccessToken();
      
      $user = $oAuth->userinfo->get();
      $userId = $user['id'];
    }
    
    if ($this->_app->session->get('account_jsReturn')) {
      echo sprintf('<body onload="window.onunload = window.opener.postMessage({status:0,type:\'assign\',accountId:\'%s\',placeHolder:\'%s\'},\'*\'); window.close();"/>',
                   $userId, $this->_app->session->get('account_jsPlaceHolder'));
    } else {
      $validator = Validator::get('user', 'UserValidator');
      $validator->setValues(array('googleId' => $userId));
      
      $this->_app->response->addParams(array('backwards'=>1));
      return 'eBack';
    }
  }
}

?>
