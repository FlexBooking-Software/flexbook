<?php

class AjaxGetEventCalendar extends AjaxAction {

  protected function _userRun() {  
    $start = @date('Y-m-d H:i:s', $this->_params['start']);
    $end = @date('Y-m-d H:i:s', $this->_params['end']);
    $center = $this->_params['center'];
    
    $s = new SEvent;
    $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
    $s->addStatement(new SqlStatementBi($s->columns['center'], $center, '%s=%s'));
    $s->addStatement(new SqlStatementQuad($start, $s->columns['start'],
                                          $s->columns['end'], $end,
                                          '%s<=%s AND %s<=%s'));
    $s->setColumnsMask(array('event_id','external_id','name','start','end'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $row['allDay'] = false;
      $row['id'] = $row['event_id'];
      $row['assetId'] = $row['external_id'];
      $row['title'] = $row['name'];
      $row['start'] = $row['start'];
      $row['end'] = $row['end'];
      $this->_result[] = $this->_request->convertOutput($row);
    }
  }
}

?>
