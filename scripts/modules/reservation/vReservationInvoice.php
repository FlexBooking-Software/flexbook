<?php

class ModuleReservationInvoice extends DocumentModule {

  protected function _userInsert() {
    $this->_app->history->getBackwards(1);
    $this->_app->setDebug(false);

    $reservationId = $this->_app->request->getParams('id');

    $s = new SReservation;
    $s->addStatement(new SqlStatementBi($s->columns['reservation_id'], $reservationId, '%s=%s'));

    if (!$this->_app->auth->isAdministrator()) {
      $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
      if (!$this->_app->auth->isProvider()) $s->addStatement(new SqlStatementBi($s->columns['user'], $this->_app->auth->getUserId(), '%s=%s'));
    }
    $s->addStatement(new SqlStatementMono($s->columns['invoice_number'], '%s IS NOT NULL'));
    $s->setColumnsMask(array('reservation_id','invoice'));
    $res = $this->_app->db->doQuery($s->toString());
    if (!$row = $this->_app->db->fetchAssoc($res)) die('Invalid reservation!');
    if (!$row['invoice']) die('Invalid reservation!');

    $o = new OFile($row['invoice']);
    $oData = $o->getData();
    
    $this->insertTemplateVar('children', $oData['content'], false);

    // jeste pridam storna plateb
    $s = new SCreditnote;
    $s->addStatement(new SqlStatementMono($s->columns['type'], "%s='INVOICE'"));
    $s->addStatement(new SqlStatementBi($s->columns['reservation'], $reservationId, '%s=%s'));

    $s->setColumnsMask(array('content'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $o = new OFile($row['content']);
      $oData = $o->getData();

      $this->insertTemplateVar('children', $oData['content'], false);
    }
  }
}

?>
