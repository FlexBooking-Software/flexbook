<?php

class ModulePasswordEdit extends ProjectModule {

  protected function _userInsert() {
    $this->_title = $this->_app->textStorage->getText('label.changePassword_title');
    $this->insert(new GuiChangePassword);
  }
}

?>
