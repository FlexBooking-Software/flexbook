<?php

class ModuleTwitterCall extends ExecModule {

  protected function _userRun() {
    global $TWITTER;
    
    if (!strcmp($this->_app->request->getParams('type'),'assignToUser')) $callbackUrl = $TWITTER['backAssignUrl'];
    elseif (!strcmp($this->_app->request->getParams('type'),'loginUser')) $callbackUrl = $TWITTER['backLoginUrl'];
    else die('Invalid twitter action!');
    
    // musim si nekam ulozit, kdyz se mam vratit do JS komponenty a ne do backoffice
    if ($this->_app->request->getParams('jsreturn')) {
      $this->_app->session->set('account_jsReturn', 1);
      $this->_app->session->set('account_jsPlaceHolder', urldecode($this->_app->request->getParams('jsplaceholder')));
      $this->_app->session->set('account_provider', $this->_app->request->getParams('provider'));
    }
    
    require_once $TWITTER['apiSrc'];
    
    $connection = new TwitterOAuth($TWITTER['consumerKey'], $TWITTER['consumerSecret']);
    $token = $connection->getRequestToken(sprintf($callbackUrl, $this->_app->session->getId()));
    $url = $connection->getAuthorizeURL($token['oauth_token']);
    
    $this->_app->session->set('twitterToken', $token['oauth_token']);
    $this->_app->session->set('twitterTokenSecret', $token['oauth_token_secret']);
    
    header('Location: '.$url);
  }
}

?>
