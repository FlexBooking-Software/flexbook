<?php

class ModuleLogin extends ProjectModule {

  protected function _userInsert() {
    $this->insert(new GuiLogin);

    // vLogin nebude v historii, kdyz je vyprsela session
    if ($this->_app->session->getExpired()) $this->_app->history->getBackwards(1);
  }
}

?>
