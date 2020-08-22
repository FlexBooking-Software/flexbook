<?php

class ModuleOnlinePaymentFinish extends ExecModule {
  private $_providerGwSettings = array();

  private function _getOnlinePaymentStatus($gateway, $provider, $paymentId) {
    global $PAYMENT_GATEWAY;

    switch ($gateway) {
      case 'csob':
        $gw = new CSOBGateway(array(
          'logFile'     => $PAYMENT_GATEWAY['source']['csob']['logFile'],
          'cartLabel'   => $this->_app->textStorage->getText('label.ajax_paymentGateway_cartLabel'),
          'gatewayUrl'  => $PAYMENT_GATEWAY['source']['csob']['url'],
          'gatewayKey'  => $PAYMENT_GATEWAY['source']['csob']['key'],
          'merchantId'  => $this->_providerGwSettings[$provider]->merchantId,
          'merchantKey' => $this->_providerGwSettings[$provider]->keyFile,
        ));

        break;
      case 'comgate':
        $gw = new COMGATEGateway(array(
          'logFile'     => $PAYMENT_GATEWAY['source']['comgate']['logFile'],
          'gatewayUrl'  => $PAYMENT_GATEWAY['source']['comgate']['url'],
          'merchantId'  => $this->_providerGwSettings[$provider]->merchantId,
          'secret'      => $this->_providerGwSettings[$provider]->secret,
          'test'        => $this->_providerGwSettings[$provider]->test,
        ));

        break;
      case 'gpwebpay':
        $gw = new GPWebpayGateway(array(
          'logFile'             => $PAYMENT_GATEWAY['source']['gpwebpay']['logFile'],
          'gatewayUrlWS'        => $PAYMENT_GATEWAY['source']['gpwebpay']['urlWS'],
          'gatewayUrl'          => $PAYMENT_GATEWAY['source']['gpwebpay']['url'],
          'gatewayKey'          => $PAYMENT_GATEWAY['source']['gpwebpay']['key'],
          'merchantId'          => $this->_providerGwSettings[$provider]->merchantId,
          'merchantKey'         => $this->_providerGwSettings[$provider]->keyFile,
          'merchantKeyPassword' => $this->_providerGwSettings[$provider]->keyPassword,
        ));

        break;
      case 'deminimis':
        $gw = new Deminimis(array(
          'language'  => strtoupper($this->_app->language->getLanguage()),
          'logFile'   => $PAYMENT_GATEWAY['source']['deminimis']['logFile'],
          'apiUrl'    => $PAYMENT_GATEWAY['source']['deminimis']['apiUrl'],
          'apiKey'    => $PAYMENT_GATEWAY['source']['deminimis']['apiKey'],
        ));

        break;
    }
    $payment = $gw->getPaymentStatus($paymentId);

    switch ($gateway) {
      case 'csob':
        $payment['desc'] = null;
        $payment['statusText'] = $payment['resultMessage'];
        if ($payment['paymentStatus']) $payment['statusText'] .= sprintf(': %s', $this->_app->textStorage->getText('label.ajax_paymentGateway_csob_paymentStatus_' . $payment['paymentStatus']));
        if ($payment['payed']) $payment['desc'] = sprintf('authCode:%s', $payment['authCode']);

        break;
      case 'comgate':
        $payment['desc'] = null;
        $payment['statusText'] = $payment['paymentStatus'];
        if ($payment['payed']) $payment['desc'] = sprintf('method:%s', $payment['method']);

        break;
			case 'gpwebpay':
				$payment['desc'] = null;
				$payment['statusText'] = $payment['resultMessage'];

				break;
      case 'deminimis':
        $payment['desc'] = null;
        $payment['statusText'] = $payment['resultMessage'];

        break;
    }

    return $payment;
  }

