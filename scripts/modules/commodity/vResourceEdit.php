<?php

class ModuleResourceEdit extends ProjectModule {

  protected function _userInsert() {
    $this->insert(new GuiEditResource);
  }
}

?>
