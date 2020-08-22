<?php

class SSeason extends SqlSelect {
  private $_tSeason;
  private $_tPriceList;
  
  private function _insertSeasonTable() {
    $this->_tSeason = new SqlTable('season', 's');
    
    $this->addColumn(new SqlColumn($this->_tSeason, 'season_id'));
    $this->addColumn(new SqlColumn($this->_tSeason, 'pricelist'));
    $this->addColumn(new SqlColumn($this->_tSeason, 'name'));
    $this->addColumn(new SqlColumn($this->_tSeason, 'start'));
    $this->addColumn(new SqlColumn($this->_tSeason, 'end'));
    $this->addColumn(new SqlColumn($this->_tSeason, 'base_price'));
    $this->addColumn(new SqlColumn($this->_tSeason, 'mon_price'));
    $this->addColumn(new SqlColumn($this->_tSeason, 'tue_price'));
    $this->addColumn(new SqlColumn($this->_tSeason, 'wed_price'));
    $this->addColumn(new SqlColumn($this->_tSeason, 'thu_price'));
    $this->addColumn(new SqlColumn($this->_tSeason, 'fri_price'));
    $this->addColumn(new SqlColumn($this->_tSeason, 'sat_price'));
    $this->addColumn(new SqlColumn($this->_tSeason, 'sun_price'));
  }
  
  private function _insertPriceListTable() {
    $this->_tPriceList = new SqlTable('pricelist', 'pl');
    
    $this->addColumn(new SqlColumn($this->_tPriceList, 'pricelist_id'));
    $this->addColumn(new SqlColumn($this->_tPriceList, 'provider'));
    $this->addColumn(new SqlColumn($this->_tPriceList, 'name', 'pricelist_name'));
  }
  
  protected function _initSqlSelect() {
    $this->_insertSeasonTable();
    $this->_insertPriceListTable();
    
    $this->addJoin(new SqlJoin(false, $this->_tPriceList, new SqlStatementBi($this->columns['pricelist'], $this->columns['pricelist_id'], '%s=%s')));
  }
}

?>
