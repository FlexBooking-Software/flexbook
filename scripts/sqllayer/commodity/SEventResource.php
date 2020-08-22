<?php

class SEventResource extends SqlSelect {
  private $_tEventResource;
  private $_tResource;
  
  private function _insertEventResourceTable() {
    $this->_tEventResource = new SqlTable('event_resource', 'er');
    
    $this->addColumn(new SqlColumn($this->_tEventResource, 'event'));
    $this->addColumn(new SqlColumn($this->_tEventResource, 'resource'));
  }
  
  private function _insertResourceTable() {
    $this->_tResource = new SqlTable('resource', 'r');
    
    $this->addColumn(new SqlColumn($this->_tResource, 'resource_id'));
    $this->addColumn(new SqlColumn($this->_tResource, 'name'));
    $this->addColumn(new SqlColumn($this->_tResource, 'provider'));
    $this->addColumn(new SqlColumn($this->_tResource, 'center'));
    $this->addColumn(new SqlColumn($this->_tResource, 'description'));
    $this->addColumn(new SqlColumn($this->_tResource, 'price'));
    $this->addColumn(new SqlColumn($this->_tResource, 'reservationcondition'));
    $this->addColumn(new SqlColumn($this->_tResource, 'active'));
  }
  
  protected function _initSqlSelect() {
    $this->_insertEventResourceTable();
    $this->_insertResourceTable();
    
    $this->addJoin(new SqlJoin('LEFT', $this->_tResource, new SqlStatementBi($this->columns['resource'], $this->columns['resource_id'], '%s=%s')));
  }
}

?>
