<?php

class ModuleInPageResource extends InPageModule {

  protected function _userInsert() {
    $this->insert(new GuiInPageResource);
  }
}

?>
