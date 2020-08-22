<?php

class ModuleVoucherEdit extends ExecModule {

  protected function _userRun() {
    $validator = Validator::get('voucher','VoucherValidator',true);
    if ($id = $this->_app->request->getParams('id')) $validator->loadData($id);

    return 'vVoucherEdit';
  }
}

?>
