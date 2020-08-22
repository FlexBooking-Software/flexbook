<?php

class SUserRegistration extends MySqlSelect {
  private $_tUserRegistration;
  private $_tUser;
  private $_tProvider;
  private $_tProviderCustomer;
  private $_sReservationCount;
  
  private function _insertUserRegistrationTable() {
    $this->_tUserRegistration = new SqlTable('userregistration', 'ur');
    
    $this->addColumn(new SqlColumn($this->_tUserRegistration, 'userregistration_id'));
    $this->addColumn(new SqlColumn($this->_tUserRegistration, 'user'));
    $this->addColumn(new SqlColumn($this->_tUserRegistration, 'provider'));
    $this->addColumn(new SqlColumn($this->_tUserRegistration, 'registration_timestamp'));
    $this->addColumn(new SqlColumn($this->_tUserRegistration, 'receive_advertising'));
    $this->addColumn(new SqlColumn($this->_tUserRegistration, 'credit'));
    $this->addColumn(new SqlColumn($this->_tUserRegistration, 'admin'));
    $this->addColumn(new SqlColumn($this->_tUserRegistration, 'supervisor'));
    $this->addColumn(new SqlColumn($this->_tUserRegistration, 'reception'));
    $this->addColumn(new SqlColumn($this->_tUserRegistration, 'organiser'));
    $this->addColumn(new SqlColumn($this->_tUserRegistration, 'power_organiser'));
    $this->addColumn(new SqlColumn($this->_tUserRegistration, 'role_center'));
  }
  
  private function _insertUserTable() {
    $this->_tUser = new SqlTable('user', 'u');

    $this->addColumn(new SqlColumn($this->_tUser, 'user_id'));
    $this->addColumn(new SqlColumn($this->_tUser, 'firstname'));
    $this->addColumn(new SqlColumn($this->_tUser, 'lastname'));
    $this->addColumn(new SqlColumn($this->_tUser, 'address'));
    $this->addColumn(new SqlColumn($this->_tUser, 'email'));
    
    $this->addColumn(new SqlColumn(false, new SqlStatementBi($this->columns['firstname'], $this->columns['lastname'], "CONCAT(%s,' ',%s)"), 'fullname'));
    $this->addColumn(new SqlColumn(false, new SqlStatementBi($this->columns['lastname'], $this->columns['firstname'], "CONCAT(%s,' ',%s)"), 'fullname_reversed'));
  }
  
  protected function _insertProviderTable() {
    $this->_tProvider = new SqlTable('provider', 'prov');
    
    $this->addColumn(new SqlColumn($this->_tProvider, 'provider_id'));
  }
  
  private function _insertProviderCustomerTable() {
    $this->_tProviderCustomer = new SqlTable('customer', 'c');

    $this->addColumn(new SqlColumn($this->_tProviderCustomer, 'customer_id'));
    $this->addColumn(new SqlColumn($this->_tProviderCustomer, 'name', 'provider_name'));
    $this->addColumn(new SqlColumn($this->_tProviderCustomer, 'provider', 'customer_provider'));
  }
  
  private function _insertReservationCountSelect() {
    $this->_sReservation = new SReservation;
    
    $this->_sReservation->addColumn(new SqlColumn($this->_tUserRegistration, 'user', 'outer_user', false, false, true));
    $this->_sReservation->addColumn(new SqlColumn($this->_tUserRegistration, 'provider', 'outer_provider', false, false, true));
    $this->_sReservation->addColumn(new SqlColumn(false, new SqlStatementMono($this->_sReservation->columns['reservation_id'], 'COUNT(%s)'), 'count'));
    $this->_sReservation->addStatement(new SqlStatementQuad($this->_sReservation->columns['user'], $this->_sReservation->columns['outer_user'],
                                                            $this->_sReservation->columns['provider'], $this->_sReservation->columns['outer_provider'], '%s=%s AND %s=%s'));
    $this->_sReservation->setColumnsMask(array('count'));
    
    $this->addColumn(new SqlColumn(false, $this->_sReservation, 'reservation_count'));
  }
  
  protected function _initSqlSelect() {
    $this->_insertUserRegistrationTable();
    $this->_insertUserTable();
    $this->_insertProviderTable();
    $this->_insertProviderCustomerTable();
    $this->_insertReservationCountSelect();

    $this->addJoin(new SqlJoin(false, $this->_tUser, new SqlStatementBi($this->columns['user'], $this->columns['user_id'], '%s=%s')));
    $this->addJoin(new SqlJoin(false, $this->_tProvider, new SqlStatementBi($this->columns['provider'], $this->columns['provider_id'], '%s=%s')));
    $this->addJoin(new SqlJoin(false, $this->_tProviderCustomer, new SqlStatementBi($this->columns['provider'], $this->columns['customer_provider'], '%s=%s')));
  }
}

?>
