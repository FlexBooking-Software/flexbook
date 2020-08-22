<?php

class SNotificationTemplate extends SqlSelect {
  private $_tNotificationTemplate;
  private $_tCustomer;
  private $_tProvider;
  
  private function _insertNotificationTemplateTable() {
    $this->_tNotificationTemplate = new SqlTable('notificationtemplate', 'nt');

    $this->addColumn(new SqlColumn($this->_tNotificationTemplate, 'notificationtemplate_id'));
    $this->addColumn(new SqlColumn($this->_tNotificationTemplate, 'provider'));
    $this->addColumn(new SqlColumn($this->_tNotificationTemplate, 'name'));
    $this->addColumn(new SqlColumn($this->_tNotificationTemplate, 'target'));
    $this->addColumn(new SqlColumn($this->_tNotificationTemplate, 'description'));
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
    $this->_insertNotificationTemplateTable();
    $this->_insertProviderTable();
    $this->_insertCustomerTable();

    $this->addJoin(new SqlJoin(false, $this->_tProvider, new SqlStatementBi($this->columns['provider'], $this->columns['provider_id'], '%s=%s')));
    $this->addJoin(new SqlJoin(false, $this->_tCustomer, new SqlStatementBi($this->columns['customer_provider'], $this->columns['provider_id'], '%s=%s')));
  }
}

?>
