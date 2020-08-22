<?php

class AjaxGetAvailabilityExceptionProfile extends AjaxAction {

  protected function _userRun() {  
    if (isset($this->_params['provider'])) {
      $s = new SAvailabilityExceptionProfile;
      $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_params['provider'], '%s=%s'));
      $s->addOrder(new SqlStatementAsc($s->columns['name']));
      $s->setColumnsMask(array('availabilityexceptionprofile_id','name'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $row['id'] = $row['availabilityexceptionprofile_id'];
        $this->_result[] = $this->_request->convertOutput($row);
      }
    }
  }
}

?>