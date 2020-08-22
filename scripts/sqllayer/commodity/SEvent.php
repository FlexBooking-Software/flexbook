<?php

class SEvent extends SqlSelect {
  private $_tEvent;
  private $_tProvider;
  private $_tProviderCustomer;
  private $_tOrganiserUser;
  private $_tCenter;
  private $_tAddress;
  private $_sOccupied;
  private $_tEventTag;
  private $_tTag;
  private $_tEventPortal;
  private $_tEventResource;
  private $_tResource;
  private $_tNotificationTemplate;
  private $_tNotificationTemplateItem;
  private $_tDocumentTemplate;
  private $_tDocumentTemplateItem;

  private $_sRepeatItems;
  private $_sResourceAvailability;
  public $sTag;
  
  private function _insertEventTable() {
    $this->_tEvent = new SqlTable('event', 'e');
    
    $this->addColumn(new SqlColumn($this->_tEvent, 'event_id'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'name'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'provider'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'external_id'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'center'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'start'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'end'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'description'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'max_attendees'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'max_coattendees'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'max_substitutes'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'organiser'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'price'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'accounttype'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'badge'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'active'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'repeat_parent'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'repeat_index'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'repeat_cycle'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'repeat_weekday'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'repeat_weekday_order'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'repeat_price'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'repeat_reservation'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'repeat_until'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'repeat_individual'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'reservationcondition'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'reservation_max_attendees'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'notificationtemplate'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'documenttemplate'));

    $this->addColumn(new SqlColumn($this->_tEvent, 'fe_attendee_visible'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'fe_quick_reservation'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'fe_allowed_payment'));
    
    $this->addColumn(new SqlColumn($this->_tEvent, 'url_description'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'url_price'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'url_opening'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'url_photo'));
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
    $this->addColumn(new SqlColumn($this->_tAddress, 'region'));
    $this->addColumn(new SqlColumn($this->_tAddress, 'postal_code'));
    $this->addColumn(new SqlColumn($this->_tAddress, 'state'));
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
  
  private function _insertOrganiserUserTable() {
    $this->_tOrganiserUser = new SqlTable('user', 'ou');

    $this->addColumn(new SqlColumn($this->_tOrganiserUser, 'user_id'));
    $this->addColumn(new SqlColumn($this->_tOrganiserUser, 'firstname', 'organiser_firstname'));
    $this->addColumn(new SqlColumn($this->_tOrganiserUser, 'lastname', 'organiser_lastname'));
    $this->addColumn(new SqlColumn($this->_tOrganiserUser, 'email', 'organiser_email'));
    $this->addColumn(new SqlColumn($this->_tOrganiserUser, 'phone', 'organiser_phone'));
    
    $this->addColumn(new SqlColumn(false, new SqlStatementBi($this->columns['organiser_firstname'], $this->columns['organiser_lastname'], "CONCAT(%s,' ',%s)"), 'organiser_fullname'));
  }
  
  private function _insertOccupiedSelect() {
    $this->_sOccupied = new SEventAttendee;
    $this->_sOccupied->addColumn(new SqlColumn($this->_tEvent, 'event_id', 'event_id_outer', false, false, true));
    $this->_sOccupied->addStatement(new SqlStatementMono($this->_sOccupied->columns['substitute'], "%s='N'"));
    $this->_sOccupied->addStatement(new SqlStatementBi($this->_sOccupied->columns['event'], $this->_sOccupied->columns['event_id_outer'], '%s=%s'));
    $this->_sOccupied->setColumnsMask(array('sum_places'));
    
    $this->addColumn(new SqlColumn(false, $this->_sOccupied, 'occupied'));
    $this->addColumn(new SqlColumn(false, new SqlStatementBi($this->columns['max_attendees'], $this->columns['occupied'], '%s-%s'), 'free'));
    
    $this->_sOccupied = new SEventAttendee;
    $this->_sOccupied->addColumn(new SqlColumn($this->_tEvent, 'event_id', 'event_id_outer', false, false, true));
    $this->_sOccupied->addStatement(new SqlStatementMono($this->_sOccupied->columns['substitute'], "%s='Y'"));
    $this->_sOccupied->addStatement(new SqlStatementBi($this->_sOccupied->columns['event'], $this->_sOccupied->columns['event_id_outer'], '%s=%s'));
    $this->_sOccupied->setColumnsMask(array('sum_places'));
    
    $this->addColumn(new SqlColumn(false, $this->_sOccupied, 'occupied_substitute'));
    $this->addColumn(new SqlColumn(false, new SqlStatementBi($this->columns['max_substitutes'], $this->columns['occupied_substitute'], 'IFNULL(%s,0)-%s'), 'free_substitute'));
  }
  
  private function _insertAttendeeSelect() {
    $this->_sOccupied = new SEventAttendee;
    $this->_sOccupied->addColumn(new SqlColumn($this->_tEvent, 'event_id', 'event_id_outer', false, false, true));
    $this->_sOccupied->addStatement(new SqlStatementMono($this->_sOccupied->columns['substitute'], "%s='N'"));
    $this->_sOccupied->addStatement(new SqlStatementBi($this->_sOccupied->columns['event'], $this->_sOccupied->columns['event_id_outer'], '%s=%s'));
    $this->_sOccupied->setColumnsMask(array('all_user_id'));
    
    $this->addColumn(new SqlColumn(false, $this->_sOccupied, 'attendees_id'));
    
    $this->_sOccupied = new SEventAttendee;
    $this->_sOccupied->addColumn(new SqlColumn($this->_tEvent, 'event_id', 'event_id_outer', false, false, true));
    $this->_sOccupied->addStatement(new SqlStatementMono($this->_sOccupied->columns['substitute'], "%s='N'"));
    $this->_sOccupied->addStatement(new SqlStatementBi($this->_sOccupied->columns['event'], $this->_sOccupied->columns['event_id_outer'], '%s=%s'));
    $this->_sOccupied->setColumnsMask(array('all_fullname'));
    
    $this->addColumn(new SqlColumn(false, $this->_sOccupied, 'attendees'));
    
    $this->_sOccupied = new SEventAttendee;
    $this->_sOccupied->addColumn(new SqlColumn($this->_tEvent, 'event_id', 'event_id_outer', false, false, true));
    $this->_sOccupied->addStatement(new SqlStatementMono($this->_sOccupied->columns['substitute'], "%s='Y'"));
    $this->_sOccupied->addStatement(new SqlStatementBi($this->_sOccupied->columns['event'], $this->_sOccupied->columns['event_id_outer'], '%s=%s'));
    $this->_sOccupied->setColumnsMask(array('all_fullname'));
    
    $this->addColumn(new SqlColumn(false, $this->_sOccupied, 'susbstitutes'));
  }
  
  private function _insertEventTagTable() {
    $this->_tEventTag = new SqlTable('event_tag', 'et');
    
    $this->addColumn(new SqlColumn($this->_tEventTag, 'event'));
    $this->addColumn(new SqlColumn($this->_tEventTag, 'tag'));
    
    $this->addColumn(new SqlColumn(false, new SqlStatementMono($this->columns['tag'], 'GROUP_CONCAT(%s)'), 'all_tag', true));
  }
  
  private function _insertTagTable() {
    $this->_tTag = new SqlTable('tag', 't');
    
    $this->addColumn(new SqlColumn($this->_tTag, 'tag_id'));
    $this->addColumn(new SqlColumn($this->_tTag, 'name', 'tag_name'));
  }
  
  private function _insertEventPortalTable() {
    $this->_tEventPortal = new SqlTable('event_portal', 'ep');
    
    $this->addColumn(new SqlColumn($this->_tEventPortal, 'event', 'ep_event'));
    $this->addColumn(new SqlColumn($this->_tEventPortal, 'portal'));
  }
  
  private function _insertEventResourceTable() {
    $this->_tEventResource = new SqlTable('event_resource', 'er');
    
    $this->addColumn(new SqlColumn($this->_tEventResource, 'event', 'resource_event'));
    $this->addColumn(new SqlColumn($this->_tEventResource, 'resource'));
  }
  
  private function _insertResourceTable() {
    $this->_tResource = new SqlTable('resource', 'r');
    
    $this->addColumn(new SqlColumn($this->_tResource, 'resource_id'));
    $this->addColumn(new SqlColumn($this->_tResource, 'name', 'resource_name'));
    $this->addColumn(new SqlColumn($this->_tResource, 'description', 'resource_description'));
    
    $this->addColumn(new SqlColumn(false, new SqlStatementMono($this->columns['resource_name'], 'GROUP_CONCAT(DISTINCT %s)'), 'all_resource_name', true));
  }
  
  private function _insertNotificationTemplateTable() {
    $this->_tNotificationTemplate = new SqlTable('notificationtemplate', 'nt');

    $this->addColumn(new SqlColumn($this->_tNotificationTemplate, 'notificationtemplate_id'));
    $this->addColumn(new SqlColumn($this->_tNotificationTemplate, 'notificationtemplate_provider'));
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
  
  private function _insertResourceAvailabilitySelect() {
    $this->_sResourceAvailability = new SResourceAvailability;
    $this->_sResourceAvailability->addColumn(new SqlColumn($this->_tEvent, 'start', 'outer_start', false, false, true));
    $this->_sResourceAvailability->addColumn(new SqlColumn($this->_tEvent, 'end', 'outer_end', false, false, true));
    $this->_sResourceAvailability->addColumn(new SqlColumn($this->_tEventResource, 'resource', 'outer_resource', false, false, true));
    $this->_sResourceAvailability->addStatement(new SqlStatementBi(
                              $this->_sResourceAvailability->columns['resource'], $this->_sResourceAvailability->columns['outer_resource'],
                              '%s=%s'));
    $this->_sResourceAvailability->addStatement(new SqlStatementQuad(
                              $this->_sResourceAvailability->columns['start'], $this->_sResourceAvailability->columns['outer_start'],
                              $this->_sResourceAvailability->columns['outer_end'], $this->_sResourceAvailability->columns['end'],
                              '%s<=%s AND %s<=%s'));
    $this->_sResourceAvailability->setColumnsMask(array('resourceavailability_id'));
    
    $this->addColumn(new SqlColumn(false, $this->_sResourceAvailability, 'resourceavailability'));
  }
  
  private function _insertTagCountSelect() {
    $this->sTag = new SqlSelect;
    $tTag = new SqlTable('event_tag', 'et');
    $this->sTag->addColumn(new SqlColumn($tTag, 'event'));
    $this->sTag->addColumn(new SqlColumn($tTag, 'tag'));
    $this->sTag->addColumn(new SqlColumn(false, 'event_id', 'outer_event', false, false, true));
    $this->sTag->addColumn(new SqlColumn(false, new SqlStatementMono($this->sTag->columns['tag'], 'COUNT(%s)'), 'count', true));
    $this->sTag->addStatement(new SqlStatementBi($this->sTag->columns['event'], $this->sTag->columns['outer_event'], '%s=%s'));
    $this->sTag->setColumnsMask(array('count'));
    
    $this->addColumn(new SqlColumn(false, $this->sTag, 'tag_count'));
  }

  private function _insertTagSelect() {
    $select = new SqlSelect;
    $table = new SqlTable('event_tag', 'et');
    $select->addColumn(new SqlColumn($table, 'event'));
    $select->addColumn(new SqlColumn($table, 'tag'));
    $select->addColumn(new SqlColumn(false, 'event_id', 'outer_event', false, false, true));
    $select->addStatement(new SqlStatementBi($select->columns['event'], $select->columns['outer_event'], '%s=%s'));
    $select->setColumnsMask(array('tag'));

    $this->addColumn(new SqlColumn(false, $select, 'all_tag_select'));
  }

  private function _insertRepeatItemsSelect() {
    $this->_sRepeatItems = new SqlSelect;
    $table = new SqlTable('event', 'evt');
    $this->_sRepeatItems->addColumn(new SqlColumn($table, 'event_id'));
    $this->_sRepeatItems->addColumn(new SqlColumn($table, 'active'));
    $this->_sRepeatItems->addColumn(new SqlColumn($table, 'start'));
    $this->_sRepeatItems->addColumn(new SqlColumn($table, 'end'));
    $this->_sRepeatItems->addColumn(new SqlColumn($table, 'repeat_parent'));
    $this->_sRepeatItems->addColumn(new SqlColumn($this->_tEvent, 'event_id', 'outer_event', false, false, true));
    $this->_sRepeatItems->addColumn(new SqlColumn(false, new SqlStatementMono($this->_sRepeatItems->columns['event_id'], 'COUNT(%s)'), 'count', true));
    $this->_sRepeatItems->addStatement(new SqlStatementBi($this->_sRepeatItems->columns['repeat_parent'], $this->_sRepeatItems->columns['outer_event'], '%s=%s'));
    $this->_sRepeatItems->setColumnsMask(array('count'));

    $this->addColumn(new SqlColumn(false, $this->_sRepeatItems, 'repeat_items_count'));
  }

  public function addRepeatItemsTermCondition($term, $condition) {
    $this->_sRepeatItems->addStatement(new SqlStatementTri($term, $this->_sRepeatItems->columns['start'], $this->_sRepeatItems->columns['end'], $condition));
  }

  public function addRepeatItemsActiveCondition($active) {
    $this->_sRepeatItems->addStatement(new SqlStatementBi($this->_sRepeatItems->columns['active'], $active, '%s=%s'));
  }
  
  protected function _initSqlSelect() {
    $this->_insertEventTable();
    $this->_insertCenterTable();
    $this->_insertAddressTable();
    $this->_insertProviderTable();
    $this->_insertProviderCustomerTable();
    $this->_insertOrganiserUserTable();
    $this->_insertOccupiedSelect();
    $this->_insertAttendeeSelect();
    $this->_insertEventTagTable();
    $this->_insertTagTable();
    $this->_insertEventPortalTable();
    $this->_insertEventResourceTable();
    $this->_insertResourceTable();
    $this->_insertNotificationTemplateTable();
    $this->_insertNotificationTemplateItemTable();
    $this->_insertDocumentTemplateTable();
    $this->_insertDocumentTemplateItemTable();

    $this->_insertRepeatItemsSelect();
    $this->_insertResourceAvailabilitySelect();
    $this->_insertTagCountSelect();
    $this->_insertTagSelect();
    
    $this->addColumn(new SqlColumn(false, new SqlStatementBi($this->columns['name'], $this->columns['free'], "CONCAT(%s,' (',%s,')')"), 'name_with_free'));
    $this->addColumn(new SqlColumn(false, new SqlStatementBi($this->columns['name'], $this->columns['start'], "CONCAT(%s,' (',%s,')')"), 'name_with_start'));
    $this->addColumn(new SqlColumn(false, new SqlStatementBi($this->columns['name'], $this->columns['free_substitute'], "CONCAT(%s,' (',%s,')')"), 'name_with_free_substitute'));
    
    $this->addColumn(new SqlColumn(false, new SqlStatementBi($this->columns['provider_name'], $this->columns['center_name'], "CONCAT(%s,' (',%s,')')"), 'provider_with_center'));
    $this->addColumn(new SqlColumn(false, new SqlStatementBi($this->columns['center_name'], $this->columns['full_address'], "CONCAT(%s,' - ',%s)"), 'center_description'));

    $this->addColumn(new SqlColumn(false, new SqlStatementMono($this->columns['fe_allowed_payment'], '1&%s'), 'fe_allowed_payment_credit'));
    $this->addColumn(new SqlColumn(false, new SqlStatementMono($this->columns['fe_allowed_payment'], '10&%s'), 'fe_allowed_payment_ticket'));
    $this->addColumn(new SqlColumn(false, new SqlStatementMono($this->columns['fe_allowed_payment'], '100&%s'), 'fe_allowed_payment_online'));

    $this->addJoin(new SqlJoin('LEFT', $this->_tCenter, new SqlStatementBi($this->columns['center'], $this->columns['center_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tAddress, new SqlStatementBi($this->columns['address'], $this->columns['address_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tProvider, new SqlStatementBi($this->columns['provider'], $this->columns['provider_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tProviderCustomer, new SqlStatementBi($this->columns['customer_provider'], $this->columns['provider_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tOrganiserUser, new SqlStatementBi($this->columns['organiser'], $this->columns['user_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tEventTag, new SqlStatementBi($this->columns['event'], $this->columns['event_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tTag, new SqlStatementBi($this->columns['tag'], $this->columns['tag_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tEventPortal, new SqlStatementBi($this->columns['ep_event'], $this->columns['event_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tEventResource, new SqlStatementBi($this->columns['resource_event'], $this->columns['event_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tResource, new SqlStatementBi($this->columns['resource'], $this->columns['resource_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tNotificationTemplate, new SqlStatementBi($this->columns['notificationtemplate'], $this->columns['notificationtemplate_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tNotificationTemplateItem, new SqlStatementBi($this->columns['notificationtemplateitem_notificationtemplate'], $this->columns['notificationtemplate_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tDocumentTemplate, new SqlStatementBi($this->columns['documenttemplate'], $this->columns['documenttemplate_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tDocumentTemplateItem, new SqlStatementBi($this->columns['documenttemplateitem_documenttemplate'], $this->columns['documenttemplate_id'], '%s=%s')));
  }
}

?>
