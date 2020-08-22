<?php

class ModuleEventSubstituteSave extends ExecModule {

  private function _getEventAttendee($data) {
    $ret = array();
    
    foreach ($data['eventAttendeeFirstname'] as $index=>$value) {
      $ret[$index] = array(
        'user'        => $data['eventAttendeeUser'][$index],
        'firstname'   => $data['eventAttendeeFirstname'][$index],
        'lastname'    => $data['eventAttendeeLastname'][$index],
        'email'       => $data['eventAttendeeEmail'][$index],
      );
    }
    
    return $ret;
  }

  protected function _userRun() {
    $validator = Validator::get('eventSubstitute','EventSubstituteValidator');
    $validator->initValues();
    
    parseNextActionFromRequest($nextAction, $nextActionParams);

    switch ($nextAction) {
      case 'newUser':
        $this->_app->response->addParams(array('fromEventSubstitute'=>1));
        return 'eUserEdit';
      case 'reload':
        $this->_app->response->addParams(array('backwards'=>1));
        return 'eBack';
      default: break;
    }
    
    $validator->validateValues();
    $data = $validator->getValues();
    
    $eventId = $data['eventId'];    
    $bEvent = new BEvent($eventId?$eventId:null);
    $params = array(
        'userId'              => $data['userId'],
        'places'              => $data['places'],
        'attendeePerson'      => $this->_getEventAttendee($data),
        'attribute'           => $data['attribute'],
        'mandatory'           => $data['mandatory'],
        );
    if ($data['eventAttendeeId']) $params['attendeeId'] = $data['eventAttendeeId'];
    
    $name = $bEvent->saveSubstitute($params);
    
    $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.editEvent_substituteSaveOk'), $name));

    return 'eBack';
  }
}

?>
