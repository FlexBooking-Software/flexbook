<?php

class ModuleComgateStatus extends ExecModule {

  private function _getProvider($payment) {
    // zkontroluju validitu stavu platby

    // nejdriv nactu parametry poskytovatelu pro platebni branu COMGATE
    $s = new SProviderPaymentGateway;
    $s->addStatement(new SqlStatementMono($s->columns['gateway_name'], "%s='comgate'"));
    $s->setColumnsMask(array('provider','gateway_params'));
    $res = $this->_app->db->doQuery($s->toString());
    $providerGw = array();
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $params = json_decode($row['gateway_params']);
      if (!isset($providerGw[$params->merchantId])) {
        $providerGw[$params->merchantId] = array('provider' => array($row['provider']), 'secret' => $params->secret);
      } else {
        $providerGw[$params->merchantId]['provider'][] = $row['provider'];
      }
    }

    if (!isset($providerGw[$payment['merchant']])) die('Unknown merchant.');
    elseif (strcmp($providerGw[$payment['merchant']]['secret'], $payment['secret'])) die('Invalid secret.');

    return $providerGw[$payment['merchant']]['provider'];
  }

  protected function _userRun() {
    global $PAYMENT_GATEWAY;

    $gw = new COMGATEGateway(array(
      'logFile' => $PAYMENT_GATEWAY['source']['comgate']['logFile'],
    ));
    $gw->log(sprintf('%s PUSH - payment status: %s', $_SERVER['REMOTE_ADDR'], var_export($this->_app->request->getParams(), true)));

    if (!in_array($_SERVER['REMOTE_ADDR'], $PAYMENT_GATEWAY['source']['comgate']['pushReferer'])) {
      $gw->log('Unathorized access.');
      die('code=0&message=OK');
    }

    // nactu data z requestu
    $payment = $this->_app->request->getParams(array('merchant','secret','transId','price','status','method'));
    $merchantProviders = $this->_getProvider($payment);

    // najdu online platbu
    $s = new SOnlinePayment;
    $s->addStatement(new SqlStatementMono($s->columns['end_timestamp'], '%s IS NULL'));
    $s->addStatement(new SqlStatementMono($s->columns['type'], "%s='comgate'"));
    $s->addStatement(new SqlStatementBi($s->columns['paymentid'], $payment['transId'], '%s=%s'));
    $s->setColumnsMask(array('onlinepayment_id','target','target_id','target_params','paymentid','ticket_provider'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($row = $this->_app->db->fetchAssoc($res)) {
      switch ($row['target']) {
        case 'RESERVATION':
          if ($row['target_id']) {
            if (!in_array(BReservation::getProviderFromOnlinePaymentTarget($row['target_id']), $merchantProviders)) {
              $gw->log('Invalid reservation provider.');
              die('code=0&message=OK');
            }

            if (!strcmp($payment['status'], 'PAID')) {
              // lze platit online vice rezervaci (pak jsou id-cka oddelene carkou)
              $ids = str_replace('|',',',substr($row['target_id'], 1, -1));
              $ids = explode(',', $ids);
              foreach ($ids as $id) {
                $bReservation = new BReservation($id);
                $bReservation->pay('comgate', array('comment' => sprintf('payId:%s,method:%s', $payment['transId'], $payment['method'])), true);
              }
            }
          }

          break;
        case 'TICKET':
          if ($row['ticket_provider']) {
            if (!in_array($row['ticket_provider'], $merchantProviders)) {
              $gw->log('Invalid ticket provider.');
              die('code=0&message=OK');
            }

            if (!strcmp($payment['status'], 'PAID')) {
              $bUser = new BUser;
              $bUser->saveTicketFromOnlinePayment($payment['transId']);
            }
          }

          break;
        case 'CREDIT':
          $row['target_params'] = json_decode($row['target_params']);
          if (!in_array($row['target_params']->provider, $merchantProviders)) {
            $gw->log('Invalid credit provider.');
            die('code=0&message=OK');
          }

          if (!strcmp($payment['status'],'PAID')) {
            $bUser = new BUser;
            $bUser->saveCreditFromOnlinePayment($payment['transId']);
          }

          break;
      }

      $bPayment = new BOnlinePayment;
      $bPayment->closeOnlinePayment($row['target'], $row['target_id'], $payment['transId'], !strcmp($payment['status'],'PAID')?'Y':'N',
        !strcmp($payment['status'],'PAID')?sprintf('method:%s', $payment['method']):null, $payment['status']);
    }

    die('code=0&message=OK');
  }
}

?>
