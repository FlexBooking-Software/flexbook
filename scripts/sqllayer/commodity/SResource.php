<?php

class SResource extends SqlSelect {
  private $_tResource;
  private $_tResourcePoolItem;
  private $_tUnitProfile;
  private $_tAvailabilityProfile;
  private $_tAvailabilityProfileItem;
  private $_tAvailabilityExceptionProfile;
  private $_tAvailabilityExceptionProfileItem;
  private $_tPriceList;
  private $_tCenter;
  private $_tAddress;
  private $_tCustomer;
  private $_tProvider;
  private $_tOrganiserUser;
  private $_tResourceTag;
  private $_tTag;
  private $_tResourcePortal;
  private $_tResourceAvailability;
  private $_tNotificationTemplate;
  private $_tNotificationTemplateItem;
  private $_tDocumentTemplate;
  private $_tDocumentTemplateItem;
  
  public $sTag;
  
  private function _insertResourceTable() {
    $this->_tResource = new SqlTable('resource', 'r');
    
    $this->addColumn(new SqlColumn($this->_tResource, 'resource_id'));
    $this->addColumn(new SqlColumn($this->_tResource, 'name'));
    $this->addColumn(new SqlColumn($this->_tResource, 'provider'));
    $this->addColumn(new SqlColumn($this->_tResource, 'external_id'));
    $this->addColumn(new SqlColumn($this->_tResource, 'center'));
    $this->addColumn(new SqlColumn($this->_tResource, 'description'));
    $this->addColumn(new SqlColumn($this->_tResource, 'organiser'));
    $this->addColumn(new SqlColumn($this->_tResource, 'price'));
    $this->addColumn(new SqlColumn($this->_tResource, 'pricelist'));
    $this->addColumn(new SqlColumn($this->_tResource, 'accounttype'));
    $this->addColumn(new SqlColumn($this->_tResource, 'unitprofile'));
    $this->addColumn(new SqlColumn($this->_tResource, 'availabilityprofile'));
    $this->addColumn(new SqlColumn($this->_tResource, 'availabilityexceptionprofile'));
    $this->addColumn(new SqlColumn($this->_tResource, 'active'));
    $this->addColumn(new SqlColumn($this->_tResource, 'reservationcondition'));
    $this->addColumn(new SqlColumn($this->_tResource, 'notificationtemplate'));
    $this->addColumn(new SqlColumn($this->_tResource, 'documenttemplate'));

    $this->addColumn(new SqlColumn($this->_tResource, 'fe_allowed_payment'));
    
    $this->addColumn(new SqlColumn($this->_tResource, 'url_description'));
    $this->addColumn(new SqlColumn($this->_tResource, 'url_price'));
    $this->addColumn(new SqlColumn($this->_tResource, 'url_opening'));
    $this->addColumn(new SqlColumn($this->_tResource, 'url_photo'));
  }

  private function _insertResourcePoolItemTable() {
    $this->_tResourcePoolItem = new SqlTable('resourcepoolitem', 'rpi');

    $this->addColumn(new SqlColumn($this->_tResourcePoolItem, 'resourcepool'));
    $this->addColumn(new SqlColumn($this->_tResourcePoolItem, 'resource', 'resourcepool_resource'));
  }
  
