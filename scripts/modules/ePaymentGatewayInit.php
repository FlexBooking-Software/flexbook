<?php

class ModulePaymentGatewayInit extends ExecModule {
  
  private function _createPayment($gateway, $providerId, $target, $targetId, $targetParams) {
    try {
      switch ($target) {
        case 'RESERVATION':
          $ids = str_replace('|',',',substr($targetId, 1, -1));

          $s = new SReservation;
          $s->addStatement(new SqlStatementMono($s->columns['reservation_id'], sprintf('%%s IN (%s)',$ids)));
          $s->addStatement(new SqlStatementBi($s->columns['provider'], $providerId, '%s=%s'));
          $s->addStatement(new SqlStatementMono($s->columns['payed'], '%s IS NULL'));
          $s->addStatement(new SqlStatementMono($s->columns['cancelled'], '%s IS NULL'));
          $s->setColumnsMask(array('number','total_price','user_email','fe_allowed_payment_online'));
          $res = $this->_app->db->doQuery($s->toString());
          if (!$this->_app->db->getRowsNumber($res)) throw new ExceptionUser('Invalid reservation ID: ' . $targetId);
          $price = 0; $label = ''; $email = '';
          while ($rowRes = $this->_app->db->fetchAssoc($res)) {
            if (!$rowRes['fe_allowed_payment_online']) throw new ExceptionUserTextStorage('error.payReservation_notAllowedPaymentType');

            if ($label) $label .= ',';
            $label .= $rowRes['number'];
            $price += $rowRes['total_price'];
            $email = $rowRes['user_email'];
          }

          break;
        case 'TICKET':
          if (!$targetParams) throw new ExceptionUser('Missing params for ticket ID: ' . $targetId);

          $s = new STicket;
          $s->addStatement(new SqlStatementBi($s->columns['ticket_id'], $targetId, '%s=%s'));
          $s->addStatement(new SqlStatementBi($s->columns['provider'], $providerId, '%s=%s'));
          $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
          $s->setColumnsMask(array('name', 'price'));
          $res = $this->_app->db->doQuery($s->toString());
          if (!$rowRes = $this->_app->db->fetchAssoc($res)) throw new ExceptionUser('Invalid ticket ID: ' . $targetId);

          $label = $rowRes['name'];
          $price = $rowRes['price'];

          $targetParamsObject = json_decode($targetParams);
          $s = new SUserRegistration;
          $s->addStatement(new SqlStatementBi($s->columns['provider'], $providerId, '%s=%s'));
          $s->addStatement(new SqlStatementBi($s->columns['user'], $targetParamsObject->user, '%s=%s'));
          $s->setColumnsMask(array('email'));
          $res = $this->_app->db->doQuery($s->toString());
          if (!$rowRes = $this->_app->db->fetchAssoc($res)) throw new ExceptionUser('Invalid user ID: ' . $targetParamsObject->user);

          $email = $rowRes['email'];

          break;
        case 'CREDIT':
          if (!$targetParams) throw new ExceptionUser('Missing params for credit charge user ID: ' . $targetId);

          $targetParamsObject = json_decode($targetParams);
          $s = new SUserRegistration;
          $s->addStatement(new SqlStatementBi($s->columns['provider'], $targetParamsObject->provider, '%s=%s'));
          $s->addStatement(new SqlStatementBi($s->columns['user'], $targetId, '%s=%s'));
          $s->setColumnsMask(array('provider_name','email'));
          $res = $this->_app->db->doQuery($s->toString());
          if (!$rowRes = $this->_app->db->fetchAssoc($res)) throw new ExceptionUser('Invalid user ID: ' . $targetId);

          $label = $this->_app->textStorage->getText('label.ajax_profile_credit_title').' '.$rowRes['provider_name'];
          $price = $targetParamsObject->amount;
          $email = $rowRes['email'];

          break;
        default:
          throw new ExceptionUser('Unknown payment target: ' . $target);
      }

      $s = new SProviderPaymentGateway;
      $s->addStatement(new SqlStatementBi($s->columns['provider'], $providerId, '%s=%s'));
      $s->addStatement(new SqlStatementBi($s->columns['gateway_name'], $gateway, '%s=%s'));
      $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
      $s->setColumnsMask(array('gateway_params'));
      $res = $this->_app->db->doQuery($s->toString());
      if (!$rowGw = $this->_app->db->fetchAssoc($res)) throw new ExceptionUser('Invalid payment gateway: '.$gateway);
      $gatewayParams = json_decode($rowGw['gateway_params']);

      global $PAYMENT_GATEWAY;
      switch ($gateway) {
        case 'csob':
          $backUrl = sprintf($PAYMENT_GATEWAY['backUrl'], $this->_app->session->getId());

          $bPayment = new BOnlinePayment;
          $reference = $bPayment->openOnlinePayment($price, $target, $targetId, $targetParams, null, 'csob');
          if ($this->_app->getDebug()) {
            $reference = '999' . $reference;
          }

          $gw = new CSOBGateway(array(
            'language' => strtoupper($this->_app->language->getLanguage()),
            'logFile' => $PAYMENT_GATEWAY['source']['csob']['logFile'],
            'cartLabel' => $this->_app->textStorage->getText('label.ajax_paymentGateway_cartLabel'),
            'gatewayUrl' => $PAYMENT_GATEWAY['source']['csob']['url'],
            'gatewayKey' => $PAYMENT_GATEWAY['source']['csob']['key'],
            'merchantId' => $gatewayParams->merchantId,
            'merchantKey' => $gatewayParams->keyFile,
            'merchantUrl' => $backUrl,
          ));
          $gw->createPayment($reference, $price, sprintf($this->_app->textStorage->getText('label.ajax_paymentGateway_cartDescription'), $label));
          $url = $gw->getPaymentProcessUrl();

          $bPayment->savePaymentId($gw->getPaymentId());

          break;
        case 'comgate':
          $bPayment = new BOnlinePayment;
          $bPayment->openOnlinePayment($price, $target, $targetId, $targetParams, null, 'comgate');

          // reference musi byt session, protoze do navratoveho URL jde vlozit pouze reference :(
          // a sessnu potrebuju mit, abych mohl dokoncit proces po placeni
          $reference = $this->_app->session->getId();

          $gw = new COMGATEGateway(array(
            'language' => strtoupper($this->_app->language->getLanguage()),
            'logFile' => $PAYMENT_GATEWAY['source']['comgate']['logFile'],
            'gatewayUrl' => $PAYMENT_GATEWAY['source']['comgate']['url'],
            'merchantId' => $gatewayParams->merchantId,
            'secret' => $gatewayParams->secret,
            'test' => $gatewayParams->test,
          ));
          $gw->createPayment($reference, $price, sprintf($this->_app->textStorage->getText('label.ajax_paymentGateway_cartDescription'), $label), $email);
          $url = $gw->getPaymentProcessUrl();

          $bPayment->savePaymentId($gw->getPaymentId());

          break;
        case 'gpwebpay':
          $backUrl = sprintf($PAYMENT_GATEWAY['backUrl'], $this->_app->session->getId());

          $bPayment = new BOnlinePayment;
          $reference = $bPayment->openOnlinePayment($price, $target, $targetId, $targetParams, null, 'gpwebpay');
          if ($this->_app->getDebug()) {
            $reference = '99' . $reference;
          }

          $gw = new GPWebpayGateway(array(
            'language' => strtoupper($this->_app->language->getLanguage()),
            'logFile' => $PAYMENT_GATEWAY['source']['gpwebpay']['logFile'],
            'gatewayUrl' => $PAYMENT_GATEWAY['source']['gpwebpay']['url'],
            'gatewayKey' => $PAYMENT_GATEWAY['source']['gpwebpay']['key'],
            'merchantId' => $gatewayParams->merchantId,
            'merchantKey' => $gatewayParams->keyFile,
            'merchantKeyPassword' => $gatewayParams->keyPassword,
            'merchantUrl' => $backUrl,
          ));
          $url = $gw->createPayment($reference, $price, sprintf($this->_app->textStorage->getText('label.ajax_paymentGateway_cartDescription'), $label));

          $bPayment->savePaymentId($reference);

          break;
        case 'deminimis':
          $s = new SOnlinePayment;
          $s->addStatement(new SqlStatementBi($s->columns['target_id'], $this->_app->session->get('payment_targetId'), '%s=%s'));
          $s->addStatement(new SqlStatementMono($s->columns['paymentid'], '%s IS NOT NULL'));
          $s->setColumnsMask(array('onlinepayment_id'));
          $res = $this->_app->db->doQuery($s->toString());
          if ($this->_app->db->getRowsNumber($res)) throw new ExceptionUser('Žádost o platbu již byla podána a nelze ji podat znovu.');

          $bPayment = new BOnlinePayment;
          $bPayment->openOnlinePayment($price, $target, $targetId, null, null, null);
          $bPayment->savePaymentId(null);

          $gw = new Deminimis(array(
            'language'    => strtoupper($this->_app->language->getLanguage()),
            'logFile'     => $PAYMENT_GATEWAY['source']['deminimis']['logFile'],
            'gatewayUrl'  => $PAYMENT_GATEWAY['source']['deminimis']['gwUrl'],
          ));

          $url = $gw->getPaymentGatewayUrl().sprintf('?sessid=%s',$this->_app->session->getId());

          break;
        default:
          throw new ExceptionUser('Unknown payment gateway: ' . $gateway);
      }
    } catch (ExceptionUser $e) {
      $bPayment = new BOnlinePayment;
      $bPayment->closeOnlinePayment($target, $targetId, null, 'N', null, $e->printMessage());

      throw new ExceptionUser($e->printMessage());
    }
    
    return $url;
  }

