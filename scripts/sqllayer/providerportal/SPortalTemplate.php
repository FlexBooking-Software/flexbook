<?php

class SPortalTemplate extends SqlSelect {
  private $_tPortalTemplate;
  private $_tPortalTemplatePageTemplate;
  private $_tFile;
  
  private $_sPageCountSelect;
  
  private function _insertPortalTemplateTable() {
    $this->_tPortalTemplate = new SqlTable('portaltemplate', 'pot');

    $this->addColumn(new SqlColumn($this->_tPortalTemplate, 'portaltemplate_id'));
    $this->addColumn(new SqlColumn($this->_tPortalTemplate, 'name'));
    $this->addColumn(new SqlColumn($this->_tPortalTemplate, 'css'));
    $this->addColumn(new SqlColumn($this->_tPortalTemplate, 'content'));
    $this->addColumn(new SqlColumn($this->_tPortalTemplate, 'preview'));
  }

  private function _insertFileTable() {
    $this->_tFile = new SqlTable('file', 'f');

    $this->addColumn(new SqlColumn($this->_tFile, 'file_id'));
    $this->addColumn(new SqlColumn($this->_tFile, 'hash', 'preview_hash'));
  }
  
  private function _insertPortalTemplatePageTemplateTable() {
    $this->_tPortalTemplatePageTemplate = new SqlTable('portaltemplate_pagetemplate', 'pot_pat');

    $this->addColumn(new SqlColumn($this->_tPortalTemplatePageTemplate, 'portaltemplate'));
    $this->addColumn(new SqlColumn($this->_tPortalTemplatePageTemplate, 'pagetemplate'));
  }
  
  private function _insertPageCountSelect() {
    $this->_sPageCountSelect = new SPortalTemplatePageTemplate;
    $this->_sPageCountSelect->addColumn(new SqlColumn($this->_tPortalTemplate, 'portaltemplate_id', 'portaltemplate_outer', false, false, true));
    $this->_sPageCountSelect->addColumn(new SqlColumn(false, new SqlStatementMono($this->_sPageCountSelect->columns['pagetemplate'], 'COUNT(%s)'), 'page_count', true));
    $this->_sPageCountSelect->addStatement(new SqlStatementBi($this->_sPageCountSelect->columns['portaltemplate'], $this->_sPageCountSelect->columns['portaltemplate_outer'], '%s=%s'));
    $this->_sPageCountSelect->setColumnsMask(array('page_count'));
    
    $this->addColumn(new SqlColumn(false, $this->_sPageCountSelect, 'page_count'));
  }
  
  protected function _initSqlSelect() {
    $this->_insertPortalTemplateTable();
    $this->_insertPortalTemplatePageTemplateTable();
    $this->_insertFileTable();
    
    $this->_insertPageCountSelect();
    
    $this->addJoin(new SqlJoin('LEFT', $this->_tPortalTemplatePageTemplate, new SqlStatementBi($this->columns['portaltemplate_id'], $this->columns['portaltemplate_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tFile, new SqlStatementBi($this->columns['preview'], $this->columns['file_id'], '%s=%s')));
  }
}

?>
