<?php

class ModuleEventCycleItems extends ExecModule {

  protected function _userRun() {
    $this->_app->session->set('updateCookieTab','ui-eventlist-tab=>0');

    $this->_app->response->addParams(array('gridname'=>'listEvent','gridclass'=>'GridSettingsEvent','filter[repeatParent]'=>$this->_app->request->getParams('id'),'filter[active]'=>''));
    return 'eGrid';
  }
}

?>
