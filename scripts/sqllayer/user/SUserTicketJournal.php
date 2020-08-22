<?php

class SUserTicketJournal extends MySqlSelect {
  private $_tUserTicketJournal;
  private $_tUserTicket;
  private $_tUser;
  private $_tTicket;
  private $_tChangeUser;
  
  private function _insertUserTicketJournalTable() {
    $this->_tUserTicketJournal = new SqlTable('userticketjournal', 'utj');
    
    $this->addColumn(new SqlColumn($this->_tUserTicketJournal, 'userticketjournal_id'));
    $this->addColumn(new SqlColumn($this->_tUserTicketJournal, 'userticket'));
    $this->addColumn(new SqlColumn($this->_tUserTicketJournal, 'amount'));
    $this->addColumn(new SqlColumn($this->_tUserTicketJournal, 'change_timestamp'));
    $this->addColumn(new SqlColumn($this->_tUserTicketJournal, 'change_user'));
    $this->addColumn(new SqlColumn($this->_tUserTicketJournal, 'flag'));
    $this->addColumn(new SqlColumn($this->_tUserTicketJournal, 'type'));
    $this->addColumn(new SqlColumn($this->_tUserTicketJournal, 'note'));
  }
  
  private function _insertUserTicketTable() {
    $this->_tUserTicket = new SqlTable('userticket', 'ut');
    
    $this->addColumn(new SqlColumn($this->_tUserTicket, 'userticket_id'));
    $this->addColumn(new SqlColumn($this->_tUserTicket, 'user'));
    $this->addColumn(new SqlColumn($this->_tUserTicket, 'ticket'));
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
    $this->addColumn(new SqlColumn($this->_tTicket, 'name'));
    $this->addColumn(new SqlColumn($this->_tTicket, 'provider'));
    $this->addColumn(new SqlColumn($this->_tTicket, 'from_timestamp'));
    $this->addColumn(new SqlColumn($this->_tTicket, 'to_timestamp'));
    $this->addColumn(new SqlColumn($this->_tTicket, 'center'));
    $this->addColumn(new SqlColumn($this->_tTicket, 'subject_tag'));
    $this->addColumn(new SqlColumn($this->_tTicket, 'price'));
    $this->addColumn(new SqlColumn($this->_tTicket, 'active'));
  }
  
  private function _insertChangeUserTable() {
    $this->_tChangeUser = new SqlTable('user', 'cu');

    $this->addColumn(new SqlColumn($this->_tChangeUser, 'user_id', 'change_user_id'));
    $this->addColumn(new SqlColumn($this->_tChangeUser, 'firstname', 'change_firstname'));
    $this->addColumn(new SqlColumn($this->_tChangeUser, 'lastname', 'change_lastname'));
    $this->addColumn(new SqlColumn($this->_tChangeUser, 'email', 'change_email'));
    
    $this->addColumn(new SqlColumn(false, new SqlStatementBi($this->columns['change_firstname'], $this->columns['change_lastname'], "CONCAT(%s,' ',%s)"), 'change_fullname'));
  }

  protected function _initSqlSelect() {
    $this->_insertUserTicketJournalTable();
    $this->_insertUserTicketTable();
    $this->_insertTicketTable();
    $this->_insertUserTable();
    $this->_insertChangeUserTable();
    
    $this->addJoin(new SqlJoin(false, $this->_tUserTicket, new SqlStatementBi($this->columns['userticket'], $this->columns['userticket_id'], '%s=%s')));
    $this->addJoin(new SqlJoin(false, $this->_tUser, new SqlStatementBi($this->columns['user'], $this->columns['user_id'], '%s=%s')));
    $this->addJoin(new SqlJoin(false, $this->_tTicket, new SqlStatementBi($this->columns['ticket'], $this->columns['ticket_id'], '%s=%s')));
    $this->addJoin(new SqlJoin(false, $this->_tChangeUser, new SqlStatementBi($this->columns['change_user'], $this->columns['user_id'], '%s=%s')));
  }
}

?>
