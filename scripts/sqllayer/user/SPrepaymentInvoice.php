<?php

class SPrepaymentInvoice extends MySqlSelect {
  private $_tPrepaymentInvoice;

  private function _insertPrepaymentInvoiceTable() {
    $this->_tPrepaymentInvoice = new SqlTable('prepaymentinvoice', 'pi');

    $this->addColumn(new SqlColumn($this->_tPrepaymentInvoice, 'prepaymentinvoice_id'));
    $this->addColumn(new SqlColumn($this->_tPrepaymentInvoice, 'number'));
    $this->addColumn(new SqlColumn($this->_tPrepaymentInvoice, 'userregistration'));
    $this->addColumn(new SqlColumn($this->_tPrepaymentInvoice, 'creditjournal'));
    $this->addColumn(new SqlColumn($this->_tPrepaymentInvoice, 'content'));
  }

  protected function _initSqlSelect() {
    $this->_insertPrepaymentInvoiceTable();
  }
}

?>
