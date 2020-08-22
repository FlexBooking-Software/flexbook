<?php

class ModuleEventGroupEdit extends ExecModule {

  protected function _userRun() {
    $id = $this->_app->request->getParams('id');
    
    $s = new SEvent;
    $s->addStatement(new SqlStatementMono($s->columns['event_id'], sprintf('%%s IN (%s)', $this->_app->db->escapeString($id))));
    $s->addOrder(new SqlStatementAsc($s->columns['name']));
    $s->setColumnsMask(array('provider'));
    $s->setDistinct(true);
    $res = $this->_app->db->doQuery($s->toString());
    if ($this->_app->db->getRowsNumber($res)>1) {
      $this->_app->messages->addMessage('userError', $this->_app->textStorage->getText('error.editGroupEvent_provider'));
      
      $this->_app->response->addParams(array('id'=>explode(',',$id)));
      return 'vEvent';   
    }
    $row = $this->_app->db->fetchAssoc($res);
    
    $validator = Validator::get('event','EventValidator',true);
    $validator->setValues(array('groupSave'=>'1','id'=>$id,'providerId'=>$row['provider']));

    return 'vEventEdit';
  }
}

?>
