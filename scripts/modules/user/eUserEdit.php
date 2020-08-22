<?php

class ModuleUserEdit extends ExecModule {

  protected function _userRun() {
    $subaccountEdit = $this->_app->request->getParams('subaccount');
    $uId = $this->_app->request->getParams('id');
    $new = $this->_app->request->getParams('new');

    $validator = Validator::get($subaccountEdit?'userSubaccount':'user','UserValidator',true);
    $validator->loadData($new?null:$uId);
    
    $fromReservation = $this->_app->request->getParams('fromReservation');
    $fromEventSubstitute = $this->_app->request->getParams('fromEventSubstitute');
    $fromCustomerEmployee = $this->_app->request->getParams('fromCustomerEmployee');
    $fromCustomerCoworker = $this->_app->request->getParams('fromCustomerCoworker');
    $validator->setValues(array(
                  'fromReservation'       => $fromReservation,
                  'fromEventSubstitute'   => $fromEventSubstitute,
                  'fromCustomerEmployee'  => $fromCustomerEmployee,
                  'fromCustomerCoworker'  => $fromCustomerCoworker,
                  ));
    
    setcookie('ui-user-tab', 0);

    if ($subaccountEdit) $this->_app->response->addParams(array('subaccount'=>1));
    return $subaccountEdit?'vUserSubaccountEdit':'vUserEdit';
  }
}

?>
