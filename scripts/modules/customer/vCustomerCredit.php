<?php

class ModuleCustomerCredit extends ProjectModule {

  protected function _userInsert() {
    $this->insert(new GuiCustomerCredit);
  }
}

?>
