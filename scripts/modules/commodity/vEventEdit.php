<?php

class ModuleEventEdit extends ProjectModule {

  protected function _userInsert() {
    $this->insert(new GuiEditEvent);
  }
}

?>
