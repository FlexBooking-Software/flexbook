<?php

class SOrganiser extends SqlSelect {
  private $_tUser;
  private $_tUserRegistration;
  
  private function _insertUserTable() {
    $this->_tUser = new SqlTable('user', 'u');

    $this->addColumn(new SqlColumn($this->_tUser, 'user_id'));
    $this->addColumn(new SqlColumn($this->_tUser, 'firstname'));
    $this->addColumn(new SqlColumn($this->_tUser, 'lastname'));
    $this->addColumn(new SqlColumn($this->_tUser, 'email'));
    $this->addColumn(new SqlColumn($this->_tUser, 'username'));
    $this->addColumn(new SqlColumn($this->_tUser, 'password'));
    $this->addColumn(new SqlColumn($this->_tUser, 'organiser'));
    $this->addColumn(new SqlColumn($this->_tUser, 'disabled'));
    
    $this->addColumn(new SqlColumn(false, new SqlStatementBi($this->columns['firstname'], $this->columns['lastname'], "CONCAT(%s,' ',%s)"), 'fullname'));
  }
  
  private function _insertUserRegistrationTable() {
    $this->_tUserRegistration = new SqlTable('userregistration', 'ur');
    
    $this->addColumn(new SqlColumn($this->_tUserRegistration, 'userregistration_id'));
    $this->addColumn(new SqlColumn($this->_tUserRegistration, 'user', 'registration_user'));
    $this->addColumn(new SqlColumn($this->_tUserRegistration, 'provider', 'registration_provider'));
    $this->addColumn(new SqlColumn($this->_tUserRegistration, 'organiser', 'registration_organiser'));
  }
  
  protected function _initSqlSelect() {
    $this->_insertUserTable();
    $this->_insertUserRegistrationTable();

    $this->addJoin(new SqlJoin(false, $this->_tUserRegistration, new SqlStatementTri($this->columns['user_id'], $this->columns['registration_user'], $this->columns['registration_organiser'], "%s=%s AND %s='Y'")));
  }
}

?>
