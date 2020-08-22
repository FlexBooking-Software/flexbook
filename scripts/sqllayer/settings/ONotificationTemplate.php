<?php

class ONotificationTemplate extends SqlObject {
  protected $_table = 'notificationtemplate';
  
  protected function _preDelete($ret=true) {
    $data = $this->getData();
    
    $s = new SNotificationTemplateItem;
    $s->addStatement(new SqlStatementBi($s->columns['notificationtemplate'], $data['notificationtemplate_id'], '%s=%s'));
    $s->setColumnsMask(array('notificationtemplateitem_id'));
    $ds = new SqlDataSource(new DataSourceSettings, $s);
    $ds->reset();
    while ($ds->currentData) {
      $o = new ONotificationTemplateItem($ds->currentData['notificationtemplateitem_id']);
      $o->delete();
      $ds->nextData();
    }
    
    return parent::_preDelete($ret);
  }
}

?>
