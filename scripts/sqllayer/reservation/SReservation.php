<?php

class SReservation extends MySqlSelect {
  private $_tReservation;
  private $_tUser;
  private $_tUserAddress;
  private $_tUserState;
  private $_tPriceUser;
  private $_tCustomer;
  private $_tProvider;
  private $_tProviderSettings;
  private $_tProviderCustomer;
  private $_tProviderAddress;
  private $_tProviderInvoiceAddress;
  private $_tCenter;
  private $_tCenterAddress;
  private $_tResource;
  private $_tResourceUnitProfile;
  private $_tResourceTag;
  private $_tResourceAccountType;
  private $_tEvent;
  private $_tEventAttendee;
  private $_tEventAttendeePerson;
  private $_tEventAttendeePersonUser;
  private $_tEventAttendeeEvent;
  private $_tEventTag;
  private $_tEventAccountType;
  private $_tEventResource;
  private $_tEventResourceResource;
  private $_tEventResourceTag;
  private $_tOnlinePayment;
  private $_tVoucher;

  private $_sResourceAvailability;
  private $_sJournalPayRecord;
  private $_sOpenOnlinePayment;
  
  private function _insertReservationTable() {
    $this->_tReservation = new SqlTable('reservation', 'r');
    
    $this->addColumn(new SqlColumn($this->_tReservation, 'reservation_id'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'number'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'mandatory'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'created'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'start'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'end'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'failed'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'cancelled'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'payed'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'payed_ticket'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'user'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'customer'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'provider'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'center'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'total_price'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'voucher'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'voucher_discount_amount'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'price_timestamp'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'price_comment'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'price_user'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'receipt_number'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'receipt'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'invoice_number'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'invoice'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'creditnote_number'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'creditnote'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'resource'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'resource_from'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'resource_to'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'event'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'event_places'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'event_pack'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'notification'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'pool'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'note'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'open_onlinepayment'));
  }

  private function _insertUserTable() {
    $this->_tUser = new SqlTable('user', 'u');

    $this->addColumn(new SqlColumn($this->_tUser, 'user_id'));
    $this->addColumn(new SqlColumn($this->_tUser, 'firstname', 'user_firstname'));
    $this->addColumn(new SqlColumn($this->_tUser, 'lastname', 'user_lastname'));
    $this->addColumn(new SqlColumn($this->_tUser, 'email', 'user_email'));
    $this->addColumn(new SqlColumn($this->_tUser, 'phone', 'user_phone'));
    $this->addColumn(new SqlColumn($this->_tUser, 'address', 'user_address'));
    
    $this->addColumn(new SqlColumn(false, new SqlStatementBi($this->columns['user_firstname'], $this->columns['user_lastname'], "CONCAT(%s,' ',%s)"), 'user_name'));
    $this->addColumn(new SqlColumn(false, new SqlStatementBi($this->columns['user_lastname'], $this->columns['user_firstname'], "CONCAT(%s,' ',%s)"), 'user_name_reversed'));
  }
  
  protected function _insertUserAddressTable() {
    $this->_tUserAddress = new SqlTable('address', 'ua');
    
    $this->addColumn(new SqlColumn($this->_tUserAddress, 'address_id', 'user_address_id'));
    $this->addColumn(new SqlColumn($this->_tUserAddress, 'street', 'user_street'));
    $this->addColumn(new SqlColumn($this->_tUserAddress, 'city', 'user_city'));
    $this->addColumn(new SqlColumn($this->_tUserAddress, 'postal_code', 'user_postal_code'));
    $this->addColumn(new SqlColumn($this->_tUserAddress, 'state', 'user_state'));
  }
  
  protected function _insertUserStateTable() {
    $this->_tUserState = new SqlTable('state', 'us');
    
    $this->addColumn(new SqlColumn($this->_tUserState, 'state_id', 'user_state_id'));
    $this->addColumn(new SqlColumn($this->_tUserState, 'code', 'user_state_code'));
    $this->addColumn(new SqlColumn($this->_tUserState, 'name', 'user_state_name'));
  }
  
