<?php

class EventSubstituteValidator extends Validator {

  protected function _insert() {
    $app = Application::get();

    $this->addValidatorVar(new ValidatorVar('eventAttendeeId'));
    $this->addValidatorVar(new ValidatorVar('providerId'));
    $this->addValidatorVar(new ValidatorVar('userId', true));
    $this->addValidatorVar(new ValidatorVar('mandatory'));
    $this->addValidatorVar(new ValidatorVar('userName'));
    $this->addValidatorVar(new ValidatorVar('places', true, new ValidatorTypeInteger(100)));
    $this->addValidatorVar(new ValidatorVar('eventId', true));
    $this->addValidatorVar(new ValidatorVar('eventName'));
    $this->addValidatorVar(new ValidatorVar('eventDescription'));
    $this->addValidatorVar(new ValidatorVar('eventPrice'));
    $this->addValidatorVar(new ValidatorVar('eventCoAttendees'));
    $this->addValidatorVar(new ValidatorVar('eventReservationMaxAttendees'));
    $this->addValidatorVar(new ValidatorVarArray('eventAttendeeUser'));
    $this->addValidatorVar(new ValidatorVarArray('eventAttendeeFirstname'));
    $this->addValidatorVar(new ValidatorVarArray('eventAttendeeLastname'));
    $this->addValidatorVar(new ValidatorVarArray('eventAttendeeEmail'));
    
    $this->addValidatorVar(new ValidatorVarArray('attribute'));
    
    $this->getVar('userId')->setLabel($app->textStorage->getText('label.editEventSubstitute_user'));
    $this->getVar('eventId')->setLabel($app->textStorage->getText('label.editEventSubstitute_event'));
    $this->getVar('places')->setLabel($app->textStorage->getText('label.editEventSubstitute_places'));
  }

  public function loadData($attendee,$event) {
    $app = Application::get();
    
    if ($attendee) {
      $bEvent = new BEvent($event);
      $data = $bEvent->getSubstitute($attendee,true,true);
      
      if (isset($data['eventAttendeePerson'])) {
        foreach ($data['eventAttendeePerson'] as $person) {
          $data['eventAttendeeUser'][] = $person['user'];
          $data['eventAttendeeFirstname'][] = $person['firstname'];
          $data['eventAttendeeLastname'][] = $person['lastname'];
          $data['eventAttendeeEmail'][] = $person['email'];
        }
      }
      
      foreach ($data['attribute'] as $id=>$attr) {
        $data['attribute'][$id] = $attr['value'];
      }
    } else {
      $data['eventId'] = $event;
      $data['places'] = 1;
      
      $s = new SEvent;
      $s->addStatement(new SqlStatementBi($s->columns['event_id'], $event, '%s=%s'));
      $s->setColumnsMask(array('provider','name','description','price','max_coattendees','reservation_max_attendees'));
      $res = $app->db->doQuery($s->toString());
      $row = $app->db->fetchAssoc($res);
      $data['providerId'] = $row['provider'];
      $data['eventName'] = $row['name'];
      $data['eventDescription'] = $row['description'];
      $data['eventPrice'] = $row['price'];
      $data['eventCoAttendees'] = $row['max_coattendees'];
      $data['eventReservationMaxAttendees'] = $row['reservation_max_attendees'];
    }
    
    $this->setValues($data);
  }
}

?>
