<?php

class ModuleEventBadge extends DocumentModule {

  protected function _userInsert() {
    $this->_app->history->getBackwards(1);
    $this->_app->setDebug(false);
    
    $this->insert(new GuiBadge(array('for'=>'event','id'=>$this->_app->request->getParams('id'),
                                     'provider'=>$this->_app->request->getParams('provider'))));
  }
}

?>
