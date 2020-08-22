<?php

class ModuleUserSubaccountEdit extends ProjectModule {

  protected function _userInsert() {
    $this->insert(new GuiEditUser);
  }
}

?>
