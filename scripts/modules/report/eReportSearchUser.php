<?php

class ModuleReportSearchUser extends ModuleReportSearch {
  
  public function __construct() {
    parent::__construct();
    
    $this->_formValidator = Validator::get('userReport','UserReportValidator');
  } 
  
  protected function _search($data) {
    $s = new SUser;
    
    if ($data['providerId']) $s->addStatement(new SqlStatementBi($s->columns['registration_provider'], $data['providerId'], '%s=%s'));
    if ($data['type']) {
      if (!strcmp($data['type'],'PRIMARY')) $s->addStatement(new SqlStatementMono($s->columns['parent_user'], '%s IS NULL'));
      if (!strcmp($data['type'],'SUBACCOUNT')) $s->addStatement(new SqlStatementMono($s->columns['parent_user'], '%s IS NOT NULL'));
    }
    if ($data['name']) $s->addStatement(new SqlStatementMono($s->columns['fullname'], sprintf("%%s LIKE '%%%%%s%%%%'", $this->_app->db->escapeString($data['name']))));
    if ($data['registrationFrom']) $s->addStatement(new SqlStatementBi($s->columns['registration_timestamp'], $this->_app->regionalSettings->convertHumanToDate($data['registrationFrom']), '%s>=%s'));
    if ($data['registrationTo']) $s->addStatement(new SqlStatementBi($s->columns['registration_timestamp'], $this->_app->regionalSettings->convertHumanToDate($data['registrationTo']), '%s<=%s'));
    
    $columnsMask = $this->_getColumnsMask($s);
    
    $result = array($this->_getHeaderLine($columnsMask));
    
    $s->setColumnsMask($columnsMask);
    $s->addToColumnsMask(array('user_id'));
    $this->_addGroupToSelect($s);
    $s->addOrder(new SqlStatementAsc($s->columns['lastname']));
    
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      if (isset($row['registration_timestamp'])) $row['formatted__registration_timestamp'] = $this->_app->regionalSettings->convertDateTimeToHuman($row['registration_timestamp']);
      if (isset($row['validated'])) $row['formatted__validated'] = $this->_app->regionalSettings->convertDateTimeToHuman($row['validated']);
      
      foreach ($columnsMask as $val) {
        //$newVal = str_replace(array('sum__','count__','list__'),'',$val);
        $resultLine[$val] = ifsetor($row[$val],'');
      }
      $this->_addUserAttributeToResult($resultLine, $row['user_id']);

      if (in_array('parent_user', $columnsMask)) {
        if ($row['parent_user']) $resultLine['parent_user'] = $this->_app->textStorage->getText('label.report_user_typeSUBACCOUNT');
        else $resultLine['parent_user'] = $this->_app->textStorage->getText('label.report_user_typePRIMARY');
      }

      if (isset($row['formatted__registration_timestamp'])) $resultLine['formatted__registration_timestamp'] = $row['formatted__registration_timestamp'];
      if (isset($row['formatted__validated'])) $resultLine['formatted__validated'] = $row['formatted__validated'];
      
      $result[] = $resultLine;
    }
  
    return array('data'=>$result,'summary'=>array());
  }
}

?>