  protected function _userRun() {
    if ($lang = $this->_app->request->getParams('language')) $this->_app->language->setLanguage($lang);
    
    $providerId = $this->_app->request->getParams('provider');
    $target = $this->_app->request->getParams('target');
    $targetId = $this->_app->request->getParams('targetid');
    $targetParams = $this->_app->request->getParams('targetparams');
    $placeHolder = urldecode($this->_app->request->getParams('placeholder'));
    $jsBackAction = urldecode($this->_app->request->getParams('jsbackaction'));
    $gateway = $this->_app->request->getParams('gateway');

    // obezlicka pro online platbu vice rezervaci, je potreba ulozit ID vice rezervaci do target_id
    if (!strcmp($target,'RESERVATION')) {
      $targets = explode(',',$targetId);
      $targetId = '|' . implode('|', $targets) . '|';
    }
    
    $this->_app->session->set('payment_providerId', $providerId);
    $this->_app->session->set('payment_target', $target);
    $this->_app->session->set('payment_targetId', $targetId);
    $this->_app->session->set('payment_targetParams', $targetParams);
    $this->_app->session->set('payment_placeHolder', $placeHolder);
    $this->_app->session->set('payment_jsBackAction', $jsBackAction);
    $this->_app->session->set('payment_gateway', $gateway);
    
    #error_log($this->_app->session->getId());
    #error_log($this->_app->auth->getUserId());
    #adump($this->_app->request->getParams('jsbackaction'));
    #adump($jsBackAction);die;

    try {
      $url = $this->_createPayment($gateway, $providerId, $target, $targetId, $targetParams);
    } catch (ExceptionUser $e) {
      $this->_app->db->shutdownTransaction();

      if ($placeHolder) {
        $js = sprintf('flbRefresh(\'%s\');', $placeHolder);
      } elseif ($jsBackAction) {
        $js = $jsBackAction;
      } else $js = '';
      $js = str_replace("'","\'",$js);
      
      #echo sprintf('<body onload="window.onunload = function(e) { window.opener.alert(\'%s\');%s };window.close();"/>', str_replace("'","\'",$e->getMessage()), $js);
      echo sprintf('<body onload="window.onunload = window.opener.postMessage({status:-1,message:\'%s\',action:\'%s\'},\'*\'); window.close();"/>', str_replace("'","\'",$e->printMessage()), $js);
      
      die;
    }
    
    #echo '<a href="'.$url.'">platba</a>';
    #echo sprintf('<br/><input type="button" value="close" onclick="window.opener.flbRefresh(\'#%s\');window.close();" />', $placeHolder);   
    header('Location: '.$url);
  }
}

?>
