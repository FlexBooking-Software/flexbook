<?php

class SVoucher extends SqlSelect {
  private $_tVoucher;
  private $_tProviderCustomer;

  private $_sReservation;
  
  protected function _insertVoucherTable() {
    $this->_tVoucher = new SqlTable('voucher', 'v');
    
    $this->addColumn(new SqlColumn($this->_tVoucher, 'voucher_id'));
    $this->addColumn(new SqlColumn($this->_tVoucher, 'name'));
    $this->addColumn(new SqlColumn($this->_tVoucher, 'code'));
    $this->addColumn(new SqlColumn($this->_tVoucher, 'provider'));
    $this->addColumn(new SqlColumn($this->_tVoucher, 'center'));
    $this->addColumn(new SqlColumn($this->_tVoucher, 'subject_tag'));
    $this->addColumn(new SqlColumn($this->_tVoucher, 'discount_amount'));
    $this->addColumn(new SqlColumn($this->_tVoucher, 'discount_proportion'));
    $this->addColumn(new SqlColumn($this->_tVoucher, 'application_total'));
    $this->addColumn(new SqlColumn($this->_tVoucher, 'application_user'));
    $this->addColumn(new SqlColumn($this->_tVoucher, 'validity_from'));
    $this->addColumn(new SqlColumn($this->_tVoucher, 'validity_to'));
    $this->addColumn(new SqlColumn($this->_tVoucher, 'active'));
  }
  
  private function _insertProviderCustomerTable() {
    $this->_tProviderCustomer = new SqlTable('customer', 'c');

    $this->addColumn(new SqlColumn($this->_tProviderCustomer, 'customer_id'));
    $this->addColumn(new SqlColumn($this->_tProviderCustomer, 'name', 'provider_name'));
    $this->addColumn(new SqlColumn($this->_tProviderCustomer, 'provider', 'customer_provider'));
  }

  private function _insertReservationCountSelect() {
    $this->_sReservation = new SReservation;
    $this->_sReservation->addColumn(new SqlColumn(false, 'voucher_id', 'outer_voucher', false, false, true));
    $this->_sReservation->addColumn(new SqlColumn(false, new SqlStatementMono($this->_sReservation->columns['reservation_id'], 'COUNT(%s)'), 'count', true));
    $this->_sReservation->addStatement(new SqlStatementBi($this->_sReservation->columns['voucher'], $this->_sReservation->columns['outer_voucher'], '%s=%s'));
    $this->_sReservation->addStatement(new SqlStatementMono($this->_sReservation->columns['cancelled'], '%s IS NULL'));
    $this->_sReservation->setColumnsMask(array('count'));

    $this->addColumn(new SqlColumn(false, $this->_sReservation, 'reservation_count'));
  }
  
  protected function _initSqlSelect() {
    $this->_insertVoucherTable();
    $this->_insertProviderCustomerTable();

    $this->_insertReservationCountSelect();

    $this->addColumn(new SqlColumn(false, "'PERIOD'", 'validity_type'));  // kvuli obecnemu GuiGridCellValidFor
    
    $this->addJoin(new SqlJoin(false, $this->_tProviderCustomer, new SqlStatementBi($this->columns['provider'], $this->columns['customer_provider'], '%s=%s')));
  }
}

?>
