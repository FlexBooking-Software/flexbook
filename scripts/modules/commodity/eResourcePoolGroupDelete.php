<?php

class ModuleResourceGroupDelete extends ExecModule {

  protected function _userRun() {
    if ($id = $this->_app->request->getParams('id')) {
      $id = explode(',', $id);
      
      $this->_app->db->beginTransaction();
    
      try {
        $ret = '';
        foreach ($id as $i) {
          if ($ret) $ret .= ',';
          $bResource = new BResource($i);
          $ret .= $bResource->delete();
        }
      } catch (ExceptionUser $e) {
        $this->_app->db->shutdownTransaction();
        
        $bData = $bResource->getData();
        $this->_app->messages->addMessage('userError',
            sprintf($this->_app->textStorage->getText('error.deleteGroupResource'), $bData['name']).' '.$e->printMessage());
        
        $this->_app->response->addParams(array('id'=>$id));
        return 'vResource';    
      }
      
      $this->_app->db->commitTransaction();
    
      $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.listResource_deleteOk'), $ret));
    }

    $this->_app->response->addParams(array('backwards'=>1));
    return 'eBack';
  }
}

?>
