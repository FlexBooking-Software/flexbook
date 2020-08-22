<?php

class OResourcePool extends SqlObject {
  protected $_table = 'resourcepool';
  
  protected function _preDelete($ret=true) {
    $data = $this->getData();
    
    $app = Application::get();
    
    $s = new SResourcePoolItem;
    $s->addStatement(new SqlStatementBi($s->columns['resourcepool'], $data['resourcepool_id'], '%s=%s'));
    $s->setColumnsMask(array('resourcepool','resource'));
    $res = $app->db->doQuery($s->toString());
    while ($row = $app->db->fetchAssoc($res)) {
      $o = new OResourcePoolItem(array('resourcepool'=>$row['resourcepool'],'resource'=>$row['resource']));
      $o->delete();
    }

		$s = new SResourcePoolTag;
		$s->addStatement(new SqlStatementBi($s->columns['resourcepool'], $data['resourcepool_id'], '%s=%s'));
		$s->setColumnsMask(array('resourcepool','tag'));
		$res = $app->db->doQuery($s->toString());
		while ($row = $app->db->fetchAssoc($res)) {
			$o = new OResourcePoolTag(array('resourcepool'=>$row['resourcepool'],'tag'=>$row['tag']));
			$o->delete();
		}
    
    return parent::_preDelete($ret);
  }
}

?>