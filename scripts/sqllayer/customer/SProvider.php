<?php

class SProvider extends MySqlSelect {
  private $_tProvider;
  private $_tProviderSettings;
  private $_tCustomer;
  private $_tAddress;
  private $_tInvoiceAddress;
  private $_tNotificationTemplate;
  private $_tNotificationTemplateItem;

  protected function _insertProviderTable() {
    $this->_tProvider = new SqlTable('provider', 'prov');
    
    $this->addColumn(new SqlColumn($this->_tProvider, 'provider_id'));
    $this->addColumn(new SqlColumn($this->_tProvider, 'short_name'));
    $this->addColumn(new SqlColumn($this->_tProvider, 'notificationtemplate'));
    $this->addColumn(new SqlColumn($this->_tProvider, 'phone_1'));
    $this->addColumn(new SqlColumn($this->_tProvider, 'phone_2'));
    $this->addColumn(new SqlColumn($this->_tProvider, 'www'));
    $this->addColumn(new SqlColumn($this->_tProvider, 'bank_account_number'));
    $this->addColumn(new SqlColumn($this->_tProvider, 'bank_account_suffix'));
    $this->addColumn(new SqlColumn($this->_tProvider, 'invoice_name'));
    $this->addColumn(new SqlColumn($this->_tProvider, 'invoice_address'));
    $this->addColumn(new SqlColumn($this->_tProvider, 'vat'));
    $this->addColumn(new SqlColumn($this->_tProvider, 'vat_rate'));
    $this->addColumn(new SqlColumn($this->_tProvider, 'document_year'));
    $this->addColumn(new SqlColumn($this->_tProvider, 'receipt_counter'));
    $this->addColumn(new SqlColumn($this->_tProvider, 'document_counter'));
  }

  protected function _insertProviderSettingsTable() {
    $this->_tProviderSettings = new SqlTable('providersettings', 'ps');

    $this->addColumn(new SqlColumn($this->_tProviderSettings, 'provider', 'settings_provider'));
    $this->addColumn(new SqlColumn($this->_tProviderSettings, 'receipt_number', 'settings_receipt_number'));
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
    $this->addColumn(new SqlColumn($this->_tCustomer, 'provider'));
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

  protected function _insertInvoiceAddressTable() {
    $this->_tInvoiceAddress = new SqlTable('address', 'ia');

    $this->addColumn(new SqlColumn($this->_tInvoiceAddress, 'address_id', 'invoice_address_id'));
    $this->addColumn(new SqlColumn($this->_tInvoiceAddress, 'street', 'invoice_street'));
    $this->addColumn(new SqlColumn($this->_tInvoiceAddress, 'city', 'invoice_city'));
    $this->addColumn(new SqlColumn($this->_tInvoiceAddress, 'postal_code', 'invoice_postal_code'));
    $this->addColumn(new SqlColumn($this->_tInvoiceAddress, 'state', 'invoice_state'));

    $this->addColumn(new SqlColumn(false, new SqlStatementTri($this->columns['invoice_street'], $this->columns['invoice_city'], $this->columns['invoice_postal_code'], "CONCAT(%s,' ',%s,', ',%s)"), 'invoice_full_address'));
  }

  private function _insertNotificationTemplateTable() {
    $this->_tNotificationTemplate = new SqlTable('notificationtemplate', 'nt');

    $this->addColumn(new SqlColumn($this->_tNotificationTemplate, 'notificationtemplate_id'));
    $this->addColumn(new SqlColumn($this->_tNotificationTemplate, 'provider', 'notificationtemplate_provider'));
  }
  
  private function _insertNotificationTemplateItemTable() {
    $this->_tNotificationTemplateItem = new SqlTable('notificationtemplateitem', 'nti');

    $this->addColumn(new SqlColumn($this->_tNotificationTemplateItem, 'notificationtemplateitem_id'));
    $this->addColumn(new SqlColumn($this->_tNotificationTemplateItem, 'notificationtemplate', 'notificationtemplateitem_notificationtemplate'));
    $this->addColumn(new SqlColumn($this->_tNotificationTemplateItem, 'name', 'notificationtemplateitem_name'));
    $this->addColumn(new SqlColumn($this->_tNotificationTemplateItem, 'type', 'notificationtemplateitem_type'));
    $this->addColumn(new SqlColumn($this->_tNotificationTemplateItem, 'offset', 'notificationtemplateitem_offset'));
    $this->addColumn(new SqlColumn($this->_tNotificationTemplateItem, 'to_provider', 'notificationtemplateitem_to_provider'));
    $this->addColumn(new SqlColumn($this->_tNotificationTemplateItem, 'to_organiser', 'notificationtemplateitem_to_organiser'));
    $this->addColumn(new SqlColumn($this->_tNotificationTemplateItem, 'to_user', 'notificationtemplateitem_to_user'));
    $this->addColumn(new SqlColumn($this->_tNotificationTemplateItem, 'to_attendee', 'notificationtemplateitem_to_attendee'));
    $this->addColumn(new SqlColumn($this->_tNotificationTemplateItem, 'to_substitute', 'notificationtemplateitem_to_substitute'));
    $this->addColumn(new SqlColumn($this->_tNotificationTemplateItem, 'from_address', 'notificationtemplateitem_from_address'));
    $this->addColumn(new SqlColumn($this->_tNotificationTemplateItem, 'cc_address', 'notificationtemplateitem_cc_address'));
    $this->addColumn(new SqlColumn($this->_tNotificationTemplateItem, 'bcc_address', 'notificationtemplateitem_bcc_address'));
    $this->addColumn(new SqlColumn($this->_tNotificationTemplateItem, 'content_type', 'notificationtemplateitem_content_type'));
    $this->addColumn(new SqlColumn($this->_tNotificationTemplateItem, 'subject', 'notificationtemplateitem_subject'));
    $this->addColumn(new SqlColumn($this->_tNotificationTemplateItem, 'body', 'notificationtemplateitem_body'));
  }

  protected function _initSqlSelect() {
    $this->_insertProviderTable();
    $this->_insertProviderSettingsTable();
    $this->_insertCustomerTable();
    $this->_insertAddressTable();
    $this->_insertInvoiceAddressTable();
    $this->_insertNotificationTemplateTable();
    $this->_insertNotificationTemplateItemTable();
    
    $this->addColumn(new SqlColumn(false, new SqlStatementBi($this->columns['provider_id'], $this->columns['name'], "CONCAT(%s,'#',%s)"), 'provider_id_with_name'));

    $this->addJoin(new SqlJoin('LEFT', $this->_tProviderSettings, new SqlStatementBi($this->columns['settings_provider'], $this->columns['provider_id'], '%s=%s')));
    $this->addJoin(new SqlJoin(false, $this->_tCustomer, new SqlStatementBi($this->columns['provider'], $this->columns['provider_id'], '%s=%s')));
    $this->addJoin(new SqlJoin(false, $this->_tAddress, new SqlStatementBi($this->columns['address'], $this->columns['address_id'], '%s=%s')));
    $this->addJoin(new SqlJoin(false, $this->_tInvoiceAddress, new SqlStatementBi($this->columns['invoice_address'], $this->columns['invoice_address_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tNotificationTemplate, new SqlStatementBi($this->columns['notificationtemplate_id'], $this->columns['notificationtemplate'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tNotificationTemplateItem, new SqlStatementBi($this->columns['notificationtemplateitem_notificationtemplate'], $this->columns['notificationtemplate_id'], '%s=%s')));
  }
}

?>
