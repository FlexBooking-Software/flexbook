<?php

class ModuleReservationEdit extends ProjectModule {

  protected function _userInsert() {
    $this->insert(new GuiEditReservation);
  }
}

?>
