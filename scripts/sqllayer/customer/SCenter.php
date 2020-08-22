<?php

class SCenter extends SqlSelect {
  private $_tCenter;
  private $_tAddress;
  private $_tProvider;
  private $_tCustomer;
  
  protected function _insertCenterTable() {
    $this->_tCenter = new SqlTable('center', 'cen');
    
    $this->addColumn(new SqlColumn($this->_tCenter, 'center_id'));
    $this->addColumn(new SqlColumn($this->_tCenter, 'name'));
    $this->addColumn(new SqlColumn($this->_tCenter, 'provider'));
    $this->addColumn(new SqlColumn($this->_tCenter, 'address'));
    $this->addColumn(new SqlColumn($this->_tCenter, 'payment_info'));
  }

  protected function _insertAddressTable() {
    $this->_tAddress = new SqlTable('address', 'a');
    
    $this->addColumn(new SqlColumn($this->_tAddress, 'address_id'));
    $this->addColumn(new SqlColumn($this->_tAddress, 'street'));
    $this->addColumn(new SqlColumn($this->_tAddress, 'city'));
    $this->addColumn(new SqlColumn($this->_tAddress, 'region'));
    $this->addColumn(new SqlColumn($this->_tAddress, 'postal_code'));
    $this->addColumn(new SqlColumn($this->_tAddress, 'state'));
    $this->addColumn(new SqlColumn($this->_tAddress, 'gps_latitude'));
    $this->addColumn(new SqlColumn($this->_tAddress, 'gps_longitude'));
    
    $this->addColumn(new SqlColumn(false, new SqlStatementTri($this->columns['street'], $this->columns['city'], $this->columns['postal_code'], "CONCAT(%s,', ',%s,' ',%s)"), 'full_address'));
  }
  
  protected function _insertProviderTable() {
    $this->_tProvider = new SqlTable('provider', 'prov');
    
    $this->addColumn(new SqlColumn($this->_tProvider, 'provider_id'));
    $this->addColumn(new SqlColumn($this->_tProvider, 'phone_1'));
    $this->addColumn(new SqlColumn($this->_tProvider, 'phone_2'));
    $this->addColumn(new SqlColumn($this->_tProvider, 'www'));
  }
  
  private function _insertCustomerTable() {
    $this->_tCustomer = new SqlTable('customer', 'cus');

    $this->addColumn(new SqlColumn($this->_tCustomer, 'customer_id'));
    $this->addColumn(new SqlColumn($this->_tCustomer, 'name', 'customer_name'));
    $this->addColumn(new SqlColumn($this->_tCustomer, 'address', 'customer_address'));
    $this->addColumn(new SqlColumn($this->_tCustomer, 'ic'));
    $this->addColumn(new SqlColumn($this->_tCustomer, 'dic'));
    $this->addColumn(new SqlColumn($this->_tCustomer, 'email'));
    $this->addColumn(new SqlColumn($this->_tCustomer, 'provider', 'customer_provider'));
  }
  
  protected function _initSqlSelect() {
    $this->_insertCenterTable();
    $this->_insertAddressTable();
    $this->_insertProviderTable();
    $this->_insertCustomerTable();
    
    $this->addColumn(new SqlColumn(false, new SqlStatementBi($this->columns['name'], $this->columns['full_address'], "CONCAT(%s,' - ',%s)"), 'description'));

    $this->addJoin(new SqlJoin(false, $this->_tAddress, new SqlStatementBi($this->columns['address'], $this->columns['address_id'], '%s=%s')));
    $this->addJoin(new SqlJoin(false, $this->_tProvider, new SqlStatementBi($this->columns['provider'], $this->columns['provider_id'], '%s=%s')));
    $this->addJoin(new SqlJoin(false, $this->_tCustomer, new SqlStatementBi($this->columns['customer_provider'], $this->columns['provider_id'], '%s=%s')));
  }
}

?>
