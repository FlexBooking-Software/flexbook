<?php

class SCreditJournal extends MySqlSelect {
  private $_tCreditJournal;
  private $_tPrepaymentInvoice;
  private $_tUserRegistration;
  private $_tUser;
  private $_tUserAddress;
  private $_tProvider;
  private $_tProviderCustomer;
  private $_tProviderAddress;
  private $_tProviderInvoiceAddress;
  private $_tChangeUser;
  
  private function _insertCreditJournalTable() {
    $this->_tCreditJournal = new SqlTable('creditjournal', 'cj');
    
    $this->addColumn(new SqlColumn($this->_tCreditJournal, 'creditjournal_id'));
    $this->addColumn(new SqlColumn($this->_tCreditJournal, 'userregistration'));
    $this->addColumn(new SqlColumn($this->_tCreditJournal, 'provider'));
    $this->addColumn(new SqlColumn($this->_tCreditJournal, 'customer'));
    $this->addColumn(new SqlColumn($this->_tCreditJournal, 'amount'));
    $this->addColumn(new SqlColumn($this->_tCreditJournal, 'note'));
    $this->addColumn(new SqlColumn($this->_tCreditJournal, 'change_timestamp'));
    $this->addColumn(new SqlColumn($this->_tCreditJournal, 'change_user'));
    $this->addColumn(new SqlColumn($this->_tCreditJournal, 'flag'));
    $this->addColumn(new SqlColumn($this->_tCreditJournal, 'type'));
  }

  private function _insertPrepaymentInvoiceTable() {
    $this->_tPrepaymentInvoice = new SqlTable('prepaymentinvoice', 'pi');

    $this->addColumn(new SqlColumn($this->_tPrepaymentInvoice, 'prepaymentinvoice_id'));
    $this->addColumn(new SqlColumn($this->_tPrepaymentInvoice, 'number', 'prepaymentinvoice_number'));
    $this->addColumn(new SqlColumn($this->_tPrepaymentInvoice, 'creditjournal', 'prepaymentinvoice_creditjournal'));
    $this->addColumn(new SqlColumn($this->_tPrepaymentInvoice, 'content', 'prepaymentinvoice_content'));
  }
  
  private function _insertUserRegistrationTable() {
    $this->_tUserRegistration = new SqlTable('userregistration', 'ur');
    
    $this->addColumn(new SqlColumn($this->_tUserRegistration, 'userregistration_id'));
    $this->addColumn(new SqlColumn($this->_tUserRegistration, 'user'));
    $this->addColumn(new SqlColumn($this->_tUserRegistration, 'registration_timestamp'));
    $this->addColumn(new SqlColumn($this->_tUserRegistration, 'credit'));
  }
  
  private function _insertUserTable() {
    $this->_tUser = new SqlTable('user', 'u');

    $this->addColumn(new SqlColumn($this->_tUser, 'user_id'));
    $this->addColumn(new SqlColumn($this->_tUser, 'firstname', 'user_firstname'));
    $this->addColumn(new SqlColumn($this->_tUser, 'lastname', 'user_lastname'));
    $this->addColumn(new SqlColumn($this->_tUser, 'email', 'user_email'));
    $this->addColumn(new SqlColumn($this->_tUser, 'phone', 'user_phone'));
    $this->addColumn(new SqlColumn($this->_tUser, 'address', 'user_address'));
    
    $this->addColumn(new SqlColumn(false, new SqlStatementBi($this->columns['user_firstname'], $this->columns['user_lastname'], "CONCAT(%s,' ',%s)"), 'user_fullname'));
  }

  protected function _insertUserAddressTable() {
    $this->_tUserAddress = new SqlTable('address', 'ua');

    $this->addColumn(new SqlColumn($this->_tUserAddress, 'address_id', 'user_address_id'));
    $this->addColumn(new SqlColumn($this->_tUserAddress, 'street', 'user_street'));
    $this->addColumn(new SqlColumn($this->_tUserAddress, 'city', 'user_city'));
    $this->addColumn(new SqlColumn($this->_tUserAddress, 'postal_code', 'user_postal_code'));
    $this->addColumn(new SqlColumn($this->_tUserAddress, 'state', 'user_state'));
  }

  protected function _insertProviderTable() {
    $this->_tProvider = new SqlTable('provider', 'prov');

    $this->addColumn(new SqlColumn($this->_tProvider, 'provider_id'));
    $this->addColumn(new SqlColumn($this->_tProvider, 'phone_1', 'provider_phone_1'));
    $this->addColumn(new SqlColumn($this->_tProvider, 'phone_2', 'provider_phone_2'));
    $this->addColumn(new SqlColumn($this->_tProvider, 'www', 'provider_www'));
    $this->addColumn(new SqlColumn($this->_tProvider, 'vat', 'provider_vat'));
    $this->addColumn(new SqlColumn($this->_tProvider, 'vat_rate', 'provider_vat_rate'));
    $this->addColumn(new SqlColumn($this->_tProvider, 'bank_account_number', 'provider_bank_account_number'));
    $this->addColumn(new SqlColumn($this->_tProvider, 'bank_account_suffix', 'provider_bank_account_suffix'));
    $this->addColumn(new SqlColumn($this->_tProvider, 'invoice_other', 'provider_invoice_other'));
    $this->addColumn(new SqlColumn($this->_tProvider, 'invoice_name', 'provider_invoice_name'));
    $this->addColumn(new SqlColumn($this->_tProvider, 'invoice_address', 'provider_invoice_address'));
  }

