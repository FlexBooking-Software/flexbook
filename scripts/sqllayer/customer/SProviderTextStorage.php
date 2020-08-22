<?php

class SProviderTextStorage extends SqlSelect {
  private $_tProviderTextStorage;

  private function _insertProviderTextStorageTable() {
    $this->_tProviderTextStorage = new SqlTable('providertextstorage', 'pts');

    $this->addColumn(new SqlColumn($this->_tProviderTextStorage, 'providertextstorage_id'));
    $this->addColumn(new SqlColumn($this->_tProviderTextStorage, 'provider'));
    $this->addColumn(new SqlColumn($this->_tProviderTextStorage, 'language'));
    $this->addColumn(new SqlColumn($this->_tProviderTextStorage, 'ts_key'));
    $this->addColumn(new SqlColumn($this->_tProviderTextStorage, 'original_value'));
    $this->addColumn(new SqlColumn($this->_tProviderTextStorage, 'new_value'));
  }
  
  protected function _initSqlSelect() {
    $this->_insertProviderTextStorageTable();

  }
}

?>
