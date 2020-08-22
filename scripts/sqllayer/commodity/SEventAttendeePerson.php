<?php

class SEventAttendeePerson extends SqlSelect {
  private $_tEventAttendeePerson;
  private $_tEventAttendee;
  private $_tEvent;
  private $_tReservation;
  private $_tUser;
  
  private function _insertEventAttendeePersonTable() {
    $this->_tEventAttendeePerson = new SqlTable('eventattendeeperson', 'eap');
    
    $this->addColumn(new SqlColumn($this->_tEventAttendeePerson, 'eventattendeeperson_id'));
    $this->addColumn(new SqlColumn($this->_tEventAttendeePerson, 'eventattendee'));
    $this->addColumn(new SqlColumn($this->_tEventAttendeePerson, 'user', 'subaccount'));
    $this->addColumn(new SqlColumn($this->_tEventAttendeePerson, 'firstname'));
    $this->addColumn(new SqlColumn($this->_tEventAttendeePerson, 'lastname'));
    $this->addColumn(new SqlColumn($this->_tEventAttendeePerson, 'email'));
    
    $this->addColumn(new SqlColumn(false, new SqlStatementBi($this->columns['firstname'], $this->columns['lastname'], "CONCAT(%s,' ',%s)"), 'fullname'));
  }
  
  private function _insertEventAttendeeTable() {
    $this->_tEventAttendee = new SqlTable('eventattendee', 'ea');
    
    $this->addColumn(new SqlColumn($this->_tEventAttendee, 'eventattendee_id'));
    $this->addColumn(new SqlColumn($this->_tEventAttendee, 'event'));
    $this->addColumn(new SqlColumn($this->_tEventAttendee, 'reservation'));
    $this->addColumn(new SqlColumn($this->_tEventAttendee, 'places'));
    $this->addColumn(new SqlColumn($this->_tEventAttendee, 'substitute'));
    $this->addColumn(new SqlColumn($this->_tEventAttendee, 'subscription_time'));
    $this->addColumn(new SqlColumn($this->_tEventAttendee, 'user'));
    $this->addColumn(new SqlColumn($this->_tEventAttendee, 'failed'));
  }
  
  private function _insertEventTable() {
    $this->_tEvent = new SqlTable('event', 'e');
    
    $this->addColumn(new SqlColumn($this->_tEvent, 'event_id'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'name'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'provider'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'external_id'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'center'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'start'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'end'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'description'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'max_attendees'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'max_coattendees'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'max_substitutes'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'organiser'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'price'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'active'));
  }

  private function _insertReservationTable() {
    $this->_tReservation = new SqlTable('reservation', 'r');

    $this->addColumn(new SqlColumn($this->_tReservation, 'reservation_id'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'number','reservation_number'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'event_places','reservation_event_places'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'event_pack','reservation_event_pack'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'payed','reservation_payed'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'failed','reservation_failed'));
  }

  private function _insertUserTable() {
    $this->_tUser = new SqlTable('user', 'u');

    $this->addColumn(new SqlColumn($this->_tUser, 'user_id', 'subaccount_id'));
    $this->addColumn(new SqlColumn($this->_tUser, 'firstname', 'subaccount_firstname'));
    $this->addColumn(new SqlColumn($this->_tUser, 'lastname', 'subaccount_lastname'));
    $this->addColumn(new SqlColumn($this->_tUser, 'email', 'subaccount_email'));
  }

  protected function _initSqlSelect() {
    $this->_insertEventAttendeePersonTable();
    $this->_insertEventAttendeeTable();
    $this->_insertEventTable();
    $this->_insertReservationTable();
    $this->_insertUserTable();
    
    $this->addJoin(new SqlJoin(false, $this->_tEventAttendee, new SqlStatementBi($this->columns['eventattendee'], $this->columns['eventattendee_id'], '%s=%s')));
    $this->addJoin(new SqlJoin(false, $this->_tEvent, new SqlStatementBi($this->columns['event'], $this->columns['event_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tReservation, new SqlStatementBi($this->columns['reservation'], $this->columns['reservation_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tUser, new SqlStatementBi($this->columns['subaccount'], $this->columns['subaccount_id'], '%s=%s')));
  }
}

?>
