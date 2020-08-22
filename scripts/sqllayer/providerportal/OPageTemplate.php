<?php

class OPageTemplate extends SqlObject {
  protected $_table = 'pagetemplate';
  
  protected function _preDelete($ret=true) {
    $app = Application::get();
    
    $data = $this->getData();
    
    $s = new SPortalTemplate;
    $s->addStatement(new SqlStatementBi($s->columns['pagetemplate'], $data['pagetemplate_id'], '%s=%s'));
    $s->setColumnsMask(array('portaltemplate_id','name'));
    $res = $app->db->doQuery($s->toString());
    if ($row = $app->db->fetchAssoc($res)) throw new ExceptionUser(sprintf($app->textStorage->getText('error.deletePageTemplate_portalTemplateExists'), $row['name']));
    
    return parent::_preDelete($ret);
  }
}

?>