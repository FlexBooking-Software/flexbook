<?php

class SPortalTemplatePageTemplate extends SqlSelect {
  private $_tPortalTemplatePageTemplate;
  private $_tPageTemplate;
  
  private function _insertPortalTemplatePageTemplateTable() {
    $this->_tPortalTemplatePageTemplate = new SqlTable('portaltemplate_pagetemplate', 'pot_pat');

    $this->addColumn(new SqlColumn($this->_tPortalTemplatePageTemplate, 'portaltemplate'));
    $this->addColumn(new SqlColumn($this->_tPortalTemplatePageTemplate, 'pagetemplate'));
    $this->addColumn(new SqlColumn($this->_tPortalTemplatePageTemplate, 'menu_sequence_code'));
  }
  
  private function _insertPageTemplateTable() {
    $this->_tPageTemplate = new SqlTable('pagetemplate', 'pat');

    $this->addColumn(new SqlColumn($this->_tPageTemplate, 'pagetemplate_id'));
    $this->addColumn(new SqlColumn($this->_tPageTemplate, 'name'));
    $this->addColumn(new SqlColumn($this->_tPageTemplate, 'content'));
  }
  
  protected function _initSqlSelect() {
    $this->_insertPortalTemplatePageTemplateTable();
    $this->_insertPageTemplateTable();
    
    $this->addJoin(new SqlJoin(false, $this->_tPageTemplate, new SqlStatementBi($this->columns['pagetemplate'], $this->columns['pagetemplate_id'], '%s=%s')));
  }
}

?>
