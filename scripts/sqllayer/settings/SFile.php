<?php

class SFile extends SqlSelect {
  private $_tFile;
  
  private function _insertFileTable() {
    $this->_tFile = new SqlTable('file', 'f');
    
    $this->addColumn(new SqlColumn($this->_tFile, 'file_id'));
    $this->addColumn(new SqlColumn($this->_tFile, 'hash'));
    $this->addColumn(new SqlColumn($this->_tFile, 'name'));
    $this->addColumn(new SqlColumn($this->_tFile, 'mime'));
    $this->addColumn(new SqlColumn($this->_tFile, 'length'));
    $this->addColumn(new SqlColumn($this->_tFile, 'content'));
  }
  
  protected function _initSqlSelect() {
    $this->_insertFileTable();
  }
}

?>