  private function _insertProviderCustomerTable() {
    $this->_tProviderCustomer = new SqlTable('customer', 'pc');

    $this->addColumn(new SqlColumn($this->_tProviderCustomer, 'customer_id', 'provider_customer_id'));
    $this->addColumn(new SqlColumn($this->_tProviderCustomer, 'name', 'provider_name'));
    $this->addColumn(new SqlColumn($this->_tProviderCustomer, 'address', 'provider_address'));
    $this->addColumn(new SqlColumn($this->_tProviderCustomer, 'ic', 'provider_ic'));
    $this->addColumn(new SqlColumn($this->_tProviderCustomer, 'dic', 'provider_dic'));
    $this->addColumn(new SqlColumn($this->_tProviderCustomer, 'email', 'provider_email'));
    $this->addColumn(new SqlColumn($this->_tProviderCustomer, 'phone', 'provider_phone'));
    $this->addColumn(new SqlColumn($this->_tProviderCustomer, 'provider', 'customer_provider'));
  }

  protected function _insertProviderAddressTable() {
    $this->_tProviderAddress = new SqlTable('address', 'pa');

    $this->addColumn(new SqlColumn($this->_tProviderAddress, 'address_id', 'provider_address_id'));
    $this->addColumn(new SqlColumn($this->_tProviderAddress, 'street', 'provider_street'));
    $this->addColumn(new SqlColumn($this->_tProviderAddress, 'city', 'provider_city'));
    $this->addColumn(new SqlColumn($this->_tProviderAddress, 'postal_code', 'provider_postal_code'));
    $this->addColumn(new SqlColumn($this->_tProviderAddress, 'state', 'provider_state'));
  }

  protected function _insertProviderInvoiceAddressTable() {
    $this->_tProviderInvoiceAddress = new SqlTable('address', 'pia');

    $this->addColumn(new SqlColumn($this->_tProviderInvoiceAddress, 'address_id', 'provider_invoice_address_id'));
    $this->addColumn(new SqlColumn($this->_tProviderInvoiceAddress, 'street', 'provider_invoice_street'));
    $this->addColumn(new SqlColumn($this->_tProviderInvoiceAddress, 'city', 'provider_invoice_city'));
    $this->addColumn(new SqlColumn($this->_tProviderInvoiceAddress, 'postal_code', 'provider_invoice_postal_code'));
    $this->addColumn(new SqlColumn($this->_tProviderInvoiceAddress, 'state', 'provider_invoice_state'));
  }

  private function _insertChangeUserTable() {
    $this->_tChangeUser = new SqlTable('user', 'cu');

    $this->addColumn(new SqlColumn($this->_tChangeUser, 'user_id', 'change_user_id'));
    $this->addColumn(new SqlColumn($this->_tChangeUser, 'firstname', 'change_user_firstname'));
    $this->addColumn(new SqlColumn($this->_tChangeUser, 'lastname', 'change_user_lastname'));
    $this->addColumn(new SqlColumn($this->_tChangeUser, 'email', 'change_user_email'));
    $this->addColumn(new SqlColumn($this->_tChangeUser, 'phone', 'change_user_phone'));
    
    $this->addColumn(new SqlColumn(false, new SqlStatementBi($this->columns['change_user_firstname'], $this->columns['change_user_lastname'], "CONCAT(%s,' ',%s)"), 'change_user_fullname'));
  }

  protected function _initSqlSelect() {
    $this->_insertCreditJournalTable();
    $this->_insertPrepaymentInvoiceTable();
    $this->_insertUserRegistrationTable();
    $this->_insertUserTable();
    $this->_insertUserAddressTable();
    $this->_insertProviderTable();
    $this->_insertProviderCustomerTable();
    $this->_insertProviderAddressTable();
    $this->_insertProviderInvoiceAddressTable();
    $this->_insertChangeUserTable();

    $this->addJoin(new SqlJoin('LEFT', $this->_tPrepaymentInvoice, new SqlStatementBi($this->columns['prepaymentinvoice_creditjournal'], $this->columns['creditjournal_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tUserRegistration, new SqlStatementBi($this->columns['userregistration'], $this->columns['userregistration_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tUser, new SqlStatementBi($this->columns['user'], $this->columns['user_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tUserAddress, new SqlStatementBi($this->columns['user_address'], $this->columns['user_address_id'], '%s=%s')));
    $this->addJoin(new SqlJoin(false, $this->_tProvider, new SqlStatementBi($this->columns['provider'], $this->columns['provider_id'], '%s=%s')));
    $this->addJoin(new SqlJoin(false, $this->_tProviderCustomer, new SqlStatementBi($this->columns['provider'], $this->columns['customer_provider'], '%s=%s')));
    $this->addJoin(new SqlJoin(false, $this->_tProviderAddress, new SqlStatementBi($this->columns['provider_address'], $this->columns['provider_address_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tProviderInvoiceAddress, new SqlStatementBi($this->columns['provider_invoice_address'], $this->columns['provider_invoice_address_id'], '%s=%s')));
    $this->addJoin(new SqlJoin(false, $this->_tChangeUser, new SqlStatementBi($this->columns['change_user'], $this->columns['change_user_id'], '%s=%s')));
  }
}

?>
