<?php

class BOnlinePayment extends BusinessObject {

  private function _checkAccess() {
    return true;
  }

  private function _getUserRegistration($target, $targetId, $targetParams) {
    $ret = null;

    switch ($target) {
      case 'RESERVATION':
        // pro rezervaci muze byt vic ID-cek
        $ids = str_replace('|',',',substr($targetId, 1, -1));

        $s = new SReservation;
        $s->addStatement(new SqlStatementMono($s->columns['reservation_id'], sprintf('%%s IN (%s)', $ids)));
        $s->setColumnsMask(array('user','provider'));
        $res = $this->_app->db->doQuery($s->toString());
        $row = $this->_app->db->fetchAssoc($res);

        break;
      case 'TICKET':
        $targetParams = json_decode($targetParams);

        $s = new STicket;
        $s->addStatement(new SqlStatementBi($s->columns['ticket_id'], $targetId, '%s=%s'));
        $s->setColumnsMask(array('provider'));
        $res = $this->_app->db->doQuery($s->toString());
        $row = $this->_app->db->fetchAssoc($res);

        $row['user'] = $targetParams->user;

        break;
      case 'CREDIT':
        $targetParams = json_decode($targetParams);

        $row['user'] = $targetId;
        $row['provider'] = $targetParams->provider;

        break;
      default:
        throw new ExceptionUser('Unknown payment target: ' . $target);
    }

    if (BCustomer::getProviderSettings($row['provider'],'disableOnline')=='Y') throw new ExceptionUserTextStorage('error.accessDenied');

    $s = new SUserRegistration;
    $s->addStatement(new SqlStatementBi($s->columns['provider'], $row['provider'], '%s=%s'));
    $s->addStatement(new SqlStatementBi($s->columns['user'], $row['user'], '%s=%s'));
    $s->setColumnsMask(array('userregistration_id'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($row = $this->_app->db->fetchAssoc($res)) {
      $ret = $row['userregistration_id'];
    }

    return $ret;
  }

  public function openOnlinePayment($amount, $target, $targetId, $targetParams=null, $paymentId=null, $type=null) {
    $this->_checkAccess();

    $userRegistrationId = $this->_getUserRegistration($target, $targetId, $targetParams);

    $this->_app->db->beginTransaction();

    // zkusim najit otevrenou online platbu bez paymentId a typu
    // takova vznika napr. pri placeni rovnou pri ulozeni rezervace
    // pokud takova existuje, tak ji aktualizuju, jinak vytvorim novou
    $onlinePaymentId = null;
    $s = new SOnlinePayment;
    $s->addStatement(new SqlStatementBi($s->columns['target'], $target, '%s=%s'));
    $s->addStatement(new SqlStatementBi($s->columns['target_id'], $targetId, '%s=%s'));
    $s->addStatement(new SqlStatementMono($s->columns['paymentid'], '%s IS NULL'));
    $s->addStatement(new SqlStatementMono($s->columns['type'], '%s IS NULL'));
    $s->addStatement(new SqlStatementMono($s->columns['end_timestamp'], '%s IS NULL'));
    $s->setColumnsMask(array('onlinepayment_id'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($row = $this->_app->db->fetchAssoc($res)) {
      $onlinePaymentId = $row['onlinepayment_id'];
      $oData = array();
    } else {
      // pokud je online platba na vice rezervaci, zkusim vymazat vsechny otevrene online platby na jednotlive rezervace
      // ty vznikly pri vytvoreni jednotlivych rezervaci, aby nedoslo k automatickemu zruseni kvuli nezaplaceni
      if (!strcmp($target,'RESERVATION')) {
        $ids = explode('|',substr($targetId, 1, -1));
        foreach ($ids as $id) {
          $s1 = new SOnlinePayment;
          $s1->addStatement(new SqlStatementMono($s1->columns['target'], "%s='RESERVATION'"));
          $s1->addStatement(new SqlStatementMono($s1->columns['target_id'], sprintf("%%s='|%s|'", $id)));
          $s1->addStatement(new SqlStatementMono($s1->columns['paymentid'], '%s IS NULL'));
          $s1->addStatement(new SqlStatementMono($s1->columns['type'], '%s IS NULL'));
          $s1->addStatement(new SqlStatementMono($s1->columns['end_timestamp'], '%s IS NULL'));
          $s1->setColumnsMask(array('onlinepayment_id'));
          $res1 = $this->_app->db->doQuery($s1->toString());
          while ($row1 = $this->_app->db->fetchAssoc($res1)) {
            $o1 = new OOnlinePayment($row1['onlinepayment_id']);
            $o1->delete();
          }
        }
      }

      $oData = array(
        'amount'            => $amount,
        'target'            => $target,
        'target_id'         => $targetId,
        'target_params'     => $targetParams?$targetParams:null,
        'start_timestamp'   => date('Y-m-d H:i:s'),
        'userregistration'  => $userRegistrationId,
      );
    }
    $oData['type'] = $type;
    $oData['paymentId'] = $paymentId;

    $o = new OOnlinePayment($onlinePaymentId);
    $o->setData($oData);
    $o->save();
    $onlinePaymentId = $o->getId();

    // ulozim u vsech rezervaci na ktere je online platba, ID platby
    // optimalizace, aby slo rychleji vyhledat rezervace s otevrenou online platbou
    if (!strcmp($target,'RESERVATION')) {
      $ids = explode('|',substr($targetId, 1, -1));
      foreach ($ids as $id) {
        $bRes = new BReservation($id);
        $bRes->saveOpenOnlinePayment($onlinePaymentId);
      }
    }

    $this->_app->db->commitTransaction();

    $this->_id = $o->getId();

    return $this->_id;
  }

  public function closeOnlinePayment($target, $targetId, $paymentId, $payed, $paymentDesc, $paymentStatus) {
    $this->_checkAccess();

    $s = new SOnlinePayment;
    $s->addStatement(new SqlStatementBi($s->columns['target'], $target, '%s=%s'));
    $s->addStatement(new SqlStatementBi($s->columns['target_id'], $targetId, '%s=%s'));
    $s->addStatement(new SqlStatementMono($s->columns['end_timestamp'], '%s IS NULL'));
    if ($paymentId) $s->addStatement(new SqlStatementBi($s->columns['paymentid'], $paymentId, '%s=%s'));
    else {
      // kdyz neni paymentId, uzaviram platbu, ktera jeste ani nebyla inicializovana na platebni brane
      $s->addStatement(new SqlStatementMono($s->columns['paymentid'], '%s IS NULL'));
      #$s->addStatement(new SqlStatementMono($s->columns['type'], '%s IS NULL'));
    }
    $s->setColumnsMask(array('onlinepayment_id','type','paymentid'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($row = $this->_app->db->fetchAssoc($res)) {
      $this->_app->db->beginTransaction();

      $o = new OOnlinePayment($row['onlinepayment_id']);
      $o->setData(array(
        'end_timestamp' => date('Y-m-d H:i:s'),
        'payed'         => $payed,
        'status'        => $paymentStatus,
        'paymentdesc'   => $paymentDesc,
      ));
      $o->save();

      // pokud je online platba na rezervaci, udelam zaznam do zurnalu rezervace a rusim priznak o otevrene online platbe
      if (!strcmp($target,'RESERVATION')) {
        $ids = explode('|',substr($targetId, 1, -1));
        foreach ($ids as $id) {
          $bRes = new BReservation($id);
          $bRes->saveOpenOnlinePayment(null);
          $bRes->createJournalRecord('PAYMENT_CLOSE', sprintf('%s|payId:%s%s', $row['type'], $row['paymentid'], $paymentDesc?','.$paymentDesc:''));
        }
      }

      $this->_app->db->commitTransaction();
    }
  }

  public function savePaymentId($paymentId) {
    $this->_app->db->beginTransaction();

    $o = new OOnlinePayment($this->_id);
    $oData = $o->getData();
    $o->setData(array('paymentid'=>$paymentId));
    $o->save();

    // pokud je online platba na rezervaci, udelam zaznam do zurnalu rezervace
    if (!strcmp($oData['target'],'RESERVATION')) {
      $ids = explode('|',substr($oData['target_id'], 1, -1));
      foreach ($ids as $id) {
        $bRes = new BReservation($id);
        $bRes->createJournalRecord('PAYMENT_CREATE', sprintf('%s|payId:%s', $oData['type'], $paymentId));
      }
    }

    $this->_app->db->commitTransaction();
  }

  public function saveRefund($target, $targetId, $paymentId) {
    $this->_checkAccess();

    $s = new SOnlinePayment;
    $s->addStatement(new SqlStatementBi($s->columns['target'], $target, '%s=%s'));
    $s->addStatement(new SqlStatementBi($s->columns['target_id'], $targetId, '%s=%s'));
    $s->addStatement(new SqlStatementBi($s->columns['paymentid'], $paymentId, '%s=%s'));
    $s->setColumnsMask(array('onlinepayment_id'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($row = $this->_app->db->fetchAssoc($res)) {
      $this->_app->db->beginTransaction();

      $o = new OOnlinePayment($row['onlinepayment_id']);
      $o->setData(array(
        'refund_timestamp' => date('Y-m-d H:i:s')
      ));
      $o->save();

      $this->_app->db->commitTransaction();
    }
  }
}

?>
