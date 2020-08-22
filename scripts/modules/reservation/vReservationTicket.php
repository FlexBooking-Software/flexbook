<?php

class ModuleReservationTicket extends DocumentModule {

  protected function _userInsert() {
    $this->_app->history->getBackwards(1);
    $this->_app->setDebug(false);
    
    //$this->addCssFile('style.css');
    
    $this->insert(new GuiReservationTicket);
  }
}

?>
