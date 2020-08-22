<?php

class SNotificationTemplateItem extends SqlSelect {
  private $_tNotificationTemplateItem;
  
  private function _insertNotificationTemplateItemTable() {
    $this->_tNotificationTemplateItem = new SqlTable('notificationtemplateitem', 'nti');

    $this->addColumn(new SqlColumn($this->_tNotificationTemplateItem, 'notificationtemplateitem_id'));
    $this->addColumn(new SqlColumn($this->_tNotificationTemplateItem, 'notificationtemplate'));
    $this->addColumn(new SqlColumn($this->_tNotificationTemplateItem, 'name'));
    $this->addColumn(new SqlColumn($this->_tNotificationTemplateItem, 'type'));
    $this->addColumn(new SqlColumn($this->_tNotificationTemplateItem, 'offset'));
    $this->addColumn(new SqlColumn($this->_tNotificationTemplateItem, 'to_provider'));
    $this->addColumn(new SqlColumn($this->_tNotificationTemplateItem, 'to_organiser'));
    $this->addColumn(new SqlColumn($this->_tNotificationTemplateItem, 'to_user'));
    $this->addColumn(new SqlColumn($this->_tNotificationTemplateItem, 'to_substitute'));
    $this->addColumn(new SqlColumn($this->_tNotificationTemplateItem, 'to_attendee'));
    $this->addColumn(new SqlColumn($this->_tNotificationTemplateItem, 'from_address'));
    $this->addColumn(new SqlColumn($this->_tNotificationTemplateItem, 'cc_address'));
    $this->addColumn(new SqlColumn($this->_tNotificationTemplateItem, 'bcc_address'));
    $this->addColumn(new SqlColumn($this->_tNotificationTemplateItem, 'content_type'));
    $this->addColumn(new SqlColumn($this->_tNotificationTemplateItem, 'subject'));
    $this->addColumn(new SqlColumn($this->_tNotificationTemplateItem, 'body'));
  }
  
  protected function _initSqlSelect() {
    $this->_insertNotificationTemplateItemTable();
  }
}

?>
