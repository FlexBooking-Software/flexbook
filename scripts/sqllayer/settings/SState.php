<?php

class SState extends SqlSelect {
  private $_tState;
  
  private function _insertStateTable() {
    $this->_tState = new SqlTable('state', 's');
    
    $this->addColumn(new SqlColumn($this->_tState, 'state_id'));
    $this->addColumn(new SqlColumn($this->_tState, 'code'));
    $this->addColumn(new SqlColumn($this->_tState, 'name'));
    $this->addColumn(new SqlColumn($this->_tState, 'disabled'));
  }
  
  protected function _initSqlSelect() {
    $this->_insertStateTable();
    
    $this->addOrder(new SqlStatementAsc($this->columns['name']));
  }
}

?>
