<?php

class ModuleEventEdit extends ExecModule {

  private function _findFirstCycleActiveEvent($cycle) {
    $ret = $cycle;

    $s = new SEvent;
    $s->addStatement(new SqlStatementBi($s->columns['repeat_parent'], $cycle, '%s=%s'));
    $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
    $s->addOrder(new SqlStatementAsc($s->columns['start']));
    $s->setColumnsMask(array('event_id'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($row = $this->_app->db->fetchAssoc($res)) $ret = $row['event_id'];

    return $ret;
  }

  protected function _userRun() {
    $id = $this->_app->request->getParams('id');
    $single = $this->_app->request->getParams('single');
    if ($cycle = $this->_app->request->getParams('repeat')) {
      $id = $this->_findFirstCycleActiveEvent($id);
    }

    $validator = Validator::get('event','EventValidator',true);
    if ($id) {
      $validator->loadData($id, $single);
    } else {
      $data = array('active'=>'Y','badge'=>'N','reservationMaxAttendees'=>1,'maxCoAttendees'=>1,'feAttendeePublic'=>'N','feQuickReservation'=>'N',
        'singleEventEdit'=>$single,'feAllowedPayment'=>array('credit','ticket','online'));
      
      if (!$this->_app->auth->isAdministrator()) {
        $data['providerId'] = $this->_app->auth->getActualProvider();
        $data['centerId'] = $this->_app->auth->getActualCenter();
      }
      
      $validator->setValues($data);
    }

    return 'vEventEdit';
  }
}

?>
