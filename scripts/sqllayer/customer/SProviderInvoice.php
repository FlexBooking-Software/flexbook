<?php

class SProviderInvoice extends SqlSelect {
  private $_tProviderInvoice;
  private $_tCustomer;
  private $_tFile;
  
  private function _insertProviderInvoiceTable() {
    $this->_tProviderInvoice = new SqlTable('providerinvoice', 'pi');

    $this->addColumn(new SqlColumn($this->_tProviderInvoice, 'providerinvoice_id'));
    $this->addColumn(new SqlColumn($this->_tProviderInvoice, 'provider'));
    $this->addColumn(new SqlColumn($this->_tProviderInvoice, 'number'));
    $this->addColumn(new SqlColumn($this->_tProviderInvoice, 'create_date'));
    $this->addColumn(new SqlColumn($this->_tProviderInvoice, 'account_date'));
    $this->addColumn(new SqlColumn($this->_tProviderInvoice, 'due_date'));
    $this->addColumn(new SqlColumn($this->_tProviderInvoice, 'vs'));
    $this->addColumn(new SqlColumn($this->_tProviderInvoice, 'total_amount'));
    $this->addColumn(new SqlColumn($this->_tProviderInvoice, 'file'));
    $this->addColumn(new SqlColumn($this->_tProviderInvoice, 'created'));
    $this->addColumn(new SqlColumn($this->_tProviderInvoice, 'accounted'));
    $this->addColumn(new SqlColumn($this->_tProviderInvoice, 'paid'));
  }
  
  private function _insertFileTable() {
    $this->_tFile = new SqlTable('file', 'f');
    
    $this->addColumn(new SqlColumn($this->_tFile, 'file_id'));
    $this->addColumn(new SqlColumn($this->_tFile, 'hash', 'file_hash'));
    $this->addColumn(new SqlColumn($this->_tFile, 'name', 'file_name'));
    $this->addColumn(new SqlColumn($this->_tFile, 'mime', 'file_mime'));
    $this->addColumn(new SqlColumn($this->_tFile, 'length', 'file_length'));
  }

  private function _insertCustomerTable() {
    $this->_tCustomer = new SqlTable('customer', 'c');

    $this->addColumn(new SqlColumn($this->_tCustomer, 'customer_id'));
    $this->addColumn(new SqlColumn($this->_tCustomer, 'name', 'customer_name'));
    $this->addColumn(new SqlColumn($this->_tCustomer, 'provider', 'customer_provider'));
  }
  
  protected function _initSqlSelect() {
    $this->_insertProviderInvoiceTable();
    $this->_insertFileTable();
    $this->_insertCustomerTable();
    
    $this->addJoin(new SqlJoin(false, $this->_tFile, new SqlStatementBi($this->columns['file'], $this->columns['file_id'], '%s=%s')));
    $this->addJoin(new SqlJoin(false, $this->_tCustomer, new SqlStatementBi($this->columns['provider'], $this->columns['customer_provider'], '%s=%s')));
  }
}

?>
