<?php

class SProviderSettings extends SqlSelect {
  private $_tProviderSettings;
  private $_tProvider;
  private $_tCustomer;
  private $_tInvoiceAddress;
  private $_tDocumentTemplate;
  private $_tDocumentTemplateItem;
  
  protected function _insertProviderSettingsTable() {
    $this->_tProviderSettings = new SqlTable('providersettings', 'ps');
    
    $this->addColumn(new SqlColumn($this->_tProviderSettings, 'provider'));
    $this->addColumn(new SqlColumn($this->_tProviderSettings, 'userregistration_validate'));
    $this->addColumn(new SqlColumn($this->_tProviderSettings, 'allow_user_subaccount'));
    $this->addColumn(new SqlColumn($this->_tProviderSettings, 'badge_photo'));
    $this->addColumn(new SqlColumn($this->_tProviderSettings, 'badge_template'));
    $this->addColumn(new SqlColumn($this->_tProviderSettings, 'ticket_template'));
    $this->addColumn(new SqlColumn($this->_tProviderSettings, 'generate_accounting'));
    $this->addColumn(new SqlColumn($this->_tProviderSettings, 'receipt_number'));
    $this->addColumn(new SqlColumn($this->_tProviderSettings, 'receipt_template'));
    $this->addColumn(new SqlColumn($this->_tProviderSettings, 'prepayment_invoice_number'));
    $this->addColumn(new SqlColumn($this->_tProviderSettings, 'prepayment_invoice_template'));
    $this->addColumn(new SqlColumn($this->_tProviderSettings, 'invoice_number'));
    $this->addColumn(new SqlColumn($this->_tProviderSettings, 'invoice_template'));
    $this->addColumn(new SqlColumn($this->_tProviderSettings, 'creditnote_number'));
    $this->addColumn(new SqlColumn($this->_tProviderSettings, 'creditnote_template'));
    $this->addColumn(new SqlColumn($this->_tProviderSettings, 'show_company'));
    $this->addColumn(new SqlColumn($this->_tProviderSettings, 'reservation_cancel_message'));
    $this->addColumn(new SqlColumn($this->_tProviderSettings, 'smtp_host'));
    $this->addColumn(new SqlColumn($this->_tProviderSettings, 'smtp_port'));
    $this->addColumn(new SqlColumn($this->_tProviderSettings, 'smtp_user'));
    $this->addColumn(new SqlColumn($this->_tProviderSettings, 'smtp_password'));
    $this->addColumn(new SqlColumn($this->_tProviderSettings, 'smtp_secure'));
    $this->addColumn(new SqlColumn($this->_tProviderSettings, 'invoice_account_from'));
    $this->addColumn(new SqlColumn($this->_tProviderSettings, 'invoice_month_fee'));
    $this->addColumn(new SqlColumn($this->_tProviderSettings, 'invoice_reservation_fee'));
    $this->addColumn(new SqlColumn($this->_tProviderSettings, 'invoice_reservation_price_fee'));
    $this->addColumn(new SqlColumn($this->_tProviderSettings, 'invoice_reservation_price_paid'));
    $this->addColumn(new SqlColumn($this->_tProviderSettings, 'invoice_due_length'));
    $this->addColumn(new SqlColumn($this->_tProviderSettings, 'invoice_email'));
    $this->addColumn(new SqlColumn($this->_tProviderSettings, 'allow_skip_reservation_condition'));
    $this->addColumn(new SqlColumn($this->_tProviderSettings, 'user_reservationcondition'));
    $this->addColumn(new SqlColumn($this->_tProviderSettings, 'documenttemplate'));
    $this->addColumn(new SqlColumn($this->_tProviderSettings, 'user_unique'));
    $this->addColumn(new SqlColumn($this->_tProviderSettings, 'user_subaccount_unique'));
    $this->addColumn(new SqlColumn($this->_tProviderSettings, 'allow_mandatory_reservation'));
    $this->addColumn(new SqlColumn($this->_tProviderSettings, 'organiser_mandatory_reservation'));
    $this->addColumn(new SqlColumn($this->_tProviderSettings, 'organiser_mandatory_substitute'));
    $this->addColumn(new SqlColumn($this->_tProviderSettings, 'disable_credit'));
    $this->addColumn(new SqlColumn($this->_tProviderSettings, 'disable_ticket'));
    $this->addColumn(new SqlColumn($this->_tProviderSettings, 'disable_cash'));
    $this->addColumn(new SqlColumn($this->_tProviderSettings, 'disable_online'));
  }

