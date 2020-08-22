<?php

class OEvent extends SqlObject {
  protected $_table = 'event';
  
  protected function _preDelete($ret=true) {
    $data = $this->getData();
    
    $app = Application::get();
    
    $s = new SEventAttendee;
    $s->addStatement(new SqlStatementBi($s->columns['event'], $data['event_id'], '%s=%s'));
    $s->setColumnsMask(array('eventattendee_id'));
    $res = $app->db->doQuery($s->toString());
    while ($row = $app->db->fetchAssoc($res)) {
      $o = new OEventAttendee($row['eventattendee_id']);
      $o->delete();
    }
    
    $s = new SEvent;
    $s->addStatement(new SqlStatementBi($s->columns['event_id'], $data['event_id'], '%s=%s'));
    $s->setColumnsMask(array('event','portal'));
    $res = $app->db->doQuery($s->toString());
    while ($row = $app->db->fetchAssoc($res)) {
      $o = new OEventPortal(array('event'=>$row['event'],'portal'=>$row['portal']));
      $o->delete();
    }
    
    $s = new SEventAttribute;
    $s->addStatement(new SqlStatementBi($s->columns['event'], $data['event_id'], '%s=%s'));
    $s->setColumnsMask(array('attribute'));
    $res = $app->db->doQuery($s->toString());
    while ($row = $app->db->fetchAssoc($res)) {
      $o = new OEventAttribute(array('event'=>$data['event_id'],'attribute'=>$row['attribute']));
      $o->delete();
    }
    
    $s = new SEventTag;
    $s->addStatement(new SqlStatementBi($s->columns['event'], $data['event_id'], '%s=%s'));
    $s->setColumnsMask(array('event','tag'));
    $res = $app->db->doQuery($s->toString());
    while ($row = $app->db->fetchAssoc($res)) {
      $o = new OEventTag(array('event'=>$row['event'],'tag'=>$row['tag']));
      $o->delete();
    }
    
    return parent::_preDelete($ret);
  }
}

?>