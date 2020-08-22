<?php

class ModuleInPageResourceReserve extends ExecModule {

  protected function _userRun() {
    $user = $this->_app->auth->getUserId();
    $resource = $this->_app->request->getParams('id');
    $from = $this->_app->request->getParams('from');
    $to = $this->_app->request->getParams('to');
    
    // Czechtourism muze mit pouze jednu rezervaci na jeden zdroj a celkem 10 na ruzne zdroje
    $validator = Validator::get('inpage', 'InPageValidator');
    $provider = $validator->getVarValue('providerId');
    if ($provider==14) {
      $o = new OResource($resource);
      $oData = $o->getData();
      $center = $oData['center'];
      
      /*$s = new SReservation;
      $s->addStatement(new SqlStatementBi($s->columns['provider'], $provider, '%s=%s'));
      $s->addStatement(new SqlStatementBi($s->columns['user'], $user, '%s=%s'));
      $s->addStatement(new SqlStatementMono($s->columns['cancelled'], '%s IS NULL'));
      $s->addStatement(new SqlStatementMono($s->columns['resource'], '%s IS NOT NULL'));
      $s->addStatement(new SqlStatementBi($s->columns['center'], $center, '%s=%s'));
      $s->setColumnsMask(array('reservation_id'));
      $res = $this->_app->db->doQuery($s->toString());
      if ($this->_app->db->getRowsNumber($res)>=8) throw new ExceptionUserTextStorage('error.czechTourism_reservationSum');*/
      
      $s = new SReservation;
      $s->addStatement(new SqlStatementBi($s->columns['provider'], $provider, '%s=%s'));
      $s->addStatement(new SqlStatementBi($s->columns['user'], $user, '%s=%s'));
      $s->addStatement(new SqlStatementMono($s->columns['cancelled'], '%s IS NULL'));
      $s->addStatement(new SqlStatementBi($s->columns['resource'], $resource, '%s=%s'));
      $s->setColumnsMask(array('reservation_id'));
      $res = $this->_app->db->doQuery($s->toString());
      if ($this->_app->db->getRowsNumber($res)) throw new ExceptionUserTextStorage('error.czechTourism_resourceReservation');
    }
    
    $params = array(
        'resourceParams'   => array('resourceId'=>$resource,'resourceFrom'=>$from,'resourceTo'=>$to),
        'userId'        => $user,
        );
    $bRes = new BReservation;
    $bRes->save($params);
    
    
    $this->_app->messages->addMessage('userInfo', $this->_app->textStorage->getText('info.inpage_reservation_ok'));
    
    return 'eBack';
  }
}

?>
