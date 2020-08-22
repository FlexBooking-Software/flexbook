<?php

class SResourceAvailability extends SqlSelect {
  private $_tResourceAvailability;
  
  private function _insertResourceAvailabilityTable() {
    $this->_tResourceAvailability = new SqlTable('resourceavailability', 'ra');
    
    $this->addColumn(new SqlColumn($this->_tResourceAvailability, 'resourceavailability_id'));
    $this->addColumn(new SqlColumn($this->_tResourceAvailability, 'resource'));
    $this->addColumn(new SqlColumn($this->_tResourceAvailability, 'start'));
    $this->addColumn(new SqlColumn($this->_tResourceAvailability, 'end'));
  }
  
  protected function _initSqlSelect() {
    $this->_insertResourceAvailabilityTable();
  }
}

?>
