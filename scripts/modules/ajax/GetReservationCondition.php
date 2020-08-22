<?php

class AjaxGetReservationCondition extends AjaxAction {

  protected function _userRun() {  
    if (isset($this->_params['provider'])) {
      $s = new SReservationCondition;
      $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_params['provider'], '%s=%s'));
      $s->addOrder(new SqlStatementAsc($s->columns['name']));
      $s->setColumnsMask(array('reservationcondition_id','name'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $row['id'] = $row['reservationcondition_id'];
        $this->_result[] = $this->_request->convertOutput($row);
      }
    }
  }
}

?>