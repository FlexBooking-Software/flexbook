<?php

class ModuleReservationRotate extends ExecModule {

  protected function _userRun() {
    $date = date('Y-m-d');
    
    echo sprintf("--------- %s ----------\n", date('Y-m-d H:i:s'));
    
    try {
      $this->_app->db->beginTransaction();
    
      // rezervace na akce, ktere nejsou aktivni
      $s = new SReservation;
      $s->addStatement(new SqlStatementMono($s->columns['payed'], "%s IS NOT NULL"));
      $s->addStatement(new SqlStatementMono($s->columns['realised'], "%s IS NULL"));
      $s->addStatement(new SqlStatementMono($s->columns['cancelled'], "%s IS NULL"));
      $s->addStatement(new SqlStatementMono($s->columns['event'], "%s IS NOT NULL"));
      $s->addStatement(new SqlStatementBi($s->columns['event_end'], $date, '%s<%s'));
      $s->addOrder(new SqlStatementAsc($s->columns['event_start']));
      $s->addOrder(new SqlStatementAsc($s->columns['number']));
      $s->setColumnsMask(array('reservation_id','number','provider_name','center_name'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        echo sprintf('%s - %s (%s) ... ', $row['number'], $row['provider_name'], $row['center_name']);
        $o = new OReservation($row['reservation_id']);
        $o->setData(array('realised'=>date('Y-m-d H:i:s')));
        $o->save();
        echo "OK\n";
      }
      
      // rezervace na zdroje v minulosti
      $s = new SReservation;
      $s->addStatement(new SqlStatementMono($s->columns['payed'], "%s IS NOT NULL"));
      $s->addStatement(new SqlStatementMono($s->columns['realised'], "%s IS NULL"));
      $s->addStatement(new SqlStatementMono($s->columns['cancelled'], "%s IS NULL"));
      $s->addStatement(new SqlStatementMono($s->columns['resource'], "%s IS NOT NULL"));
      $s->addStatement(new SqlStatementBi($s->columns['resource_to'], $date, '%s<%s'));
      $s->addOrder(new SqlStatementAsc($s->columns['resource_from']));
      $s->addOrder(new SqlStatementAsc($s->columns['number']));
      $s->setColumnsMask(array('reservation_id','number','provider_name','center_name'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        echo sprintf('%s - %s (%s) ... ', $row['number'], $row['provider_name'], $row['center_name']);
        $o = new OReservation($row['reservation_id']);
        $o->setData(array('realised'=>date('Y-m-d H:i:s')));
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
