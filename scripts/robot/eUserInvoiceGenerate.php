<?php

class ModuleUserInvoiceGenerate extends ExecModule {

  protected function _userRun() {
    echo sprintf("--------- %s ----------\n", date('Y-m-d H:i:s'));

    $this->_app->db->beginTransaction();

    $s = new SReservation;
    $s->addStatement(new SqlStatementMono($s->columns['invoice_number'], '%s IS NULL'));
    $s->addStatement(new SqlStatementMono($s->columns['start'], "%s>'2019-11-10'")); // budu generovat jenom pro nove vytvorene
    $s->addStatement(new SqlStatementMono($s->columns['end'], '%s<NOW()'));
    $s->addStatement(new SqlStatementMono($s->columns['payed'], '%s IS NOT NULL'));
    $s->addStatement(new SqlStatementMono($s->columns['providersettings_generate_accounting'], "%s='Y'"));
    $s->setColumnsMask(array('reservation_id','number'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      echo sprintf("Reservation %s ... ", $row['number']);

      try {
        $bRes = new BReservation($row['reservation_id']);
        $number = $bRes->generateInvoice();

        echo "$number\n";
      } catch (Exception $e) {
        echo $e->getMessage() . "\n";

        $this->_app->db->shutdownTransaction();
      }
    }

    $this->_app->db->commitTransaction();

    echo "Done.\n";
    die;
  }
}

?>
