<?php

class ModuleMain extends ProjectModule {

  protected function _userInsert() {
    $this->_app->auth->setSection('');

    if ($this->_app->auth->isAdministrator()) {
      $this->insert(new GuiElement(array('template'=>'<b>Vitejte v aplikaci FLEXBOOK</b>')));
    } else {
      $this->insert(new GuiHome);
    }
  }
}

?>
