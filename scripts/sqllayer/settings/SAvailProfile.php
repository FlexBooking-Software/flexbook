<?php

class SAvailabilityProfile extends SqlSelect {
  private $_tAvailabilityProfile;
  private $_tCustomer;
  private $_tProvider;
  
  private function _insertAvailabilityProfileTable() {
    $this->_tAvailabilityProfile = new SqlTable('availabilityprofile', 'ap');

    $this->addColumn(new SqlColumn($this->_tAvailabilityProfile, 'availabilityprofile_id'));
    $this->addColumn(new SqlColumn($this->_tAvailabilityProfile, 'name'));
    $this->addColumn(new SqlColumn($this->_tAvailabilityProfile, 'provider'));
  }
  
  protected function _insertProviderTable() {
    $this->_tProvider = new SqlTable('provider', 'prov');
    
    $this->addColumn(new SqlColumn($this->_tProvider, 'provider_id'));
    $this->addColumn(new SqlColumn($this->_tProvider, 'phone_1'));
    $this->addColumn(new SqlColumn($this->_tProvider, 'phone_2'));
    $this->addColumn(new SqlColumn($this->_tProvider, 'www'));
  }
  
  private function _insertCustomerTable() {
    $this->_tCustomer = new SqlTable('customer', 'c');

    $this->addColumn(new SqlColumn($this->_tCustomer, 'customer_id'));
    $this->addColumn(new SqlColumn($this->_tCustomer, 'name', 'provider_name'));
    $this->addColumn(new SqlColumn($this->_tCustomer, 'address', 'provider_address_id'));
    $this->addColumn(new SqlColumn($this->_tCustomer, 'ic', 'provider_ic'));
    $this->addColumn(new SqlColumn($this->_tCustomer, 'dic', 'provider_dic'));
    $this->addColumn(new SqlColumn($this->_tCustomer, 'email', 'provider_email'));
    $this->addColumn(new SqlColumn($this->_tCustomer, 'provider', 'customer_provider'));
  }

  protected function _initSqlSelect() {
    $this->_insertAvailabilityProfileTable();
    $this->_insertProviderTable();
    $this->_insertCustomerTable();

    $this->addJoin(new SqlJoin('LEFT', $this->_tProvider, new SqlStatementBi($this->columns['provider'], $this->columns['provider_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tCustomer, new SqlStatementBi($this->columns['customer_provider'], $this->columns['provider_id'], '%s=%s')));
  }
}

?>
