<?php

class SProviderPortalPage extends SqlSelect {
  private $_tProviderPortalPage;
  private $_tProviderPortal;
  
  private function _insertProviderPortalPageTable() {
    $this->_tProviderPortalPage = new SqlTable('providerportalpage', 'popp');

    $this->addColumn(new SqlColumn($this->_tProviderPortalPage, 'providerportalpage_id'));
    $this->addColumn(new SqlColumn($this->_tProviderPortalPage, 'providerportal'));
    $this->addColumn(new SqlColumn($this->_tProviderPortalPage, 'from_template'));
    $this->addColumn(new SqlColumn($this->_tProviderPortalPage, 'short_name'));
    $this->addColumn(new SqlColumn($this->_tProviderPortalPage, 'name'));
    $this->addColumn(new SqlColumn($this->_tProviderPortalPage, 'content'));
  }
  
  private function _insertProviderPortalTable() {
    $this->_tProviderPortal = new SqlTable('providerportal', 'pop');

    $this->addColumn(new SqlColumn($this->_tProviderPortal, 'providerportal_id'));
    $this->addColumn(new SqlColumn($this->_tProviderPortal, 'provider'));
    $this->addColumn(new SqlColumn($this->_tProviderPortal, 'active'));
  }
  
  protected function _initSqlSelect() {
    $this->_insertProviderPortalPageTable();
    $this->_insertProviderPortalTable();
    
    $this->addJoin(new SqlJoin('LEFT', $this->_tProviderPortal, new SqlStatementBi($this->columns['providerportal'], $this->columns['providerportal_id'], '%s=%s')));
  }
}

?>
