<?php

class ModuleEventGroupCreate extends ProjectModule {

  protected function _userInsert() {
    $this->insert(new GuiEditEventGroup);
  }
}

?>
