<?php

class ModuleUserFacebookAssign extends ExecModule {
  
  protected function _userRun() {
    global $FACEBOOK;

    if (!$this->_app->request->getParams('sessid','get')) {
      $state = $this->_app->request->getParams('state');
      $url = sprintf('%s&code=%s&sessid=%s&state=%s', $FACEBOOK['backAssignUrl'], $this->_app->request->getParams('code'), $state, $state);
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
      $accessToken = $helper->getAccessToken($FACEBOOK['backAssignUrl']);
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
    
    $user = $response->getGraphUser();
    $userId = isset($user['id'])&&$user['id']?$user['id']:'';

    if ($this->_app->session->get('account_jsReturn')) {
      echo sprintf('<body onload="window.onunload = window.opener.postMessage({status:0,type:\'assign\',accountId:\'%s\',placeHolder:\'%s\'},\'*\'); window.close();"/>',
                   $userId, $this->_app->session->get('account_jsPlaceHolder'));
    } else {
      $validator = Validator::get('user', 'UserValidator');
      $validator->setValues(array('facebookId' => $userId));
      
      $this->_app->response->addParams(array('backwards'=>1));
      return 'eBack';
    }
  }
}

?>
