<?php

class SNotificationFile extends SqlSelect {
  private $_tNotificationFile;
  private $_tFile;
  
  private function _insertNotificationFileTable() {
    $this->_tNotificationFile = new SqlTable('notification_file', 'nf');
    
    $this->addColumn(new SqlColumn($this->_tNotificationFile, 'notification'));
    $this->addColumn(new SqlColumn($this->_tNotificationFile, 'file'));
  }

  private function _insertFileTable() {
    $this->_tFile = new SqlTable('file', 'f');

    $this->addColumn(new SqlColumn($this->_tFile, 'file_id'));
    $this->addColumn(new SqlColumn($this->_tFile, 'hash', 'file_hash'));
    $this->addColumn(new SqlColumn($this->_tFile, 'name', 'file_name'));
    $this->addColumn(new SqlColumn($this->_tFile, 'mime', 'file_mime'));
    $this->addColumn(new SqlColumn($this->_tFile, 'length', 'file_length'));
  }

  protected function _initSqlSelect() {
    $this->_insertNotificationFileTable();
    $this->_insertFileTable();

    $this->addJoin(new SqlJoin(false, $this->_tFile, new SqlStatementBi($this->columns['file'], $this->columns['file_id'], '%s=%s')));
  }
}

?>
