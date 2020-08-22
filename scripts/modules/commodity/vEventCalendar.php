<?php

class ModuleEventCalendar extends ProjectModule {

  protected function _userInsert() {
    $this->insert(new GuiEventCalendar(array('id'=>$this->_app->request->getParams('event_id'))));
  }
}

?>
