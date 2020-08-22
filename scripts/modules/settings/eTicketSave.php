<?php

class ModuleTicketSave extends ExecModule {
  
  protected function _userRun() {
    if (!$this->_app->auth->haveRight('settings_admin', $this->_app->auth->getActualProvider())) throw new ExceptionUserTextStorage('error.accessDenied');

    $validator = Validator::get('ticket','TicketValidator');
    $validator->initValues();
    #adump($validator->getValues());die;
    
    $validator->validateValues();
    $valData = $validator->getValues();

    if ($valData['validityType']=='LENGTH') {
      if (!$valData['validityCount']||!$valData['validityUnit']) throw new ExceptionUserTextStorage('error.editTicket_missingValidity');
    } elseif ($valData['validityType']=='PERIOD') {
      if (!$valData['validityFrom']&&!$valData['validityTo']) throw new ExceptionUserTextStorage('error.editTicket_missingValidity');
    }

    $id = $validator->getVarValue('id');
    
    $bTicket = new BTicket($id?$id:null);
    $bTicket->save(array(
      'name'          => $valData['name'],
      'providerId'    => $valData['providerId'],
      'validityType'  => $valData['validityType'],
      'validityUnit'  => $valData['validityUnit'],
      'validityCount' => $valData['validityCount'],
      'validityFrom'  => $this->_app->regionalSettings->convertHumanToDate($valData['validityFrom']),
      'validityTo'    => $this->_app->regionalSettings->convertHumanToDate($valData['validityTo']),
      'center'        => $valData['center'],
      'subjectTag'    => $valData['subjectTag'],
      'price'         => $valData['price'],
      'value'         => $valData['value'],
      'active'        => $valData['active'],
    ));

    $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.editTicket_saveOk'), $valData['name']));

    return 'eBack';
  }
}

?>
