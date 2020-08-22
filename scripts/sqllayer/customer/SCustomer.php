<?php

class SCustomer extends MySqlSelect {
  private $_tCustomer;
  private $_tCustomerRegistration;
  private $_tAddress;
  private $_tProvider;
  
  private $_sCustomerRegistration;
  
  private function _insertCustomerTable() {
    $this->_tCustomer = new SqlTable('customer', 'c');

    $this->addColumn(new SqlColumn($this->_tCustomer, 'customer_id'));
    $this->addColumn(new SqlColumn($this->_tCustomer, 'name'));
    $this->addColumn(new SqlColumn($this->_tCustomer, 'address'));
    $this->addColumn(new SqlColumn($this->_tCustomer, 'ic'));
    $this->addColumn(new SqlColumn($this->_tCustomer, 'dic'));
    $this->addColumn(new SqlColumn($this->_tCustomer, 'email'));
    $this->addColumn(new SqlColumn($this->_tCustomer, 'phone'));
    $this->addColumn(new SqlColumn($this->_tCustomer, 'provider'));
  }
  
  private function _insertCustomerRegistrationTable() {
    $this->_tCustomerRegistration = new SqlTable('customerregistration', 'cr');
    
    $this->addColumn(new SqlColumn($this->_tCustomerRegistration, 'customerregistration_id'));
    $this->addColumn(new SqlColumn($this->_tCustomerRegistration, 'customer', 'registration_customer'));
    $this->addColumn(new SqlColumn($this->_tCustomerRegistration, 'provider', 'registration_provider'));
    $this->addColumn(new SqlColumn($this->_tCustomerRegistration, 'registration_timestamp'));
    $this->addColumn(new SqlColumn($this->_tCustomerRegistration, 'credit', 'registration_credit'));
  }
  
  protected function _insertAddressTable() {
    $this->_tAddress = new SqlTable('address', 'a');
    
    $this->addColumn(new SqlColumn($this->_tAddress, 'address_id'));
    $this->addColumn(new SqlColumn($this->_tAddress, 'street'));
    $this->addColumn(new SqlColumn($this->_tAddress, 'city'));
    $this->addColumn(new SqlColumn($this->_tAddress, 'postal_code'));
    $this->addColumn(new SqlColumn($this->_tAddress, 'state'));
    $this->addColumn(new SqlColumn($this->_tAddress, 'gps_latitude'));
    $this->addColumn(new SqlColumn($this->_tAddress, 'gps_longitude'));
    
    $this->addColumn(new SqlColumn(false, new SqlStatementTri($this->columns['street'], $this->columns['city'], $this->columns['postal_code'], "CONCAT(%s,' ',%s,', ',%s)"), 'full_address'));
  }
  
  protected function _insertProviderTable() {
    $this->_tProvider = new SqlTable('provider', 'prov');
    
    $this->addColumn(new SqlColumn($this->_tProvider, 'provider_id'));
  }
  
  protected function _insertCustomerRegistrationSelect() {
    $this->_sCustomerRegistration = new SCustomerRegistration;
    
    $this->_sCustomerRegistration->addColumn(new SqlColumn($this->_tCustomer, 'customer_id', 'outer_customer', false, false, true));
    $this->_sCustomerRegistration->addColumn(new SqlColumn(false, new SqlStatementMono($this->_sCustomerRegistration->columns['customerregistration_id'], 'SUM(%s)'), 'sum'));
    $this->_sCustomerRegistration->addStatement(new SqlStatementBi($this->_sCustomerRegistration->columns['customer'], $this->_sCustomerRegistration->columns['outer_customer'], '%s=%s'));
    $this->_sCustomerRegistration->setColumnsMask(array('sum'));
    
    $this->addColumn(new SqlColumn(false, $this->_sCustomerRegistration, 'provider_registration'));
  }
  
  public function addCustomerRegistrationSelectStatement($columns, $condition) {
    $statement = null;
    switch (count($columns)) {
      case 1: $statement = new SqlStatementMono($this->_sCustomerRegistration->columns[$columns[0]], $condition); break;
      case 1: $statement = new SqlStatementBi($this->_sCustomerRegistration->columns[$columns[0]], $this->_sCustomerRegistration->columns[$columns[1]], $condition); break;
      default: break;
    }
    
    if ($statement) $this->_sCustomerRegistration->addStatement($statement);
  }

  protected function _initSqlSelect() {
    $this->_insertCustomerTable();
    $this->_insertCustomerRegistrationTable();
    $this->_insertAddressTable();
    $this->_insertProviderTable();
    
    $this->_insertCustomerRegistrationSelect();
    
    $this->addJoin(new SqlJoin('LEFT', $this->_tCustomerRegistration, new SqlStatementBi($this->columns['customer_id'], $this->columns['registration_customer'], '%s=%s')));
    $this->addJoin(new SqlJoin(false, $this->_tAddress, new SqlStatementBi($this->columns['address'], $this->columns['address_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tProvider, new SqlStatementBi($this->columns['provider'], $this->columns['provider_id'], '%s=%s')));
  }
}

?>
