<?php

class SProviderFile extends SqlSelect {
  private $_tProviderFile;
  private $_tFile;
  
  private function _insertProviderFileTable() {
    $this->_tProviderFile = new SqlTable('providerfile', 'pof');

    $this->addColumn(new SqlColumn($this->_tProviderFile, 'providerfile_id'));
    $this->addColumn(new SqlColumn($this->_tProviderFile, 'provider'));
    $this->addColumn(new SqlColumn($this->_tProviderFile, 'short_name'));
    $this->addColumn(new SqlColumn($this->_tProviderFile, 'name'));
    $this->addColumn(new SqlColumn($this->_tProviderFile, 'file'));
  }
  
  private function _insertFileTable() {
    $this->_tFile = new SqlTable('file', 'f');
    
    $this->addColumn(new SqlColumn($this->_tFile, 'file_id'));
    $this->addColumn(new SqlColumn($this->_tFile, 'hash'));
    $this->addColumn(new SqlColumn($this->_tFile, 'name', 'file_name'));
    $this->addColumn(new SqlColumn($this->_tFile, 'mime'));
    $this->addColumn(new SqlColumn($this->_tFile, 'length'));
  }
  
  protected function _initSqlSelect() {
    $this->_insertProviderFileTable();
    $this->_insertFileTable();
    
    $this->addJoin(new SqlJoin(false, $this->_tFile, new SqlStatementBi($this->columns['file'], $this->columns['file_id'], '%s=%s')));
  }
}

?>
