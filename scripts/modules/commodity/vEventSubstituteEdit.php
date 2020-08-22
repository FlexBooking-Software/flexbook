<?php

class ModuleEventSubstituteEdit extends ProjectModule {

  protected function _userInsert() {
    $this->insert(new GuiEditEventSubstitute);
  }
}

?>
