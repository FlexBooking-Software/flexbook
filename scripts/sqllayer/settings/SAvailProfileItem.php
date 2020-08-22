<?php

class SAvailabilityProfileItem extends SqlSelect {
  private $_tAvailabilityProfileItem;
  
  private function _insertAvailabilityProfileItemTable() {
    $this->_tAvailabilityProfileItem = new SqlTable('availabilityprofileitem', 'api');

    $this->addColumn(new SqlColumn($this->_tAvailabilityProfileItem, 'availabilityprofileitem_id'));
    $this->addColumn(new SqlColumn($this->_tAvailabilityProfileItem, 'availabilityprofile'));
    $this->addColumn(new SqlColumn($this->_tAvailabilityProfileItem, 'weekday'));
    $this->addColumn(new SqlColumn($this->_tAvailabilityProfileItem, 'time_from'));
    $this->addColumn(new SqlColumn($this->_tAvailabilityProfileItem, 'time_to'));
  }
  
  protected function _initSqlSelect() {
    $this->_insertAvailabilityProfileItemTable();
  }
}

?>
