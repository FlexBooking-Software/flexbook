<?php

class SUser extends MySqlSelect {
  private $_tUser;
  private $_tUserRegistration;
	private $_tProvider;
  private $_tUserValidation;
  private $_tAddress;
  private $_tUserAttribute;
  private $_tAttribute;

  private $_sUserRegistration;
  private $_sEventAttendee;
  
  private function _insertUserTable() {
    $this->_tUser = new SqlTable('user', 'u');

    $this->addColumn(new SqlColumn($this->_tUser, 'user_id'));
    $this->addColumn(new SqlColumn($this->_tUser, 'parent_user'));
    $this->addColumn(new SqlColumn($this->_tUser, 'firstname'));
    $this->addColumn(new SqlColumn($this->_tUser, 'lastname'));
    $this->addColumn(new SqlColumn($this->_tUser, 'email'));
    $this->addColumn(new SqlColumn($this->_tUser, 'phone'));
    $this->addColumn(new SqlColumn($this->_tUser, 'address'));
    $this->addColumn(new SqlColumn($this->_tUser, 'username'));
    $this->addColumn(new SqlColumn($this->_tUser, 'password'));
    $this->addColumn(new SqlColumn($this->_tUser, 'facebook_id'));
    $this->addColumn(new SqlColumn($this->_tUser, 'google_id'));
    $this->addColumn(new SqlColumn($this->_tUser, 'twitter_id'));
    $this->addColumn(new SqlColumn($this->_tUser, 'validated'));
    $this->addColumn(new SqlColumn($this->_tUser, 'admin'));
    #$this->addColumn(new SqlColumn($this->_tUser, 'organiser'));
    #$this->addColumn(new SqlColumn($this->_tUser, 'provider'));
    $this->addColumn(new SqlColumn($this->_tUser, 'disabled'));
		$this->addColumn(new SqlColumn($this->_tUser, 'reservationcondition'));
    
    $this->addColumn(new SqlColumn(false, new SqlStatementBi($this->columns['firstname'], $this->columns['lastname'], "CONCAT(%s,' ',%s)"), 'fullname'));
		$this->addColumn(new SqlColumn(false, new SqlStatementBi($this->columns['lastname'], $this->columns['firstname'], "CONCAT(%s,' ',%s)"), 'fullname_reversed'));
  }
  
  private function _insertUserRegistrationTable() {
    $this->_tUserRegistration = new SqlTable('userregistration', 'ur');
    
    $this->addColumn(new SqlColumn($this->_tUserRegistration, 'userregistration_id'));
    $this->addColumn(new SqlColumn($this->_tUserRegistration, 'user', 'registration_user'));
    $this->addColumn(new SqlColumn($this->_tUserRegistration, 'registration_timestamp'));
    $this->addColumn(new SqlColumn($this->_tUserRegistration, 'receive_advertising','registration_advertising'));
    $this->addColumn(new SqlColumn($this->_tUserRegistration, 'provider', 'registration_provider'));
    $this->addColumn(new SqlColumn($this->_tUserRegistration, 'credit', 'registration_credit'));
    $this->addColumn(new SqlColumn($this->_tUserRegistration, 'organiser', 'registration_organiser'));
    $this->addColumn(new SqlColumn($this->_tUserRegistration, 'power_organiser', 'registration_power_organiser'));
    $this->addColumn(new SqlColumn($this->_tUserRegistration, 'admin', 'registration_admin'));
		$this->addColumn(new SqlColumn($this->_tUserRegistration, 'supervisor', 'registration_supervisor'));
    $this->addColumn(new SqlColumn($this->_tUserRegistration, 'reception', 'registration_reception'));
  }

	protected function _insertProviderTable() {
		$this->_tProvider = new SqlTable('provider', 'prov');

		$this->addColumn(new SqlColumn($this->_tProvider, 'provider_id'));
		$this->addColumn(new SqlColumn($this->_tProvider, 'short_name', 'provider_short_name'));
		$this->addColumn(new SqlColumn($this->_tProvider, 'invoice_name', 'provider_invoice_name'));
	}

  private function _insertUserValidationTable() {
    $this->_tUserValidation = new SqlTable('uservalidation', 'uv');

    $this->addColumn(new SqlColumn($this->_tUserValidation, 'user', 'validation_user'));
    $this->addColumn(new SqlColumn($this->_tUserValidation, 'validation_string'));
  }
  
  protected function _insertUserRegistrationSelect() {
    $this->_sUserRegistration = new SUserRegistration;
    
    $this->_sUserRegistration->addColumn(new SqlColumn($this->_tUser, 'user_id', 'outer_user', false, false, true));
    $this->_sUserRegistration->addColumn(new SqlColumn(false, new SqlStatementMono($this->_sUserRegistration->columns['userregistration_id'], 'COUNT(%s)'), 'count'));
    $this->_sUserRegistration->addStatement(new SqlStatementBi($this->_sUserRegistration->columns['user'], $this->_sUserRegistration->columns['outer_user'], '%s=%s'));
    $this->_sUserRegistration->setColumnsMask(array('count'));
    
    $this->addColumn(new SqlColumn(false, $this->_sUserRegistration, 'provider_registration'));
  }

