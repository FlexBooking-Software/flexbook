<?php

class AjaxGetUserSubaccount extends AjaxAction {

  protected function _userRun() {  
    if (isset($this->_params['user'])) {
    	$bUser = new BUser($this->_params['user']);
    	$subAccount = $bUser->getSubaccount();
      foreach ($subAccount as $account) {
				$this->_result[] = $this->_request->convertOutput($account);
			}
    }
  }
}

?>