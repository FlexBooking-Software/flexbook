<?php

class ModuleReservationChooseCancel extends ExecModule {

  protected function _userRun() {
    $reservationId = $this->_app->request->getParams('id');
    
    $o = new OReservation($reservationId);
    $oData = $o->getData();
    $this->_app->dialog->set(array(
      'width'     => 500,
      'template'  => sprintf('
        <form method="post">
          <input type="hidden" name="sessid" value="{%%sessid%%}" />
          <input type="hidden" name="id" value="%s" />
          <input type="hidden" name="eventPackItem" value="%s" />
          <div class="message">%s</div>
          <br />
          <div class="button">
            <input type="submit" class="ui-button inputSubmit" name="action_eReservationPrepareCancel" value="{__button.editReservation_cancelAllEvents}" />
            <input type="submit" class="ui-button inputSubmit" name="action_eReservationCancelEventPackItem" value="{__button.editReservation_cancelOneEvent}" />
          </div>
        </form>', $reservationId, $this->_app->request->getParams('event'), sprintf($this->_app->textStorage->getText('label.editReservation_cancelChoose'), $oData['number'])
    )));
    
    $this->_app->response->addParams(array('backwards'=>1));
    return 'eBack';
  }
}

?>
