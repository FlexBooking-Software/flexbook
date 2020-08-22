<?php

class OUser extends SqlObject {
  protected $_table = 'user';
  
  protected function _preDelete($ret=true) {
    $app = Application::get();
    
    $data = $this->getData();

    $s = new SUserRegistration;
    $s->addStatement(new SqlStatementBi($s->columns['user'], $data['user_id'], '%s=%s'));
    $s->setColumnsMask(array('userregistration_id'));
    $res = $app->db->doQuery($s->toString());
    while ($row = $app->db->fetchAssoc($res)) {
      $o = new OUserRegistration($row['userregistration_id']);
      $o->delete();
    }
    
    $s = new SEmployee;
    $s->addStatement(new SqlStatementBi($s->columns['user'], $data['user_id'], '%s=%s'));
    $s->setColumnsMask(array('employee_id'));
    $res = $app->db->doQuery($s->toString());
    while ($row = $app->db->fetchAssoc($res)) {
      $o = new OEmployee($row['employee_id']);
      $o->delete();
    }
    
    $s = new SUserValidation;
    $s->addStatement(new SqlStatementBi($s->columns['user'], $data['user_id'], '%s=%s'));
    $s->setColumnsMask(array('user'));
    $res = $app->db->doQuery($s->toString());
    while ($row = $app->db->fetchAssoc($res)) {
      $o = new OUserValidation(array('user'=>$row['user']));
      $o->delete();
    }
    
    $s = new SUserAttribute;
    $s->addStatement(new SqlStatementBi($s->columns['user'], $data['user_id'], '%s=%s'));
    $s->setColumnsMask(array('attribute'));
    $res = $app->db->doQuery($s->toString());
    while ($row = $app->db->fetchAssoc($res)) {
      $o = new OUserAttribute(array('user'=>$data['user_id'],'attribute'=>$row['attribute']));
      $o->delete();
    }

		$s = new SDocument;
		$s->addStatement(new SqlStatementBi($s->columns['user'], $data['user_id'], '%s=%s'));
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
    if ($data['address']) {
      $oAddress = new OAddress($data['address']);
      $oAddress->delete();
    }
    
    return parent::_postDelete($ret);
  }
}

?>