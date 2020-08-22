<?php

class ModuleCzechTourismPortalEventReserve extends ExecModule {

  protected function _userRun() {
    #throw new ExceptionUserTextStorage('error.czechTourismPortal_reservationClosed');
  
    $user = $this->_app->auth->getUserId();
    $event = $this->_app->request->getParams('id');
    
    $validator = Validator::get('login', 'InPageLoginValidator');
    $validator->initValues();
    $validator->validateLastValues();
    
    // kontrola na pocet rezervaci
    $s = new SReservation;
    $s->addStatement(new SqlStatementBi($s->columns['user'], $user, '%s=%s'));
    $s->addStatement(new SqlStatementMono($s->columns['cancelled'], '%s IS NULL'));
    $s->addStatement(new SqlStatementMono($s->columns['event'], '%s IS NOT NULL'));
    $s->addStatement(new SqlStatementMono($s->columns['center'], '%s=58'));
    $s->setColumnsMask(array('reservation_id'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($this->_app->db->getRowsNumber($res)>8) throw new ExceptionUserTextStorage('error.czechTourismPortal_reservationSum');
    
    $params = array(
        'eventParams'   => array('eventId'=>$event,'eventPlaces'=>1),
        'userId'        => $user,
        );
    $bRes = new BReservation;
    $bRes->save($params);
    $resData = $bRes->getData();
    
    $this->_app->messages->addMessage('userInfo', $this->_app->textStorage->getText('info.inpage_reservation_ok'));
    
    return 'eBack';
  }
}

?>