  private function _finishOnlinePayments($gateway) {
    // nejdriv nactu parametry poskytovatelu pro platebni branu
    $s = new SProviderPaymentGateway;
    $s->addStatement(new SqlStatementBi($s->columns['gateway_name'], $gateway, '%s=%s'));
    $s->setColumnsMask(array('provider','gateway_params'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $this->_providerGwSettings[$row['provider']] = json_decode($row['gateway_params']);
    }

    echo sprintf("Getting not finished %s payments:\n", strtoupper($gateway));

    $s = new SOnlinePayment;
    $s->addStatement(new SqlStatementMono($s->columns['end_timestamp'], '%s IS NULL'));
    $s->addStatement(new SqlStatementBi($s->columns['type'], $gateway, '%s=%s'));
    $s->addStatement(new SqlStatementMono($s->columns['paymentid'], '%s IS NOT NULL'));
    $s->setColumnsMask(array('onlinepayment_id','target','target_id','target_params','paymentid','ticket_name','ticket_provider','user_fullname'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      if (!strcmp($row['target'],'RESERVATION')) $provider = BReservation::getProviderFromOnlinePaymentTarget($row['target_id']);
      elseif (!strcmp($row['target'],'TICKET')) $provider = $row['ticket_provider'];
      elseif (!strcmp($row['target'],'CREDIT')) {
        $params = json_decode($row['target_params']);
        $provider = $params->provider;
      } else {
        echo sprintf("Payment %s: invalid target %s -> skipping\n", $row['onlinepayment_id'], $row['target']);
        continue;
      }

      if ($provider) {
        $payment = $this->_getOnlinePaymentStatus($gateway, $provider, $row['paymentid']);

        switch ($row['target']) {
          case 'RESERVATION':
            echo sprintf("RESERVATION: %s: %s %s... ", $row['target_id'], $payment['statusText'], $payment['desc']);

            if ($payment['payed']) {
              // lze platit online vice rezervaci (pak jsou id-cka oddelene carkou)
              $ids = str_replace('|',',',substr($row['target_id'], 1, -1));
              $ids = explode(',', $ids);
              foreach ($ids as $id) {
                $bReservation = new BReservation($id);
                $bReservation->payOnline($gateway, array('comment' => sprintf('payId:%s%s', $payment['paymentId'], $payment['desc']?','.$payment['desc']:'')));
              }
            }

            break;
          case 'TICKET':
            echo sprintf("TICKET: %s (%s): %s %s... ", $row['ticket_name'], $row['target_id'], $payment['statusText'], $payment['desc']);

            if ($payment['payed']) {
              $bUser = new BUser;
              $bUser->saveTicketFromOnlinePayment($payment['paymentId']);
            }

            break;
          case 'CREDIT':
            echo sprintf("CREDIT: %s (%s): %s %s... ", $row['user_fullname'], $row['target_id'], $payment['statusText'], $payment['desc']);

            if ($payment['payed']) {
              $bUser = new BUser;
              $bUser->saveCreditFromOnlinePayment($payment['paymentId']);
            }

            break;
        }
      } else {
        // kdyz neni provider, tak uz rezervace/ticket neexistuje, jenom zavru online platbu jako nezaplacenou
        $payment = array(
          'paymentId'   => $row['paymentid'],
          'payed'       => 0,
          'desc'        => '',
          'statusText'  => 'nonexisting target',
          'notFinished' => false,
        );
        echo sprintf("TRANSACTION: %s (%s) ... ", $payment['paymentId'], $payment['statusText']);
      }

      if ($payment['notFinished']) {
        echo "in progress -> skipping\n";
      } else {
        $bPayment = new BOnlinePayment;
        $bPayment->closeOnlinePayment($row['target'], $row['target_id'], $payment['paymentId'], $payment['payed']?'Y':'N', $payment['desc'], $payment['statusText']);

        echo "closed\n";
      }
    }
  }

  private function _finishNotStartedPayments() {
    echo sprintf("Getting not started payments:\n");

    $s = new SOnlinePayment;
    $s->addStatement(new SqlStatementMono($s->columns['end_timestamp'], '%s IS NULL'));
    #$s->addStatement(new SqlStatementMono($s->columns['type'], '%s IS NULL'));
    $s->addStatement(new SqlStatementMono($s->columns['paymentid'], '%s IS NULL'));
    $s->setColumnsMask(array('onlinepayment_id','target','target_id','start_timestamp','ticket_name','user_fullname'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      if (!in_array($row['target'],array('RESERVATION','TICKET','CREDIT'))) {
        echo sprintf("Payment %s: invalid target %s -> skipping\n", $row['onlinepayment_id'], $row['target']);
      }

      switch ($row['target']) {
        case 'RESERVATION':
          echo sprintf("RESERVATION: %s: %s... ", $row['target_id'], $row['start_timestamp']);

          break;
        case 'TICKET':
          echo sprintf("TICKET: %s (%s): %s... ", $row['ticket_name'], $row['target_id'], $row['start_timestamp']);

          break;
        case 'CREDIT':
          echo sprintf("CREDIT: %s (%s): %s... ", $row['user_fullname'], $row['target_id'], $row['start_timestamp']);

          break;
      }

      // kdyz je neincializovana platba starsi 10min, tak ji zrusim
      if (date('Y-m-d H:i:s', time()-10*60)<=$row['start_timestamp']) {
        echo "in progress -> skipping\n";
      } else {
        $bPayment = new BOnlinePayment;
        $bPayment->closeOnlinePayment($row['target'], $row['target_id'], null, 'N', null, 'Not started');

        echo "closed\n";
      }
    }
  }

  protected function _userRun() {
    
    echo sprintf("--------- %s ----------\n", date('Y-m-d H:i:s'));
    
    try {
      $this->_finishOnlinePayments('csob');
      $this->_finishOnlinePayments('comgate');
      $this->_finishOnlinePayments('gpwebpay');
      $this->_finishOnlinePayments('deminimis');

      $this->_finishNotStartedPayments();
      
    } catch (Exception $e) {
      echo $e->getMessage()."\n";
      
      $this->_app->db->shutdownTransaction();
    }
    
    echo "Done.\n";
  }
}

?>
