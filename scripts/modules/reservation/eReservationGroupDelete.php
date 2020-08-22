<?php

class ModuleReservationGroupDelete extends ExecModule {

  protected function _userRun() {
    if ($id = $this->_app->request->getParams('id')) {
      $id = explode(',', $id);
      
      $this->_app->db->beginTransaction();
    
      try {
        $ret = '';
        foreach ($id as $i) {
          if ($ret) $ret .= ',';
          $bReservation = new BReservation($i);
          $ret .= $bReservation->delete();
        }
      } catch (ExceptionUser $e) {
        $this->_app->db->shutdownTransaction();
        
        $bData = $bReservation->getData();
        $this->_app->messages->addMessage('userError',
            sprintf($this->_app->textStorage->getText('error.deleteGroupReservation'), $bData['number']).' '.$e->printMessage());
        
        $this->_app->response->addParams(array('id'=>$id));
        return 'vReservation';    
      }
      
      $this->_app->db->commitTransaction();
    
      $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.listReservation_deleteOk'), $ret));
    }

    $this->_app->response->addParams(array('backwards'=>1));
    return 'eBack';
  }
}

?>
