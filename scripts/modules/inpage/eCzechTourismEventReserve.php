<?php

class ModuleInPageCzechTourismEventReserve extends ExecModule {

  protected function _userRun() {
    $user = $this->_app->auth->getUserId();
    $event = $this->_app->request->getParams('id');
    
    $validator = Validator::get('login', 'InPageLoginValidator');
    $validator->initValues();
    $validator->validateValues();
    
    if (!$confirm = $this->_app->request->getParams('confirm')) throw new ExceptionUserTextStorage('error.czechTourism_confirmNeeded');
    
    $params = array(
        'eventParams'   => array('eventId'=>$event,'eventPlaces'=>1),
        'userId'        => $user,
        );
    $bRes = new BReservation;
    $bRes->save($params);
    $resData = $bRes->getData();
    
    global $NOTIFICATION;
    $mail = new PHPMailer;
    if (isset($NOTIFICATION['host'])&&$NOTIFICATION['host']) {
      $mail->Host       = $NOTIFICATION['host'];
      $mail->Mailer     = 'smtp';
    }    
    $mail->From       = $NOTIFICATION['defaultAddressFrom'];
    $mail->FromName   = '';
    $mail->Sender     = $NOTIFICATION['defaultAddressFrom'];
    $mail->ContentType = 'text/html';
    $mail->CharSet = 'UTF-8';
    $mail->AddAddress($validator->getVarValue('email'));
    $mail->AddAddress('ozz@czechtourism.cz');
    $mail->Subject  = sprintf('POTVRZENÍ ZÁVAZNÉ OBJEDNÁVKY (%s)', $resData['number']);
    $mail->Body     = czechTourismGetConfirmDialog(true);
    $mail->Send();
  
    $this->_app->messages->addMessage('userInfo', $this->_app->textStorage->getText('info.inpage_reservation_ok'));
    
    return 'eBack';
  }
}

?>
