<?php

class ModuleProviderPortalEdit extends ProjectModule {

  protected function _userInsert() {
    $this->insert(new GuiEditProviderPortal);
  }
}

?>
