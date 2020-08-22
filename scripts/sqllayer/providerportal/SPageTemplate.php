<?php

class SPageTemplate extends SqlSelect {
  private $_tPageTemplate;
  
  private function _insertPageTemplateTable() {
    $this->_tPageTemplate = new SqlTable('pagetemplate', 'pat');

    $this->addColumn(new SqlColumn($this->_tPageTemplate, 'pagetemplate_id'));
    $this->addColumn(new SqlColumn($this->_tPageTemplate, 'name'));
    $this->addColumn(new SqlColumn($this->_tPageTemplate, 'content'));
  }
  
  protected function _initSqlSelect() {
    $this->_insertPageTemplateTable();

  }
}

?>
