<?php

class ModuleUserTicketRefund extends ExecModule {

  protected function _userRun() {
    if (($user=$this->_app->request->getParams('user'))&&($ticket=$this->_app->request->getParams('id'))) {
      $b = new BUser($user);
      $name = $b->refundTicket($ticket);
      
      $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.refundUserTicket_ok'), $name));
    }
    
    $this->_app->response->addParams(array('backwards'=>1));
    return 'eBack';
  }
}

?>
