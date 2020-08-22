<?php

class OProviderPortalPage extends SqlObject {
  protected $_table = 'providerportalpage';
  
  protected function _preDelete($ret=true) {
    $app = Application::get();
    
    $data = $this->getData();
    $s = new SProviderPortalMenu;
    $s->addStatement(new SqlStatementBi($s->columns['providerportalpage'], $data['providerportalpage_id'], '%s=%s'));
    $s->setColumnsMask(array('providerportalmenu_id'));
    $res = $app->db->doQuery($s->toString());
    while ($row = $app->db->fetchAssoc($res)) {
      $o = new OProviderPortalMenu($row['providerportalmenu_id']);
      $o->delete();
    }
    
    return parent::_preDelete($ret);
  }
}

?>