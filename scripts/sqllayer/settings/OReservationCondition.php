<?php

class OReservationCondition extends SqlObject {
  protected $_table = 'reservationcondition';
  
  protected function _preDelete($ret=true) {
    $data = $this->getData();
    
    $s = new SReservationConditionItem;
    $s->addStatement(new SqlStatementBi($s->columns['reservationcondition'], $data['reservationcondition_id'], '%s=%s'));
    $s->setColumnsMask(array('reservationconditionitem_id'));
    $ds = new SqlDataSource(new DataSourceSettings, $s);
    $ds->reset();
    while ($ds->currentData) {
      $o = new OReservationConditionItem($ds->currentData['reservationconditionitem_id']);
      $o->delete();
      $ds->nextData();
    }
    
    return parent::_preDelete($ret);
  }
}

?>
