<?php

class AjaxGetVersion extends AjaxAction {

  protected function _userRun() {  
    global $AJAX;
    
    $this->_result = ifsetor($AJAX['version'], 'NOT_SET');
  }
}

?>