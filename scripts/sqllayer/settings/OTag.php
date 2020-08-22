<?php

class OTag extends SqlObject {
  protected $_table = 'tag';
  
  protected function _preDelete($ret=true) {
    $data = $this->getData();
    
    $app = Application::get();
    
    $s = new SResourceTag;
    $s->addStatement(new SqlStatementBi($s->columns['tag'], $data['tag_id'], '%s=%s'));
    $s->setColumnsMask(array('resource','tag'));
    $res = $app->db->doQuery($s->toString());
    while ($row = $app->db->fetchAssoc($res)) {
      $o = new OResourceTag(array('resource'=>$row['resource'],'tag'=>$row['tag']));
      $o->delete();
    }

		$s = new SResourcePoolTag;
		$s->addStatement(new SqlStatementBi($s->columns['tag'], $data['tag_id'], '%s=%s'));
		$s->setColumnsMask(array('resourcepool','tag'));
		$res = $app->db->doQuery($s->toString());
		while ($row = $app->db->fetchAssoc($res)) {
			$o = new OResourcePoolTag(array('resourcepool'=>$row['resourcepool'],'tag'=>$row['tag']));
			$o->delete();
		}
    
    $s = new SEventTag;
    $s->addStatement(new SqlStatementBi($s->columns['tag'], $data['tag_id'], '%s=%s'));
    $s->setColumnsMask(array('event','tag'));
    $res = $app->db->doQuery($s->toString());
    while ($row = $app->db->fetchAssoc($res)) {
      $o = new OEventTag(array('event'=>$row['event'],'tag'=>$row['tag']));
      $o->delete();
    }
    
    $s = new STagProvider;
    $s->addStatement(new SqlStatementBi($s->columns['tag'], $data['tag_id'], '%s=%s'));
    $s->setColumnsMask(array('provider','tag'));
    $res = $app->db->doQuery($s->toString());
    while ($row = $app->db->fetchAssoc($res)) {
      $o = new OTagProvider(array('provider'=>$row['provider'],'tag'=>$row['tag']));
      $o->delete();
    }
    
    $s = new STag;
    $s->addStatement(new SqlStatementBi($s->columns['tag_id'], $data['tag_id'], '%s=%s'));
    $s->setColumnsMask(array('tag_id','portal'));
    $res = $app->db->doQuery($s->toString());
    while ($row = $app->db->fetchAssoc($res)) {
      $o = new OTagPortal(array('tag'=>$row['tag_id'],'portal'=>$row['portal']));
      $o->delete();
    }
    
    return parent::_preDelete($ret);
  }
}

?>