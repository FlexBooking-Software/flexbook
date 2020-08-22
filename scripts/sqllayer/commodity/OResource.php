<?php

class OResource extends SqlObject {
  protected $_table = 'resource';
  
  protected function _preDelete($ret=true) {
    $data = $this->getData();
    
    $app = Application::get();
    
    $s = new SResourceTag;
    $s->addStatement(new SqlStatementBi($s->columns['resource'], $data['resource_id'], '%s=%s'));
    $s->setColumnsMask(array('resource','tag'));
    $res = $app->db->doQuery($s->toString());
    while ($row = $app->db->fetchAssoc($res)) {
      $o = new OResourceTag(array('resource'=>$row['resource'],'tag'=>$row['tag']));
      $o->delete();
    }
    
    $s = new SResource;
    $s->addStatement(new SqlStatementBi($s->columns['resource_id'], $data['resource_id'], '%s=%s'));
    $s->setColumnsMask(array('resource','portal'));
    $res = $app->db->doQuery($s->toString());
    while ($row = $app->db->fetchAssoc($res)) {
      $o = new OResourcePortal(array('resource'=>$row['resource'],'portal'=>$row['portal']));
      $o->delete();
    }
    
    $s = new SResourceAvailability;
    $s->addStatement(new SqlStatementBi($s->columns['resource'], $data['resource_id'], '%s=%s'));
    $s->setColumnsMask(array('resourceavailability_id'));
    $res = $app->db->doQuery($s->toString());
    while ($row = $app->db->fetchAssoc($res)) {
      $o = new OResourceAvailability($row['resourceavailability_id']);
      $o->delete();
    }
    
    $s = new SResourceAttribute;
    $s->addStatement(new SqlStatementBi($s->columns['resource'], $data['resource_id'], '%s=%s'));
    $s->setColumnsMask(array('attribute'));
    $res = $app->db->doQuery($s->toString());
    while ($row = $app->db->fetchAssoc($res)) {
      $o = new OResourceAttribute(array('resource'=>$data['resource_id'],'attribute'=>$row['attribute']));
      $o->delete();
    }
    
    return parent::_preDelete($ret);
  }
}

?>