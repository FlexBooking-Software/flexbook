<?php

class AjaxSaveSubstitute extends AjaxAction {

  protected function _userRun() {
    if ($this->_app->session->getExpired()||!$this->_app->auth->getUserId()) throw new ExceptionUserTextStorage('error.ajax_sessionExpired');
    
    $event = ifsetor($this->_params['event']);
    if ($event) {
      $params = array();
      if (isset($this->_params['user'])) $params['userId'] = $this->_params['user'];
      if (isset($this->_params['places'])) $params['places'] = $this->_params['places'];
      if (isset($this->_params['attendee'])) $params['attendeePerson'] = json_decode($this->_params['attendee'], true);
      if (isset($this->_params['attribute'])) $params['attribute'] = $this->_params['attribute'];
      #adump($params);die;
      
      $b = new BEvent($event);
      $name = $b->saveSubstitute($params);
      
      $this->_result = array('error'=>false,'popup'=>sprintf($this->_app->textStorage->getText('info.editEvent_substituteSaveOk'), $name));
    } else $this->_result = array('error'=>true);
  }
}

?>
