<?php

class ModuleResourceGroupDisable extends ExecModule {

  protected function _userRun() {
    if ($id = $this->_app->request->getParams('id')) {
      $id = explode(',', $id);
      
      $this->_app->db->beginTransaction();
    
      try {
        $ret = '';
        foreach ($id as $i) {
          if ($ret) $ret .= ',';
          $bResource = new BResource($i);
          $ret .= $bResource->disable();
        }
      } catch (ExceptionUser $e) {
        $this->_app->db->shutdownTransaction();
        
        $bData = $bResource->getData();
        $this->_app->messages->addMessage('userError',
            sprintf($this->_app->textStorage->getText('error.disableGroupResource'), $bData['name']).' '.$e->printMessage());
        
        $this->_app->response->addParams(array('id'=>$id));
        return 'vResource';    
      }
      
      $this->_app->db->commitTransaction();
    
      $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.listResource_disableOk'), $ret));
    }

    return 'vResource';  
  }
}

?>
