<?php

class SAttributeName extends SqlSelect {
  private $_tAttributeName;
  
  private function _insertAttributeNameTable() {
    $this->_tAttributeName = new SqlTable('attributename', 'mn');
    
    $this->addColumn(new SqlColumn($this->_tAttributeName, 'attributename_id'));
    $this->addColumn(new SqlColumn($this->_tAttributeName, 'attribute'));
    $this->addColumn(new SqlColumn($this->_tAttributeName, 'lang'));
    $this->addColumn(new SqlColumn($this->_tAttributeName, 'name'));
  }
  
  protected function _initSqlSelect() {
    $this->_insertAttributeNameTable();
  }
}

?>
