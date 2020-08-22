<?php

class SUserAttribute extends MySqlSelect {
  private $_tUserAttribute;
  private $_tAttribute;
  private $_tAttributeName;
  private $_tCustomer;
  
  private function _insertUserAttributeTable() {
    $this->_tUserAttribute = new SqlTable('user_attribute', 'ua');
    
    $this->addColumn(new SqlColumn($this->_tUserAttribute, 'user'));
    $this->addColumn(new SqlColumn($this->_tUserAttribute, 'attribute'));
    $this->addColumn(new SqlColumn($this->_tUserAttribute, 'value'));
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
  }
  
  private function _insertAttributeNameTable() {
    $this->_tAttributeName = new SqlTable('attributename', 'an');
    
    $this->addColumn(new SqlColumn($this->_tAttributeName, 'attributename_id'));
    $this->addColumn(new SqlColumn($this->_tAttributeName, 'attribute', 'attributename_attribute'));
    $this->addColumn(new SqlColumn($this->_tAttributeName, 'lang'));
    $this->addColumn(new SqlColumn($this->_tAttributeName, 'name', 'attribute_name'));
  }
  
  private function _insertProviderTable() {
    $this->_tCustomer = new SqlTable('customer', 'p');

    $this->addColumn(new SqlColumn($this->_tCustomer, 'customer_id'));
    $this->addColumn(new SqlColumn($this->_tCustomer, 'name', 'provider_name'));
    $this->addColumn(new SqlColumn($this->_tCustomer, 'provider', 'customer_provider'));
  }
  
  protected function _initSqlSelect() {
    $this->_insertUserAttributeTable();
    $this->_insertAttributeTable();
    $this->_insertAttributeNameTable();
    $this->_insertProviderTable();
    
    $this->addJoin(new SqlJoin(false, $this->_tAttribute, new SqlStatementBi($this->columns['attribute'], $this->columns['attribute_id'], '%s=%s')));
    $this->addJoin(new SqlJoin(false, $this->_tAttributeName, new SqlStatementBi($this->columns['attribute_id'], $this->columns['attributename_attribute'], '%s=%s')));
    $this->addJoin(new SqlJoin(false, $this->_tCustomer, new SqlStatementBi($this->columns['provider'], $this->columns['customer_provider'], '%s=%s')));
  }
}

?>
