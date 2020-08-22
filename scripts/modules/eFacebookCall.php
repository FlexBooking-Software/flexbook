<?php

class ModuleFacebookCall extends ExecModule {

  protected function _userRun() {
    global $FACEBOOK;
    
    if (!strcmp($this->_app->request->getParams('type'),'assignToUser')) $callbackUrl = $FACEBOOK['backAssignUrl'];
    elseif (!strcmp($this->_app->request->getParams('type'),'loginUser')) $callbackUrl = $FACEBOOK['backLoginUrl'];
    else die('Invalid facebook action!');
    
    // musim si nekam ulozit, kdyz se mam vratit do JS komponenty a ne do backoffice
    if ($this->_app->request->getParams('jsreturn')) {
      $this->_app->session->set('account_jsReturn', 1);
      $this->_app->session->set('account_jsPlaceHolder', urldecode($this->_app->request->getParams('jsplaceholder')));
      $this->_app->session->set('account_provider', $this->_app->request->getParams('provider'));
    }
    
    require_once $FACEBOOK['apiSrc'];
    
    $facebook = new Facebook\Facebook(array(
      'app_id'                => $FACEBOOK['appId'],
      'app_secret'            => $FACEBOOK['appSecret'],
      'default_graph_version' => 'v2.7',
    ));
    
    $helper = $facebook->getRedirectLoginHelper();
    $helper->getPersistentDataHandler()->set('state', $this->_app->session->getId());
    $url = $helper->getLoginUrl($callbackUrl, $FACEBOOK['permissions']);
    
    header('Location: '.$url);
  }
}

?>
