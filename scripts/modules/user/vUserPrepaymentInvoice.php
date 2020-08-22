<?php

class ModuleUserPrepaymentInvoice extends DocumentModule {

  protected function _userInsert() {
    $this->_app->history->getBackwards(1);
    $this->_app->setDebug(false);

    $creditJournalId = $this->_app->request->getParams('id');

    $s = new SCreditJournal;
    $s->addStatement(new SqlStatementBi($s->columns['creditjournal_id'], $creditJournalId, '%s=%s'));

    if (!$this->_app->auth->isAdministrator()) {
      $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
      if (!$this->_app->auth->isProvider()) $s->addStatement(new SqlStatementBi($s->columns['user'], $this->_app->auth->getUserId(), '%s=%s'));
    }
    $s->addStatement(new SqlStatementMono($s->columns['prepaymentinvoice_number'], '%s IS NOT NULL'));
    $s->setColumnsMask(array('creditjournal_id','prepaymentinvoice_content'));
    $res = $this->_app->db->doQuery($s->toString());
    if (!$row = $this->_app->db->fetchAssoc($res)) die('Invalid record!');
    if (!$row['prepaymentinvoice_content']) die('Invalid record!');

    $o = new OFile($row['prepaymentinvoice_content']);
    $oData = $o->getData();
    
    $this->insertTemplateVar('children', $oData['content'], false);
  }
}

?>
