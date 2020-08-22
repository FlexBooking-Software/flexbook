<?php

class SEventTag extends SqlSelect {
  private $_tEventTag;
  private $_tEvent;
  private $_tTag;
  private $_tProviderCustomer;
  
  private function _insertEventTagTable() {
    $this->_tEventTag = new SqlTable('event_tag', 'et');
    
    $this->addColumn(new SqlColumn($this->_tEventTag, 'event'));
    $this->addColumn(new SqlColumn($this->_tEventTag, 'tag'));
  }
  
  private function _insertTagTable() {
    $this->_tTag = new SqlTable('tag', 't');
    
    $this->addColumn(new SqlColumn($this->_tTag, 'tag_id'));
    $this->addColumn(new SqlColumn($this->_tTag, 'name'));
  }

  private function _insertEventTable() {
    $this->_tEvent = new SqlTable('event', 'e');

    $this->addColumn(new SqlColumn($this->_tEvent, 'event_id'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'start', 'event_start'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'name', 'event_name'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'provider', 'event_provider'));
  }

  private function _insertProviderCustomerTable() {
    $this->_tProviderCustomer = new SqlTable('customer', 'c');

    $this->addColumn(new SqlColumn($this->_tProviderCustomer, 'customer_id'));
    $this->addColumn(new SqlColumn($this->_tProviderCustomer, 'name', 'provider_name'));
    $this->addColumn(new SqlColumn($this->_tProviderCustomer, 'provider', 'customer_provider'));
  }
  
  protected function _initSqlSelect() {
    $this->_insertEventTagTable();
    $this->_insertEventTable();
    $this->_insertTagTable();
    $this->_insertProviderCustomerTable();
    
    $this->addJoin(new SqlJoin(false, $this->_tEvent, new SqlStatementBi($this->columns['event'], $this->columns['event_id'], '%s=%s')));
    $this->addJoin(new SqlJoin(false, $this->_tTag, new SqlStatementBi($this->columns['tag'], $this->columns['tag_id'], '%s=%s')));
    $this->addJoin(new SqlJoin(false, $this->_tProviderCustomer, new SqlStatementBi($this->columns['event_provider'], $this->columns['customer_provider'], '%s=%s')));
  }
}

?>
