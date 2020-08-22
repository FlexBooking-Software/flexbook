<?php

class OPriceList extends SqlObject {
  protected $_table = 'pricelist';
  
  protected function _preDelete($ret=true) {
    $data = $this->getData();
    
    $app = Application::get();
    
    $s = new SSeason;
    $s->addStatement(new SqlStatementBi($s->columns['pricelist'], $data['pricelist_id'], '%s=%s'));
    $s->setColumnsMask(array('season_id'));
    $res = $app->db->doQuery($s->toString());
    while ($row = $app->db->fetchAssoc($res)) {
      $o = new OSeason($row['season_id']);
      $o->delete();
    }
    
    return parent::_preDelete($ret);
  }
}

?>