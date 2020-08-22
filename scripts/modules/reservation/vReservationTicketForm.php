<?php

class ModuleReservationTicketForm extends ProjectModule {

  protected function _userInsert() {
    $this->insert(new GuiReservationTicketForm);
  }
}

?>
