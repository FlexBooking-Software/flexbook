<?php

class ModuleEventUserExport extends ProjectModule {

  protected function _userInsert() {
    $eventId = $this->_app->request->getParams('event');
    
    $s = new SEvent;
    $s->addStatement(new SqlStatementBi($s->columns['event_id'], $eventId, '%s=%s'));
    $s->setColumnsMask(array('provider'));
    $res = $this->_app->db->doQuery($s->toString());
    $row = $this->_app->db->fetchAssoc($res);
    $providerId = $row['provider'];
  
    $eventUser = array();
    $s = new SEventAttendee;
    $s->addStatement(new SqlStatementBi($s->columns['event'], $eventId, '%s=%s'));
    $s->setColumnsMask(array('user'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      if ($row['user']) $eventUser[] = $row['user'];
    }
  
    $result = array();
    
    $s = new SUser;
    $s->addStatement(new SqlStatementBi($s->columns['registration_provider'], $providerId, '%s=%s'));
    if (count($eventUser)) $s->addStatement(new SqlStatementMono($s->columns['user_id'], sprintf('%%s NOT IN (%s)', implode(',',$eventUser))));
    $s->setColumnsMask(array('user_id','firstname','lastname','email','phone','street','city','postal_code','state'));
    $s->addOrder(new SqlStatementAsc($s->columns['lastname']));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $result[] = array(
          $row['firstname'],$row['lastname'],$row['email'],$row['phone'],
          $row['street'],$row['city'],$row['postal_code'],$row['state']
          );
    }
      
    header('Content-Type: application/vnd.ms-excel');
    header('Content-disposition: attachment; filename="export.csv"');
    
    foreach ($result as $r) {
      echo implode(';',$r)."\n";
    }
    
    die;
  }
}

?>
