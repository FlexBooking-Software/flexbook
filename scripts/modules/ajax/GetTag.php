<?php

class AjaxGetTag extends AjaxAction {

  protected function _userRun() {  
    if (isset($this->_params['term'])) {
      $s = new STag;
      $s->addStatement(new SqlStatementMono($s->columns['name'], sprintf("%%s LIKE '%%%%%s%%%%'", $this->_app->db->escapeString($this->_params['term']))));
      if (isset($this->_params['provider'])) $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_params['provider'], '%s=%s'));
      if (isset($this->_params['center'])) $s->addStatement(new SqlStatementQuad($s->columns['resource_center'], $this->_params['center'],
        $s->columns['event_center'], $this->_params['center'], '(%s=%s OR %s=%s)'));
      if (isset($this->_params['commodity'])) {
        if (!strcmp($this->_params['commodity'],'event')) $s->addStatement(new SqlStatementMono($s->columns['event_count'], '%s>0'));
        elseif (!strcmp($this->_params['commodity'],'resource')) $s->addStatement(new SqlStatementMono($s->columns['resource_count'], '%s>0'));
      }
      $s->addOrder(new SqlStatementAsc($s->columns['name']));
      $s->setColumnsMask(array('tag_id','name'));
      $s->setDistinct(true);
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $row['id'] = $row['tag_id'];
        $this->_result[] = $this->_request->convertOutput($row);
      }
    } elseif (isset($this->_params['id'])) {
      $s = new STag;
      $s->addStatement(new SqlStatementBi($s->columns['tag_id'], $this->_params['id'], '%s=%s'));
      if (isset($this->_params['provider'])) $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_params['provider'], '%s=%s'));
      $s->setColumnsMask(array('tag_id','name'));
      $res = $this->_app->db->doQuery($s->toString());
      if ($row = $this->_app->db->fetchAssoc($res)) {
        $row['id'] = $row['tag_id'];
        
        $this->_result = $this->_request->convertOutput($row);
      }
    }
  }
}

?>