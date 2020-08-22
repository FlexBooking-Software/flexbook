<?php

class SReservationConditionItem extends SqlSelect {
  private $_tReservationConditionItem;
  private $_tReservationCondition;
  private $_tCustomer;
  
  private function _insertReservationConditionItemTable() {
    $this->_tReservationConditionItem = new SqlTable('reservationconditionitem', 'rci');

    $this->addColumn(new SqlColumn($this->_tReservationConditionItem, 'reservationconditionitem_id'));
    $this->addColumn(new SqlColumn($this->_tReservationConditionItem, 'reservationcondition'));
    $this->addColumn(new SqlColumn($this->_tReservationConditionItem, 'name'));
    $this->addColumn(new SqlColumn($this->_tReservationConditionItem, 'time_from'));
    $this->addColumn(new SqlColumn($this->_tReservationConditionItem, 'time_to'));
    $this->addColumn(new SqlColumn($this->_tReservationConditionItem, 'limit_center'));
    $this->addColumn(new SqlColumn($this->_tReservationConditionItem, 'limit_center_message'));
    $this->addColumn(new SqlColumn($this->_tReservationConditionItem, 'limit_quantity'));
    $this->addColumn(new SqlColumn($this->_tReservationConditionItem, 'limit_quantity_period'));
    $this->addColumn(new SqlColumn($this->_tReservationConditionItem, 'limit_quantity_type'));
    $this->addColumn(new SqlColumn($this->_tReservationConditionItem, 'limit_quantity_scope'));
    $this->addColumn(new SqlColumn($this->_tReservationConditionItem, 'limit_quantity_message'));
    $this->addColumn(new SqlColumn($this->_tReservationConditionItem, 'limit_first_time_before_start'));
    $this->addColumn(new SqlColumn($this->_tReservationConditionItem, 'limit_first_time_before_message'));
    $this->addColumn(new SqlColumn($this->_tReservationConditionItem, 'limit_last_time_before_start'));
    $this->addColumn(new SqlColumn($this->_tReservationConditionItem, 'limit_last_time_before_message'));
    $this->addColumn(new SqlColumn($this->_tReservationConditionItem, 'limit_after_start_event'));
    $this->addColumn(new SqlColumn($this->_tReservationConditionItem, 'limit_after_start_event_message'));
    $this->addColumn(new SqlColumn($this->_tReservationConditionItem, 'advance_payment'));
    $this->addColumn(new SqlColumn($this->_tReservationConditionItem, 'advance_payment_message'));
    $this->addColumn(new SqlColumn($this->_tReservationConditionItem, 'cancel_before_start'));
    $this->addColumn(new SqlColumn($this->_tReservationConditionItem, 'cancel_before_start_message'));
    $this->addColumn(new SqlColumn($this->_tReservationConditionItem, 'cancel_payed_before_start'));
    $this->addColumn(new SqlColumn($this->_tReservationConditionItem, 'cancel_payed_before_start_message'));
    $this->addColumn(new SqlColumn($this->_tReservationConditionItem, 'limit_anonymous_before_start'));
    $this->addColumn(new SqlColumn($this->_tReservationConditionItem, 'limit_anonymous_before_message'));
    $this->addColumn(new SqlColumn($this->_tReservationConditionItem, 'limit_other_scope'));
    $this->addColumn(new SqlColumn($this->_tReservationConditionItem, 'required_event'));
    $this->addColumn(new SqlColumn($this->_tReservationConditionItem, 'required_event_exists'));
    $this->addColumn(new SqlColumn($this->_tReservationConditionItem, 'required_event_payed'));
    $this->addColumn(new SqlColumn($this->_tReservationConditionItem, 'required_event_all'));
    $this->addColumn(new SqlColumn($this->_tReservationConditionItem, 'required_event_message'));
    $this->addColumn(new SqlColumn($this->_tReservationConditionItem, 'required_resource'));
    $this->addColumn(new SqlColumn($this->_tReservationConditionItem, 'required_resource_exists'));
    $this->addColumn(new SqlColumn($this->_tReservationConditionItem, 'required_resource_payed'));
    $this->addColumn(new SqlColumn($this->_tReservationConditionItem, 'required_resource_all'));
    $this->addColumn(new SqlColumn($this->_tReservationConditionItem, 'required_resource_message'));
    $this->addColumn(new SqlColumn($this->_tReservationConditionItem, 'limit_total_quantity'));
    $this->addColumn(new SqlColumn($this->_tReservationConditionItem, 'limit_total_quantity_period'));
    $this->addColumn(new SqlColumn($this->_tReservationConditionItem, 'limit_total_quantity_type'));
    $this->addColumn(new SqlColumn($this->_tReservationConditionItem, 'limit_total_quantity_tag'));
    $this->addColumn(new SqlColumn($this->_tReservationConditionItem, 'limit_total_quantity_message'));
    $this->addColumn(new SqlColumn($this->_tReservationConditionItem, 'limit_overlap_quantity'));
    $this->addColumn(new SqlColumn($this->_tReservationConditionItem, 'limit_overlap_quantity_scope'));
    $this->addColumn(new SqlColumn($this->_tReservationConditionItem, 'limit_overlap_quantity_tag'));
    $this->addColumn(new SqlColumn($this->_tReservationConditionItem, 'limit_overlap_quantity_message'));
  }
  
  private function _insertReservationConditionTable() {
    $this->_tReservationCondition = new SqlTable('reservationcondition', 'rc');

    $this->addColumn(new SqlColumn($this->_tReservationCondition, 'reservationcondition_id'));
    $this->addColumn(new SqlColumn($this->_tReservationCondition, 'name', 'reservationcondition_name'));
    $this->addColumn(new SqlColumn($this->_tReservationCondition, 'provider', 'reservationcondition_provider'));
    $this->addColumn(new SqlColumn($this->_tReservationCondition, 'evaluation', 'reservationcondition_evaluation'));
  }
  
  private function _insertCustomerTable() {
    $this->_tCustomer = new SqlTable('customer', 'c');

    $this->addColumn(new SqlColumn($this->_tCustomer, 'customer_id'));
    $this->addColumn(new SqlColumn($this->_tCustomer, 'name', 'provider_name'));
    $this->addColumn(new SqlColumn($this->_tCustomer, 'provider', 'customer_provider'));
  }
  
  protected function _initSqlSelect() {
    $this->_insertReservationConditionItemTable();
    $this->_insertReservationConditionTable();
    $this->_insertCustomerTable();
    
    $this->addJoin(new SqlJoin(false, $this->_tReservationCondition, new SqlStatementBi($this->columns['reservationcondition'], $this->columns['reservationcondition_id'], '%s=%s')));
    $this->addJoin(new SqlJoin(false, $this->_tCustomer, new SqlStatementBi($this->columns['customer_provider'], $this->columns['reservationcondition_provider'], '%s=%s')));
  }
}

?>
