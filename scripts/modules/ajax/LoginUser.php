<?php

class AjaxLoginUser extends AjaxAction {

  protected function _userRun() {
    if (!isset($this->_params['username'])||!$this->_params['username']) {
      throw new ExceptionUserTextStorage('error.login_missingUsername');
    }
    if (!isset($this->_params['password'])||!$this->_params['password']) {
      throw new ExceptionUserTextStorage('error.login_missingPassword');
    }
    
    if (!$this->_app->auth->authenticate(array(
                'provider' => $this->_params['provider'],
                'username' => $this->_params['username'],
                'password' => $this->_params['password'],
                ))) {
      $this->_result = array(
        'error' => true,
        'message' => $this->_app->textStorage->getText('error.authFailed'),
      );

      // musim rozlisit, jestli se prihlaseni nepovedlo kvuli spatnemu heslu nebo ucet neni potvrzeny
      $s = new SUser;
      $s->addStatement(new SqlStatementBi($s->columns['registration_provider'], $this->_params['provider'], '%s=%s'));
      $s->addStatement(new SqlStatementBi($s->columns['username'], $this->_params['username'], '%s=%s'));
      $s->addStatement(new SqlStatementBi($s->columns['password'], $this->_params['password'], '%s=%s'));
      $s->setColumnsMask(array('validated','disabled'));
      $res = $this->_app->db->doQuery($s->toString());
      if ($row = $this->_app->db->fetchAssoc($res)) {
        if (!$row['validated']) {
          $this->_result['notValidated'] = true;
          $this->_result['message'] = $this->_app->textStorage->getText('error.authFailed_notValidated');
        }
      }
    } else {
      $this->_result = array(
        'userid' => $this->_app->auth->getUserId(),
        'username' => $this->_app->auth->getFullname(),
        'useremail' => $this->_app->auth->getEmail(),
        'sessionid' => $this->_app->session->getId(),
      );
    }
  }
}

?>
