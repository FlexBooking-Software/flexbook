<?php

class AjaxGetUserDetail extends AjaxAction {

  protected function _userRun() {
    $this->_result = array();
    
    if ($this->_app->session->getExpired()||!$this->_app->auth->getUserId()) return; #throw new ExceptionUserTextStorage('error.ajax_sessionExpired');
    
    if (isset($this->_params['user'])&&$this->_params['user']) {
      // kontrola, jestli muze ziskavat informace o uzivateli
      if ($this->_params['user']!=$this->_app->auth->getUserId()) {
        if (!$this->_app->auth->haveRight('credit_admin',$this->_params['provider'])&&
          !$this->_app->auth->haveRight('organiser',$this->_params['provider'])&&
          !$this->_app->auth->haveRight('power_organiser',$this->_params['provider'])) throw new ExceptionUserTextStorage('error.accessDenied');
      }
      
      $s = new SUser;
      $s->addStatement(new SqlStatementBi($s->columns['user_id'], $this->_params['user'], '%s=%s'));
      $s->addStatement(new SqlStatementBi($s->columns['registration_provider'], ifsetor($this->_params['provider']), '%s=%s'));
      $s->setColumnsMask(array('user_id','firstname','lastname','email','phone','registration_credit'));
      $res = $this->_app->db->doQuery($s->toString());
      if ($row = $this->_app->db->fetchAssoc($res)) {
        $this->_result = array(
                  'id'          => $row['user_id'],
                  'firstname'   => $row['firstname'],
                  'lastname'    => $row['lastname'],
                  'email'       => $row['email'],
                  'phone'       => $row['phone'],
                  'credit'      => $row['registration_credit'],
                  'ticket'      => array(),
                  );
        
        if (isset($this->_params['resource'])||isset($this->_params['event'])) {
          $center = null;
          if (isset($this->_params['resource'])) {
            $s = new SResource;
            $s->addStatement(new SqlStatementBi($s->columns['resource_id'], $this->_params['resource'], '%s=%s'));
          } else {
            $s = new SEvent;
            $s->addStatement(new SqlStatementBi($s->columns['event_id'], $this->_params['event'], '%s=%s'));
          }
          $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
          $s->setColumnsMask(array('center','all_tag'));
          $res = $this->_app->db->doQuery($s->toString($res));
          if ($row = $this->_app->db->fetchAssoc($res)) {
            $center = $row['center'];
            $tag = explode(',',$row['all_tag']);
            
            $u = new BUser($this->_params['user']);
            $this->_result['ticket'] = $u->getAvailableTicket($this->_params['provider'], true, $center, $tag, ifsetor($this->_params['price'],0));
          }
        }
      }
    }
  }
}

?>
