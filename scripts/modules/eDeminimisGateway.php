<?php

class ModuleDeminimisGateway extends ExecModule {

  private function _createDeminimisRequest() {
    global $PAYMENT_GATEWAY;

    $this->_app->db->beginTransaction();

    // kontrola, jestli uz neni o zaplaceni pozadano
    $s = new SOnlinePayment;
    $s->addStatement(new SqlStatementBi($s->columns['target_id'], $this->_app->session->get('payment_targetId'), '%s=%s'));
    $s->addStatement(new SqlStatementMono($s->columns['paymentid'], '%s IS NOT NULL'));
    $s->setColumnsMask(array('onlinepayment_id'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($this->_app->db->getRowsNumber($res)) throw new ExceptionUser('Žádost o platbu již byla podána a nelze ji podat znovu.');

    // deminimis je jenom na placeni rezervaci
    // nactu data o rezervaci
    $reservation = str_replace('|','',$this->_app->session->get('payment_targetId'));
    $s = new SReservation;
    $s->addStatement(new SqlStatementBi($s->columns['reservation_id'], $reservation, '%s=%s'));
    $s->setColumnsMask(array('number','total_price','user','user_name','description','eventattendeeperson_user'));
    $res = $this->_app->db->doQuery($s->toString());
    $row = $this->_app->db->fetchAssoc($res);
    if ($row['eventattendeeperson_user']) {
      $oU = new OUser($row['eventattendeeperson_user']);
      if ($oUData = $oU->getData()) {
        $row['subaccount_name'] = sprintf('%s %s', $oUData['firstname'], $oUData['lastname']);
      }
    }
    if (!$row['description']) $row['description'] = $row['number'];

    // nactu atribut, kde ma parent user IC
    $s = new SUserAttribute;
    $s->addStatement(new SqlStatementBi($s->columns['user'], $row['user'], '%s=%s'));
    $s->addStatement(new SqlStatementBi($s->columns['short_name'], $PAYMENT_GATEWAY['source']['deminimis']['attr_ic'], '%s=%s'));
    $s->setColumnsMask(array('value'));
    $res = $this->_app->db->doQuery($s->toString());
    $row1 = $this->_app->db->fetchAssoc($res);

    if (!$row['description']||!$row['total_price']||!$row['eventattendeeperson_user']||!$row1||!$row1['value']) {
      echo "Reservation detail:\n";
      adump($row);
      echo "User attribute detail:\n";
      adump($row1);

      die('Internal error!');
    }

    $gw = new Deminimis(array(
      'language'  => strtoupper($this->_app->language->getLanguage()),
      'logFile'   => $PAYMENT_GATEWAY['source']['deminimis']['logFile'],
      'apiUrl'    => $PAYMENT_GATEWAY['source']['deminimis']['apiUrl'],
      'apiKey'    => $PAYMENT_GATEWAY['source']['deminimis']['apiKey'],
    ));
    $paymentId = $gw->createPayment($row1['value'], $row['total_price'], $row['description']);

    $bPayment = new BOnlinePayment;
    $bPayment->openOnlinePayment($row['total_price'], $this->_app->session->get('payment_target'), $this->_app->session->get('payment_targetId'), null, $paymentId, 'deminimis');
    $bPayment->savePaymentId($paymentId); // kvuli zurnalu rezervace

    $this->_app->db->commitTransaction();
  }

  protected function _userRun() {
    $placeHolder = $this->_app->session->get('payment_placeHolder');
    $jsBackAction = $this->_app->session->get('payment_jsBackAction');
    
    if ($placeHolder) {
      $js = sprintf('flbRefresh(\'%s\');', $placeHolder);
    } elseif ($jsBackAction) {
      $js = $jsBackAction;
    } else $js = '';
    $js = str_replace("'","\'",$js);
    
    try {
      $this->_createDeminimisRequest();
    } catch (ExceptionUser $e) {
      $this->_app->db->shutdownTransaction();

      #echo sprintf('<body onload="window.onunload = function(e) { window.opener.alert(\'%s\');%s };window.close();"/>', str_replace("'","\'",$e->getMessage()), $js);
      echo sprintf('<body onload="window.onunload = window.opener.postMessage({status:-1,message:\'%s\',action:\'%s\'},\'*\'); window.close();">Payment was not successful.</body>', str_replace("'","\'",$e->printMessage()), $js);
      
      die;
    }
    
    #echo sprintf('<body onload="window.onunload = function(e) { %s };window.close();"/>', $js);
    echo sprintf('<body onload="window.onunload = window.opener.postMessage({status:0,message:\'%s\',action:\'%s\'},\'*\'); window.close();">Payment was successful.</body>', 'Žádost byla odeslána.', $js);
  }
}

?>
