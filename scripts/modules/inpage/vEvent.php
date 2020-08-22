<?php

class ModuleInPageEvent extends InPageModule {

  protected function _userInsert() {
    $this->insert(new GuiInPageEvent);
  }
}

?>
