<?php

class ModuleReservationCancelRefund extends ExecModule {

  protected function _userRun() {
    if ($id = $this->_app->request->getParams('id')) {
      $validator = Validator::get('reservation','ReservationValidator');
      $validator->initValues();
      $vData = $validator->getValues();
      
      $refundTo = $this->_app->request->getParams('refundTo');

      $bReservation = new BReservation($id);
      $bReservationData = $bReservation->getData();
      
      if (!$vData['confirmCancel']) {
        $this->_app->dialog->set(array(
                  'width'     => 500,
                  'template'  => sprintf('
                      <div class="message">{__label.editReservation_cancelNote}:</div>
                      <br/>
                      <input type="hidden" name="confirmCancel" value="Y"/>
                      <input type="hidden" name="refundTo" value="%s" />
                      <input type="text" name="cancelNote" id="fi_reservationCancelNote"/>
                      <div class="button">
                        <input type="button" class="ui-button inputSubmit" name="save" value="{__button.listReservation_cancel}" onclick="document.getElementById(\'%s\').click();"/>
                      </div>', $refundTo, $bReservationData['payed']?'fb_eReservationCancelRefundNoConfirm':'fb_eReservationCancelNoConfirm'),
                ));
        
        $this->_app->response->addParams(array('backwards'=>1));
        return 'eBack';
      }
      
      // kdyz je rezeravace zaplacena, musi byt vybrano, jestli se ma vracet platba nebo ne
      if ($bReservationData['payed']&&(!$refundTo||!strcmp($refundTo,'refundFromEdit'))) {
        $dialogMessage = $dialogButton = '';
        
        // kdyz se rusi rezeravce z editace rezervace, tak se nebude znovu potvrzovat, ze je zaplacena
        // dialog se bude zobrazovat pouze kdyz bylo placenou platebni kartou a je tedy moznost vracet penize na platebni kartu nebo na kredit
        if (strcmp($refundTo,'refundFromEdit')) {
          // toto je potreba pro zruseni rezervace ze seznamu rezervaci, kde je jedno tlacitko pro zruseni i pro refund
          $dialogMessage = sprintf('<div class="message">{__label.editReservation_payedInfo}</div>');
          $dialogButton = '<input type="button" class="ui-button inputSubmit" name="save" value="{__button.listReservation_cancelNoRefund}" onclick="document.getElementById(\'fb_eReservationCancelNoRefundNoConfirm\').click();"/>';
        }

        $onlinePayment = null;
        foreach ($bReservationData['journal'] as $journalItem) {
          if (!strcmp($journalItem['action'],'PAY')) {
            $notePart = explode('|', $journalItem['note']);
            
            global $PAYMENT_GATEWAY;
            if (in_array($notePart[0], array_keys($PAYMENT_GATEWAY['source']))) {
              $onlinePayment = $notePart[0];

              // kdyz bylo placeno pres platebni branu, musi se vybrat, kam vratit penize
              $dialogMessage .= sprintf('<div class="message">{__label.editReservation_payInfo}: <b>%s</b></div>
                                   <div class="message">{__label.editReservation_refundTo}:</div>
                                   <div class="message"><select name="refundTo">
                                     <option value="credit">{__label.editReservation_refundTo_CREDIT}</option>
                                     <option value="%s">{__label.editReservation_refundTo_GW} %s</option>
                                   </select></div>', $this->_app->textStorage->getText('label.ajax_paymentGateway_'.$onlinePayment), $onlinePayment,
                                   $this->_app->textStorage->getText('label.ajax_paymentGateway_'.$onlinePayment));
              $dialogButton .= '&nbsp;<input type="button" class="ui-button inputSubmit" name="save" value="{__button.listReservation_cancelRefund}" onclick="document.getElementById(\'fb_eReservationCancelRefundNoConfirm\').click();"/>';
            } else {
              // kdyz nebylo placeno pres platebni branu, pridam pouze tlacitko na vraceni na credit
              $dialogButton .= '&nbsp;<input type="button" class="ui-button inputSubmit" name="save" value="{__button.listReservation_cancelRefund}" onclick="document.getElementById(\'fb_eReservationCancelRefundCreditNoConfirm\').click();"/>';
            }
            
            break;
          }
        }
        // kdyz se rusi rezervace z editace rezervace a nebyla placena plat. branou, vracim na kredit
        if (!strcmp($refundTo,'refundFromEdit')&&!$dialogMessage) {
          $refundTo = 'credit';
        }

        if ($dialogMessage) {
          // kdyz bylo zaplaceno online a je zakazanej kredit, budu rovnou vracet online
          $forceOnline = false;
          if ($onlinePayment) {
            $providerSettings = BCustomer::getProviderSettings($this->_app->auth->getActualProvider(),array('disableCredit','disableTicket'));

            if (($vData['payedTicket']&&($providerSettings['disableTicket']=='Y'))||
                (!$vData['payedTicket']&&($providerSettings['disableCredit']=='Y'))) {
              $forceOnline = true;
              $refundTo = $onlinePayment;
            }
          }

          if (!$forceOnline) {
            // vratim confirm dialog
            $this->_app->dialog->set(array(
              'width' => 480,
              'template' => sprintf('%s<div class="button">%s</div>', $dialogMessage, $dialogButton),
            ));

            $this->_app->response->addParams(array('backwards' => 1));
            return 'eBack';
          }
        }
      }
      if (!$refundTo) throw new ExceptionUser('error.cancelReservation_unknownRefundType');

      #adump($refundTo);die;
      if (!strcmp($refundTo,'none')) {
        $number = $bReservation->cancel(true, $vData['cancelNote']);
        $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.listReservation_cancelOk'), $number));
      } else {
        $number = $bReservation->cancelWithRefund(true, $refundTo, $vData['cancelNote']);
        $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.listReservation_cancelRefundOk'), $number));
      }
    }

    if (!$this->_app->request->getParams('editReservation')) $this->_app->response->addParams(array('backwards'=>1));
    return 'eBack';
  }
}

?>
