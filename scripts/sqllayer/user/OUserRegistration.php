<?php

class OUserRegistration extends SqlObject {
  protected $_table = 'userregistration';

  protected function _preDelete($ret=true) {
    $app = Application::get();

    $data = $this->getData();

    $s = new SCreditJournal;
    $s->addStatement(new SqlStatementBi($s->columns['userregistration'], $data['userregistration_id'], '%s=%s'));
    $s->setColumnsMask(array('creditjournal_id'));
    $res = $app->db->doQuery($s->toString());
    while ($row = $app->db->fetchAssoc($res)) {
      $o = new OCreditJournal($row['creditjournal_id']);
      $o->delete();
    }

    return parent::_preDelete($ret);
  }
}

?>