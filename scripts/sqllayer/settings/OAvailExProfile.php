<?php

class OAvailabilityExceptionProfile extends SqlObject {
  protected $_table = 'availabilityexceptionprofile';
  
  protected function _preDelete($ret=true) {
    $data = $this->getData();
    
    $s = new SAvailabilityExceptionProfileItem;
    $s->addStatement(new SqlStatementBi($s->columns['availabilityexceptionprofile'], $data['availabilityexceptionprofile_id'], '%s=%s'));
    $s->setColumnsMask(array('availabilityexceptionprofileitem_id'));
    $ds = new SqlDataSource(new DataSourceSettings, $s);
    $ds->reset();
    while ($ds->currentData) {
      $o = new OAvailabilityExceptionProfileItem($ds->currentData['availabilityexceptionprofileitem_id']);
      $o->delete();
      $ds->nextData();
    }
    
    return parent::_preDelete($ret);
  }
}

?>