  public function addUserRegistrationSelectStatement($columns, $condition) {
    $statement = null;
    switch (count($columns)) {
      case 1: $statement = new SqlStatementMono($this->_sUserRegistration->columns[$columns[0]], $condition); break;
      case 2: $statement = new SqlStatementBi($this->_sUserRegistration->columns[$columns[0]], $this->_sUserRegistration->columns[$columns[1]], $condition); break;
      default: break;
    }
    
    if ($statement) $this->_sUserRegistration->addStatement($statement);
  }

  protected function _insertEventAttendeeSelect() {
    $this->_sEventAttendee = new SEventAttendee;

    $this->_sEventAttendee->addColumn(new SqlColumn($this->_tUser, 'user_id', 'outer_user', false, false, true));
    $this->_sEventAttendee->addColumn(new SqlColumn(false, new SqlStatementMono($this->_sEventAttendee->columns['eventattendee_id'], 'SUM(%s)'), 'sum'));
    $this->_sEventAttendee->addStatement(new SqlStatementMono($this->_sEventAttendee->columns['failed'], '%s IS NULL'));
    $this->_sEventAttendee->addStatement(new SqlStatementMono($this->_sEventAttendee->columns['reservation'], '%s IS NOT NULL'));
    $this->_sEventAttendee->addStatement(new SqlStatementMono($this->_sEventAttendee->columns['reservation_cancelled'], '%s IS NULL'));
    $this->_sEventAttendee->addStatement(new SqlStatementBi($this->_sEventAttendee->columns['user'], $this->_sEventAttendee->columns['outer_user'], '%s=%s'));
    $this->_sEventAttendee->setColumnsMask(array('sum'));

    $this->addColumn(new SqlColumn(false, $this->_sEventAttendee, 'eventattendee'));
  }

  public function addEventAttendeeStatement($columns, $condition) {
    $statement = null;
    switch (count($columns)) {
      case 1: $statement = new SqlStatementMono($this->_sEventAttendee->columns[$columns[0]], $condition); break;
      case 2: $statement = new SqlStatementBi($this->_sEventAttendee->columns[$columns[0]], $this->_sEventAttendee->columns[$columns[1]], $condition); break;
      default: break;
    }

    if ($statement) $this->_sEventAttendee->addStatement($statement);
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

  private function _insertUserAttributeTable() {
    $this->_tUserAttribute = new SqlTable('user_attribute', 'uattr');

    $this->addColumn(new SqlColumn($this->_tUserAttribute, 'user','attribute_user'));
    $this->addColumn(new SqlColumn($this->_tUserAttribute, 'attribute'));
    $this->addColumn(new SqlColumn($this->_tUserAttribute, 'value','attribute_value'));
  }

  private function _insertAttributeTable() {
    $this->_tAttribute = new SqlTable('attribute', 'attr');

    $this->addColumn(new SqlColumn($this->_tAttribute, 'attribute_id'));
    $this->addColumn(new SqlColumn($this->_tAttribute, 'provider','attribute_provider'));
    $this->addColumn(new SqlColumn($this->_tAttribute, 'restricted','attribute_restricted'));
    $this->addColumn(new SqlColumn($this->_tAttribute, 'short_name','attribute_short_name'));
    $this->addColumn(new SqlColumn($this->_tAttribute, 'type','attribute_type'));
    $this->addColumn(new SqlColumn($this->_tAttribute, 'disabled','attribute_disabled'));

    $this->addColumn(new SqlColumn(false, new SqlStatementBi($this->columns['attribute_id'], $this->columns['attribute_value'], "CONCAT(%s,':',%s)"), 'attribute_value_json'));
		$this->addColumn(new SqlColumn(false, new SqlStatementBi($this->columns['attribute_value_json'], $this->columns['attribute_id'], 'GROUP_CONCAT(%s ORDER BY %s ASC)'), 'all_attribute_value_json', true));
  }
  
  protected function _initSqlSelect() {
    $this->_insertUserTable();
    $this->_insertUserRegistrationTable();
		$this->_insertProviderTable();
    $this->_insertUserValidationTable();
    $this->_insertAddressTable();
    $this->_insertUserAttributeTable();
    $this->_insertAttributeTable();
    
    $this->_insertUserRegistrationSelect();
    $this->_insertEventAttendeeSelect();
    
    $this->addColumn(new SqlColumn(false, new SqlStatementTri($this->columns['user_id'], $this->columns['fullname'], $this->columns['email'], "CONCAT(%s,'#',%s,'#',%s)"), 'user_id_with_name_email'));
		$this->addColumn(new SqlColumn(false, new SqlStatementBi($this->columns['fullname_reversed'], $this->columns['email'], "CONCAT(%s,' (',%s,')')"), 'fullname_reversed_with_email'));
  
    $this->addJoin(new SqlJoin('LEFT', $this->_tUserRegistration, new SqlStatementBi($this->columns['registration_user'], $this->columns['user_id'], '%s=%s')));
		$this->addJoin(new SqlJoin('LEFT', $this->_tProvider, new SqlStatementBi($this->columns['registration_provider'], $this->columns['provider_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tUserValidation, new SqlStatementBi($this->columns['validation_user'], $this->columns['user_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tAddress, new SqlStatementBi($this->columns['address'], $this->columns['address_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tUserAttribute, new SqlStatementBi($this->columns['attribute_user'], $this->columns['user_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tAttribute, new SqlStatementQuad($this->columns['attribute'], $this->columns['attribute_id'], $this->columns['attribute_disabled'], $this->columns['attribute_restricted'], "%s=%s AND %s='N' AND %s IS NULL")));
  }
}

?>