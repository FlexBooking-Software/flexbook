<?php

class SCustomerRegistration extends MySqlSelect {
  private $_tCustomerRegistration;
  private $_tCustomer;
  private $_tProvider;
  private $_tProviderCustomer;
  
  private function _insertCustomerRegistrationTable() {
    $this->_tCustomerRegistration = new SqlTable('customerregistration', 'cr');
    
    $this->addColumn(new SqlColumn($this->_tCustomerRegistration, 'customerregistration_id'));
    $this->addColumn(new SqlColumn($this->_tCustomerRegistration, 'customer'));
    $this->addColumn(new SqlColumn($this->_tCustomerRegistration, 'provider'));
    $this->addColumn(new SqlColumn($this->_tCustomerRegistration, 'registration_timestamp'));
    $this->addColumn(new SqlColumn($this->_tCustomerRegistration, 'receive_advertising'));
    $this->addColumn(new SqlColumn($this->_tCustomerRegistration, 'credit'));
  }
  
  private function _insertCustomerTable() {
    $this->_tCustomer = new SqlTable('customer', 'c');

    $this->addColumn(new SqlColumn($this->_tCustomer, 'customer_id'));
    $this->addColumn(new SqlColumn($this->_tCustomer, 'name'));
    $this->addColumn(new SqlColumn($this->_tCustomer, 'address'));
    $this->addColumn(new SqlColumn($this->_tCustomer, 'ic'));
    $this->addColumn(new SqlColumn($this->_tCustomer, 'dic'));
    $this->addColumn(new SqlColumn($this->_tCustomer, 'email'));
  }
  
  protected function _insertProviderTable() {
    $this->_tProvider = new SqlTable('provider', 'prov');
    
    $this->addColumn(new SqlColumn($this->_tProvider, 'provider_id'));
  }
  
  private function _insertProviderCustomerTable() {
    $this->_tProviderCustomer = new SqlTable('customer', 'pc');

    $this->addColumn(new SqlColumn($this->_tProviderCustomer, 'customer_id', 'provider_customer_id'));
    $this->addColumn(new SqlColumn($this->_tProviderCustomer, 'name', 'provider_name'));
    $this->addColumn(new SqlColumn($this->_tProviderCustomer, 'provider', 'provider_provider'));
  }

  protected function _initSqlSelect() {
    $this->_insertCustomerRegistrationTable();
    $this->_insertCustomerTable();
    $this->_insertProviderTable();
    $this->_insertProviderCustomerTable();

    $this->addJoin(new SqlJoin(false, $this->_tCustomer, new SqlStatementBi($this->columns['customer'], $this->columns['customer_id'], '%s=%s')));
    $this->addJoin(new SqlJoin(false, $this->_tProvider, new SqlStatementBi($this->columns['provider'], $this->columns['provider_id'], '%s=%s')));
    $this->addJoin(new SqlJoin(false, $this->_tProviderCustomer, new SqlStatementBi($this->columns['provider_id'], $this->columns['provider_provider'], '%s=%s')));
  }
}

?>
