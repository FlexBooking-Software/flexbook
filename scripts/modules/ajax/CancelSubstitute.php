<?php

class AjaxCancelSubstitute extends AjaxAction {

  protected function _userRun() {
    if ($this->_app->session->getExpired()||!$this->_app->auth->getUserId()) throw new ExceptionUserTextStorage('error.ajax_sessionExpired');
    
    $eventAttendee = ifsetor($this->_params['id']);
    
    if ($eventAttendee) {
      $s = new SEventAttendee;
      $s->addStatement(new SqlStatementBi($s->columns['eventattendee_id'], $eventAttendee, '%s=%s'));
      $s->setColumnsMask(array('event'));
      $res = $this->_app->db->doQuery($s->toString());
      if ($row = $this->_app->db->fetchAssoc($res)) {
        $bEvent = new BEvent($row['event']);
        $name = $bEvent->deleteSubstitute(array('eventAttendeeId'=>$eventAttendee));
      }
    }
    
    $this->_result = array('error'=>false);
  }
}

?>
