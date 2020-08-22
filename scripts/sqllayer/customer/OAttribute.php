<?php

class OAttribute extends SqlObject {
  protected $_table = 'attribute';
  
  protected function _preDelete($ret=true) {
    $app = Application::get();
    
    $data = $this->getData();
    
    $s = new SAttributeName;
    $s->addStatement(new SqlStatementBi($s->columns['attribute'], $data['attribute_id'], '%s=%s'));
    $s->setColumnsMask(array('attributename_id'));
    $res = $app->db->doQuery($s->toString());
    while ($row = $app->db->fetchAssoc($res)) {
      $o = new OAttributeName($row['attributename_id']);
      $o->delete();
    }
    
    return parent::_preDelete($ret);
  }
}

?>