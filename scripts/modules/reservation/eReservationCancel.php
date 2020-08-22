<?php

class ModuleReservationCancel extends ExecModule {

  protected function _userRun() {
    if ($id = $this->_app->request->getParams('id')) {
      $reversePayment = false;

      $validator = Validator::get('reservation','ReservationValidator');
      $validator->initValues();
      $vData = $validator->getValues();

      // zjistim, jestli probiha online platba na rezervaci
      if ($vData['openOnlinepayment']&&!$vData['confirmOnlinepayment']) {
        $s = new SOnlinePayment;
        $s->addStatement(new SqlStatementBi($s->columns['onlinepayment_id'], $vData['openOnlinepayment'], '%s=%s'));
        $s->setColumnsMask(array('type','start_timestamp','end_timestamp'));
        $res = $this->_app->db->doQuery($s->toString());
        $row = $this->_app->db->fetchAssoc($res);
        // kdyz je open payment deminimis, je potreba ji zrusit
        if (!$row['end_timestamp']&&!strcmp($row['type'],'deminimis')) {
          $reversePayment = true;
        } else {
          $desc = sprintf('%s %s', $row['type'], $this->_app->regionalSettings->convertDateTimeToHuman($row['start_timestamp']));
          $message = sprintf($this->_app->textStorage->getText('label.editReservation_cancelWithOnlinePayment'), $desc);
          $this->_app->dialog->set(array(
            'width' => 500,
            'template' => sprintf('
                        <div class="message">%s</div>
                        <br/>
                        <input type="hidden" name="confirmOnlinepayment" value="Y"/>
                        <div class="button">
                          <input type="button" class="ui-button inputSubmit" name="save" value="{__button.grid_cancel}" onclick="document.getElementById(\'fb_eReservationCancelNoConfirm\').click();"/>
                        </div>', $message),
          ));

          $this->_app->response->addParams(array('backwards' => 1));
          return 'eBack';
        }
      }

      if (!$vData['confirmCancel']) {
        $this->_app->dialog->set(array(
                  'width'     => 500,
                  'template'  => sprintf('
                      <div class="message">{__label.editReservation_cancelNote}:</div>
                      <br/>
                      <input type="hidden" name="confirmCancel" value="Y"/>
                      <input type="text" name="cancelNote" id="fi_reservationCancelNote"/>
                      <div class="button">
                        <input type="button" class="ui-button inputSubmit" name="save" value="{__button.grid_cancel}" onclick="document.getElementById(\'fb_eReservationCancelNoConfirm\').click();"/>
                      </div>'),
                ));
        
        $this->_app->response->addParams(array('backwards'=>1));
        return 'eBack';
      }

      #adump('cancel');die;
      $bReservation = new BReservation($id);
      $number = $bReservation->cancel(true, $vData['cancelNote'], $reversePayment);
    
      $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.listReservation_cancelOk'), $number));
    }

    if (!$this->_app->request->getParams('editReservation')) $this->_app->response->addParams(array('backwards'=>1));
    return 'eBack';
  }
}

?>
