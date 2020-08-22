<?php

class ModuleCzechTourismPortalOrderSave extends ExecModule {

  protected function _userRun() {
    $user = $this->_app->auth->getUserId();
    // kdyz vyprsela session
    if (!$user) {
      $this->_app->messages->addMessage('userError', $this->_app->textStorage->getText('error.czechTourismPortal_sessionError'));
      $this->_app->response->addParams(array('id'=>'czechtourism'));
      return 'eInit';
    }
    
    $validator = Validator::get('cz', 'CzechTourismValidator');
    $validator->initValues();
    $validator->validateValues();
    $data = $validator->getValues();
    
    // bude ucastnikem akce, kvuli omezeni na 82 mist
    try {
      $params = array(
          'eventParams'   => array('eventId'=>3397,'eventPlaces'=>1),
          'userId'        => $user,
          );
      $bRes = new BReservation;
      $bRes->save($params);
    } catch (Exception $e) {
      if (!strcmp($e->printMessage(),$this->_app->textStorage->getText('error.saveReservation_eventNotEnoughPlaces'))) {
        throw new ExceptionUserTextStorage('error.czechTourismPortal_orderSum');
      } else throw new ExceptionUser($e->printMessage());
    }
    
    $attribute = array(
        9     => date('Y-m-d H-i-s'),
        8     => 'N',
        7     => $data['fee'],
        12    => $data['role1'],
        4     => $data['firstname2'],
        5     => $data['lastname2'],
        6     => $data['email2'],
        11    => $data['role2'],
        10    => $data['phone2'],
        14    => $data['catering']?'Y':'N',
        );
    $bUser = new BUser($this->_app->auth->getUserId());
    $bUser->saveAttribute($attribute, false);
    
    $totalPrice = substr($data['fee'], 0, 4);
    if ($data['catering']) $totalPrice += 300;
    $totalPrice = round($totalPrice * 1.21);
    
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
    $mail->AddAddress($validator->getVarValue('email1'));
    $mail->AddAddress('david.pasek@gmail.com');
    #$mail->AddAddress('kos@anthill.cz');
    $mail->AddAddress('omzt@czechtourism.cz');
    $mail->AddAddress('trainee@czechtourism.cz');
    $mail->AddAddress('yousifova@abf.cz');
    $mail->Subject  = sprintf('POTVRZENÍ ZÁVAZNÉ OBJEDNÁVKY');
    $mail->Body     = sprintf("Děkujeme za Vaši registraci na akci Czech Republic – Land of Stories, INCOMING WORKSHOP 20. 10. 2015 a těšíme se na setkání s Vámi!<br/><br/>".
"Po uhrazení účastnického poplatku Vám bude zaslán přístup do rezervačního systému schůzek s nákupčími.<br/><br/>".
"Účastnický poplatek %s,- Kč prosím uhraďte na číslo účtu: 166985233/0300 ČSOB a uveďte variabilní symbol: %s<br/><br/>".
"Vstupenku obdržíte na registraci v PVA EXPO PRAHA Letňany při příchodu na veletrh.<br/>".
"Aktuální informace najdete na http://www.travelmeetingpoint.cz<br/><br/>POTVZENÍ ZÁVAZNÉ OBJEDNÁVKY:<br/>%s<br/>".
"<span class=\"totalPrice\">Celková cena: %s,- Kč</span><br/><br/>". 
"V případě nejasností nás kontaktujte na: trainee@czechtourism.cz, tel: 221 580 482",
$totalPrice, $this->_app->auth->getUserId(), czechTourismGetConfirmDialog2(true),$totalPrice);
    $mail->Send();
  
    $this->_app->messages->addMessage('userInfo', $this->_app->textStorage->getText('info.czechTourism_userOrder_ok'));
    
    $this->_app->response->addParams(array('section'=>'welcome'));
    return 'vCzechTourismPortal';
  }
}

?>
