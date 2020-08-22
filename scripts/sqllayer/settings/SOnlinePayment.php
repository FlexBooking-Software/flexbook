<?php

class SOnlinePayment extends SqlSelect {
  private $_tOnlinePayment;
  private $_tUserRegistration;
  private $_tUser;
  private $_tTicket;
  
  private function _insertOnlinePaymentTable() {
    $this->_tOnlinePayment = new SqlTable('onlinepayment', 'op');
    
    $this->addColumn(new SqlColumn($this->_tOnlinePayment, 'onlinepayment_id'));
    $this->addColumn(new SqlColumn($this->_tOnlinePayment, 'amount'));
    $this->addColumn(new SqlColumn($this->_tOnlinePayment, 'target'));
    $this->addColumn(new SqlColumn($this->_tOnlinePayment, 'target_id'));
    $this->addColumn(new SqlColumn($this->_tOnlinePayment, 'target_params'));
    $this->addColumn(new SqlColumn($this->_tOnlinePayment, 'userregistration'));
    $this->addColumn(new SqlColumn($this->_tOnlinePayment, 'start_timestamp'));
    $this->addColumn(new SqlColumn($this->_tOnlinePayment, 'end_timestamp'));
    $this->addColumn(new SqlColumn($this->_tOnlinePayment, 'type'));
    $this->addColumn(new SqlColumn($this->_tOnlinePayment, 'paymentid'));
    $this->addColumn(new SqlColumn($this->_tOnlinePayment, 'payed'));
    $this->addColumn(new SqlColumn($this->_tOnlinePayment, 'paymentdesc'));
    $this->addColumn(new SqlColumn($this->_tOnlinePayment, 'status'));
    $this->addColumn(new SqlColumn($this->_tOnlinePayment, 'refund_timestamp'));
  }

  private function _insertUserRegistrationTable() {
    $this->_tUserRegistration = new SqlTable('userregistration', 'ur');

    $this->addColumn(new SqlColumn($this->_tUserRegistration, 'userregistration_id'));
    $this->addColumn(new SqlColumn($this->_tUserRegistration, 'user'));
    $this->addColumn(new SqlColumn($this->_tUserRegistration, 'provider', 'user_provider'));
  }

  protected function _insertUserTable() {
    $this->_tUser = new SqlTable('user', 'u');

    $this->addColumn(new SqlColumn($this->_tUser, 'user_id'));
    $this->addColumn(new SqlColumn($this->_tUser, 'firstname', 'user_firstname'));
    $this->addColumn(new SqlColumn($this->_tUser, 'lastname', 'user_lastname'));

    $this->addColumn(new SqlColumn(false, new SqlStatementBi($this->columns['user_firstname'], $this->columns['user_lastname'], "CONCAT(%s,' ',%s)"), 'user_fullname'));
  }

  protected function _insertTicketTable() {
    $this->_tTicket = new SqlTable('ticket', 't');

    $this->addColumn(new SqlColumn($this->_tTicket, 'ticket_id'));
    $this->addColumn(new SqlColumn($this->_tTicket, 'name', 'ticket_name'));
    $this->addColumn(new SqlColumn($this->_tTicket, 'provider', 'ticket_provider'));
  }
  
  protected function _initSqlSelect() {
    $this->_insertOnlinePaymentTable();
    $this->_insertUserRegistrationTable();
    $this->_insertUserTable();
    $this->_insertTicketTable();

    $this->addJoin(new SqlJoin('LEFT', $this->_tUserRegistration, new SqlStatementBi($this->columns['userregistration'], $this->columns['userregistration_id'], "%s=%s")));
    $this->addJoin(new SqlJoin('LEFT', $this->_tUser, new SqlStatementBi($this->columns['user'], $this->columns['user_id'], "%s=%s")));
    $this->addJoin(new SqlJoin('LEFT', $this->_tTicket, new SqlStatementTri($this->columns['target'], $this->columns['target_id'], $this->columns['ticket_id'], "%s='TICKET' AND %s=%s")));
  }
}

?>
