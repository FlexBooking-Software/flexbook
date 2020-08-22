<?php

class ModuleInPageEventReserve extends ExecModule {

  protected function _userRun() {
    $user = $this->_app->auth->getUserId();
    $event = $this->_app->request->getParams('id');
    $substitute = $this->_app->request->getParams('substitute');
    
    if (!$substitute) {
      $params = array(
          'eventParams'   => array('eventId'=>$event,'eventPlaces'=>1),
          'userId'        => $user,
          );
      $bRes = new BReservation;
      $bRes->save($params);
    } else {
      $bEvent = new BEvent($event);
      $params = array(
          'userId'         => $user,
          'places'         => 1,
          );
      $bEvent->saveSubstitute($params);
    }
    
    $this->_app->messages->addMessage('userInfo', $this->_app->textStorage->getText('info.inpage_reservation_ok'));
    
    return 'eBack';
  }
}

?>
