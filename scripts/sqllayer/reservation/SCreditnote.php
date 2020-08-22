<?php

class SCreditnote extends MySqlSelect {
  private $_tCreditnote;

  private function _insertCreditnoteTable() {
    $this->_tCreditnote = new SqlTable('creditnote', 'cn');

    $this->addColumn(new SqlColumn($this->_tCreditnote, 'creditnote_id'));
    $this->addColumn(new SqlColumn($this->_tCreditnote, 'type'));
    $this->addColumn(new SqlColumn($this->_tCreditnote, 'number'));
    $this->addColumn(new SqlColumn($this->_tCreditnote, 'reservation'));
    $this->addColumn(new SqlColumn($this->_tCreditnote, 'content'));
  }

  protected function _initSqlSelect() {
    $this->_insertCreditnoteTable();
  }
}

?>
