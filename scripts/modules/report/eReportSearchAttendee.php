<?php

class ModuleReportSearchAttendee extends ModuleReportSearch {
  
  public function __construct() {
    parent::__construct();
    
    $this->_formValidator = Validator::get('attendeeReport','AttendeeReportValidator');
  }

  private function _fillAttendee($columnsMask, $row, & $resultLine) {
    if (isset($row['person_user'])&&$row['person_user']) {
      if (in_array('person_firstname', $columnsMask)) $resultLine['person_firstname'] = $row['person_user_firstname'];
      if (in_array('person_lastname', $columnsMask)) $resultLine['person_lastname'] = $row['person_user_lastname'];
      if (in_array('person_email', $columnsMask)) $resultLine['person_email'] = $row['person_user_email'];
    }
  }
  
  protected function _search($data) {    
    $s = new SEventAttendee;

    $s->addStatement(new SqlStatementMono($s->columns['center'], sprintf('%%s IN (%s)', $this->_app->auth->getAllowedCenter('list'))));
    $s->addStatement(new SqlStatementMono($s->columns['substitute'], "%s='N'"));
    if ($data['failed']) {
      if ($data['failed'] == 'Y') $s->addStatement(new SqlStatementMono($s->columns['failed'], '%s IS NOT NULL'));
      if ($data['failed'] == 'N') $s->addStatement(new SqlStatementMono($s->columns['failed'], '%s IS NULL'));
    }
    if ($data['providerId']) $s->addStatement(new SqlStatementBi($s->columns['provider'], $data['providerId'], '%s=%s'));
    if ($data['centerId']) $s->addStatement(new SqlStatementBi($s->columns['center'], $data['centerId'], '%s=%s'));
    if ($data['tag']) $s->addStatement(new SqlStatementMono($s->columns['et_tag'], sprintf('%%s IN (%s)', $this->_app->db->escapeString($data['tag']))));
    if ($data['eventId']) $s->addStatement(new SqlStatementBi($s->columns['event'], $data['eventId'], '%s=%s'));
    if ($data['pastEventId']) $s->addStatement(new SqlStatementBi($s->columns['event'], $data['pastEventId'], '%s=%s'));
    if ($data['organiserId']) $s->addStatement(new SqlStatementBi($s->columns['organiser'], $data['organiserId'], '%s=%s'));
    if ($data['from']) $s->addStatement(new SqlStatementBi($s->columns['start'], $this->_app->regionalSettings->convertHumanToDate($data['from']), '%s>=%s'));
    if ($data['to']) $s->addStatement(new SqlStatementBi($s->columns['start'], $this->_app->regionalSettings->convertHumanToDate($data['to']), '%s<=%s'));
    if ($data['fulltext']) {
      $likeCond = $this->_app->db->escapeString(sprintf('%%%%%s%%%%', $data['fulltext']));
      $s->addStatement(new SqlStatementHexa($s->columns['person_firstname'], $s->columns['person_lastname'], $s->columns['person_email'],
        $s->columns['person_user_firstname'], $s->columns['person_user_lastname'], $s->columns['person_user_email'],
        sprintf("((%%s LIKE '%s') OR (%%s LIKE '%s') OR (%%s LIKE '%s') OR (%%s LIKE '%s') OR (%%s LIKE '%s') OR (%%s LIKE '%s'))",
          $likeCond, $likeCond, $likeCond, $likeCond, $likeCond, $likeCond)));
    }
    
    $columnsMask = $this->_getColumnsMask($s);
    
    $result = array($this->_getHeaderLine($columnsMask));
    
    $s->setColumnsMask($columnsMask);
    $s->addToColumnsMask(array('user_id','person_user','person_user_firstname','person_user_lastname','person_user_email'));
    $this->_addGroupToSelect($s);
    $s->addOrder(new SqlStatementAsc($s->columns['start']));
    
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      if (isset($row['subscription_time'])) $row['formatted__subscription_time'] = $this->_app->regionalSettings->convertDateTimeToHuman($row['subscription_time']);
      if (isset($row['start'])) $row['formatted__start'] = $this->_app->regionalSettings->convertDateTimeToHuman($row['start']);
      if (isset($row['end'])) $row['formatted__end'] = $this->_app->regionalSettings->convertDateTimeToHuman($row['end']);
      
      foreach ($columnsMask as $val) {
        //$newVal = str_replace(array('sum__','count__','list__'),'',$val);
        $resultLine[$val] = ifsetor($row[$val],'');
      }
      if (isset($row['person_user'])&&$row['person_user']) $this->_addUserAttributeToResult($resultLine, $row['person_user']);
      $this->_fillAttendee($columnsMask, $row, $resultLine);

      if (isset($row['formatted__subscription_time'])) $resultLine['formatted__subscription_time'] = $row['formatted__subscription_time'];
      if (isset($row['formatted__start'])) $resultLine['formatted__start'] = $row['formatted__start'];
      if (isset($row['formatted__end'])) $resultLine['formatted__end'] = $row['formatted__end'];
      
      $result[] = $resultLine;
    }
  
    return array('data'=>$result,'summary'=>array());
  }
}

?>
