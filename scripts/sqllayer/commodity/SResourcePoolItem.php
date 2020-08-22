<?php

class SResourcePoolItem extends SqlSelect {
  private $_tResourcePoolItem;
  private $_tResourcePool;
  private $_tResource;
  private $_tUnitProfile;
  private $_tPriceList;
  
  private function _insertResourcePoolItemTable() {
    $this->_tResourcePoolItem = new SqlTable('resourcepoolitem', 'rpi');
    
    $this->addColumn(new SqlColumn($this->_tResourcePoolItem, 'resourcepool'));
    $this->addColumn(new SqlColumn($this->_tResourcePoolItem, 'resource'));
  }
  
  private function _insertResourcePoolTable() {
    $this->_tResourcePool = new SqlTable('resourcepool', 'rp');
    
    $this->addColumn(new SqlColumn($this->_tResourcePool, 'resourcepool_id'));
    $this->addColumn(new SqlColumn($this->_tResourcePool, 'provider'));
    $this->addColumn(new SqlColumn($this->_tResourcePool, 'external_id'));
    $this->addColumn(new SqlColumn($this->_tResourcePool, 'name'));
    $this->addColumn(new SqlColumn($this->_tResourcePool, 'description'));
    $this->addColumn(new SqlColumn($this->_tResourcePool, 'active'));
  }
  
  private function _insertResourceTable() {
    $this->_tResource = new SqlTable('resource', 'r');
    
    $this->addColumn(new SqlColumn($this->_tResource, 'resource_id'));
    $this->addColumn(new SqlColumn($this->_tResource, 'name', 'resource_name'));
    $this->addColumn(new SqlColumn($this->_tResource, 'description', 'resource_description'));
    $this->addColumn(new SqlColumn($this->_tResource, 'unitprofile', 'resource_unitprofile'));
    $this->addColumn(new SqlColumn($this->_tResource, 'price', 'resource_price'));
    $this->addColumn(new SqlColumn($this->_tResource, 'pricelist', 'resource_pricelist'));
    $this->addColumn(new SqlColumn($this->_tResource, 'active', 'resource_active'));
  }
  
  private function _insertUnitProfileTable() {
    $this->_tUnitProfile = new SqlTable('unitprofile', 'up');

    $this->addColumn(new SqlColumn($this->_tUnitProfile, 'unitprofile_id'));
    $this->addColumn(new SqlColumn($this->_tUnitProfile, 'name', 'unitprofile_name'));
    $this->addColumn(new SqlColumn($this->_tUnitProfile, 'unit', 'unitprofile_unit'));
    $this->addColumn(new SqlColumn($this->_tUnitProfile, 'unit_rounding', 'unitprofile_unit_rounding'));
    $this->addColumn(new SqlColumn($this->_tUnitProfile, 'minimum_quantity', 'unitprofile_minimum_quantity'));
    $this->addColumn(new SqlColumn($this->_tUnitProfile, 'maximum_quantity', 'unitprofile_maximum_quantity'));
    $this->addColumn(new SqlColumn($this->_tUnitProfile, 'time_alignment_from', 'unitprofile_time_alignment_from'));
    $this->addColumn(new SqlColumn($this->_tUnitProfile, 'time_alignment_to', 'unitprofile_time_alignment_to'));
    $this->addColumn(new SqlColumn($this->_tUnitProfile, 'time_alignment_grid', 'unitprofile_time_alignment_grid'));
    $this->addColumn(new SqlColumn($this->_tUnitProfile, 'time_end_from', 'unitprofile_time_end_from'));
    $this->addColumn(new SqlColumn($this->_tUnitProfile, 'time_end_to', 'unitprofile_time_end_to'));
  }
  
  private function _insertPriceListTable() {
    $this->_tPriceList = new SqlTable('pricelist', 'pl');
    
    $this->addColumn(new SqlColumn($this->_tPriceList, 'pricelist_id'));
    $this->addColumn(new SqlColumn($this->_tPriceList, 'name', 'pricelist_name'));
  }
  
  protected function _initSqlSelect() {
    $this->_insertResourcePoolItemTable();
    $this->_insertResourcePoolTable();
    $this->_insertResourceTable();
    $this->_insertUnitProfileTable();
    $this->_insertPriceListTable();
  
    $this->addJoin(new SqlJoin(false, $this->_tResourcePool, new SqlStatementBi($this->columns['resourcepool'], $this->columns['resourcepool_id'], '%s=%s')));
    $this->addJoin(new SqlJoin(false, $this->_tResource, new SqlStatementBi($this->columns['resource'], $this->columns['resource_id'], '%s=%s')));
    $this->addJoin(new SqlJoin(false, $this->_tUnitProfile, new SqlStatementBi($this->columns['resource_unitprofile'], $this->columns['unitprofile_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tPriceList, new SqlStatementBi($this->columns['resource_pricelist'], $this->columns['pricelist_id'], '%s=%s')));
  }
}

?>
