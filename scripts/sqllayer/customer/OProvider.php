<?php

class OProvider extends SqlObject {
  protected $_table = 'provider';
  
  protected function _preDelete($ret=true) {
    $app = Application::get();
    
    $data = $this->getData();
    
    $s = new SCenter;
    $s->addStatement(new SqlStatementBi($s->columns['provider'], $data['provider_id'], '%s=%s'));
    $s->setColumnsMask(array('center_id'));
    $res = $app->db->doQuery($s->toString());
    while ($row = $app->db->fetchAssoc($res)) {
      $o = new OCenter($row['center_id']);
      $o->delete();
    }
    
    $s = new SProviderSettings;
    $s->addStatement(new SqlStatementBi($s->columns['provider'], $data['provider_id'], '%s=%s'));
    $s->setColumnsMask(array('provider'));
    $res = $app->db->doQuery($s->toString());
    while ($row = $app->db->fetchAssoc($res)) {
      $o = new OProviderSettings(array('provider'=>$row['provider']));
      $o->delete();
    }
    
    $s = new SProviderFile;
    $s->addStatement(new SqlStatementBi($s->columns['provider'], $data['provider_id'], '%s=%s'));
    $s->setColumnsMask(array('providerfile_id'));
    $res = $app->db->doQuery($s->toString());
    while ($row = $app->db->fetchAssoc($res)) {
      $o = new OProviderFile($row['providerfile_id']);
      $o->delete();
    }

    $s = new SProviderInvoice;
    $s->addStatement(new SqlStatementBi($s->columns['provider'], $data['provider_id'], '%s=%s'));
    $s->setColumnsMask(array('providerinvoice_id'));
    $res = $app->db->doQuery($s->toString());
    while ($row = $app->db->fetchAssoc($res)) {
      $o = new OProviderInvoice($row['providerinvoice_id']);
      $o->delete();
    }

		$s = new SProviderTextStorage;
		$s->addStatement(new SqlStatementBi($s->columns['provider'], $data['provider_id'], '%s=%s'));
		$s->setColumnsMask(array('providertextstorage_id'));
		$res = $app->db->doQuery($s->toString());
		while ($row = $app->db->fetchAssoc($res)) {
			$o = new OProviderTextStorage($row['providertextstorage_id']);
			$o->delete();
		}
    
    return parent::_preDelete($ret);
  }
  
  protected function _postDelete($ret=true) {
    $data = $this->getData();
    if ($data['invoice_address']) {
      $oAddress = new OAddress($data['invoice_address']);
      $oAddress->delete();
    }
    
    return parent::_postDelete($ret);
  }
}

?>