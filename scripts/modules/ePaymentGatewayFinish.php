<?php

class ModulePaymentGatewayFinish extends ExecModule {
  
  private function _finishPayment($gateway, $providerId, $target, $targetId) {
    $s = new SProviderPaymentGateway;
    $s->addStatement(new SqlStatementBi($s->columns['provider'], $providerId, '%s=%s'));
    $s->addStatement(new SqlStatementBi($s->columns['gateway_name'], $gateway, '%s=%s'));
    $s->setColumnsMask(array('gateway_params'));
    $res = $this->_app->db->doQuery($s->toString());
    if (!$rowGw = $this->_app->db->fetchAssoc($res)) throw new ExceptionUser('Invalid payment gateway!');
    $gatewayParams = json_decode($rowGw['gateway_params']);

    global $PAYMENT_GATEWAY;
    $paid = false;

    switch ($gateway) {
      case 'csob':
        $gw = new CSOBGateway(array(
          'logFile'         => $PAYMENT_GATEWAY['source']['csob']['logFile'],
          'cartLabel'       => $this->_app->textStorage->getText('label.ajax_paymentGateway_cartLabel'),
          'gatewayUrl'      => $PAYMENT_GATEWAY['source']['csob']['url'],
          'gatewayKey'      => $PAYMENT_GATEWAY['source']['csob']['key'],
          'merchantId'      => $gatewayParams->merchantId,
          'merchantKey'     => $gatewayParams->keyFile,
        ));
        
        $response = $this->_app->request->getParams(array('resultCode','resultMessage','payId','dttm','paymentStatus','authCode','merchantData','signature'));
        $payment = $gw->closePayment($response);

        $status = $response['resultMessage'];
        if ($response['paymentStatus']) $status .= sprintf(': %s', $this->_app->textStorage->getText('label.ajax_paymentGateway_csob_paymentStatus_'.$response['paymentStatus']));

        if ($payment['payed']) {
          $paid = true;
          $payComment = sprintf('payId:%s,authCode:%s', $payment['paymentId'], $payment['authCode']);
          $paymentId = $payment['paymentId'];
        }

        $bPayment = new BOnlinePayment;
        $bPayment->closeOnlinePayment($target, $targetId, $payment['paymentId'], $payment['payed']?'Y':'N',
          $payment['payed']?sprintf('authCode:%s', $payment['authCode']):null, $status);
        
        break;
      case 'gpwebpay':
        $gw = new GPWebpayGateway(array(
          'language' => strtoupper($this->_app->language->getLanguage()),
          'logFile' => $PAYMENT_GATEWAY['source']['gpwebpay']['logFile'],
          'gatewayUrl' => $PAYMENT_GATEWAY['source']['gpwebpay']['url'],
          'gatewayKey' => $PAYMENT_GATEWAY['source']['gpwebpay']['key'],
          'merchantId' => $gatewayParams->merchantId,
          'merchantKey' => $gatewayParams->keyFile,
          'merchantKeyPassword' => $gatewayParams->keyPassword,
        ));
        $response = $this->_app->request->getParams(array('OPERATION','ORDERNUMBER','PRCODE','SRCODE','RESULTTEXT','TOKEN','ACCODE','DIGEST'));
        $payment = $gw->closePayment($response);

        $status = $response['RESULTTEXT'];

        if ($payment['payed']) {
          $paid = true;
          $payComment = sprintf('payId:%s', $payment['paymentId']);
          $paymentId = $payment['paymentId'];
        }

        $bPayment = new BOnlinePayment;
        $bPayment->closeOnlinePayment($target, $targetId, $payment['paymentId'], $payment['payed']?'Y':'N',
          $payment['payed']?null:null, $status);

        break;
      case 'comgate':
        // neni potreba nic resit
        // stav platby se komunikuje na pozadi solo procesem
        break;
      case 'deminimis':
        // neni potreba nic resit
        // platba se musi potvrdit rucne (trva to)
        break;
      default: throw new ExceptionUser('Unknown payment gateway!');
    }

    if ($paid) {
      switch ($target) {
        case 'RESERVATION':
          // lze platit online vice rezervaci (pak jsou id-cka oddelene '|')
          $ids = str_replace('|',',',substr($targetId, 1, -1));
          $ids = explode(',', $ids);
          foreach ($ids as $id) {
            $bReservation = new BReservation($id);
            $bReservation->payOnline($gateway, array('comment' => $payComment));
          }

          break;
        case 'TICKET':
          $bUser = new BUser;
          $bUser->saveTicketFromOnlinePayment($paymentId);

          break;
        case 'CREDIT':
          $bUser = new BUser;
          $bUser->saveCreditFromOnlinePayment($paymentId);

          break;
        default:
          throw new ExceptionUser('Unknown payment target: ' . $target);
      }
    }
  }

  protected function _userRun() {
    #error_log($this->_app->session->getId());
    #error_log($this->_app->auth->getUserId());
    #error_log($this->_app->session->get('payment_reservationId'));
    
    $providerId = $this->_app->session->get('payment_providerId');
    $target = $this->_app->session->get('payment_target');
    $targetId = $this->_app->session->get('payment_targetId');
    $placeHolder = $this->_app->session->get('payment_placeHolder');
    $jsBackAction = $this->_app->session->get('payment_jsBackAction');
    $gateway = $this->_app->session->get('payment_gateway');
    
    if ($placeHolder) {
      $js = sprintf('flbRefresh(\'%s\');', $placeHolder);
    } elseif ($jsBackAction) {
      $js = $jsBackAction;
    } else $js = '';
    $js = str_replace("'","\'",$js);
    
    try {
      $this->_finishPayment($gateway, $providerId, $target, $targetId);
    } catch (ExceptionUser $e) {
      $this->_app->db->shutdownTransaction();

      #echo sprintf('<body onload="window.onunload = function(e) { window.opener.alert(\'%s\');%s };window.close();"/>', str_replace("'","\'",$e->getMessage()), $js);
      echo sprintf('<body onload="window.onunload = window.opener.postMessage({status:-1,message:\'%s\',action:\'%s\'},\'*\'); window.close();">Payment was not successful.</body>', str_replace("'","\'",$e->printMessage()), $js);
      
      die;
    }
    
    #echo sprintf('<body onload="window.onunload = function(e) { %s };window.close();"/>', $js);
    echo sprintf('<body onload="window.onunload = window.opener.postMessage({status:0,action:\'%s\'},\'*\'); window.close();">Payment was successful.</body>', $js);
  }
}

?>
