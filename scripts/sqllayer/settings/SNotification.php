<?php

class SNotification extends SqlSelect {
  private $_tNotification;
  private $_tProvider;
  
  private function _insertNotificationTable() {
    $this->_tNotification = new SqlTable('notification', 'n');
    
    $this->addColumn(new SqlColumn($this->_tNotification, 'notification_id'));
    $this->addColumn(new SqlColumn($this->_tNotification, 'provider'));
    $this->addColumn(new SqlColumn($this->_tNotification, 'type'));
    $this->addColumn(new SqlColumn($this->_tNotification, 'from_address'));
    $this->addColumn(new SqlColumn($this->_tNotification, 'cc_address'));
    $this->addColumn(new SqlColumn($this->_tNotification, 'bcc_address'));
    $this->addColumn(new SqlColumn($this->_tNotification, 'to_address'));
    $this->addColumn(new SqlColumn($this->_tNotification, 'content_type'));
    $this->addColumn(new SqlColumn($this->_tNotification, 'subject'));
    $this->addColumn(new SqlColumn($this->_tNotification, 'body'));
    $this->addColumn(new SqlColumn($this->_tNotification, 'generate_params'));
    $this->addColumn(new SqlColumn($this->_tNotification, 'parsed'));
    $this->addColumn(new SqlColumn($this->_tNotification, 'created'));
    $this->addColumn(new SqlColumn($this->_tNotification, 'to_send'));
    $this->addColumn(new SqlColumn($this->_tNotification, 'sent'));
    $this->addColumn(new SqlColumn($this->_tNotification, 'reservation'));
    $this->addColumn(new SqlColumn($this->_tNotification, 'reservation_not_payed'));
    $this->addColumn(new SqlColumn($this->_tNotification, 'error_timestamp'));
    $this->addColumn(new SqlColumn($this->_tNotification, 'error_text'));
  }

  protected function _insertProviderTable() {
    $this->_tProvider = new SqlTable('provider', 'prov');

    $this->addColumn(new SqlColumn($this->_tProvider, 'provider_id'));
    $this->addColumn(new SqlColumn($this->_tProvider, 'short_name','provider_short_name'));
  }
  
  protected function _initSqlSelect() {
    $this->_insertNotificationTable();
    $this->_insertProviderTable();

    $this->addJoin(new SqlJoin(false, $this->_tProvider, new SqlStatementBi($this->columns['provider'], $this->columns['provider_id'], '%s=%s')));
  }
}

?>
