<?php

class ModuleProviderPortalPrepare extends ProjectModule {

  protected function _userInsert() {  
    $this->insert(new GuiCreateProviderPortal);
  }
}

?>
