<?php

class OEventAttendeeAttribute extends SqlObject {
  protected $_table = 'eventattendee_attribute';
  protected $_identity = false;

  protected function _postDelete($ret=true) {
    $data = $this->getData();

    $s = new SAttribute;
    $s->addStatement(new SqlStatementBi($s->columns['attribute_id'], $data['attribute'], '%s=%s'));
    $s->addStatement(new SqlStatementMono($s->columns['applicable'], "%s='RESERVATION'"));
    $s->addStatement(new SqlStatementMono($s->columns['type'], "%s='FILE'"));
    $s->setColumnsMask(array('type'));
    $res = $this->_db->doQuery($s->toString());
    if ($row = $this->_db->fetchAssoc($res)) {
      $file = new BFile($data['value']);
      $file->delete();
    }

    return parent::_postDelete($ret);
  }
}

?>