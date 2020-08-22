<?php

class SProviderPortal extends SqlSelect {
  private $_tProviderPortal;
  private $_tProvider;
  private $_tProviderCustomer;
  
  private $_sPageCountSelect;
  
  private function _insertProviderPortalTable() {
    $this->_tProviderPortal = new SqlTable('providerportal', 'pop');

    $this->addColumn(new SqlColumn($this->_tProviderPortal, 'providerportal_id'));
    $this->addColumn(new SqlColumn($this->_tProviderPortal, 'provider'));
    $this->addColumn(new SqlColumn($this->_tProviderPortal, 'from_template'));
    $this->addColumn(new SqlColumn($this->_tProviderPortal, 'name'));
    $this->addColumn(new SqlColumn($this->_tProviderPortal, 'url_name'));
    $this->addColumn(new SqlColumn($this->_tProviderPortal, 'active'));
    $this->addColumn(new SqlColumn($this->_tProviderPortal, 'css'));
    $this->addColumn(new SqlColumn($this->_tProviderPortal, 'javascript'));
    $this->addColumn(new SqlColumn($this->_tProviderPortal, 'content'));
    $this->addColumn(new SqlColumn($this->_tProviderPortal, 'home_page'));
  }
  
  protected function _insertProviderTable() {
    $this->_tProvider = new SqlTable('provider', 'prov');
    
    $this->addColumn(new SqlColumn($this->_tProvider, 'provider_id'));
    $this->addColumn(new SqlColumn($this->_tProvider, 'short_name', 'provider_short_name'));
    $this->addColumn(new SqlColumn($this->_tProvider, 'phone_1', 'provider_phone_1'));
    $this->addColumn(new SqlColumn($this->_tProvider, 'phone_2', 'provider_phone_2'));
    $this->addColumn(new SqlColumn($this->_tProvider, 'www', 'provider_www'));
  }
  
  private function _insertProviderCustomerTable() {
    $this->_tProviderCustomer = new SqlTable('customer', 'c');

    $this->addColumn(new SqlColumn($this->_tProviderCustomer, 'customer_id'));
    $this->addColumn(new SqlColumn($this->_tProviderCustomer, 'name', 'provider_name'));
    $this->addColumn(new SqlColumn($this->_tProviderCustomer, 'address', 'provider_address_id'));
    $this->addColumn(new SqlColumn($this->_tProviderCustomer, 'ic', 'provider_ic'));
    $this->addColumn(new SqlColumn($this->_tProviderCustomer, 'dic', 'provider_dic'));
    $this->addColumn(new SqlColumn($this->_tProviderCustomer, 'email', 'provider_email'));
    $this->addColumn(new SqlColumn($this->_tProviderCustomer, 'provider', 'customer_provider'));
  }
  
  private function _insertPageCountSelect() {
    $this->_sPageCountSelect = new SProviderPortalPage;
    $this->_sPageCountSelect->addColumn(new SqlColumn($this->_tProviderPortal, 'providerportal_id', 'providerportal_outer', false, false, true));
    $this->_sPageCountSelect->addColumn(new SqlColumn(false, new SqlStatementMono($this->_sPageCountSelect->columns['providerportalpage_id'], 'COUNT(%s)'), 'page_count', true));
    $this->_sPageCountSelect->addStatement(new SqlStatementBi($this->_sPageCountSelect->columns['providerportal'], $this->_sPageCountSelect->columns['providerportal_outer'], '%s=%s'));
    $this->_sPageCountSelect->setColumnsMask(array('page_count'));
    
    $this->addColumn(new SqlColumn(false, $this->_sPageCountSelect, 'page_count'));
  }
  
  protected function _initSqlSelect() {
    $this->_insertProviderPortalTable();
    $this->_insertProviderTable();
    $this->_insertProviderCustomerTable();
    
    $this->addJoin(new SqlJoin('LEFT', $this->_tProvider, new SqlStatementBi($this->columns['provider'], $this->columns['provider_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tProviderCustomer, new SqlStatementBi($this->columns['customer_provider'], $this->columns['provider_id'], '%s=%s')));
    
    $this->_insertPageCountSelect();    
  }
}

?>
