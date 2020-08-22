<?php

class SUserTicket extends MySqlSelect {
  private $_tUserTicket;
  private $_tUser;
  private $_tTicket;
  private $_tProviderCustomer;
  
  private function _insertUserTicketTable() {
    $this->_tUserTicket = new SqlTable('userticket', 'ut');
    
    $this->addColumn(new SqlColumn($this->_tUserTicket, 'userticket_id'));
    $this->addColumn(new SqlColumn($this->_tUserTicket, 'user'));
    $this->addColumn(new SqlColumn($this->_tUserTicket, 'ticket'));
    $this->addColumn(new SqlColumn($this->_tUserTicket, 'from_timestamp'));
    $this->addColumn(new SqlColumn($this->_tUserTicket, 'to_timestamp'));
    $this->addColumn(new SqlColumn($this->_tUserTicket, 'name'));
    $this->addColumn(new SqlColumn($this->_tUserTicket, 'original_value'));
    $this->addColumn(new SqlColumn($this->_tUserTicket, 'value'));
    $this->addColumn(new SqlColumn($this->_tUserTicket, 'created'));
  }
  
  private function _insertUserTable() {
    $this->_tUser = new SqlTable('user', 'u');

    $this->addColumn(new SqlColumn($this->_tUser, 'user_id'));
    $this->addColumn(new SqlColumn($this->_tUser, 'firstname'));
    $this->addColumn(new SqlColumn($this->_tUser, 'lastname'));
    $this->addColumn(new SqlColumn($this->_tUser, 'address'));
    $this->addColumn(new SqlColumn($this->_tUser, 'email'));
    
    $this->addColumn(new SqlColumn(false, new SqlStatementBi($this->columns['firstname'], $this->columns['lastname'], "CONCAT(%s,' ',%s)"), 'fullname'));
  }
  
  protected function _insertTicketTable() {
    $this->_tTicket = new SqlTable('ticket', 't');
    
    $this->addColumn(new SqlColumn($this->_tTicket, 'ticket_id'));
    $this->addColumn(new SqlColumn($this->_tTicket, 'provider'));
    $this->addColumn(new SqlColumn($this->_tTicket, 'center'));
    $this->addColumn(new SqlColumn($this->_tTicket, 'subject_tag'));
    $this->addColumn(new SqlColumn($this->_tTicket, 'price'));
    $this->addColumn(new SqlColumn($this->_tTicket, 'active'));
  }
  
  private function _insertProviderCustomerTable() {
    $this->_tProviderCustomer = new SqlTable('customer', 'c');

    $this->addColumn(new SqlColumn($this->_tProviderCustomer, 'customer_id'));
    $this->addColumn(new SqlColumn($this->_tProviderCustomer, 'name', 'provider_name'));
    $this->addColumn(new SqlColumn($this->_tProviderCustomer, 'provider', 'customer_provider'));
  }
  
  protected function _initSqlSelect() {
    $this->_insertUserTicketTable();
    $this->_insertUserTable();
    $this->_insertTicketTable();
    $this->_insertProviderCustomerTable();

    $this->addColumn(new SqlColumn(false, "'PERIOD'", 'validity_type'));  // kvuli obecnemu GuiGridCellValidFor
    $this->addColumn(new SqlColumn($this->_tUserTicket, 'from_timestamp', 'validity_from'));
    $this->addColumn(new SqlColumn($this->_tUserTicket, 'to_timestamp', 'validity_to'));

    $this->addJoin(new SqlJoin(false, $this->_tUser, new SqlStatementBi($this->columns['user'], $this->columns['user_id'], '%s=%s')));
    $this->addJoin(new SqlJoin(false, $this->_tTicket, new SqlStatementBi($this->columns['ticket'], $this->columns['ticket_id'], '%s=%s')));
    $this->addJoin(new SqlJoin(false, $this->_tProviderCustomer, new SqlStatementBi($this->columns['provider'], $this->columns['customer_provider'], '%s=%s')));
  }
}

?>
