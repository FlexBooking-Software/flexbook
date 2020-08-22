<?php

class ModuleUserTwitterAssign extends ExecModule {
  
  protected function _userRun() {
    global $TWITTER;
    
    require_once $TWITTER['apiSrc'];
    
    $userId = '';
    if (($token=$this->_app->request->getParams('oauth_token'))&&($token==$this->_app->session->get('twitterToken'))) {
      $connection = new TwitterOAuth($TWITTER['consumerKey'], $TWITTER['consumerSecret'], $token , $this->_app->session->get('twitterTokenSecret'));
      $token = $connection->getAccessToken($this->_app->request->getParams('oauth_verifier'));
      if ($connection->http_code == '200') {
        $user = $connection->get('account/verify_credentials', array('include_email'=>'true'));
        
        $userId = $user->id;
      }
    }
    
    if ($this->_app->session->get('account_jsReturn')) {
      echo sprintf('<body onload="window.onunload = window.opener.postMessage({status:0,type:\'assign\',accountId:\'%s\',placeHolder:\'%s\'},\'*\'); window.close();"/>',
                   $userId, $this->_app->session->get('account_jsPlaceHolder'));
    } else {
      $validator = Validator::get('user', 'UserValidator');
      $validator->setValues(array('twitterId' => $userId));
      
      $this->_app->response->addParams(array('backwards'=>1));
      return 'eBack';
    }
  }
}

?>
