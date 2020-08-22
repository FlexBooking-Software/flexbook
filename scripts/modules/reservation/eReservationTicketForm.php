<?php

class ModuleReservationTicketForm extends ExecModule {

  protected function _userRun() {
    $id = $this->_app->request->getParams('id');
    $group = $this->_app->request->getParams('group');
    $from = $this->_app->regionalSettings->convertHumanToDate($this->_app->request->getParams('from'));
    $to = $this->_app->regionalSettings->convertHumanToDate($this->_app->request->getParams('to'));
    $equal = $this->_app->request->getParams('equal');
    
    if ($id) {
      if ($group) {
        $ids = array();
        
        $oR = new OReservation($id);
        $data = $oR->getData();
        
        $s = new SReservation;
        $s->addStatement(new SqlStatementBi($s->columns['start'], $data['start'], '%s>=%s'));
        $s->addStatement(new SqlStatementMono($s->columns['failed'], '%s IS NULL'));
        $s->addStatement(new SqlStatementMono($s->columns['cancelled'], '%s IS NULL'));
        if (!strcmp($group,'provider')) $s->addStatement(new SqlStatementBi($s->columns['provider'], $data['provider'], '%s=%s'));
        elseif (!strcmp($group,'center')) $s->addStatement(new SqlStatementBi($s->columns['center'], $data['center'], '%s=%s'));
        if ($from) $s->addStatement(new SqlStatementBi($s->columns['start'], $from, '%s>=%s'));
        if ($to) $s->addStatement(new SqlStatementBi($s->columns['start'], $to, '%s<=%s'));
        if ($equal) {
          if ($equal=='USER') $s->addStatement(new SqlStatementBi($s->columns['user'], $data['user'], '%s=%s'));
          elseif ($equal=='EVENT') $s->addStatement(new SqlStatementBi($s->columns['event'], $data['event'], '%s=%s'));
          elseif ($equal=='RESOURCE') $s->addStatement(new SqlStatementBi($s->columns['resource'], $data['resource'], '%s=%s'));
        }

        $s->addOrder(new SqlStatementAsc($s->columns['start']));
        $s->setColumnsMask(array('reservation_id'));
        $res = $this->_app->db->doQuery($s->toString());
        while ($row = $this->_app->db->fetchAssoc($res)) {
          $ids[] = $row['reservation_id'];
        }
      } else $ids = array($id);
      
      $this->_app->response->addParams(array('id'=>$ids));
      return 'vReservationTicket';
    }

    $this->_app->response->addParams(array('backwards'=>1));
    return 'eBack';
  }
}

?>
