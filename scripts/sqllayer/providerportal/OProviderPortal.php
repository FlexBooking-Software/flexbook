<?php

class OProviderPortal extends SqlObject {
  protected $_table = 'providerportal';
  
  protected function _preDelete($ret=true) {
    $app = Application::get();
    
    $this->setData(array('home_page'=>null));
    $this->save();
    
    $data = $this->getData();
    
    $s = new SProviderPortalMenu;
    $s->addStatement(new SqlStatementBi($s->columns['providerportal'], $data['providerportal_id'], '%s=%s'));
    $s->setColumnsMask(array('providerportalmenu_id'));
    $res = $app->db->doQuery($s->toString());
    while ($row = $app->db->fetchAssoc($res)) {
      $o = new OProviderPortalMenu($row['providerportalmenu_id']);
      $o->delete();
    }
    
    $s = new SProviderPortalPage;
    $s->addStatement(new SqlStatementBi($s->columns['providerportal'], $data['providerportal_id'], '%s=%s'));
    $s->setColumnsMask(array('providerportalpage_id'));
    $res = $app->db->doQuery($s->toString());
    while ($row = $app->db->fetchAssoc($res)) {
      $o = new OProviderPortalPage($row['providerportalpage_id']);
      $o->delete();
    }
    
    $s = new SProviderPortalFile;
    $s->addStatement(new SqlStatementBi($s->columns['providerportal'], $data['providerportal_id'], '%s=%s'));
    $s->setColumnsMask(array('providerportalfile_id'));
    $res = $app->db->doQuery($s->toString());
    while ($row = $app->db->fetchAssoc($res)) {
      $o = new OProviderPortalFile($row['providerportalfile_id']);
      $o->delete();
    }
    
    return parent::_preDelete($ret);
  }
}

?>