<?php

class AjaxGetAttributeCategory extends AjaxAction {

  protected function _userRun() {
    $s = new SAttribute;   
    if (isset($this->_params['provider'])) $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_params['provider'], '%s=%s'));
    if (isset($this->_params['query'])) $s->addStatement(new SqlStatementMono($s->columns['category'], sprintf("%%s LIKE ('%%%%%s%%%%')", $this->_app->db->escapeString($this->_params['query']))));
    $s->addOrder(new SqlStatementAsc($s->columns['category']));
    $s->setDistinct(true);
    $s->setColumnsMask(array('category'));
    $res = $this->_app->db->doQuery($s->toString());
    $temp = array();
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $temp[] = $this->_request->convertOutput($row['category']);
    }
    
    $this->_result['suggestions'] = $temp;
  }
}

?>