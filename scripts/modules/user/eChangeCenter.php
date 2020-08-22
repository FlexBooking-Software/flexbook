<?php

class ModuleChangeCenter extends ExecModule {

  protected function _userRun() {
    $newCenter = $this->_app->request->getParams('center');
    
    if ($newCenter) {
      $s = new SCenter;
      $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
      $s->addStatement(new SqlStatementMono($s->columns['center_id'], sprintf('%%s IN (%s)', $this->_app->auth->getAllowedCenter('list'))));
      $s->addStatement(new SqlStatementBi($s->columns['center_id'], $newCenter, '%s=%s'));
      $s->setColumnsMask(array('center_id'));
      $res = $this->_app->db->doQuery($s->toString());
      if (!$this->_app->db->getRowsNumber($res)) throw new ExceptionUserTextStorage('error.changeCenter');
    }
    
    $this->_app->auth->setActualCenter($newCenter);
    setcookie('actualCenter', $newCenter?$newCenter:-1);
    
    $validator = Validator::get('home', 'HomeValidator', true);
    
    return 'eMain';
  }
}

?>
