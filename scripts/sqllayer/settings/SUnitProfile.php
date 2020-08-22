<?php

class SUnitProfile extends SqlSelect {
  private $_tUnitProfile;
  private $_tCustomer;
  private $_tProvider;
  
  private function _insertUnitProfileTable() {
    $this->_tUnitProfile = new SqlTable('unitprofile', 'up');

    $this->addColumn(new SqlColumn($this->_tUnitProfile, 'unitprofile_id'));
    $this->addColumn(new SqlColumn($this->_tUnitProfile, 'provider'));
    $this->addColumn(new SqlColumn($this->_tUnitProfile, 'name'));
    $this->addColumn(new SqlColumn($this->_tUnitProfile, 'unit'));
    $this->addColumn(new SqlColumn($this->_tUnitProfile, 'unit_rounding'));
    $this->addColumn(new SqlColumn($this->_tUnitProfile, 'minimum_quantity'));
    $this->addColumn(new SqlColumn($this->_tUnitProfile, 'maximum_quantity'));
    $this->addColumn(new SqlColumn($this->_tUnitProfile, 'time_alignment_from'));
    $this->addColumn(new SqlColumn($this->_tUnitProfile, 'time_alignment_to'));
    $this->addColumn(new SqlColumn($this->_tUnitProfile, 'time_alignment_grid'));
    $this->addColumn(new SqlColumn($this->_tUnitProfile, 'time_end_from'));
    $this->addColumn(new SqlColumn($this->_tUnitProfile, 'time_end_to'));
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
    $this->_insertUnitProfileTable();
    $this->_insertProviderTable();
    $this->_insertCustomerTable();

    $this->addJoin(new SqlJoin('LEFT', $this->_tProvider, new SqlStatementBi($this->columns['provider'], $this->columns['provider_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tCustomer, new SqlStatementBi($this->columns['customer_provider'], $this->columns['provider_id'], '%s=%s')));
  }
}

?>
