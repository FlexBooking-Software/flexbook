<?php

class SEmployee extends SqlSelect {
  private $_tEmployee;
  private $_tUser;
  private $_tUserRegistration;
  private $_tCustomer;
  
  protected function _insertEmployeeTable() {
    $this->_tEmployee = new SqlTable('employee', 'e');
    
    $this->addColumn(new SqlColumn($this->_tEmployee, 'employee_id'));
    $this->addColumn(new SqlColumn($this->_tEmployee, 'customer'));
    $this->addColumn(new SqlColumn($this->_tEmployee, 'user'));
    $this->addColumn(new SqlColumn($this->_tEmployee, 'credit_access'));
  }
  
  private function _insertUserTable() {
    $this->_tUser = new SqlTable('user', 'u');

    $this->addColumn(new SqlColumn($this->_tUser, 'user_id'));
    $this->addColumn(new SqlColumn($this->_tUser, 'firstname'));
    $this->addColumn(new SqlColumn($this->_tUser, 'lastname'));
    $this->addColumn(new SqlColumn($this->_tUser, 'email'));
    
    $this->addColumn(new SqlColumn(false, new SqlStatementBi($this->columns['firstname'], $this->columns['lastname'], "CONCAT(%s,' ',%s)"), 'fullname'));
  }
  
  private function _insertUserRegistrationTable() {
    $this->_tUserRegistration = new SqlTable('userregistration', 'ur');
    
    $this->addColumn(new SqlColumn($this->_tUserRegistration, 'userregistration_id'));
    $this->addColumn(new SqlColumn($this->_tUserRegistration, 'user', 'registration_user'));
    $this->addColumn(new SqlColumn($this->_tUserRegistration, 'provider', 'registration_provider'));
  }
  
  private function _insertCustomerTable() {
    $this->_tCustomer = new SqlTable('customer', 'c');

    $this->addColumn(new SqlColumn($this->_tCustomer, 'customer_id'));
    $this->addColumn(new SqlColumn($this->_tCustomer, 'name', 'customer_name'));
    $this->addColumn(new SqlColumn($this->_tCustomer, 'email', 'customer_email'));
    $this->addColumn(new SqlColumn($this->_tCustomer, 'provider', 'customer_provider'));
  }
  
  protected function _initSqlSelect() {
    $this->_insertEmployeeTable();
    $this->_insertUserTable();
    $this->_insertUserRegistrationTable();
    $this->_insertCustomerTable();
    
    $this->addJoin(new SqlJoin(false, $this->_tUser, new SqlStatementBi($this->columns['user'], $this->columns['user_id'], '%s=%s')));
    $this->addJoin(new SqlJoin(false, $this->_tUserRegistration, new SqlStatementBi($this->columns['user'], $this->columns['registration_user'], '%s=%s')));
    $this->addJoin(new SqlJoin(false, $this->_tCustomer, new SqlStatementBi($this->columns['customer'], $this->columns['customer_id'], '%s=%s')));
  }
}

?>
