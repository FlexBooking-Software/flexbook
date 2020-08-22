<?php

class ModuleEventRotate extends ExecModule {

  protected function _userRun() {
    $date = date('Y-m-d H:i:s');
    
    echo sprintf("--------- %s ----------\n", date('Y-m-d H:i:s'));
    
    try {
      $this->_app->db->beginTransaction();
    
      $s = new SEvent;
      $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
      $s->addStatement(new SqlStatementBi($s->columns['end'], $date, '%s<%s'));
      $s->addOrder(new SqlStatementAsc($s->columns['start']));
      $s->addOrder(new SqlStatementAsc($s->columns['name']));
      $s->setColumnsMask(array('event_id','start','end','name','provider_name','center_name','reservationcondition'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        // akce se bude neaktivit az po skonceni
        /*if ($row['reservationcondition']&&($date<$row['end'])) {
          // kdyz existuje podminka rezervace, musim zkontrolovat, jestli akci nelze rezervovat jeste v prubehu
          // pak bych ji jeste nechal aktivni
          $s1 = new SReservationConditionItem;
          $s1->addStatement(new SqlStatementBi($s1->columns['reservationcondition'], $row['reservationcondition'], '%s=%s'));
          $s1->addStatement(new SqlStatementTri($s1->columns['time_from'], $s1->columns['time_from'], $date, '(%s IS NULL OR %s<%s)'));
          $s1->addStatement(new SqlStatementTri($s1->columns['time_to'], $date, $s1->columns['time_to'], '(%s IS NULL OR %s<%s)'));
          $s1->addStatement(new SqlStatementMono($s1->columns['limit_after_start_event'], "%s='N'"));
          $res1 = $this->_app->db->doQuery($s1->toString());
          if ($this->_app->db->getRowsNumber($res1)) continue;
        }*/
        echo sprintf('%s: %s - %s (%s) - %s->%s ... ', $row['event_id'], $row['name'], $row['provider_name'], $row['center_name'], $row['start'], $row['end']);
        $o = new OEvent($row['event_id']);
        $o->setData(array('active'=>'N'));
        $o->save();
        echo "OK\n";
      }
    
      $this->_app->db->commitTransaction();
    } catch (Exception $e) {
      echo $e->getMessage()."\n";
      
      $this->_app->db->shutdownTransaction();
    }
    
    echo "Done.\n";
  
    die;
  }
}

?>
