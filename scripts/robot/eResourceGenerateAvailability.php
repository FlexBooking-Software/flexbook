<?php

class ModuleResourceGenerateAvailability extends ExecModule {

  protected function _userRun() {
    $dateFrom = date('Y-m-d');
    
    global $RESOURCE_AVAILABILITY;
    echo sprintf("Generating availability table for resources from %s until %s\n", $dateFrom, $this->_app->regionalSettings->increaseDate($dateFrom, $RESOURCE_AVAILABILITY['future']));

    $s = new SResource;
    #$s->addStatement(new SqlStatementMono($s->columns['resource_id'], '%s=666'));
    #$s->addStatement(new SqlStatementMono($s->columns['provider'], "%s=1"));
    $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
    $s->addOrder(new SqlStatementAsc($s->columns['provider']));
    $s->addOrder(new SqlStatementAsc($s->columns['center']));
    $s->addOrder(new SqlStatementAsc($s->columns['name']));
    $s->setColumnsMask(array('resource_id','name','provider_name','center_name'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      echo sprintf('%s (%s) - %s ... ', $row['provider_name'], $row['center_name'], $row['name']);

      try {
        $this->_app->db->beginTransaction();

        $b = new BResource($row['resource_id']);
        $b->generateAvailabilityTable($dateFrom);
        echo "OK\n";

        $this->_app->db->commitTransaction();
      } catch (Exception $e) {
        echo $e->getMessage() . "\n";

        $this->_app->db->shutdownTransaction();
      }
    }
    
    echo "Done.\n";
  
    die;
  }
}

?>
