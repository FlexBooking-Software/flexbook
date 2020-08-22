<?php

class AjaxGetCoworker extends AjaxAction {

  protected function _userRun() {  
    if (isset($this->_params['provider'])) {
      $s = new SUserRegistration;
      $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_params['provider'], '%s=%s'));
      $s->addStatement(new SqlStatementBi($s->columns['admin'], $s->columns['reception'], "((%s='Y') OR (%s='Y'))"));
      $s->addOrder(new SqlStatementAsc($s->columns['fullname']));
      $s->setColumnsMask(array('user','fullname'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $row['id'] = $row['user'];
        $row['name'] = $row['fullname'];
        $this->_result[] = $this->_request->convertOutput($row);
      }
    }
  }
}

?>