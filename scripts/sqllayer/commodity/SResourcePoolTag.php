<?php

class SResourcePoolTag extends SqlSelect {
  private $_tResourcePoolTag;
  private $_tResourcePool;
  private $_tTag;
  private $_tProviderCustomer;
  
  private function _insertResourcePoolTagTable() {
    $this->_tResourcePoolTag = new SqlTable('resourcepool_tag', 'et');
    
    $this->addColumn(new SqlColumn($this->_tResourcePoolTag, 'resourcepool'));
    $this->addColumn(new SqlColumn($this->_tResourcePoolTag, 'tag'));
  }

  private function _insertResourcePoolTable() {
    $this->_tResourcePool = new SqlTable('resourcepool', 'rp');

    $this->addColumn(new SqlColumn($this->_tResourcePool, 'resourcepool_id'));
    $this->addColumn(new SqlColumn($this->_tResourcePool, 'name', 'resourcepool_name'));
    $this->addColumn(new SqlColumn($this->_tResourcePool, 'provider', 'resourcepool_provider'));
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
    $this->_insertResourcePoolTagTable();
    $this->_insertResourcePoolTable();
    $this->_insertTagTable();
    $this->_insertProviderCustomerTable();

    $this->addJoin(new SqlJoin(false, $this->_tResourcePool, new SqlStatementBi($this->columns['resourcepool'], $this->columns['resourcepool_id'], '%s=%s')));
    $this->addJoin(new SqlJoin(false, $this->_tTag, new SqlStatementBi($this->columns['tag'], $this->columns['tag_id'], '%s=%s')));
    $this->addJoin(new SqlJoin(false, $this->_tProviderCustomer, new SqlStatementBi($this->columns['resourcepool_provider'], $this->columns['customer_provider'], '%s=%s')));
  }
}

?>
