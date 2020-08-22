<?php

class ModuleInPageRegistration extends InPageModule {

  protected function _userInsert() {
    $this->insert(new GuiInPageRegistration);
  }
}

?>
