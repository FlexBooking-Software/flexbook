<?php

class ModulePriceListEdit extends ExecModule {

  protected function _userRun() {
    $validator = Validator::get('priceList','PriceListValidator',true);
    if ($id = $this->_app->request->getParams('id')) $validator->loadData($id);

    return 'vPriceListEdit';
  }
}

?>
