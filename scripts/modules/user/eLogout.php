<?php

class ModuleLogout extends ExecModule {

  protected function _userRun() {
    $this->_app->session->unsetSession();

    // vymazani informace o trvalem prihlaseni
    if ($permanent = ifsetor($_COOKIE['permanent_login'])) {
      list($userId, $code) = explode(":", $permanent);

      $o = new OUserPermanentLogin(array('authuser'=>$userId,'code'=>$code));
      $o->delete();
      
      setcookie('permanent_login', '', 1);
    }
    
    // odhlaseni facebooku
    global $FACEBOOK;

    return 'vLogin';
  }
}

?>
