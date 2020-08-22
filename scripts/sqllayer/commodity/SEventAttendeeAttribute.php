<?php

class SEventAttendeeAttribute extends MySqlSelect {
  private $_tEventAttendeeAttribute;
  private $_tAttribute;
  private $_tAttributeName;
  
  private function _insertEventAttendeeAttributeTable() {
    $this->_tEventAttendeeAttribute = new SqlTable('eventattendee_attribute', 'eaa');
    
    $this->addColumn(new SqlColumn($this->_tEventAttendeeAttribute, 'eventattendee'));
    $this->addColumn(new SqlColumn($this->_tEventAttendeeAttribute, 'attribute'));
    $this->addColumn(new SqlColumn($this->_tEventAttendeeAttribute, 'value'));
  }
  
  private function _insertAttributeTable() {
    $this->_tAttribute = new SqlTable('attribute', 'a');
    
    $this->addColumn(new SqlColumn($this->_tAttribute, 'attribute_id'));
    $this->addColumn(new SqlColumn($this->_tAttribute, 'provider'));
    $this->addColumn(new SqlColumn($this->_tAttribute, 'restricted'));
    $this->addColumn(new SqlColumn($this->_tAttribute, 'short_name'));
    $this->addColumn(new SqlColumn($this->_tAttribute, 'mandatory'));
    $this->addColumn(new SqlColumn($this->_tAttribute, 'category'));
    $this->addColumn(new SqlColumn($this->_tAttribute, 'sequence'));
    $this->addColumn(new SqlColumn($this->_tAttribute, 'type'));
    $this->addColumn(new SqlColumn($this->_tAttribute, 'allowed_values'));
    $this->addColumn(new SqlColumn($this->_tAttribute, 'disabled'));
    $this->addColumn(new SqlColumn($this->_tAttribute, 'applicable'));
  }
  
  private function _insertAttributeNameTable() {
    $this->_tAttributeName = new SqlTable('attributename', 'an');
    
    $this->addColumn(new SqlColumn($this->_tAttributeName, 'attributename_id'));
    $this->addColumn(new SqlColumn($this->_tAttributeName, 'attribute', 'attributename_attribute'));
    $this->addColumn(new SqlColumn($this->_tAttributeName, 'lang'));
    $this->addColumn(new SqlColumn($this->_tAttributeName, 'name', 'attribute_name'));
  }
  
  protected function _initSqlSelect() {
    $this->_insertEventAttendeeAttributeTable();
    $this->_insertAttributeTable();
    $this->_insertAttributeNameTable();
    
    $this->addJoin(new SqlJoin(false, $this->_tAttribute, new SqlStatementBi($this->columns['attribute'], $this->columns['attribute_id'], '%s=%s')));
    $this->addJoin(new SqlJoin(false, $this->_tAttributeName, new SqlStatementBi($this->columns['attribute_id'], $this->columns['attributename_attribute'], '%s=%s')));
  }
}

?>
