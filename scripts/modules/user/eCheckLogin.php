<?php

class ModuleCheckLogin extends ExecModule {

  protected function _userRun() {
    if ($permanent = ifsetor($_COOKIE["permanent_login"])) {
      list($userId, $code) = explode(":", $permanent);

      $s = new SUserPermanentLogin;
      $s->addStatement(new SqlStatementBi($s->columns['authuser'], $userId, '%s=%s'));
      $s->addStatement(new SqlStatementBi($s->columns['code'], $code, '%s=%s'));
      $s->setColumnsMask(array('username','password'));
      $res = $this->_app->db->doQuery($s->toString());
      if ($row = $this->_app->db->fetchAssoc($res)) {
        $o = new OUserPermanentLogin(array('authuser'=>$userId,'code'=>$code));
        $o->setData(array('add_time'=>date('Y-m-d H:i:s')));
        $o->save();

        setcookie('permanent_login', $userId.':'.$code, strtotime("+1 month"));
          
        $params = array('login_username'=>$row['username'],'login_password'=>$row['password'],'login_permanent'=>false);
        $this->_app->response->addParams($params);
        
        //if ($this->_app->session->getExpired()) $this->_app->messages->addMessage('userInfo', $this->_app->textStorage->getText('info.sessionExpired_auth'));
        return 'eLogin';
      }
    }

    //if ($this->_app->session->getExpired()) $this->_app->messages->addMessage('userError', $this->_app->textStorage->getText('error.sessionExpired_auth'));
    // zkouska, jestli to zafunguje
    $validator = Validator::get('login', 'LoginValidator');
    $validator->setValues(array('login_accounts'=>null));
    return 'vLogin';
  }
}

?>
