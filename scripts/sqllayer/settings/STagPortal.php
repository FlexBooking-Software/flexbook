<?php

class STagPortal extends SqlSelect {
  private $_tTagPortal;
  
  private function _insertTagPortalTable() {
    $this->_tTagPortal = new SqlTable('tag_portal', 'tpor');
    
    $this->addColumn(new SqlColumn($this->_tTagPortal, 'tag'));
    $this->addColumn(new SqlColumn($this->_tTagPortal, 'portal'));
  }
  
  protected function _initSqlSelect() {
    $this->_insertTagPortalTable();
  }
}

?>
