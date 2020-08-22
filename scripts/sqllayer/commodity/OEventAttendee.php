<?php

class OEventAttendee extends SqlObject {
  protected $_table = 'eventattendee';
 
  protected function _preDelete($ret=true) {
    $data = $this->getData();
    
    $app = Application::get();
    
    $s = new SEventAttendeePerson;
    $s->addStatement(new SqlStatementBi($s->columns['eventattendee'], $data['eventattendee_id'], '%s=%s'));
    $s->setColumnsMask(array('eventattendeeperson_id'));
    $res = $app->db->doQuery($s->toString());
    while ($row = $app->db->fetchAssoc($res)) {
      $o = new OEventAttendeePerson($row['eventattendeeperson_id']);
      $o->delete();
    }
    
    $s = new SEventAttendeeAttribute;
    $s->addStatement(new SqlStatementBi($s->columns['eventattendee'], $data['eventattendee_id'], '%s=%s'));
    $s->setColumnsMask(array('eventattendee','attribute'));
    $res = $app->db->doQuery($s->toString());
    while ($row = $app->db->fetchAssoc($res)) {
      $o = new OEventAttendeeAttribute(array('eventattendee'=>$row['eventattendee'],'attribute'=>$row['attribute']));
      $o->delete();
    }
    
    return parent::_preDelete($ret);
  }
}

?>