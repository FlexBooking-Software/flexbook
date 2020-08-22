<?php

class ModuleEventGroupDelete extends ExecModule {

  protected function _userRun() {
    $id = $this->_app->request->getParams('id');
    $deleteCycle = $this->_app->request->getParams('repeat');

    if ($id) {
      $id = explode(',', $id);
      
      $this->_app->db->beginTransaction();
    
      try {
        $ret = '';
        foreach ($id as $i) {
          if ($ret) $ret .= ',';
          $bEvent = new BEvent($i);
          $ret .= $bEvent->delete($deleteCycle);
        }
      } catch (ExceptionUser $e) {
        $this->_app->db->shutdownTransaction();
        
        $bData = $bEvent->getData();
        $this->_app->messages->addMessage('userError',
            sprintf($this->_app->textStorage->getText('error.deleteGroupEvent'), $bData['name']).' '.$e->printMessage());
        
        $this->_app->response->addParams(array('id'=>$id));
        return 'vEvent';    
      }
      
      $this->_app->db->commitTransaction();
    
      $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.listEvent_deleteOk'), $ret));
    }

    $this->_app->response->addParams(array('backwards'=>1));
    return 'eBack';
  }
}

?>
