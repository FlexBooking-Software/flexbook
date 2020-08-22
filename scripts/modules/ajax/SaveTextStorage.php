<?php

class AjaxSaveTextStorage extends AjaxAction {

  protected function _userRun() {
    if ($this->_app->session->getExpired()) throw new ExceptionUserTextStorage('error.ajax_sessionExpired');
    
    if (isset($this->_params['provider'])&&isset($this->_params['textStorage'])) {
      $ts = array();
      foreach ($this->_params['textStorage'] as $p) {
        $ts[$p['id']] = $p['value'];
      }
      BCustomer::updateTextStorage($this->_params['provider'], $ts);
    }

    $this->_result = array();
  }
}

?>
