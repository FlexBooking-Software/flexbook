<?php

class ModuleResourcePoolGroupDisable extends ExecModule {

  protected function _userRun() {
    if ($id = $this->_app->request->getParams('id')) {
      $id = explode(',', $id);
      
      $this->_app->db->beginTransaction();
    
      try {
        $ret = '';
        foreach ($id as $i) {
          if ($ret) $ret .= ',';
          $bResourcePool = new BResourcePool($i);
          $ret .= $bResourcePool->disable();
        }
      } catch (ExceptionUser $e) {
        $this->_app->db->shutdownTransaction();
        
        $bData = $bResourcePool->getData();
        $this->_app->messages->addMessage('userError',
            sprintf($this->_app->textStorage->getText('error.disableGroupResourcePool'), $bData['name']).' '.$e->printMessage());
        
        $this->_app->response->addParams(array('id'=>$id));
        return 'vResourcePool';    
      }
      
      $this->_app->db->commitTransaction();
    
      $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.listResourcePool_disableOk'), $ret));
    }

    return 'vResource';  
  }
}

?>
