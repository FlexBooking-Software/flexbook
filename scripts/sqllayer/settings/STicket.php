<?php

class STicket extends SqlSelect {
  private $_tTicket;
  private $_tProviderCustomer;
  
  protected function _insertTicketTable() {
    $this->_tTicket = new SqlTable('ticket', 't');
    
    $this->addColumn(new SqlColumn($this->_tTicket, 'ticket_id'));
    $this->addColumn(new SqlColumn($this->_tTicket, 'name'));
    $this->addColumn(new SqlColumn($this->_tTicket, 'provider'));
    $this->addColumn(new SqlColumn($this->_tTicket, 'validity_type'));
    $this->addColumn(new SqlColumn($this->_tTicket, 'validity_count'));
    $this->addColumn(new SqlColumn($this->_tTicket, 'validity_unit'));
    $this->addColumn(new SqlColumn($this->_tTicket, 'validity_from'));
    $this->addColumn(new SqlColumn($this->_tTicket, 'validity_to'));
    $this->addColumn(new SqlColumn($this->_tTicket, 'center'));
    $this->addColumn(new SqlColumn($this->_tTicket, 'subject_tag'));
    $this->addColumn(new SqlColumn($this->_tTicket, 'price'));
    $this->addColumn(new SqlColumn($this->_tTicket, 'value'));
    $this->addColumn(new SqlColumn($this->_tTicket, 'active'));
  }
  
  private function _insertProviderCustomerTable() {
    $this->_tProviderCustomer = new SqlTable('customer', 'c');

    $this->addColumn(new SqlColumn($this->_tProviderCustomer, 'customer_id'));
    $this->addColumn(new SqlColumn($this->_tProviderCustomer, 'name', 'provider_name'));
    $this->addColumn(new SqlColumn($this->_tProviderCustomer, 'provider', 'customer_provider'));
  }
  
  protected function _initSqlSelect() {
    $this->_insertTicketTable();
    $this->_insertProviderCustomerTable();
    
    $this->addJoin(new SqlJoin(false, $this->_tProviderCustomer, new SqlStatementBi($this->columns['provider'], $this->columns['customer_provider'], '%s=%s')));
  }
}

?>
