<?php

class SPortal extends MySqlSelect {
  private $_tPortal;
  
  private function _insertPortalTable() {
    $this->_tPortal = new SqlTable('portal', 'p');

    $this->addColumn(new SqlColumn($this->_tPortal, 'portal_id'));
    $this->addColumn(new SqlColumn($this->_tPortal, 'name'));
  }
  
  protected function _initSqlSelect() {
    $this->_insertPortalTable();
  }
}

?>
