<?php

class ModuleTicketEdit extends ExecModule {

  protected function _userRun() {
    $validator = Validator::get('ticket','TicketValidator',true);
    if ($id = $this->_app->request->getParams('id')) $validator->loadData($id);

    return 'vTicketEdit';
  }
}

?>
