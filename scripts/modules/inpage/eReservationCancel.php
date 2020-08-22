<?php

class ModuleInPageReservationCancel extends ExecModule {

  protected function _userRun() {
    if ($id = $this->_app->request->getParams('id')) {
      $bReservation = new BReservation($id);
      $number = $bReservation->cancel();

      $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.listReservation_cancelOk'), $number));
    }

    $this->_app->response->addParams(array('backwards'=>1));
    return 'eBack';
  }
}

?>
