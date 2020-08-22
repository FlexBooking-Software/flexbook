<?php

class ModuleResourcePoolEdit extends ProjectModule {

  protected function _userInsert() {
    $this->insert(new GuiEditResourcePool);
  }
}

?>
