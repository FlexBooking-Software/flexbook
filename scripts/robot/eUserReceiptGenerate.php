<?php

// patchovaci script na vytvoreni/ulozeni dokladu o zaplaceni rezervaci (v minulosti),
// nove rezervace to budou mit automaticky vygenerovane po zaplaceni
class ModuleUserReceiptGenerate extends ExecModule {

  protected function _userRun() {
    echo sprintf("Generating receipts for reservations:\n");

    $this->_app->db->beginTransaction();

    $s = new SReservation;
    $s->addStatement(new SqlStatementMono($s->columns['receipt_number'], '%s IS NOT NULL'));
    #$s->addStatement(new SqlStatementMono($s->columns['reservation_id'], '%s=41835'));
    $s->addStatement(new SqlStatementMono($s->columns['start'], "%s>'2019-11-10'")); // budu generovat pouze pro nove vytvorene 
    $s->addStatement(new SqlStatementMono($s->columns['payed'], '%s IS NOT NULL'));
    $s->addStatement(new SqlStatementMono($s->columns['receipt'], '%s IS NULL'));
    $s->setColumnsMask(array('reservation_id','number'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      echo sprintf("Reservation %s ... \n", $row['number']);

      $receiptGui = new GuiReservationReceipt(array('reservation'=>$row['reservation_id']));
      $file = new BFile;
      $receiptFile = $file->saveFromString(array('content'=>$receiptGui->render()));

      $oR = new OReservation($row['reservation_id']);
      $oR->setData(array('receipt'=>$receiptFile));
      $oR->save();
    }

    $this->_app->db->commitTransaction();

    echo "Done.\n";
    die;
  }
}

?>
