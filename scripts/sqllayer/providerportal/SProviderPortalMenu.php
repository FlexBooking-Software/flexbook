<?php

class SProviderPortalMenu extends SqlSelect {
  private $_tProviderPortalMenu;
  private $_tProviderPortalPage;
  
  private function _insertProviderPortalMenuTable() {
    $this->_tProviderPortalMenu = new SqlTable('providerportalmenu', 'popm');

    $this->addColumn(new SqlColumn($this->_tProviderPortalMenu, 'providerportalmenu_id'));
    $this->addColumn(new SqlColumn($this->_tProviderPortalMenu, 'providerportal'));
    $this->addColumn(new SqlColumn($this->_tProviderPortalMenu, 'name'));
    $this->addColumn(new SqlColumn($this->_tProviderPortalMenu, 'sequence_code'));
    $this->addColumn(new SqlColumn($this->_tProviderPortalMenu, 'providerportalpage'));
  }
  
  private function _insertProviderPortalPageTable() {
    $this->_tProviderPortalPage = new SqlTable('providerportalpage', 'popp');

    $this->addColumn(new SqlColumn($this->_tProviderPortalPage, 'providerportalpage_id'));
    $this->addColumn(new SqlColumn($this->_tProviderPortalPage, 'short_name', 'page_short_name'));
    $this->addColumn(new SqlColumn($this->_tProviderPortalPage, 'name', 'page_name'));
    $this->addColumn(new SqlColumn($this->_tProviderPortalPage, 'content', 'page_content'));
  }
  
  protected function _initSqlSelect() {
    $this->_insertProviderPortalMenuTable();
    $this->_insertProviderPortalPageTable();
    
    $this->addJoin(new SqlJoin('LEFT', $this->_tProviderPortalPage, new SqlStatementBi($this->columns['providerportalpage'], $this->columns['providerportalpage_id'], '%s=%s')));
  }
}

?>
