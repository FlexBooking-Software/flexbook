<?php

class ModuleNotificationSend extends ExecModule {
  private $_lockFileName;

  public function __construct() {
    parent::__construct();

    global $NOTIFICATION;
    $this->_lockFileName = ifsetor($NOTIFICATION['lockFile'],'/tmp/flexbook_notification.lock');
  }

  private function _getpidinfo($pid=null, $ps_opt="aux"){
    $ps=shell_exec("ps ".$ps_opt);
    $ps=explode("\n", $ps);

    foreach($ps as $key=>$val){
      $ps[$key]=explode(" ", preg_replace("/ +/", " ", trim($ps[$key])));
    }

    $pidInfo = array();
    for ($i=1;$i<count($ps);$i++) {
      $pidInfo[$i-1] = array();
      foreach($ps[0] as $key=>$val) {
        $pidInfo[$i-1][$val] = ifsetor($ps[$i][$key]);
        unset($ps[$i][$key]);
      }
      if (is_array($ps[$i])) {
        $pidInfo[$i-1][$val] .= " ".implode(" ", $ps[$i]);
      }
    }

    // kdyz byl zadan pid vratim pouze zaznam k danemu procesu
    if ($pid) {
      foreach ($pidInfo as $index=>$info) {
        if ($pid == $info['PID']) {
          return array($pidInfo[$index]);
        }
      }

      return false;
    }

    return $pidInfo;
  }

  private function _createLock() {
    // kdyz lock file existuje, zkontroluju, jestli proces jeste bezi
    if (file_exists($this->_lockFileName)) {
      $file = fopen($this->_lockFileName, 'r');
      $pid = fread($file, filesize($this->_lockFileName));
      fclose($file);

      if ($r = $this->_getpidinfo($pid)) {
        echo sprintf("Process is currently running (%s), exiting....\n", $pid);
        die;
      }
    }

    $file = fopen($this->_lockFileName, 'w');
    fwrite($file, getmypid());
    fclose($file);
  }

  private function _removeLock() {
    unlink($this->_lockFileName);
  }

  protected function _userRun() {
    $this->_createLock();

    $date = date('Y-m-d H:i:s');
    
    echo sprintf("--------- %s ----------\n", $date);

    $s = new SNotification;
    $s->addStatement(new SqlStatementMono($s->columns['sent'], "%s IS NULL"));
    $s->addStatement(new SqlStatementBi($s->columns['to_send'], $date, '%s<=%s'));
    $s->addOrder(new SqlStatementAsc($s->columns['to_send']));
    $s->setColumnsMask(array('notification_id','from_address','to_address'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      try {
        echo sprintf('%s: %s -> %s ... ', $row['notification_id'], $row['from_address'], $row['to_address']);
        $b = new BNotification($row['notification_id']);
        if ($ret = $b->send()) echo $ret."\n";
        else echo "OK\n";
      } catch (Exception $e) {
        echo $e->getMessage()."\n";
      }
    }

    // kontrola, jestli jsou odeslany vsechny notifikace ktere maji byt odeslany
    // kdyz ne, tak poslu info (pres zalozni SMTP, pokud existuje)
    global $NOTIFICATION;
    $s = new SNotification;
    $s->addStatement(new SqlStatementMono($s->columns['sent'], "%s IS NULL"));
    $s->addStatement(new SqlStatementBi($s->columns['to_send'], $date, '%s<=%s'));
    $s->addOrder(new SqlStatementAsc($s->columns['to_send']));
    $s->setColumnsMask(array('notification_id','provider_short_name','from_address','to_address','subject','error_text'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($this->_app->db->getRowsNumber($res)>=$NOTIFICATION['errorCountToNotify']) {
      $body = '';
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $body .= sprintf("(%d) - %s - %s -> %s: %s\n\t\t%s\n", $row['notification_id'], $row['provider_short_name'],
          $row['from_address'], $row['to_address'], $row['subject'], $row['error_text']);
      }

      $params = array(
        'fromAddress'         => $NOTIFICATION['defaultAddressFrom'],
        'toAddress'           => $NOTIFICATION['adminEmail'],
        'subject'             => 'Notification problem alert',
        'body'                => $body,
      );
      if (isset($NOTIFICATION['backupSmtp'])) {
        $smtpParams = array(
          'smtp'                => true,
          'smtpHost'            => $NOTIFICATION['backupSmtp']['smtpHost'],
          'smtpPort'            => ifsetor($NOTIFICATION['backupSmtp']['smtpPort']),
          'smtpUser'            => ifsetor($NOTIFICATION['backupSmtp']['smtpUser']),
          'smtpPassword'        => ifsetor($NOTIFICATION['backupSmtp']['smtpPassword']),
          'smtpSecure'          => ifsetor($NOTIFICATION['backupSmtp']['smtpSecure'])
        );
        $params = array_merge($params, $smtpParams);
      }

			echo sprintf("Sending notification alert.\n");
      $b = new BNotification;
      $b->rawSend($params);
    }

    $this->_removeLock();
    
    die;
  }
}

?>
