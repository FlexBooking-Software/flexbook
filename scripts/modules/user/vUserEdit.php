<?php

class ModuleUserEdit extends ProjectModule {

  protected function _userInsert() {
    $this->insert(new GuiEditUser);
  }
}

?>
