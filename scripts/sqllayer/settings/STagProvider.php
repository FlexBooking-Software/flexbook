<?php

class STagProvider extends SqlSelect {
  private $_tTagProvider;
  
  private function _insertTagProviderTable() {
    $this->_tTagProvider = new SqlTable('tag_provider', 'tpro');
    
    $this->addColumn(new SqlColumn($this->_tTagProvider, 'tag'));
    $this->addColumn(new SqlColumn($this->_tTagProvider, 'provider'));
  }
  
  protected function _initSqlSelect() {
    $this->_insertTagProviderTable();
  }
}

?>
