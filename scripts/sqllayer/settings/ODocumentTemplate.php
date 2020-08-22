<?php

class ODocumentTemplate extends SqlObject {
  protected $_table = 'documenttemplate';
  
  protected function _preDelete($ret=true) {
    $data = $this->getData();
    
    $s = new SDocumentTemplateItem;
    $s->addStatement(new SqlStatementBi($s->columns['documenttemplate'], $data['documenttemplate_id'], '%s=%s'));
    $s->setColumnsMask(array('documenttemplateitem_id'));
    $ds = new SqlDataSource(new DataSourceSettings, $s);
    $ds->reset();
    while ($ds->currentData) {
      $o = new ODocumentTemplateItem($ds->currentData['documenttemplateitem_id']);
      $o->delete();
      $ds->nextData();
    }
    
    return parent::_preDelete($ret);
  }
}

?>
