<?php

class ModuleReportSearchCredit extends ModuleReportSearch {
  
  public function __construct() {
    parent::__construct();
    
    $this->_formValidator = Validator::get('creditReport','CreditReportValidator');
  }
  
  protected function _search($data) {  
    // todle by se melo nejak pridat
    /*$s = new SReservationJournal;
    $s->addStatement(new SqlStatementMono($s->columns['action'], "%s='PAY'"));
    $s->addStatement(new SqlStatementMono($s->columns['user'], '%s IS NULL'));
    $s->addStatement(new SqlStatementMono($s->columns['cancelled'], '%s IS NULL'));
    if ($data['providerId']) $s->addStatement(new SqlStatementBi($s->columns['provider'], $data['providerId'], '%s=%s'));
    if ($data['userId']) $s->addStatement(new SqlStatementBi($s->columns['change_user'], $data['userId'], '%s=%s'));
    if ($data['from']) $s->addStatement(new SqlStatementBi($s->columns['change_timestamp'], $this->_app->regionalSettings->convertHumanToDate($data['from']), '%s>=%s'));
    if ($data['to']) $s->addStatement(new SqlStatementBi($s->columns['change_timestamp'], $this->_app->regionalSettings->convertHumanToDate($data['to']), '%s<=%s'));
    $s->setColumnsMask(array('user_id','firstname','lastname','email','change_timestamp','total_price'));
    $res = $this->_app->db->doQuery($s->toString());
    
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $result[] = array(
          $row['firstname'],$row['lastname'],$row['email'],
          $this->_app->regionalSettings->convertDateTimeToHuman($row['change_timestamp']),$row['total_price'],
          );
    }*/
  
    $s = new SCreditJournal;
    // hotovostni platby jsou bud dobyti kreditu pro registraci nebo platba/refund za anonymni rezervaci
    $s->addStatement(new SqlStatementQuad($s->columns['type'], $s->columns['userregistration'], $s->columns['type'], $s->columns['userregistration'],
                                          "((%s='CASH' AND %s IS NOT NULL) OR (%s='RESERVATION' AND %s IS NULL))"));
    if ($data['providerId']) $s->addStatement(new SqlStatementBi($s->columns['provider'], $data['providerId'], '%s=%s'));
    if ($data['userId']) $s->addStatement(new SqlStatementBi($s->columns['change_user'], $data['userId'], '%s=%s'));
    if ($data['from']) $s->addStatement(new SqlStatementBi($s->columns['change_timestamp'], $this->_app->regionalSettings->convertHumanToDateTime($data['from']), '%s>=%s'));
    if ($data['to']) $s->addStatement(new SqlStatementBi($s->columns['change_timestamp'], $this->_app->regionalSettings->convertHumanToDateTime($data['to']), '%s<=%s'));

    if ($this->_app->auth->haveRight('report_reception', $this->_app->auth->getActualProvider())) $s->addStatement(new SqlStatementBi($s->columns['change_user'], $this->_app->auth->getUserId(), '%s=%s'));
    
    $summarySelect = clone $s;
     
    $columnsMask = $this->_getColumnsMask($s);
    
    $result = array($this->_getHeaderLine($columnsMask));
    
    $s->setColumnsMask($columnsMask);
    $s->addToColumnsMask(array('user_id','type','amount','flag','note','user_fullname'));
    $this->_addGroupToSelect($s);
    $s->addOrder(new SqlStatementAsc($s->columns['change_timestamp']));
    
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      if (isset($row['change_timestamp'])) $row['formatted__change_timestamp'] = $this->_app->regionalSettings->convertDateTimeToHuman($row['change_timestamp']);
      if (in_array('description',$columnsMask)) {
        if (!strcmp($row['type'],'CASH')) {
          if ($row['amount']>0) $row['description'] = sprintf($this->_app->textStorage->getText('label.report_credit_resultDescription_user'), $row['user_fullname']);
          else $row['description'] = sprintf($this->_app->textStorage->getText('label.report_credit_resultDescription_userRefund'), $row['user_fullname']);
        } elseif (!strcmp($row['flag'],'C')) $row['description'] = sprintf($this->_app->textStorage->getText('label.report_credit_resultDescription_reservationPayment'), $row['note']);
        elseif (!strcmp($row['flag'],'D')) $row['description'] = sprintf($this->_app->textStorage->getText('label.report_credit_resultDescription_reservationRefund'), $row['note']);
      }
      
      foreach ($columnsMask as $val) {
        //$newVal = str_replace(array('sum__','count__','list__'),'',$val);
        $resultLine[$val] = ifsetor($row[$val],'');
      }
      $this->_addUserAttributeToResult($resultLine, $row['user_id']);

      if (isset($row['formatted__change_timestamp'])) $resultLine['formatted__change_timestamp'] = $row['formatted__change_timestamp'];
      
      $result[] = $resultLine;
    }
    #adump($result);die;
    
    $summary = array();
    $summarySelect->addColumn(new SqlColumn(false, new SqlStatementMono($summarySelect->columns['amount'], 'SUM(%s)'), 'sum__amount', true));
    $summarySelect->setColumnsMask(array('sum__amount'));
    $res = $this->_app->db->doQuery($summarySelect->toString());
    if ($row = $this->_app->db->fetchAssoc($res)) {
      $summary[] = sprintf($this->_app->textStorage->getText('label.report_credit_resultSummaryPrice'), $this->_app->regionalSettings->convertNumberToHuman($row['sum__amount']?$row['sum__amount']:0,2));
    }
  
    return array('data'=>$result,'summary'=>$summary);
  }
}

?>
