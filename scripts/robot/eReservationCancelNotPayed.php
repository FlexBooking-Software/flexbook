<?php

class ModuleReservationCancelNotPayed extends ExecModule {

  protected function _userRun() {
    $date = date('Y-m-d');
    
    echo sprintf("--------- %s ----------\n", date('Y-m-d H:i:s'));
    echo sprintf("Getting conditions with payment required:\n");
    
    // nejdriv nactu podminky rezervaci, ktere me zajimaji
    $condition = array();$id = '';
    $s = new SReservationConditionItem;
    $s->addStatement(new SqlStatementQuad($s->columns['time_from'], $s->columns['time_from'], $s->columns['time_to'], $s->columns['time_to'],
                                          "((%s IS NULL OR %s<NOW()) AND (%s IS NULL OR NOW()<%s))"));
    $s->addStatement(new SqlStatementMono($s->columns['advance_payment'], "%s IS NOT NULL"));
    $s->addStatement(new SqlStatementMono($s->columns['reservationcondition_evaluation'], "%s='ALL'"));
    $s->setColumnsMask(array('name','advance_payment','reservationcondition_id','reservationcondition_name','reservationcondition_provider','provider_name'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $condition[$row['reservationcondition_id']] = array('id'=>$row['reservationcondition_id'],'name'=>$row['reservationcondition_name'],
                         'itemName'=>$row['name'], 'advancePayment'=>$row['advance_payment'],
                         'providerId'=>$row['reservationcondition_provider'],'providerName'=>$row['provider_name'],
                         'resource'=>array(),'resourceAllId'=>'','resourceAllName'=>'',
                         'event'=>array(),'eventAllId'=>'','eventAllName'=>'',);
      if ($id) $id .= ','; $id .= $row['reservationcondition_id'];
      
      // pridam poznamku pri zruseni rezervace
      $s1 = new SProviderSettings;
      $s1->addStatement(new SqlStatementBi($s1->columns['provider'], $row['reservationcondition_provider'], '%s=%s'));
      $s1->setColumnsMask(array('reservation_cancel_message'));
      $res1 = $this->_app->db->doQuery($s1->toString());
      if ($row1 = $this->_app->db->fetchAssoc($res1)) {
        if ($row1['reservation_cancel_message']) $condition[$row['reservationcondition_id']]['cancelNote'] = $row1['reservation_cancel_message'];
      }
      if (!isset($condition[$row['reservationcondition_id']]['cancelNote'])||!$condition[$row['reservationcondition_id']]['cancelNote']) {
        $condition[$row['reservationcondition_id']]['cancelNote'] = $this->_app->textStorage->getText('label.robot_cancelNotPayedReservation');
      }
    }
    if (!$id) return;
    
    // pridam k nim zdroje
    $s = new SResource;
    $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
    $s->addStatement(new SqlStatementMono($s->columns['reservationcondition'], sprintf('%%s IN (%s)', $id)));
    $s->addOrder(new SqlStatementAsc($s->columns['reservationcondition']));
    $s->addOrder(new SqlStatementAsc($s->columns['name']));
    $s->setColumnsMask(array('resource_id','name','reservationcondition'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $condition[$row['reservationcondition']]['resource'][] = array('id'=>$row['resource_id'],'name'=>$row['name']);
      
      if ($condition[$row['reservationcondition']]['resourceAllId']) $condition[$row['reservationcondition']]['resourceAllId'] .= ',';
      $condition[$row['reservationcondition']]['resourceAllId'] .= $row['resource_id'];
      if ($condition[$row['reservationcondition']]['resourceAllName']) $condition[$row['reservationcondition']]['resourceAllName'] .= ',';
      $condition[$row['reservationcondition']]['resourceAllName'] .= $row['name'];
    }
    // pridam k nim akce
    $s = new SEvent;
    $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
    $s->addStatement(new SqlStatementMono($s->columns['reservationcondition'], sprintf('%%s IN (%s)', $id)));
    $s->addOrder(new SqlStatementAsc($s->columns['reservationcondition']));
    $s->addOrder(new SqlStatementAsc($s->columns['name']));
    $s->setColumnsMask(array('event_id','name','reservationcondition'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $condition[$row['reservationcondition']]['event'][] = array('id'=>$row['event_id'],'name'=>$row['name']);
      
      if ($condition[$row['reservationcondition']]['eventAllId']) $condition[$row['reservationcondition']]['eventAllId'] .= ',';
      $condition[$row['reservationcondition']]['eventAllId'] .= $row['event_id'];
      if ($condition[$row['reservationcondition']]['eventAllName']) $condition[$row['reservationcondition']]['eventAllName'] .= ',';
      $condition[$row['reservationcondition']]['eventAllName'] .= $row['name'];
    }
    
    foreach ($condition as $c) {
      echo sprintf("%s - %s (%s) - %s: %s, %s\n", $c['providerName'], $c['name'], $c['itemName'], $c['advancePayment'],
                              $c['resourceAllName']?$c['resourceAllName']:'zadny zdroj',
                              $c['eventAllName']?$c['eventAllName']:'zadna akce');
    }

    // budu prochazet rezervace s nalezenych zdroju/akci, ktere nejsou zaplaceny a mely by byt
    // kdyz takovou najdu, tak ji zrusim
    foreach ($condition as $c) {
      $s = new SReservation;
      #$s->addStatement(new SqlStatementMono($s->columns['reservation_id'], "%s=6760"));
      $s->addStatement(new SqlStatementMono($s->columns['start'], '%s>DATE_SUB(NOW(),INTERVAL 1 DAY)'));
      $s->addStatement(new SqlStatementMono($s->columns['total_price'], "%s>0"));
      $s->addStatement(new SqlStatementMono($s->columns['payed'], "%s IS NULL"));
      $s->addStatement(new SqlStatementMono($s->columns['failed'], "%s IS NULL"));
      $s->addStatement(new SqlStatementMono($s->columns['cancelled'], "%s IS NULL"));
      $s->addStatement(new SqlStatementBi($s->columns['resource'], $s->columns['event'], sprintf('(%%s IN (%s) OR %%s IN (%s))',
                                          $c['resourceAllId']?$c['resourceAllId']:'NULL', $c['eventAllId']?$c['eventAllId']:'NULL')));
      $s->addStatement(new SqlStatementMono($s->columns['start'], sprintf('%%s<FROM_UNIXTIME(%s)', time()+60*$c['advancePayment'])));
      $s->addOrder(new SqlStatementAsc($s->columns['start']));
      $s->addOrder(new SqlStatementAsc($s->columns['number']));
      $s->setDistinct(true);
      $s->setColumnsMask(array('reservation_id','number','start','user_name','description'));
      #adump($s->toString());die;
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        // jeste musim vynechat rezervace, ktere maji "rozdelanou" online platbu
        // nejde to najoinovat, protoze ID-cka rezervaci jsou u online platby spojeny
        // puvodni podminka: $s->addStatement(new SqlStatementBi($s->columns['onlinepayment_id'], $s->columns['onlinepayment_end'], '(%s IS NULL OR %s IS NOT NULL)'));
        $s1 = new SOnlinePayment;
        $s1->addStatement(new SqlStatementMono($s1->columns['end_timestamp'], '%s IS NULL'));
        $s1->addStatement(new SqlStatementMono($s1->columns['target_id'], sprintf("%%s LIKE '%%%%|%s|%%%%'", $row['reservation_id'])));
        $s1->setColumnsMask(array('onlinepayment_id'));
        $res1 = $this->_app->db->doQuery($s1->toString());
        if ($this->_app->db->getRowsNumber($res1)) continue;

        echo sprintf('CANCELLING: %s/%s - %s (%s) with note "%s" ... ', $row['number'], $row['start'], $row['description'], $row['user_name'], ifsetor($c['cancelNote']));

        try {
          $b = new BReservation($row['reservation_id']);
          $b->cancel(true, ifsetor($c['cancelNote']));

          echo "OK\n";
        } catch (Exception $e) {
          echo $e->getMessage()."\n";

          $this->_app->db->shutdownTransaction();
        }
      }
    }
    
    echo "Done.\n";
  }
}

?>
