<?php

class OAvailabilityProfile extends SqlObject {
  protected $_table = 'availabilityprofile';
  
  protected function _preDelete($ret=true) {
    $data = $this->getData();
    
    $s = new SAvailabilityProfileItem;
    $s->addStatement(new SqlStatementBi($s->columns['availabilityprofile'], $data['availabilityprofile_id'], '%s=%s'));
    $s->setColumnsMask(array('availabilityprofileitem_id'));
    $ds = new SqlDataSource(new DataSourceSettings, $s);
    $ds->reset();
    while ($ds->currentData) {
      $o = new OAvailabilityProfileItem($ds->currentData['availabilityprofileitem_id']);
      $o->delete();
      $ds->nextData();
    }
    
    return parent::_preDelete($ret);
  }
}

?>