  private function _insertPriceUserTable() {
    $this->_tPriceUser = new SqlTable('user', 'pu');

    $this->addColumn(new SqlColumn($this->_tPriceUser, 'user_id', 'price_user_id'));
    $this->addColumn(new SqlColumn($this->_tPriceUser, 'firstname', 'price_user_firstname'));
    $this->addColumn(new SqlColumn($this->_tPriceUser, 'lastname', 'price_user_lastname'));
    
    $this->addColumn(new SqlColumn(false, new SqlStatementBi($this->columns['price_user_firstname'], $this->columns['price_user_lastname'], "CONCAT(%s,' ',%s)"), 'price_user_name'));
  }
  
  private function _insertCustomerTable() {
    $this->_tCustomer = new SqlTable('customer', 'c');

    $this->addColumn(new SqlColumn($this->_tCustomer, 'customer_id'));
    $this->addColumn(new SqlColumn($this->_tCustomer, 'name', 'customer_name'));
    $this->addColumn(new SqlColumn($this->_tCustomer, 'email', 'customer_email'));
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

  protected function _insertProviderSettingsTable() {
    $this->_tProviderSettings = new SqlTable('providersettings', 'provs');

    $this->addColumn(new SqlColumn($this->_tProviderSettings, 'provider', 'providersettings_provider'));
    $this->addColumn(new SqlColumn($this->_tProviderSettings, 'generate_accounting', 'providersettings_generate_accounting'));
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
  
  private function _insertCenterTable() {
    $this->_tCenter = new SqlTable('center', 'cen');

    $this->addColumn(new SqlColumn($this->_tCenter, 'center_id'));
    $this->addColumn(new SqlColumn($this->_tCenter, 'name', 'center_name'));
    $this->addColumn(new SqlColumn($this->_tCenter, 'address', 'center_address'));
  }
  
  protected function _insertCenterAddressTable() {
    $this->_tCenterAddress = new SqlTable('address', 'ca');
    
    $this->addColumn(new SqlColumn($this->_tCenterAddress, 'address_id', 'center_address_id'));
    $this->addColumn(new SqlColumn($this->_tCenterAddress, 'street', 'center_street'));
    $this->addColumn(new SqlColumn($this->_tCenterAddress, 'city', 'center_city'));
    $this->addColumn(new SqlColumn($this->_tCenterAddress, 'postal_code', 'center_postal_code'));
    $this->addColumn(new SqlColumn($this->_tCenterAddress, 'state', 'center_state'));
  }
  
  private function _insertResourceTable() {
    $this->_tResource = new SqlTable('resource', 'res');
    
    $this->addColumn(new SqlColumn($this->_tResource, 'resource_id'));
    $this->addColumn(new SqlColumn($this->_tResource, 'name', 'resource_name'));
    $this->addColumn(new SqlColumn($this->_tResource, 'provider', 'resource_provider'));
    #$this->addColumn(new SqlColumn($this->_tResource, 'address', 'resource_address'));
    $this->addColumn(new SqlColumn($this->_tResource, 'description', 'resource_description'));
    $this->addColumn(new SqlColumn($this->_tResource, 'unitprofile', 'resource_unitprofile'));
    $this->addColumn(new SqlColumn($this->_tResource, 'reservationcondition', 'resource_reservationcondition'));
    $this->addColumn(new SqlColumn($this->_tResource, 'accounttype', 'resource_accounttype'));
    $this->addColumn(new SqlColumn($this->_tResource, 'fe_allowed_payment', 'resource_fe_allowed_payment'));
  }
  
  /*protected function _insertResourceAddressTable() {
    $this->_tResourceAddress = new SqlTable('address', 'ra');
    
    $this->addColumn(new SqlColumn($this->_tResourceAddress, 'address_id', 'resource_address_id'));
    $this->addColumn(new SqlColumn($this->_tResourceAddress, 'street', 'resource_street'));
    $this->addColumn(new SqlColumn($this->_tResourceAddress, 'city', 'resource_city'));
    $this->addColumn(new SqlColumn($this->_tResourceAddress, 'postal_code', 'resource_postal_code'));
    $this->addColumn(new SqlColumn($this->_tResourceAddress, 'state', 'resource_state'));
    $this->addColumn(new SqlColumn($this->_tResourceAddress, 'gps_latitude', 'resource_gps_latitude'));
    $this->addColumn(new SqlColumn($this->_tResourceAddress, 'gps_longitude', 'resource_gps_longitude'));
    
    $this->addColumn(new SqlColumn(false, new SqlStatementTri($this->columns['resource_street'], $this->columns['resource_city'],
                                                              $this->columns['resource_postal_code'], "CONCAT(%s,' ',%s,', ',%s)"), 'resource_full_address'));
  }*/
  
  private function _insertResourceUnitProfileTable() {
    $this->_tResourceUnitProfile = new SqlTable('unitprofile', 'rup');

    $this->addColumn(new SqlColumn($this->_tResourceUnitProfile, 'unitprofile_id'));
    $this->addColumn(new SqlColumn($this->_tResourceUnitProfile, 'name', 'resource_unitprofile_name'));
    $this->addColumn(new SqlColumn($this->_tResourceUnitProfile, 'unit', 'resource_unit'));
  }
  
  private function _insertResourceTagTable() {
    $this->_tResourceTag = new SqlTable('resource_tag', 'rt');
    
    $this->addColumn(new SqlColumn($this->_tResourceTag, 'resource', 'rt_resource'));
    $this->addColumn(new SqlColumn($this->_tResourceTag, 'tag', 'rt_tag'));
    
    $this->addColumn(new SqlColumn(false, new SqlStatementMono($this->columns['rt_tag'], 'GROUP_CONCAT(%s)'), 'all_resource_tag', true));
  }
  
  private function _insertResourceAccountTypeTable() {
    $this->_tResourceAccountType = new SqlTable('provideraccounttype', 'r_pat');
    
    $this->addColumn(new SqlColumn($this->_tResourceAccountType, 'provideraccounttype_id', 'resource_accounttype_id'));
    $this->addColumn(new SqlColumn($this->_tResourceAccountType, 'name', 'resource_accounttype_name'));
  }
  
  private function _insertEventTable() {
    $this->_tEvent = new SqlTable('event', 'e');
    
    $this->addColumn(new SqlColumn($this->_tEvent, 'event_id'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'name', 'event_name'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'provider', 'event_provider'));
    #$this->addColumn(new SqlColumn($this->_tEvent, 'address', 'event_address'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'start', 'event_start'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'end', 'event_end'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'price', 'event_price'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'badge', 'event_badge'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'description', 'event_description'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'reservationcondition', 'event_reservationcondition'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'accounttype', 'event_accounttype'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'fe_allowed_payment', 'event_fe_allowed_payment'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'active', 'event_active'));
  }
  
  private function _insertEventAttendeeTable() {
    $this->_tEventAttendee = new SqlTable('eventattendee', 'ea');
    
    $this->addColumn(new SqlColumn($this->_tEventAttendee, 'eventattendee_id'));
    $this->addColumn(new SqlColumn($this->_tEventAttendee, 'event', 'eventattendee_event'));
    $this->addColumn(new SqlColumn($this->_tEventAttendee, 'reservation', 'eventattendee_reservation'));
    $this->addColumn(new SqlColumn($this->_tEventAttendee, 'failed', 'eventattendee_failed'));
  }
  
  private function _insertEventAttendeePersonTable() {
    $this->_tEventAttendeePerson = new SqlTable('eventattendeeperson', 'eap');
    
    $this->addColumn(new SqlColumn($this->_tEventAttendeePerson, 'eventattendeeperson_id'));
    $this->addColumn(new SqlColumn($this->_tEventAttendeePerson, 'eventattendee'));
    $this->addColumn(new SqlColumn($this->_tEventAttendeePerson, 'user', 'eventattendeeperson_user'));
    $this->addColumn(new SqlColumn($this->_tEventAttendeePerson, 'firstname', 'eventattendeeperson_firstname'));
    $this->addColumn(new SqlColumn($this->_tEventAttendeePerson, 'lastname', 'eventattendeeperson_lastname'));
    $this->addColumn(new SqlColumn($this->_tEventAttendeePerson, 'email', 'eventattendeeperson_email'));

    $this->addColumn(new SqlColumn(false, new SqlStatementBi($this->columns['eventattendeeperson_firstname'], $this->columns['eventattendeeperson_lastname'], "CONCAT(%s,' ',%s)"), 'eventattendeeperson_fullname'));
  }

  private function _insertEventAttendeePersonUserTable() {
    $this->_tEventAttendeePersonUser = new SqlTable('user', 'eapu');

    $this->addColumn(new SqlColumn($this->_tEventAttendeePersonUser, 'user_id', 'eventattendeeperson_user_id'));
    $this->addColumn(new SqlColumn($this->_tEventAttendeePersonUser, 'firstname', 'eventattendeeperson_user_firstname'));
    $this->addColumn(new SqlColumn($this->_tEventAttendeePersonUser, 'lastname', 'eventattendeeperson_user_lastname'));
    $this->addColumn(new SqlColumn($this->_tEventAttendeePersonUser, 'email', 'eventattendeeperson_user_email'));

    $this->addColumn(new SqlColumn(false, new SqlStatementBi($this->columns['eventattendeeperson_user_firstname'], $this->columns['eventattendeeperson_user_lastname'], "CONCAT(%s,' ',%s)"), 'eventattendeeperson_user_fullname'));
  }

  private function _insertEventAttendeeEventTable() {
    $this->_tEventAttendeeEvent = new SqlTable('event', 'eae');
    
    $this->addColumn(new SqlColumn($this->_tEventAttendeeEvent, 'event_id', 'eventattendee_event_id'));
    $this->addColumn(new SqlColumn($this->_tEventAttendeeEvent, 'start', 'eventattendee_event_start'));
    $this->addColumn(new SqlColumn($this->_tEventAttendeeEvent, 'end', 'eventattendee_event_end'));
  }
  
  /*protected function _insertEventAddressTable() {
    $this->_tEventAddress = new SqlTable('address', 'ea');
    
    $this->addColumn(new SqlColumn($this->_tEventAddress, 'address_id', 'event_address_id'));
    $this->addColumn(new SqlColumn($this->_tEventAddress, 'street', 'event_street'));
    $this->addColumn(new SqlColumn($this->_tEventAddress, 'city', 'event_city'));
    $this->addColumn(new SqlColumn($this->_tEventAddress, 'postal_code', 'event_postal_code'));
    $this->addColumn(new SqlColumn($this->_tEventAddress, 'state', 'event_state'));
    $this->addColumn(new SqlColumn($this->_tEventAddress, 'gps_latitude', 'event_gps_latitude'));
    $this->addColumn(new SqlColumn($this->_tEventAddress, 'gps_longitude', 'event_gps_longitude'));
    
    $this->addColumn(new SqlColumn(false, new SqlStatementTri($this->columns['event_street'], $this->columns['event_city'],
                                                              $this->columns['event_postal_code'], "CONCAT(%s,' ',%s,', ',%s)"), 'event_full_address'));
  }*/
  
  private function _insertEventTagTable() {
    $this->_tEventTag = new SqlTable('event_tag', 'et');
    
    $this->addColumn(new SqlColumn($this->_tEventTag, 'event', 'et_event'));
    $this->addColumn(new SqlColumn($this->_tEventTag, 'tag', 'et_tag'));
    
    $this->addColumn(new SqlColumn(false, new SqlStatementMono($this->columns['et_tag'], 'GROUP_CONCAT(%s)'), 'all_event_tag', true));
  }
  
  private function _insertEventAccountTypeTable() {
    $this->_tEventAccountType = new SqlTable('provideraccounttype', 'e_pat');
    
    $this->addColumn(new SqlColumn($this->_tEventAccountType, 'provideraccounttype_id', 'event_accounttype_id'));
    $this->addColumn(new SqlColumn($this->_tEventAccountType, 'name', 'event_accounttype_name'));
  }
  
  private function _insertEventResourceTable() {
    $this->_tEventResource = new SqlTable('event_resource', 'er');
    
    $this->addColumn(new SqlColumn($this->_tEventResource, 'event', 'er_event'));
    $this->addColumn(new SqlColumn($this->_tEventResource, 'resource', 'er_resource'));
  }
    
  private function _insertEventResourceResourceTable() {
    $this->_tEventResourceResource = new SqlTable('resource', 'err');
    
    $this->addColumn(new SqlColumn($this->_tEventResourceResource, 'resource_id', 'er_resource_id'));
    $this->addColumn(new SqlColumn($this->_tEventResourceResource, 'name', 'er_name'));
    $this->addColumn(new SqlColumn($this->_tEventResourceResource, 'description', 'er_description'));
    $this->addColumn(new SqlColumn($this->_tEventResourceResource, 'reservationcondition', 'er_reservationcondition'));
  }
  
  private function _insertEventResourceTagTable() {
    $this->_tEventResourceTag = new SqlTable('resource_tag', 'ert');
    
    $this->addColumn(new SqlColumn($this->_tEventResourceTag, 'resource', 'ert_resource'));
    $this->addColumn(new SqlColumn($this->_tEventResourceTag, 'tag', 'ert_tag'));
    
    $this->addColumn(new SqlColumn(false, new SqlStatementMono($this->columns['ert_tag'], 'GROUP_CONCAT(%s)'), 'all_event_resource_tag', true));
  }

  private function _insertOnlinePaymentTable() {
    $this->_tOnlinePayment = new SqlTable('onlinepayment', 'op');

    $this->addColumn(new SqlColumn($this->_tOnlinePayment, 'onlinepayment_id'));
    $this->addColumn(new SqlColumn($this->_tOnlinePayment, 'target', 'onlinepayment_target'));
    $this->addColumn(new SqlColumn($this->_tOnlinePayment, 'target_id', 'onlinepayment_target_id'));
    $this->addColumn(new SqlColumn($this->_tOnlinePayment, 'type', 'onlinepayment_type'));
    $this->addColumn(new SqlColumn($this->_tOnlinePayment, 'paymentid', 'onlinepayment_paymentid'));
    $this->addColumn(new SqlColumn($this->_tOnlinePayment, 'payed', 'onlinepayment_payed'));
    $this->addColumn(new SqlColumn($this->_tOnlinePayment, 'start_timestamp', 'onlinepayment_start'));
    $this->addColumn(new SqlColumn($this->_tOnlinePayment, 'end_timestamp', 'onlinepayment_end'));
  }

  protected function _insertVoucherTable() {
    $this->_tVoucher = new SqlTable('voucher', 'vou');

    $this->addColumn(new SqlColumn($this->_tVoucher, 'voucher_id'));
    $this->addColumn(new SqlColumn($this->_tVoucher, 'name', 'voucher_name'));
    $this->addColumn(new SqlColumn($this->_tVoucher, 'code', 'voucher_code'));
  }
  
  private function _insertEventResourceSelect() {
    $select = new SEventResource;
    $select->addColumn(new SqlColumn($this->_tReservation, 'event', 'outer_event', false, false, true));
    $select->addStatement(new SqlStatementBi($select->columns['event'], $select->columns['outer_event'], '%s=%s'));
    $select->addColumn(new SqlColumn(false, new SqlStatementMono($select->columns['name'], 'GROUP_CONCAT(%s)'), 'all_resource_name', true));
    $select->setColumnsMask(array('all_resource_name'));
  
    $this->addColumn(new SqlColumn(false, $select, 'all_event_resource_name'));
  }
  
  private function _insertResourceAvailabilitySelect() {
    $this->_sResourceAvailability = new SResourceAvailability;
    $this->_sResourceAvailability->addColumn(new SqlColumn($this->_tReservation, 'resource_from', 'outer_from', false, false, true));
    $this->_sResourceAvailability->addColumn(new SqlColumn($this->_tReservation, 'resource_to', 'outer_to', false, false, true));
    $this->_sResourceAvailability->addColumn(new SqlColumn($this->_tReservation, 'resource', 'outer_resource', false, false, true));
    $this->_sResourceAvailability->addStatement(new SqlStatementBi(
                              $this->_sResourceAvailability->columns['resource'], $this->_sResourceAvailability->columns['outer_resource'],
                              '%s=%s'));
    $this->_sResourceAvailability->addStatement(new SqlStatementQuad(
                              $this->_sResourceAvailability->columns['start'], $this->_sResourceAvailability->columns['outer_from'],
                              $this->_sResourceAvailability->columns['outer_to'], $this->_sResourceAvailability->columns['end'],
                              '%s<=%s AND %s<=%s'));
    $this->_sResourceAvailability->setColumnsMask(array('resourceavailability_id'));
    
    $this->addColumn(new SqlColumn(false, $this->_sResourceAvailability, 'resourceavailability'));
  }

  private function _insertJournalPayRecordSelect() {
    $this->_sJournalPayRecord = new SReservationJournal;
    $this->_sJournalPayRecord->addColumn(new SqlColumn($this->_tReservation, 'reservation_id', 'outer_reservation', false, false, true));
    $this->_sJournalPayRecord->addStatement(new SqlStatementTri(
      $this->_sJournalPayRecord->columns['reservation'], $this->_sJournalPayRecord->columns['outer_reservation'],
      $this->_sJournalPayRecord->columns['action'], "%s=%s AND %s='PAY'"));
    $this->_sJournalPayRecord->addOrder(new SqlStatementDesc($this->_sJournalPayRecord->columns['change_timestamp']));
    $this->_sJournalPayRecord->setColumnsMask(array('note'));
    $this->_sJournalPayRecord->setLimit(1);

    $this->addColumn(new SqlColumn(false, $this->_sJournalPayRecord, 'journal_payrecord'));
  }

  private function _insertOpenOnlinePaymentSelect() {
    $this->_sOpenOnlinePayment = new SOnlinePayment();
    $this->_sOpenOnlinePayment->addColumn(new SqlColumn($this->_tReservation, 'reservation_id', 'outer_reservation', false, false, true));
    $this->_sOpenOnlinePayment->addColumn(new SqlColumn(false, new SqlStatementMono($this->_sOpenOnlinePayment->columns['onlinepayment_id'], 'COUNT(%s)'), 'count', true));
    $this->_sOpenOnlinePayment->addStatement(new SqlStatementTri(
      $this->_sOpenOnlinePayment->columns['target'], $this->_sOpenOnlinePayment->columns['target_id'],
      $this->_sOpenOnlinePayment->columns['outer_reservation'], "%s='RESERVATION' AND %s LIKE CONCAT('%%|',%s,'|%%')"));
    $this->_sOpenOnlinePayment->addStatement(new SqlStatementMono($this->_sOpenOnlinePayment->columns['end_timestamp'], '%s IS NULL'));

    $this->_sOpenOnlinePayment->setColumnsMask(array('count'));

    $this->addColumn(new SqlColumn(false, $this->_sOpenOnlinePayment, 'onlinepayment_open_count'));
  }
  
  protected function _initSqlSelect() {
    $this->_insertReservationTable();
    $this->_insertUserTable();
    $this->_insertUserAddressTable();
    $this->_insertUserStateTable();
    $this->_insertPriceUserTable();
    $this->_insertCustomerTable();
    $this->_insertProviderTable();
    $this->_insertProviderSettingsTable();
    $this->_insertProviderCustomerTable();
    $this->_insertProviderAddressTable();
    $this->_insertProviderInvoiceAddressTable();
    $this->_insertCenterTable();
    $this->_insertCenterAddressTable();
    $this->_insertResourceTable();
    #$this->_insertResourceAddressTable();
    $this->_insertResourceUnitProfileTable();
    $this->_insertResourceTagTable();
    $this->_insertResourceAccountTypeTable();
    $this->_insertEventTable();
    $this->_insertEventAttendeeTable();
    $this->_insertEventAttendeePersonTable();
    $this->_insertEventAttendeePersonUserTable();
    $this->_insertEventAttendeeEventTable();
    #$this->_insertEventAddressTable();
    $this->_insertEventResourceTable();
    $this->_insertEventResourceResourceTable();
    $this->_insertEventResourceTagTable();
    $this->_insertEventResourceSelect();
    $this->_insertEventTagTable();
    $this->_insertEventAccountTypeTable();
    $this->_insertVoucherTable();

    $this->_insertOnlinePaymentTable();
    $this->_insertOpenOnlinePaymentSelect();
    
    $this->_insertResourceAvailabilitySelect();
    $this->_insertJournalPayRecordSelect();
    
    $this->addColumn(new SqlColumn(false, new SqlStatementBi($this->columns['provider_name'], $this->columns['center_name'], "CONCAT(%s,'(',%s,')')"), 'provider_with_center'));
    $this->addColumn(new SqlColumn(false, new SqlStatementBi($this->columns['resource_name'], $this->columns['all_event_resource_name'], "IFNULL(%s,IFNULL(%s,'-'))"), 'mixed_resource_name'));
    $this->addColumn(new SqlColumn(false, new SqlStatementTri($this->columns['resource'], $this->columns['event_accounttype'], $this->columns['resource_accounttype'], 'IF(%s IS NULL,%s,%s)'), 'mixed_accounttype'));
    $this->addColumn(new SqlColumn(false, new SqlStatementTri($this->columns['resource'], $this->columns['event_accounttype_name'], $this->columns['resource_accounttype_name'], 'IF(%s IS NULL,%s,%s)'), 'mixed_accounttype_name'));
    $this->addColumn(new SqlColumn(false, new SqlStatementHexa($this->columns['event'], $this->columns['event_name'], $this->columns['mixed_resource_name'], $this->columns['start'],
                                                              $this->columns['resource_name'], $this->columns['start'],
                                                              "IF(%s IS NOT NULL,CONCAT(%s,' (',%s,') & ',%s),CONCAT(%s,' & ',%s))"), 'description'));

    $this->addColumn(new SqlColumn(false, new SqlStatementTri($this->columns['event'], $this->columns['event_fe_allowed_payment'], $this->columns['resource_fe_allowed_payment'],
      'IF(%s IS NOT NULL, %s, %s)'), 'fe_allowed_payment'));
    $this->addColumn(new SqlColumn(false, new SqlStatementTri($this->columns['event'], $this->columns['event_fe_allowed_payment'], $this->columns['resource_fe_allowed_payment'],
      'IF(%s IS NOT NULL, 1&%s, 1&%s)'), 'fe_allowed_payment_credit'));
    $this->addColumn(new SqlColumn(false, new SqlStatementTri($this->columns['event'], $this->columns['event_fe_allowed_payment'], $this->columns['resource_fe_allowed_payment'],
      'IF(%s IS NOT NULL, 10&%s, 10&%s)'), 'fe_allowed_payment_ticket'));
    $this->addColumn(new SqlColumn(false, new SqlStatementTri($this->columns['event'], $this->columns['event_fe_allowed_payment'], $this->columns['resource_fe_allowed_payment'],
      'IF(%s IS NOT NULL, 100&%s, 100&%s)'), 'fe_allowed_payment_online'));

    $this->addJoin(new SqlJoin('LEFT', $this->_tUser, new SqlStatementBi($this->columns['user'], $this->columns['user_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tUserAddress, new SqlStatementBi($this->columns['user_address'], $this->columns['user_address_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tUserState, new SqlStatementBi($this->columns['user_state'], $this->columns['user_state_code'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tPriceUser, new SqlStatementBi($this->columns['price_user'], $this->columns['price_user_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tCustomer, new SqlStatementBi($this->columns['customer'], $this->columns['customer_id'], '%s=%s')));
    $this->addJoin(new SqlJoin(false, $this->_tProvider, new SqlStatementBi($this->columns['provider'], $this->columns['provider_id'], '%s=%s')));
    $this->addJoin(new SqlJoin(false, $this->_tProviderSettings, new SqlStatementBi($this->columns['providersettings_provider'], $this->columns['provider_id'], '%s=%s')));
    $this->addJoin(new SqlJoin(false, $this->_tProviderCustomer, new SqlStatementBi($this->columns['provider'], $this->columns['customer_provider'], '%s=%s')));
    $this->addJoin(new SqlJoin(false, $this->_tProviderAddress, new SqlStatementBi($this->columns['provider_address'], $this->columns['provider_address_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tProviderInvoiceAddress, new SqlStatementBi($this->columns['provider_invoice_address'], $this->columns['provider_invoice_address_id'], '%s=%s')));
    $this->addJoin(new SqlJoin(false, $this->_tCenter, new SqlStatementBi($this->columns['center'], $this->columns['center_id'], '%s=%s')));
    $this->addJoin(new SqlJoin(false, $this->_tCenterAddress, new SqlStatementBi($this->columns['center_address'], $this->columns['center_address_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tResource, new SqlStatementBi($this->columns['resource'], $this->columns['resource_id'], '%s=%s')));
    #$this->addJoin(new SqlJoin('LEFT', $this->_tResourceAddress, new SqlStatementBi($this->columns['resource_address'], $this->columns['resource_address_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tResourceUnitProfile, new SqlStatementBi($this->columns['resource_unitprofile'], $this->columns['unitprofile_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tResourceTag, new SqlStatementBi($this->columns['rt_resource'], $this->columns['resource'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tResourceAccountType, new SqlStatementBi($this->columns['resource_accounttype'], $this->columns['resource_accounttype_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tEvent, new SqlStatementBi($this->columns['event'], $this->columns['event_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tEventAttendee, new SqlStatementBi($this->columns['reservation_id'], $this->columns['eventattendee_reservation'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tEventAttendeePerson, new SqlStatementBi($this->columns['eventattendee_id'], $this->columns['eventattendee'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tEventAttendeePersonUser, new SqlStatementBi($this->columns['eventattendeeperson_user'], $this->columns['eventattendeeperson_user_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tEventAttendeeEvent, new SqlStatementBi($this->columns['eventattendee_event'], $this->columns['eventattendee_event_id'], '%s=%s')));
    #$this->addJoin(new SqlJoin('LEFT', $this->_tEventAddress, new SqlStatementBi($this->columns['event_address'], $this->columns['event_address_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tEventTag, new SqlStatementBi($this->columns['et_event'], $this->columns['event'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tEventAccountType, new SqlStatementBi($this->columns['event_accounttype'], $this->columns['event_accounttype_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tEventResource, new SqlStatementBi($this->columns['er_event'], $this->columns['event_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tEventResourceResource, new SqlStatementBi($this->columns['er_resource'], $this->columns['er_resource_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tEventResourceTag, new SqlStatementBi($this->columns['ert_resource'], $this->columns['er_resource'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tVoucher, new SqlStatementBi($this->columns['voucher'], $this->columns['voucher_id'], '%s=%s')));

    $this->addJoin(new SqlJoin('LEFT', $this->_tOnlinePayment, new SqlStatementTri($this->columns['onlinepayment_target'], $this->columns['onlinepayment_target_id'], $this->columns['reservation_id'], "%s='RESERVATION' AND %s LIKE CONCAT('%%|',%s,'|%%')")));
  }
}

?>
