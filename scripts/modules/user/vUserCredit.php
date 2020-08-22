<?php

class ModuleUserCredit extends ProjectModule {

  protected function _userInsert() {
    $this->insert(new GuiUserCredit);
  }
}

?>
