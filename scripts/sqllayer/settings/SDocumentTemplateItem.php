<?php

class SDocumentTemplateItem extends MySqlSelect {
  private $_tDocumentTemplateItem;
  private $_tDocumentTemplate;
  
  private function _insertDocumentTemplateItemTable() {
    $this->_tDocumentTemplateItem = new SqlTable('documenttemplateitem', 'dti');

    $this->addColumn(new SqlColumn($this->_tDocumentTemplateItem, 'documenttemplateitem_id'));
    $this->addColumn(new SqlColumn($this->_tDocumentTemplateItem, 'documenttemplate'));
    $this->addColumn(new SqlColumn($this->_tDocumentTemplateItem, 'name'));
    $this->addColumn(new SqlColumn($this->_tDocumentTemplateItem, 'code'));
    $this->addColumn(new SqlColumn($this->_tDocumentTemplateItem, 'type'));
    $this->addColumn(new SqlColumn($this->_tDocumentTemplateItem, 'number'));
    $this->addColumn(new SqlColumn($this->_tDocumentTemplateItem, 'counter'));
    $this->addColumn(new SqlColumn($this->_tDocumentTemplateItem, 'content'));
  }

  private function _insertDocumentTemplateTable() {
    $this->_tDocumentTemplate = new SqlTable('documenttemplate', 'nt');

    $this->addColumn(new SqlColumn($this->_tDocumentTemplate, 'documenttemplate_id'));
    $this->addColumn(new SqlColumn($this->_tDocumentTemplate, 'provider'));
    $this->addColumn(new SqlColumn($this->_tDocumentTemplate, 'target'));
  }
  
  protected function _initSqlSelect() {
    $this->_insertDocumentTemplateItemTable();
    $this->_insertDocumentTemplateTable();

    $this->addJoin(new SqlJoin(false, $this->_tDocumentTemplate, new SqlStatementBi($this->columns['documenttemplate'], $this->columns['documenttemplate_id'], '%s=%s')));
  }
}

?>
