<?php

class ModuleProviderPortalPageEdit extends ProjectModule {

  protected function _userInsert() {
    $this->insert(new GuiEditProviderPortalPage);
  }
}

?>
