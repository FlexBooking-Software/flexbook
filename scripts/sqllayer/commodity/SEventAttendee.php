<?php

class SEventAttendee extends SqlSelect {
  private $_tEventAttendee;
  private $_tEventAttendeePerson;
  private $_tEventAttendeePersonUser;
  private $_tEvent;
  private $_tEventTag;
  private $_tUser;
  private $_tAddress;
  private $_tOrganiser;
  private $_tReservation;

  private $_sOccupied;
  private $_sOccupiedTable;
  private $_sEventTag;
  
  private function _insertEventAttendeeTable() {
    $this->_tEventAttendee = new SqlTable('eventattendee', 'ea');
    
    $this->addColumn(new SqlColumn($this->_tEventAttendee, 'eventattendee_id'));
    $this->addColumn(new SqlColumn($this->_tEventAttendee, 'event'));
    $this->addColumn(new SqlColumn($this->_tEventAttendee, 'reservation'));
    $this->addColumn(new SqlColumn($this->_tEventAttendee, 'places'));
    $this->addColumn(new SqlColumn($this->_tEventAttendee, 'substitute'));
    $this->addColumn(new SqlColumn($this->_tEventAttendee, 'substitute_mandatory'));
    $this->addColumn(new SqlColumn($this->_tEventAttendee, 'subscription_time'));
    $this->addColumn(new SqlColumn($this->_tEventAttendee, 'user'));
    $this->addColumn(new SqlColumn($this->_tEventAttendee, 'failed'));
    
    $this->addColumn(new SqlColumn(false, new SqlStatementMono($this->columns['places'], 'IFNULL(SUM(%s),0)'), 'sum_places', true));
  }
  
  private function _insertEventAttendeePersonTable() {
    $this->_tEventAttendeePerson = new SqlTable('eventattendeeperson', 'eap');
    
    $this->addColumn(new SqlColumn($this->_tEventAttendeePerson, 'eventattendeeperson_id'));
    $this->addColumn(new SqlColumn($this->_tEventAttendeePerson, 'eventattendee'));
    $this->addColumn(new SqlColumn($this->_tEventAttendeePerson, 'user', 'person_user'));
    $this->addColumn(new SqlColumn($this->_tEventAttendeePerson, 'firstname', 'person_firstname'));
    $this->addColumn(new SqlColumn($this->_tEventAttendeePerson, 'lastname', 'person_lastname'));
    $this->addColumn(new SqlColumn($this->_tEventAttendeePerson, 'email', 'person_email'));
    
    $this->addColumn(new SqlColumn(false, new SqlStatementTri($this->columns['person_firstname'], $this->columns['person_lastname'], $this->columns['person_email'], "CONCAT(%s,' ',%s,' (',%s,')')"), 'person_fullname_with_email'));
    $this->addColumn(new SqlColumn(false, new SqlStatementMono($this->columns['person_fullname_with_email'], "GROUP_CONCAT(%s)"), 'all_person_fullname_with_email', true));
  }

  private function _insertEventAttendeePersonUserTable() {
    $this->_tEventAttendeePersonUser = new SqlTable('user', 'eapu');

    $this->addColumn(new SqlColumn($this->_tEventAttendeePersonUser, 'user_id', 'person_user_id'));
    $this->addColumn(new SqlColumn($this->_tEventAttendeePersonUser, 'firstname', 'person_user_firstname'));
    $this->addColumn(new SqlColumn($this->_tEventAttendeePersonUser, 'lastname', 'person_user_lastname'));
    $this->addColumn(new SqlColumn($this->_tEventAttendeePersonUser, 'email', 'person_user_email'));
  }
  
  private function _insertEventTable() {
    $this->_tEvent = new SqlTable('event', 'e');
    
    $this->addColumn(new SqlColumn($this->_tEvent, 'event_id'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'external_id'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'name'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'description'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'provider'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'center'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'organiser'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'start'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'end'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'price'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'max_attendees'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'max_coattendees'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'reservation_max_attendees'));
  }

  private function _insertEventTagTable() {
    $this->_tEventTag = new SqlTable('event_tag', 'et');

    $this->addColumn(new SqlColumn($this->_tEventTag, 'event', 'et_event'));
    $this->addColumn(new SqlColumn($this->_tEventTag, 'tag', 'et_tag'));

    $this->addColumn(new SqlColumn(false, new SqlStatementMono($this->columns['et_tag'], 'GROUP_CONCAT(%s)'), 'all_event_tag', true));
  }
  
  private function _insertUserTable() {
    $this->_tUser = new SqlTable('user', 'u');

    $this->addColumn(new SqlColumn($this->_tUser, 'user_id'));
    $this->addColumn(new SqlColumn($this->_tUser, 'firstname', 'firstname'));
    $this->addColumn(new SqlColumn($this->_tUser, 'lastname', 'lastname'));
    $this->addColumn(new SqlColumn($this->_tUser, 'email', 'email'));
    $this->addColumn(new SqlColumn($this->_tUser, 'phone', 'phone'));
    $this->addColumn(new SqlColumn($this->_tUser, 'address', 'address'));
    
    $this->addColumn(new SqlColumn(false, new SqlStatementBi($this->columns['firstname'], $this->columns['lastname'], "CONCAT(%s,' ',%s)"), 'fullname'));
    
    $this->addColumn(new SqlColumn(false, new SqlStatementMono($this->columns['fullname'], "GROUP_CONCAT(%s)"), 'all_fullname', true));
    $this->addColumn(new SqlColumn(false, new SqlStatementMono($this->columns['user_id'], "GROUP_CONCAT(%s)"), 'all_user_id', true));
  }
  
