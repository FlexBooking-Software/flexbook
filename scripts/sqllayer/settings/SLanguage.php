<?php

class SLanguage extends SqlSelect {
  private $_tLanguage;
  
  private function _insertLanguageTable() {
    $this->_tLanguage = new SqlTable('language', 'l');
    
    $this->addColumn(new SqlColumn($this->_tLanguage, 'code'));
    $this->addColumn(new SqlColumn($this->_tLanguage, 'name'));
  }
  
  protected function _initSqlSelect() {
    $this->_insertLanguageTable();
  }
}

?>
