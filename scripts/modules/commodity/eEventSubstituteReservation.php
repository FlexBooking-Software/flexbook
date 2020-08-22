<?php

class ModuleEventSubstituteReservation extends ExecModule {

  protected function _userRun() {
    $validator = Validator::get('reservation','ReservationValidator',true);
    
    $id = $this->_app->request->getParams('id');
    $s = new SEventAttendee;
    $s->addStatement(new SqlStatementBi($s->columns['eventattendee_id'], $id, '%s=%s'));
    $s->setColumnsMask(array('event','name','description',
                             'user','firstname','lastname','fullname','email',
                             'places'));
    $res = $this->_app->db->doQuery($s->toString());
    $row = $this->_app->db->fetchAssoc($res);
    
    $subaccount = $firstname = $lastname = $email = array();
    $s1 = new SEventAttendeePerson;
    $s1->addStatement(new SqlStatementBi($s1->columns['eventattendee'], $id, '%s=%s'));
    $s1->setColumnsMask(array('eventattendeeperson_id','subaccount','firstname','lastname','email'));
    $res1 = $this->_app->db->doQuery($s1->toString());
    while ($row1 = $this->_app->db->fetchAssoc($res1)) {
      $subaccount[] = $row1['subaccount'];
      $firstname[] = $row1['firstname'];
      $lastname[] = $row1['lastname'];
      $email[] = $row1['email'];
    }
    $attribute = array();
    $s1 = new SEventAttendeeAttribute;
    $s1->addStatement(new SqlStatementBi($s1->columns['eventattendee'], $id, '%s=%s'));
    $s1->setColumnsMask(array('attribute','value'));
    $res1 = $this->_app->db->doQuery($s1->toString());
    while ($row1 = $this->_app->db->fetchAssoc($res1)) {
      $attribute[$row1['attribute']] = $row1['value'];
    }
    
    $validator->setValues(array(
          'fromSubstitute'          => $id,
          'commodity'               => 'event',
          'userId'                  => $row['user'],
          'userName'                => $row['fullname'],
          'userNameSelected'        => $row['fullname'],
          'userEmail'               => $row['email'],
          'eventId'                 => $row['event'],
          'eventName'               => $row['name'],
          'eventDescription'        => $row['description'],
          'eventPlaces'             => $row['places'],
          'eventAttendeeUser'       => $subaccount,
          'eventAttendeeFirstname'  => $firstname,
          'eventAttendeeLastname'   => $lastname,
          'eventAttendeeEmail'      => $email,
          'attribute'               => $attribute,
          ));
    
    return 'vReservationEdit';
  }
}

?>
