<?php

class AjaxGetCenter extends AjaxAction {

  protected function _userRun() {  
    if (isset($this->_params['provider'])) {
      $s = new SCenter;
      $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_params['provider'], '%s=%s'));
      $s->addOrder(new SqlStatementAsc($s->columns['name']));
      $s->setColumnsMask(array('center_id','description'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $row['id'] = $row['center_id'];
        $row['name'] = $row['description'];
        $this->_result[] = $this->_request->convertOutput($row);
      }
    }
  }
}

?>