  private function _insertUnitProfileTable() {
    $this->_tUnitProfile = new SqlTable('unitprofile', 'up');

    $this->addColumn(new SqlColumn($this->_tUnitProfile, 'unitprofile_id'));
    $this->addColumn(new SqlColumn($this->_tUnitProfile, 'name', 'unitprofile_name'));
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
  
  private function _insertAvailabilityProfileTable() {
    $this->_tAvailabilityProfile = new SqlTable('availabilityprofile', 'ap');

    $this->addColumn(new SqlColumn($this->_tAvailabilityProfile, 'availabilityprofile_id'));
    $this->addColumn(new SqlColumn($this->_tAvailabilityProfile, 'name', 'availabilityprofile_name'));
  }
  
  private function _insertAvailabilityProfileItemTable() {
    $this->_tAvailabilityProfileItem = new SqlTable('availabilityprofileitem', 'api');

    $this->addColumn(new SqlColumn($this->_tAvailabilityProfileItem, 'availabilityprofileitem_id'));
    $this->addColumn(new SqlColumn($this->_tAvailabilityProfileItem, 'availabilityprofile', 'availabilityprofileitem_availabilityprofile'));
    $this->addColumn(new SqlColumn($this->_tAvailabilityProfileItem, 'weekday', 'availabilityprofileitem_weekday'));
    $this->addColumn(new SqlColumn($this->_tAvailabilityProfileItem, 'time_from', 'availabilityprofileitem_time_from'));
    $this->addColumn(new SqlColumn($this->_tAvailabilityProfileItem, 'time_to', 'availabilityprofileitem_time_to'));
  }
  
  private function _insertAvailabilityExceptionProfileTable() {
    $this->_tAvailabilityExceptionProfile = new SqlTable('availabilityexceptionprofile', 'aep');

    $this->addColumn(new SqlColumn($this->_tAvailabilityExceptionProfile, 'availabilityexceptionprofile_id'));
    $this->addColumn(new SqlColumn($this->_tAvailabilityExceptionProfile, 'name', 'availabilityexceptionprofile_name'));
  }
  
  private function _insertAvailabilityExceptionProfileItemTable() {
    $this->_tAvailabilityExceptionProfileItem = new SqlTable('availabilityexceptionprofileitem', 'aepi');

    $this->addColumn(new SqlColumn($this->_tAvailabilityExceptionProfileItem, 'availabilityexceptionprofileitem_id'));
    $this->addColumn(new SqlColumn($this->_tAvailabilityExceptionProfileItem, 'availabilityexceptionprofile', 'availabilityexceptionprofileitem_availabilityexceptionprofile'));
    $this->addColumn(new SqlColumn($this->_tAvailabilityExceptionProfileItem, 'name', 'availabilityexceptionprofileitem_name'));
    $this->addColumn(new SqlColumn($this->_tAvailabilityExceptionProfileItem, 'time_from', 'availabilityexceptionprofileitem_time_from'));
    $this->addColumn(new SqlColumn($this->_tAvailabilityExceptionProfileItem, 'time_to', 'availabilityexceptionprofileitem_time_to'));
    $this->addColumn(new SqlColumn($this->_tAvailabilityExceptionProfileItem, 'repeated', 'availabilityexceptionprofileitem_repeated'));
    $this->addColumn(new SqlColumn($this->_tAvailabilityExceptionProfileItem, 'repeat_cycle', 'availabilityexceptionprofileitem_repeat_cycle'));
    $this->addColumn(new SqlColumn($this->_tAvailabilityExceptionProfileItem, 'repeat_weekday', 'availabilityexceptionprofileitem_repeat_weekday'));
    $this->addColumn(new SqlColumn($this->_tAvailabilityExceptionProfileItem, 'repeat_until', 'availabilityexceptionprofileitem_repeat_until'));
  }
  
  private function _insertPriceListTable() {
    $this->_tPriceList = new SqlTable('pricelist', 'pl');
    
    $this->addColumn(new SqlColumn($this->_tPriceList, 'pricelist_id'));
    $this->addColumn(new SqlColumn($this->_tPriceList, 'name', 'pricelist_name'));
  }
  
  protected function _insertCenterTable() {
    $this->_tCenter = new SqlTable('center', 'cen');
    
    $this->addColumn(new SqlColumn($this->_tCenter, 'center_id'));
    $this->addColumn(new SqlColumn($this->_tCenter, 'name', 'center_name'));
    $this->addColumn(new SqlColumn($this->_tCenter, 'address'));
    $this->addColumn(new SqlColumn($this->_tCenter, 'payment_info', 'center_payment_info'));
  }
  
  protected function _insertAddressTable() {
    $this->_tAddress = new SqlTable('address', 'a');
    
    $this->addColumn(new SqlColumn($this->_tAddress, 'address_id'));
    $this->addColumn(new SqlColumn($this->_tAddress, 'street'));
    $this->addColumn(new SqlColumn($this->_tAddress, 'city'));
    $this->addColumn(new SqlColumn($this->_tAddress, 'postal_code'));
    $this->addColumn(new SqlColumn($this->_tAddress, 'state'));
    $this->addColumn(new SqlColumn($this->_tAddress, 'region'));
    $this->addColumn(new SqlColumn($this->_tAddress, 'gps_latitude'));
    $this->addColumn(new SqlColumn($this->_tAddress, 'gps_longitude'));
    
    $this->addColumn(new SqlColumn(false, new SqlStatementTri($this->columns['street'], $this->columns['city'], $this->columns['postal_code'], "CONCAT(%s,' ',%s,', ',%s)"), 'full_address'));
  }
  
  protected function _insertProviderTable() {
    $this->_tProvider = new SqlTable('provider', 'prov');
    
    $this->addColumn(new SqlColumn($this->_tProvider, 'provider_id'));
    $this->addColumn(new SqlColumn($this->_tProvider, 'phone_1', 'provider_phone_1'));
    $this->addColumn(new SqlColumn($this->_tProvider, 'phone_2', 'provider_phone_2'));
    $this->addColumn(new SqlColumn($this->_tProvider, 'www', 'provider_www'));
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

  private function _insertOrganiserUserTable() {
    $this->_tOrganiserUser = new SqlTable('user', 'ou');

    $this->addColumn(new SqlColumn($this->_tOrganiserUser, 'user_id'));
    $this->addColumn(new SqlColumn($this->_tOrganiserUser, 'firstname', 'organiser_firstname'));
    $this->addColumn(new SqlColumn($this->_tOrganiserUser, 'lastname', 'organiser_lastname'));
    $this->addColumn(new SqlColumn($this->_tOrganiserUser, 'email', 'organiser_email'));
    $this->addColumn(new SqlColumn($this->_tOrganiserUser, 'phone', 'organiser_phone'));

    $this->addColumn(new SqlColumn(false, new SqlStatementBi($this->columns['organiser_firstname'], $this->columns['organiser_lastname'], "CONCAT(%s,' ',%s)"), 'organiser_fullname'));
  }
  
  private function _insertResourceTagTable() {
    $this->_tResourceTag = new SqlTable('resource_tag', 'rt');
    
    $this->addColumn(new SqlColumn($this->_tResourceTag, 'resource'));
    $this->addColumn(new SqlColumn($this->_tResourceTag, 'tag'));
    
    $this->addColumn(new SqlColumn(false, new SqlStatementMono($this->columns['tag'], 'GROUP_CONCAT(%s)'), 'all_tag', true));
  }
  
  private function _insertTagTable() {
    $this->_tTag = new SqlTable('tag', 't');
    
    $this->addColumn(new SqlColumn($this->_tTag, 'tag_id'));
    $this->addColumn(new SqlColumn($this->_tTag, 'name', 'tag_name'));
  }
  
  private function _insertResourcePortalTable() {
    $this->_tResourcePortal = new SqlTable('resource_portal', 'rp');
    
    $this->addColumn(new SqlColumn($this->_tResourcePortal, 'resource', 'rp_resource'));
    $this->addColumn(new SqlColumn($this->_tResourcePortal, 'portal'));
  }
  
  private function _insertResourceAvailabilityTable() {
    $this->_tResourceAvailability = new SqlTable('resourceavailability', 'ra');
    
    $this->addColumn(new SqlColumn($this->_tResourceAvailability, 'resourceavailability_id'));
    $this->addColumn(new SqlColumn($this->_tResourceAvailability, 'resource', 'resourceavailability_resource'));
    $this->addColumn(new SqlColumn($this->_tResourceAvailability, 'start', 'resourceavailability_start'));
    $this->addColumn(new SqlColumn($this->_tResourceAvailability, 'end', 'resourceavailability_end'));
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

  private function _insertTagCountSelect() {
    $this->sTag = new SqlSelect;
    $tTag = new SqlTable('resource_tag', 'rt');
    $this->sTag->addColumn(new SqlColumn($tTag, 'resource'));
    $this->sTag->addColumn(new SqlColumn($tTag, 'tag'));
    $this->sTag->addColumn(new SqlColumn(false, 'resource_id', 'outer_resource', false, false, true));
    $this->sTag->addColumn(new SqlColumn(false, new SqlStatementMono($this->sTag->columns['tag'], 'COUNT(%s)'), 'count', true));
    $this->sTag->addStatement(new SqlStatementBi($this->sTag->columns['resource'], $this->sTag->columns['outer_resource'], '%s=%s'));
    $this->sTag->setColumnsMask(array('count'));
    
    $this->addColumn(new SqlColumn(false, $this->sTag, 'tag_count'));
  }

  private function _insertTagSelect() {
    $select = new SqlSelect;
    $table = new SqlTable('resource_tag', 'rt');
    $select->addColumn(new SqlColumn($table, 'resource'));
    $select->addColumn(new SqlColumn($table, 'tag'));
    $select->addColumn(new SqlColumn(false, 'resource_id', 'outer_resource', false, false, true));
    $select->addStatement(new SqlStatementBi($select->columns['resource'], $select->columns['outer_resource'], '%s=%s'));
    $select->setColumnsMask(array('tag'));

    $this->addColumn(new SqlColumn(false, $select, 'all_tag_select'));
  }
  
  protected function _initSqlSelect() {
    $this->_insertResourceTable();
    $this->_insertResourcePoolItemTable();
    $this->_insertUnitProfileTable();
    $this->_insertAvailabilityProfileTable();
    $this->_insertAvailabilityProfileItemTable();
    $this->_insertAvailabilityExceptionProfileTable();
    $this->_insertAvailabilityExceptionProfileItemTable();
    $this->_insertPriceListTable();
    $this->_insertCenterTable();
    $this->_insertAddressTable();
    $this->_insertProviderTable();
    $this->_insertOrganiserUserTable();
    $this->_insertCustomerTable();
    $this->_insertResourceTagTable();
    $this->_insertTagTable();
    $this->_insertResourcePortalTable();
    $this->_insertResourceAvailabilityTable();
    $this->_insertNotificationTemplateTable();
    $this->_insertNotificationTemplateItemTable();
    $this->_insertDocumentTemplateTable();
    $this->_insertDocumentTemplateItemTable();
    
    $this->_insertTagCountSelect();
    $this->_insertTagSelect();
    
    $this->addColumn(new SqlColumn(false, new SqlStatementBi($this->columns['provider_name'], $this->columns['center_name'], "CONCAT(%s,' (',%s,')')"), 'provider_with_center'));
    $this->addColumn(new SqlColumn(false, new SqlStatementBi($this->columns['center_name'], $this->columns['full_address'], "CONCAT(%s,' - ',%s)"), 'center_description'));

    $this->addColumn(new SqlColumn(false, new SqlStatementMono($this->columns['fe_allowed_payment'], '1&%s'), 'fe_allowed_payment_credit'));
    $this->addColumn(new SqlColumn(false, new SqlStatementMono($this->columns['fe_allowed_payment'], '10&%s'), 'fe_allowed_payment_ticket'));
    $this->addColumn(new SqlColumn(false, new SqlStatementMono($this->columns['fe_allowed_payment'], '100&%s'), 'fe_allowed_payment_online'));

    $this->addJoin(new SqlJoin('LEFT', $this->_tResourcePoolItem, new SqlStatementBi($this->columns['resourcepool_resource'], $this->columns['resource_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tUnitProfile, new SqlStatementBi($this->columns['unitprofile'], $this->columns['unitprofile_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tAvailabilityProfile, new SqlStatementBi($this->columns['availabilityprofile'], $this->columns['availabilityprofile_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tAvailabilityProfileItem, new SqlStatementBi($this->columns['availabilityprofileitem_availabilityprofile'], $this->columns['availabilityprofile_id'], '%s=%s')));
    $this->addJoin(new SqlJoin(false, $this->_tAvailabilityExceptionProfile, new SqlStatementBi($this->columns['availabilityexceptionprofile'], $this->columns['availabilityexceptionprofile_id'], '%s=%s')));
    $this->addJoin(new SqlJoin(false, $this->_tAvailabilityExceptionProfileItem, new SqlStatementBi($this->columns['availabilityexceptionprofileitem_availabilityexceptionprofile'], $this->columns['availabilityexceptionprofile_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tPriceList, new SqlStatementBi($this->columns['pricelist'], $this->columns['pricelist_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tProvider, new SqlStatementBi($this->columns['provider'], $this->columns['provider_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tCenter, new SqlStatementBi($this->columns['center'], $this->columns['center_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tAddress, new SqlStatementBi($this->columns['address'], $this->columns['address_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tCustomer, new SqlStatementBi($this->columns['customer_provider'], $this->columns['provider_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tOrganiserUser, new SqlStatementBi($this->columns['organiser'], $this->columns['user_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tResourceTag, new SqlStatementBi($this->columns['resource'], $this->columns['resource_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tTag, new SqlStatementBi($this->columns['tag'], $this->columns['tag_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tResourcePortal, new SqlStatementBi($this->columns['rp_resource'], $this->columns['resource_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tResourceAvailability, new SqlStatementBi($this->columns['resourceavailability_resource'], $this->columns['resource_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tNotificationTemplate, new SqlStatementBi($this->columns['notificationtemplate'], $this->columns['notificationtemplate_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tNotificationTemplateItem, new SqlStatementBi($this->columns['notificationtemplateitem_notificationtemplate'], $this->columns['notificationtemplate_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tDocumentTemplate, new SqlStatementBi($this->columns['documenttemplate'], $this->columns['documenttemplate_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tDocumentTemplateItem, new SqlStatementBi($this->columns['documenttemplateitem_documenttemplate'], $this->columns['documenttemplate_id'], '%s=%s')));
  }
}

?>
