<?php

class ModuleInPageLogin extends ExecModule {

  protected function _userRun() {
    $validator = Validator::get('inpage', 'InPageValidator');
    $provider = $validator->getVarValue('providerId');
    
    $username = $this->_app->request->getParams('username');
    $password = $this->_app->request->getParams('password');
      
    if ($username&&$password) {
      if (!$ret = $this->_app->auth->authenticate(array(
            'provider'    => $provider,
            'username'    => $username,
            'password'    => $password))) {
        throw new ExceptionUserTextStorage('error.inpage_authFailed');
      }
    }
    
    $validator = Validator::get('login','InPageLoginValidator');
    $bUser = new BUser($this->_app->auth->getUserId());
    $data = $bUser->getData();
    $validator->setValues(array(
          'firstname'             => $data['firstname'],
          'lastname'              => $data['lastname'],
          'street'                => $data['street'],
          'city'                  => $data['city'],
          'postalCode'            => $data['postalCode'],
          'state'                 => $data['state'],
          'email'                 => $data['email'],
          'phone'                 => $data['phone'],
          ));

    $this->_app->response->addParams(array('backwards'=>1));
    return 'eBack';
  }
}

?>
