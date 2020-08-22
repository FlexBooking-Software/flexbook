<?php

class SProviderPortalFile extends SqlSelect {
  private $_tProviderPortalFile;
  private $_tFile;
  
  private function _insertProviderPortalFileTable() {
    $this->_tProviderPortalFile = new SqlTable('providerportalfile', 'popf');

    $this->addColumn(new SqlColumn($this->_tProviderPortalFile, 'providerportalfile_id'));
    $this->addColumn(new SqlColumn($this->_tProviderPortalFile, 'providerportal'));
    $this->addColumn(new SqlColumn($this->_tProviderPortalFile, 'name'));
    $this->addColumn(new SqlColumn($this->_tProviderPortalFile, 'type'));
    $this->addColumn(new SqlColumn($this->_tProviderPortalFile, 'file'));
  }
  
  private function _insertFileTable() {
    $this->_tFile = new SqlTable('file', 'f');
    
    $this->addColumn(new SqlColumn($this->_tFile, 'file_id'));
    $this->addColumn(new SqlColumn($this->_tFile, 'mime'));
    $this->addColumn(new SqlColumn($this->_tFile, 'length'));
  }
  
  protected function _initSqlSelect() {
    $this->_insertProviderPortalFileTable();
    $this->_insertFileTable();
    
    $this->addJoin(new SqlJoin(false, $this->_tFile, new SqlStatementBi($this->columns['file'], $this->columns['file_id'], '%s=%s')));
  }
}

?>
