<?php

class ModuleInPageConfirmRegistration extends ExecModule {

  protected function _userRun() {
    $code = $this->_app->request->getParams('code');
    if ($code) {
      $s = new SUserValidation;
      $s->addStatement(new SqlStatementBi($s->columns['validation_string'], $code, '%s=%s'));
      $s->setColumnsMask(array('user'));
      $res = $this->_app->db->doQuery($s->toString());
      if ($row = $this->_app->db->fetchAssoc($res)) {
        $b = new BUser($row['user']);
        $b->validate();
        
        echo $this->_app->textStorage->getText('label.inpage_registration_confirmOk');
      } else echo "Invalid code!";
    } else echo "Missing code!";
    
    die;
  }
}

?>
