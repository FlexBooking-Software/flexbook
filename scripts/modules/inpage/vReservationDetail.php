<?php

class ModuleInPageReservationDetail extends InPageModule {

  protected function _userInsert() {
    $this->insert(new GuiInPageReservation);
  }
}

?>
