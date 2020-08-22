<?php

class SAttribute extends SqlSelect {
  private $_tAttribute;
  private $_tAttributeName;
  private $_tCustomer;
  
  private function _insertAttributeTable() {
    $this->_tAttribute = new SqlTable('attribute', 'm');
    
    $this->addColumn(new SqlColumn($this->_tAttribute, 'attribute_id'));
    $this->addColumn(new SqlColumn($this->_tAttribute, 'provider'));
    $this->addColumn(new SqlColumn($this->_tAttribute, 'applicable'));
    $this->addColumn(new SqlColumn($this->_tAttribute, 'applicable_type'));
    $this->addColumn(new SqlColumn($this->_tAttribute, 'short_name'));
    $this->addColumn(new SqlColumn($this->_tAttribute, 'url'));
    $this->addColumn(new SqlColumn($this->_tAttribute, 'restricted'));
    $this->addColumn(new SqlColumn($this->_tAttribute, 'mandatory'));
    $this->addColumn(new SqlColumn($this->_tAttribute, 'category'));
    $this->addColumn(new SqlColumn($this->_tAttribute, 'sequence'));
    $this->addColumn(new SqlColumn($this->_tAttribute, 'type'));
    $this->addColumn(new SqlColumn($this->_tAttribute, 'allowed_values'));
    $this->addColumn(new SqlColumn($this->_tAttribute, 'disabled'));
  }
  
  private function _insertAttributeNameTable() {
    $this->_tAttributeName = new SqlTable('attributename', 'mn');
    
    $this->addColumn(new SqlColumn($this->_tAttributeName, 'attributename_id'));
    $this->addColumn(new SqlColumn($this->_tAttributeName, 'attribute'));
    $this->addColumn(new SqlColumn($this->_tAttributeName, 'lang'));
    $this->addColumn(new SqlColumn($this->_tAttributeName, 'name'));
  }
  
  private function _insertCustomerTable() {
    $this->_tCustomer = new SqlTable('customer', 'c');

    $this->addColumn(new SqlColumn($this->_tCustomer, 'customer_id'));
    $this->addColumn(new SqlColumn($this->_tCustomer, 'name', 'customer_name'));
    $this->addColumn(new SqlColumn($this->_tCustomer, 'provider', 'customer_provider'));
  }
  
  protected function _initSqlSelect() {
    $this->_insertAttributeTable();
    $this->_insertAttributeNameTable();
    $this->_insertCustomerTable();
    
    $this->addColumn(new SqlColumn(false, new SqlStatementMono($this->columns['name'], 'GROUP_CONCAT(%s)'), 'all_name', true));
    
    $this->addJoin(new SqlJoin(false, $this->_tAttributeName, new SqlStatementBi($this->columns['attribute_id'], $this->columns['attribute'], '%s=%s')));
    $this->addJoin(new SqlJoin(false, $this->_tCustomer, new SqlStatementBi($this->columns['provider'], $this->columns['customer_provider'], '%s=%s')));
  }
}

?>
