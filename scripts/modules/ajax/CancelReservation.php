<?php

class AjaxCancelReservation extends AjaxAction {

  protected function _userRun() {
    if ($this->_app->session->getExpired()||!$this->_app->auth->getUserId()) throw new ExceptionUserTextStorage('error.ajax_sessionExpired');
    
    $reservation = ifsetor($this->_params['id']);
    
    if ($reservation) {
      $refund = isset($this->_params['refund'])&&($this->_params['refund']=='Y');
      $refundTo = 'credit';

      $cancelMessage = sprintf('zruseno uzivatelem %s', $this->_app->auth->getEmail());
      
      $b = new BReservation($reservation);
      if ($refund) {
      	// kdyz se vraci penize a je zakazany kredit vraci ze penize online, pokud byla rezervace zaplacena online
				// jinak se vraci penize vzdy na kredit
				$bData = $b->getData();

				$providerSettings = BCustomer::getProviderSettings($bData['providerId'],array('disableCredit'));
				if ($providerSettings['disableCredit']=='Y') {
					global $PAYMENT_GATEWAY;

					foreach ($bData['journal'] as $journalItem) {
						if (!strcmp($journalItem['action'],'PAY')) {
							$notePart = explode('|', $journalItem['note']);

							if (in_array($notePart[0], array_keys($PAYMENT_GATEWAY['source']))) {
								$refundTo = $notePart[0];
							}

							break;
						}
					}
				}

				$number = $b->cancelWithRefund($this->_app->auth->isUser()?false:true, $refundTo, $cancelMessage);
			} else {
				$number = $b->cancel($this->_app->auth->isUser()?false:true, $cancelMessage);
			}
    }
    
    $this->_result = array('error'=>false);
  }
}

?>