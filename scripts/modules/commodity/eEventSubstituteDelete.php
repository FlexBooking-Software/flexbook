<?php

class ModuleEventSubstituteDelete extends ExecModule {

  protected function _userRun() {
    $event = $this->_app->request->getParams('event');
    $id = $this->_app->request->getParams('id');
    if ($id&&$event) {
      $bEvent = new BEvent($event);
      $name = $bEvent->deleteSubstitute(array('eventAttendeeId'=>$id));
    
      $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.listEvent_substituteDeleteOk'), $name));
    }

    $this->_app->response->addParams(array('backwards'=>1));
    return 'eBack';
  }
}

?>
