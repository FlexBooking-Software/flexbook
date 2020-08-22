<?php

class OReservation extends SqlObject {
  protected $_table = 'reservation';
  
  protected function _preDelete($ret=true) {
    $data = $this->getData();
    
    $app = Application::get();
    
    $s = new SReservationJournal;
    $s->addStatement(new SqlStatementBi($s->columns['reservation'], $data['reservation_id'], '%s=%s'));
    $s->setColumnsMask(array('reservationjournal_id'));
    $res = $app->db->doQuery($s->toString());
    while ($row = $app->db->fetchAssoc($res)) {
      $o = new OReservationJournal($row['reservationjournal_id']);
      $o->delete();
    }
    
    $s = new SNotification;
    $s->addStatement(new SqlStatementBi($s->columns['reservation'], $data['reservation_id'], '%s=%s'));
    $s->setColumnsMask(array('notification_id'));
    $res = $app->db->doQuery($s->toString());
    while ($row = $app->db->fetchAssoc($res)) {
      $o = new ONotification($row['notification_id']);
      $o->delete();
    }
    
    $s = new SReservationAttribute;
    $s->addStatement(new SqlStatementBi($s->columns['reservation'], $data['reservation_id'], '%s=%s'));
    $s->setColumnsMask(array('attribute'));
    $res = $app->db->doQuery($s->toString());
    while ($row = $app->db->fetchAssoc($res)) {
      $o = new OReservationAttribute(array('reservation'=>$data['reservation_id'],'attribute'=>$row['attribute']));
      $o->delete();
    }

		$s = new SCreditnote;
		$s->addStatement(new SqlStatementBi($s->columns['reservation'], $data['reservation_id'], '%s=%s'));
		$s->setColumnsMask(array('creditnote_id'));
		$res = $app->db->doQuery($s->toString());
		while ($row = $app->db->fetchAssoc($res)) {
			$o = new OCreditnote($row['creditnote_id']);
			$o->delete();
		}

		$s = new SDocument;
		$s->addStatement(new SqlStatementBi($s->columns['reservation'], $data['reservation_id'], '%s=%s'));
		$s->setColumnsMask(array('document_id'));
		$res = $app->db->doQuery($s->toString());
		while ($row = $app->db->fetchAssoc($res)) {
			$o = new ODocument($row['document_id']);
			$o->delete();
		}
    
    return parent::_preDelete($ret);
  }
  
  protected function _postDelete($ret=true) {
    $data = $this->getData();

    if ($data['receipt']) {
			$oFile = new OFile($data['receipt']);
			$oFile->delete();
		}

		if ($data['invoice']) {
			$oFile = new OFile($data['invoice']);
			$oFile->delete();
		}
    
    return parent::_postDelete($ret);
  }
}

?>