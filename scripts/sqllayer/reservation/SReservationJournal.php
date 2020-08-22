<?php

class SReservationJournal extends MySqlSelect {
  private $_tReservationJournal;
  private $_tReservation;
  private $_tUser;
  
  private function _insertReservationJournalTable() {
    $this->_tReservationJournal = new SqlTable('reservationjournal', 'rj');
    
    $this->addColumn(new SqlColumn($this->_tReservationJournal, 'reservationjournal_id'));
    $this->addColumn(new SqlColumn($this->_tReservationJournal, 'reservation'));
    $this->addColumn(new SqlColumn($this->_tReservationJournal, 'change_timestamp'));
    $this->addColumn(new SqlColumn($this->_tReservationJournal, 'change_user'));
    $this->addColumn(new SqlColumn($this->_tReservationJournal, 'change_ip'));
    $this->addColumn(new SqlColumn($this->_tReservationJournal, 'action'));
    $this->addColumn(new SqlColumn($this->_tReservationJournal, 'note'));
    $this->addColumn(new SqlColumn($this->_tReservationJournal, 'note_2'));
  }
  
  private function _insertReservationTable() {
    $this->_tReservation = new SqlTable('reservation', 'r');
    
    $this->addColumn(new SqlColumn($this->_tReservation, 'reservation_id'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'number'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'created'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'failed'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'cancelled'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'payed'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'user'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'customer'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'provider'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'center'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'total_price'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'resource'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'resource_from'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'resource_to'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'event'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'event_places'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'total_price'));
  }
  
  private function _insertChangeUserTable() {
    $this->_tUser = new SqlTable('user', 'u');

    $this->addColumn(new SqlColumn($this->_tUser, 'user_id'));
    $this->addColumn(new SqlColumn($this->_tUser, 'firstname'));
    $this->addColumn(new SqlColumn($this->_tUser, 'lastname'));
    $this->addColumn(new SqlColumn($this->_tUser, 'email'));
    
    $this->addColumn(new SqlColumn(false, new SqlStatementBi($this->columns['firstname'], $this->columns['lastname'], "CONCAT(%s,' ',%s)"), 'fullname'));
  }
  
  protected function _initSqlSelect() {
    $this->_insertReservationJournalTable();
    $this->_insertReservationTable();
    $this->_insertChangeUserTable();
    
    $this->addJoin(new SqlJoin(false, $this->_tReservation, new SqlStatementBi($this->columns['reservation'], $this->columns['reservation_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tUser, new SqlStatementBi($this->columns['change_user'], $this->columns['user_id'], '%s=%s')));
  }
}

?>