  protected function _insertProviderTable() {
    $this->_tProvider = new SqlTable('provider', 'p');

    $this->addColumn(new SqlColumn($this->_tProvider, 'provider_id'));
    $this->addColumn(new SqlColumn($this->_tProvider, 'invoice_name'));
    $this->addColumn(new SqlColumn($this->_tProvider, 'invoice_address'));
    $this->addColumn(new SqlColumn($this->_tProvider, 'vat'));
    $this->addColumn(new SqlColumn($this->_tProvider, 'vat_rate'));
  }
  
  private function _insertCustomerTable() {
    $this->_tCustomer = new SqlTable('customer', 'c');

    $this->addColumn(new SqlColumn($this->_tCustomer, 'customer_id'));
    $this->addColumn(new SqlColumn($this->_tCustomer, 'name'));
    $this->addColumn(new SqlColumn($this->_tCustomer, 'address'));
    $this->addColumn(new SqlColumn($this->_tCustomer, 'ic'));
    $this->addColumn(new SqlColumn($this->_tCustomer, 'dic'));
    $this->addColumn(new SqlColumn($this->_tCustomer, 'email'));
    $this->addColumn(new SqlColumn($this->_tCustomer, 'phone'));
    $this->addColumn(new SqlColumn($this->_tCustomer, 'provider', 'customer_provider'));
  }

  protected function _insertInvoiceAddressTable() {
    $this->_tInvoiceAddress = new SqlTable('address', 'ia');

    $this->addColumn(new SqlColumn($this->_tInvoiceAddress, 'address_id', 'invoice_address_id'));
    $this->addColumn(new SqlColumn($this->_tInvoiceAddress, 'street', 'invoice_street'));
    $this->addColumn(new SqlColumn($this->_tInvoiceAddress, 'city', 'invoice_city'));
    $this->addColumn(new SqlColumn($this->_tInvoiceAddress, 'postal_code', 'invoice_postal_code'));

    $this->addColumn(new SqlColumn(false, new SqlStatementTri($this->columns['invoice_street'], $this->columns['invoice_city'], $this->columns['invoice_postal_code'], "CONCAT(%s,' ',%s,', ',%s)"), 'invoice_full_address'));
  }

  private function _insertDocumentTemplateTable() {
    $this->_tDocumentTemplate = new SqlTable('documenttemplate', 'dt');

    $this->addColumn(new SqlColumn($this->_tDocumentTemplate, 'documenttemplate_id'));
    $this->addColumn(new SqlColumn($this->_tDocumentTemplate, 'documenttemplate_provider'));
  }

  private function _insertDocumentTemplateItemTable() {
    $this->_tDocumentTemplateItem = new SqlTable('documenttemplateitem', 'dti');

    $this->addColumn(new SqlColumn($this->_tDocumentTemplateItem, 'documenttemplateitem_id'));
    $this->addColumn(new SqlColumn($this->_tDocumentTemplateItem, 'documenttemplate', 'documenttemplateitem_documenttemplate'));
    $this->addColumn(new SqlColumn($this->_tDocumentTemplateItem, 'name', 'documenttemplateitem_name'));
    $this->addColumn(new SqlColumn($this->_tDocumentTemplateItem, 'code', 'documenttemplateitem_code'));
    $this->addColumn(new SqlColumn($this->_tDocumentTemplateItem, 'type', 'documenttemplateitem_type'));
    $this->addColumn(new SqlColumn($this->_tDocumentTemplateItem, 'number', 'documenttemplateitem_number'));
    $this->addColumn(new SqlColumn($this->_tDocumentTemplateItem, 'content', 'documenttemplateitem_content'));
  }
  
  protected function _initSqlSelect() {
    $this->_insertProviderSettingsTable();
    $this->_insertProviderTable();
    $this->_insertCustomerTable();
    $this->_insertInvoiceAddressTable();
    $this->_insertDocumentTemplateTable();
    $this->_insertDocumentTemplateItemTable();

    $this->addJoin(new SqlJoin(false, $this->_tProvider, new SqlStatementBi($this->columns['provider'], $this->columns['provider_id'], '%s=%s')));
    $this->addJoin(new SqlJoin(false, $this->_tCustomer, new SqlStatementBi($this->columns['provider'], $this->columns['customer_provider'], '%s=%s')));
    $this->addJoin(new SqlJoin(false, $this->_tInvoiceAddress, new SqlStatementBi($this->columns['invoice_address'], $this->columns['invoice_address_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tDocumentTemplate, new SqlStatementBi($this->columns['documenttemplate'], $this->columns['documenttemplate_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tDocumentTemplateItem, new SqlStatementBi($this->columns['documenttemplateitem_documenttemplate'], $this->columns['documenttemplate_id'], '%s=%s')));
  }
}

?>
