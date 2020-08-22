<?php

class SResourceTag extends SqlSelect {
  private $_tResourceTag;
  private $_tResource;
  private $_tTag;
  private $_tProviderCustomer;
  
  private function _insertResourceTagTable() {
    $this->_tResourceTag = new SqlTable('resource_tag', 'et');
    
    $this->addColumn(new SqlColumn($this->_tResourceTag, 'resource'));
    $this->addColumn(new SqlColumn($this->_tResourceTag, 'tag'));
  }

  private function _insertResourceTable() {
    $this->_tResource = new SqlTable('resource', 'r');

    $this->addColumn(new SqlColumn($this->_tResource, 'resource_id'));
    $this->addColumn(new SqlColumn($this->_tResource, 'name', 'resource_name'));
    $this->addColumn(new SqlColumn($this->_tResource, 'provider', 'resource_provider'));
  }
  
  private function _insertTagTable() {
    $this->_tTag = new SqlTable('tag', 't');
    
    $this->addColumn(new SqlColumn($this->_tTag, 'tag_id'));
    $this->addColumn(new SqlColumn($this->_tTag, 'name'));
  }

  private function _insertProviderCustomerTable() {
    $this->_tProviderCustomer = new SqlTable('customer', 'c');

    $this->addColumn(new SqlColumn($this->_tProviderCustomer, 'customer_id'));
    $this->addColumn(new SqlColumn($this->_tProviderCustomer, 'name', 'provider_name'));
    $this->addColumn(new SqlColumn($this->_tProviderCustomer, 'provider', 'customer_provider'));
  }
  
  protected function _initSqlSelect() {
    $this->_insertResourceTagTable();
    $this->_insertResourceTable();
    $this->_insertTagTable();
    $this->_insertProviderCustomerTable();

    $this->addJoin(new SqlJoin(false, $this->_tResource, new SqlStatementBi($this->columns['resource'], $this->columns['resource_id'], '%s=%s')));
    $this->addJoin(new SqlJoin(false, $this->_tTag, new SqlStatementBi($this->columns['tag'], $this->columns['tag_id'], '%s=%s')));
    $this->addJoin(new SqlJoin(false, $this->_tProviderCustomer, new SqlStatementBi($this->columns['resource_provider'], $this->columns['customer_provider'], '%s=%s')));
  }
}

?>
