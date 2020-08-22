<?php

class SAvailabilityExceptionProfileItem extends SqlSelect {
  private $_tAvailabilityExceptionProfileItem;
  
  private function _insertAvailabilityExceptionProfileItemTable() {
    $this->_tAvailabilityExceptionProfileItem = new SqlTable('availabilityexceptionprofileitem', 'api');

    $this->addColumn(new SqlColumn($this->_tAvailabilityExceptionProfileItem, 'availabilityexceptionprofileitem_id'));
    $this->addColumn(new SqlColumn($this->_tAvailabilityExceptionProfileItem, 'availabilityexceptionprofile'));
    $this->addColumn(new SqlColumn($this->_tAvailabilityExceptionProfileItem, 'name'));
    $this->addColumn(new SqlColumn($this->_tAvailabilityExceptionProfileItem, 'time_from'));
    $this->addColumn(new SqlColumn($this->_tAvailabilityExceptionProfileItem, 'time_to'));
    $this->addColumn(new SqlColumn($this->_tAvailabilityExceptionProfileItem, 'repeated'));
    $this->addColumn(new SqlColumn($this->_tAvailabilityExceptionProfileItem, 'repeat_cycle'));
    $this->addColumn(new SqlColumn($this->_tAvailabilityExceptionProfileItem, 'repeat_weekday'));
    $this->addColumn(new SqlColumn($this->_tAvailabilityExceptionProfileItem, 'repeat_until'));
  }
  
  protected function _initSqlSelect() {
    $this->_insertAvailabilityExceptionProfileItemTable();
  }
}

?>
