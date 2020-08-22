<?php

class ModuleGoogleCall extends ExecModule {

  protected function _userRun() {
    global $GOOGLE;
    
    if (!strcmp($this->_app->request->getParams('type'),'assignToUser')) $callbackUrl = $GOOGLE['backAssignUrl'];
    elseif (!strcmp($this->_app->request->getParams('type'),'loginUser')) $callbackUrl = $GOOGLE['backLoginUrl'];
    else die('Invalid google action!');
    
    // musim si nekam ulozit, kdyz se mam vratit do JS komponenty a ne do backoffice
    if ($this->_app->request->getParams('jsreturn')) {
      $this->_app->session->set('account_jsReturn', 1);
      $this->_app->session->set('account_jsPlaceHolder', urldecode($this->_app->request->getParams('jsplaceholder')));
      $this->_app->session->set('account_provider', $this->_app->request->getParams('provider'));
    }
  
    require_once $GOOGLE['apiSrc'];

    $client = new Google_Client();
    $client->setClientId($GOOGLE['clientId']);
    $client->setClientSecret($GOOGLE['clientSecret']);
    $client->setRedirectUri($callbackUrl);
    $client->setState($this->_app->session->getId());
    $client->addScope('https://www.googleapis.com/auth/userinfo.email');
    $client->addScope('https://www.googleapis.com/auth/userinfo.profile');

    $url = $client->createAuthUrl();
    
    header('Location: '.$url);
  }
}

?>
