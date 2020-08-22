<?php

class ModuleProviderPortalPagePrepare extends ProjectModule {

  protected function _userInsert() {  
    $this->insert(new GuiCreateProviderPortalPage);
  }
}

?>
