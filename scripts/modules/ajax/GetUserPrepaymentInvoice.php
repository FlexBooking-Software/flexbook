<?php

class AjaxGetUserPrepaymentInvoice extends AjaxAction {
  
  private function _getUserPrepaymentInvoice($registration) {
    $ret = array();
    
    // kontrola, jestli ma pravo videt historii
    $o = new OUserRegistration($registration);
    $oData = $o->getData();
    // kdyz neni registrace prihlaseneho uzivatele, musi byt admin, nebo spravny poskytovatel
    if ($this->_app->auth->getUserId()!=$oData['user']) {
      if (!in_array($oData['provider'], $this->_app->auth->getAllowedProvider('credit_admin','array'))) throw new ExceptionUserTextStorage('error.accessDenied');
    }
    
    $s = new SCreditJournal;
    $s->addStatement(new SqlStatementBi($s->columns['userregistration'], $registration, '%s=%s'));
    $s->addStatement(new SqlStatementMono($s->columns['prepaymentinvoice_id'], '%s IS NOT NULL'));
    $s->addOrder(new SqlStatementDesc($s->columns['change_timestamp']));
    $s->setColumnsMask(array('creditjournal_id','amount','change_timestamp','flag','prepaymentinvoice_number'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $item = array(
                'id'              => $row['creditjournal_id'],
                'number'          => $row['prepaymentinvoice_number'],
                'timestamp'       => $this->_app->regionalSettings->convertDateTimeToHuman($row['change_timestamp']),
                'amount'          => $this->_app->regionalSettings->convertNumberToHuman($row['amount'],2),
                'currency'        => $this->_app->textStorage->getText('label.currency_CZK'),
                );
      
      $ret[] = $this->_request->convertOutput($item);
    }
    
    return $ret;
  }
  
  protected function _userRun() {
    $this->_result = array();
    
    $registration = null;
    if (($user=$this->_app->request->getParams('user'))&&($provider=$this->_app->request->getParams('provider'))) {
      $s = new SUserRegistration;
      $s->addStatement(new SqlStatementBi($s->columns['user'], $user, '%s=%s'));
      $s->addStatement(new SqlStatementBi($s->columns['provider'], $provider, '%s=%s'));
      $s->setColumnsMask(array('userregistration_id'));
      $res = $this->_app->db->doQuery($s->toString());
      if ($row = $this->_app->db->fetchAssoc($res)) $registration = $row['userregistration_id'];
    } else {
      $registration = $this->_app->request->getParams('registration');
    }
    
    if ($registration) {
      $this->_result = $this->_getUserPrepaymentInvoice($registration);
    }
  }
}

?>
