<?php

class ModuleCzechTourismPortalOrder extends ExecModule {

  protected function _userRun() {
    $validatorIn = Validator::get('login', 'InPageLoginValidator');
    $validatorOut = Validator::get('cz', 'CzechTourismValidator');
    $data = $validatorIn->getValues();
    $data['firstname1'] = $data['firstname'];
    $data['lastname1'] = $data['lastname'];
    $data['email1'] = $data['email'];
    $data['phone1'] = $data['phone'];
    $validatorOut->setValues($data);
  
    $this->_app->response->addParams(array('section'=>'userOrder'));  
    return 'vCzechTourismPortal';
  }
}

?>