  protected function _insertAddressTable() {
    $this->_tAddress = new SqlTable('address', 'a');
    
    $this->addColumn(new SqlColumn($this->_tAddress, 'address_id'));
    $this->addColumn(new SqlColumn($this->_tAddress, 'street'));
    $this->addColumn(new SqlColumn($this->_tAddress, 'city'));
    $this->addColumn(new SqlColumn($this->_tAddress, 'postal_code'));
    $this->addColumn(new SqlColumn($this->_tAddress, 'state'));
  }
  
  private function _insertOrganiserTable() {
    $this->_tOrganiser = new SqlTable('user', 'uo');

    $this->addColumn(new SqlColumn($this->_tOrganiser, 'user_id', 'organiser_user_id'));
    $this->addColumn(new SqlColumn($this->_tOrganiser, 'firstname', 'organiser_firstname'));
    $this->addColumn(new SqlColumn($this->_tOrganiser, 'lastname', 'organiser_lastname'));
    $this->addColumn(new SqlColumn($this->_tOrganiser, 'email', 'organiser_email'));
    
    $this->addColumn(new SqlColumn(false, new SqlStatementBi($this->columns['organiser_firstname'], $this->columns['organiser_lastname'], "CONCAT(%s,' ',%s)"), 'organiser_fullname'));
  }
  
  private function _insertReservationTable() {
    $this->_tReservation = new SqlTable('reservation', 'r');
    
    $this->addColumn(new SqlColumn($this->_tReservation, 'reservation_id'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'provider', 'reservation_provider'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'total_price', 'reservation_price'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'number', 'reservation_number'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'payed', 'reservation_payed'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'failed', 'reservation_failed'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'cancelled', 'reservation_cancelled'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'event_pack', 'reservation_event_pack'));
  }

  private function _insertOccupiedSelect() {
    $this->_sOccupied = new SqlSelect;
    $this->_sOccupiedTable = new SqlTable('eventattendee', 'oet');
    $this->_sOccupied->addColumn(new SqlColumn($this->_sOccupiedTable, 'event'));
    $this->_sOccupied->addColumn(new SqlColumn($this->_sOccupiedTable, 'places'));
    $this->_sOccupied->addColumn(new SqlColumn($this->_sOccupiedTable, 'substitute'));
    $this->_sOccupied->addColumn(new SqlColumn(false, new SqlStatementMono($this->_sOccupied->columns['places'], 'IFNULL(SUM(%s),0)'), 'sum_places', true));
    $this->_sOccupied->addColumn(new SqlColumn($this->_tEvent, 'event_id', 'event_id_outer', false, false, true));
    $this->_sOccupied->addStatement(new SqlStatementMono($this->_sOccupied->columns['substitute'], "%s='N'"));
    $this->_sOccupied->addStatement(new SqlStatementBi($this->_sOccupied->columns['event'], $this->_sOccupied->columns['event_id_outer'], '%s=%s'));
    $this->_sOccupied->setColumnsMask(array('sum_places'));

    $this->addColumn(new SqlColumn(false, $this->_sOccupied, 'occupied'));
    $this->addColumn(new SqlColumn(false, new SqlStatementBi($this->columns['max_attendees'], $this->columns['occupied'], '%s-%s'), 'free'));
  }

  private function _insertEventTagSelect() {
    $this->_sEventTag = new SEventTag;
    $this->_sEventTag->addColumn(new SqlColumn($this->_tEventAttendee, 'event', 'outer_event', false, false, true));
    $this->_sEventTag->addStatement(new SqlStatementBi($this->_sEventTag->columns['event'], $this->_sEventTag->columns['outer_event'], '%s=%s'));
    $this->_sEventTag->setColumnsMask(array('name'));

    $this->addColumn(new SqlColumn(false, $this->_sEventTag, 'event_all_tag_name'));
  }
  
  protected function _initSqlSelect() {
    $this->_insertEventAttendeeTable();
    $this->_insertEventAttendeePersonTable();
    $this->_insertEventAttendeePersonUserTable();
    $this->_insertEventTable();
    $this->_insertEventTagTable();
    $this->_insertUserTable();
    $this->_insertAddressTable();
    $this->_insertOrganiserTable();
    $this->_insertReservationTable();

    $this->_insertOccupiedSelect();
    $this->_insertEventTagSelect();
    
    $this->addJoin(new SqlJoin(false, $this->_tEventAttendeePerson, new SqlStatementBi($this->columns['eventattendee'], $this->columns['eventattendee_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tEventAttendeePersonUser, new SqlStatementBi($this->columns['person_user'], $this->columns['person_user_id'], '%s=%s')));
    $this->addJoin(new SqlJoin(false, $this->_tEvent, new SqlStatementBi($this->columns['event'], $this->columns['event_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tEventTag, new SqlStatementBi($this->columns['event'], $this->columns['et_event'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tUser, new SqlStatementBi($this->columns['user'], $this->columns['user_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tAddress, new SqlStatementBi($this->columns['address'], $this->columns['address_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tOrganiser, new SqlStatementBi($this->columns['organiser'], $this->columns['organiser_user_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tReservation, new SqlStatementBi($this->columns['reservation'], $this->columns['reservation_id'], '%s=%s')));
  }
}

?>
