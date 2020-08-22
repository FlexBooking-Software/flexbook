<?php

class ModuleInvoiceCreate extends ExecModule {

  protected function _userRun() {
    global $NOTIFICATION;
    global $INVOICE;

    // jako parametr muze dostat mesic (YYYY-MM), pro ktery se maji vystavit faktury
    // pokud neni parametr, vystavuji se faktury na "minuly" mesic
    // jako dalsi parametr muze byt datum vystaveni (YYYY-MM-DD), kdyz neni, je to aktualni datum
    global $argc, $argv;
    if ($argc>1) {
      $dateFrom = $argv[1].'-01';
      if (!$this->_app->regionalSettings->checkDate($dateFrom)) die('Period for invoices has to be in this format: "YYYY-MM"!');

      $period = $argv[1];

      if ($argc>2) {
        $createDate = $argv[2];
      } else $createDate = null;
    } else {
      $dateFrom = date('Y-m-01');
      $dateFrom = $this->_app->regionalSettings->decreaseDate($dateFrom, 0, 1);

      $period = substr($dateFrom,0,7);
      $createDate = null;
    }
    $dateTo = $this->_app->regionalSettings->decreaseDate($this->_app->regionalSettings->increaseDate($dateFrom,0, 1));

    echo sprintf("Generating invoices for period: %s - %s\n", $dateFrom, $dateTo);

    $s = new SCustomer;
    $s->setColumnsMask(array('customer_id','name','provider','email'));
    $res = $this->_app->db->doQuery($s->toString());
    $invoiceNumber = array();
    while ($row = $this->_app->db->fetchAssoc($res)) {
      echo sprintf("Generating invoice for customer: %s ... ", $row['name']);

      try {
        $b = new BCustomer($row['customer_id']);
        $number = $b->createInvoice($period, $createDate);
        if ($number) {
          echo $number;

          $invoiceNumber[] = $number;

          global $AJAX;
          $s = new SProviderInvoice;
          $s->addStatement(new SqlStatementBi($s->columns['number'], $number, '%s=%s'));
          $s->setColumnsMask(array('file_hash'));
          $res1 = $this->_app->db->doQuery($s->toString());
          $row1 = $this->_app->db->fetchAssoc($res1);
          $invoiceUrl = sprintf('%s/getfile.php?id=%s', dirname($AJAX['url']), $row1['file_hash']);

          $bNot = new BNotification;
          $bNot->create(array(
            'provider'      => $row['provider'],
            'fromAddress'   => $NOTIFICATION['defaultAddressFrom'],
            'toAddress'     => $row['email'],
            'subject'       => $INVOICE['notificationSubject'],
            'body'          => str_replace(array('{invoiceUrl}','{invoiceNumber}'), array($invoiceUrl,$number), $INVOICE['notificationBody']),
            'toSend'        => date('Y-m-d H:i:s'),
          ));
        }

        echo "\n";
      } catch (Exception $e) {
        echo $e->getMessage() . "\n";

        $this->_app->db->shutdownTransaction();
      }
    }

    if (count($invoiceNumber)) {
      $b = new BNotification;
      $b->rawSend(array(
        'fromAddress'         => $NOTIFICATION['defaultAddressFrom'],
        'toAddress'           => $NOTIFICATION['adminEmail'],
        'subject'             => 'Automaticka fakturace FLEXBOOK',
        'body'                => sprintf('Byly vygenerovany faktury %s za obdobi %s.', implode(',', $invoiceNumber), $period),
      ));
    }

    die;
  }
}

?>
