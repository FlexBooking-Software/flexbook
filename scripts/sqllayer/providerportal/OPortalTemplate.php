<?php

class OPortalTemplate extends SqlObject {
  protected $_table = 'portaltemplate';
  
  protected function _preDelete($ret=true) {
    $app = Application::get();
    
    $data = $this->getData();
    
    $s = new SPortalTemplatePageTemplate;
    $s->addStatement(new SqlStatementBi($s->columns['portaltemplate'], $data['portaltemplate_id'], '%s=%s'));
    $s->setColumnsMask(array('pagetemplate'));
    $res = $app->db->doQuery($s->toString());
    while ($row = $app->db->fetchAssoc($res)) {
      $o = new OPortalTemplatePageTemplate(array('portaltemplate'=>$data['portaltemplate_id'],'pagetemplate'=>$row['pagetemplate']));
      $o->delete();
    }
    
    return parent::_preDelete($ret);
  }
}

?>