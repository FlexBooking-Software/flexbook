<?php

class ModuleCustomerEdit extends ProjectModule {

  protected function _userInsert() {
    $this->insert(new GuiEditCustomer);
  }
}

?>
