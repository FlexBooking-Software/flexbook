<?php

class ModuleEventSubstituteEdit extends ExecModule {

  protected function _userRun() {
    $validator = Validator::get('eventSubstitute','EventSubstituteValidator',true);
    $attendee = $this->_app->request->getParams('attendeeId');
    $event = $this->_app->request->getParams('event');
    $validator->loadData($attendee,$event);

    return 'vEventSubstituteEdit';
  }
}

?>
