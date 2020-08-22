<?php

class AjaxSaveSubaccount extends AjaxAction {

  protected function _userRun() {
    if ($this->_app->session->getExpired()) throw new ExceptionUserTextStorage('error.ajax_sessionExpired');
    
    $params = array();
    if (isset($this->_params['id'])) $userId = $this->_params['id'];
    if (isset($this->_params['firstname'])) $params['firstname'] = $this->_params['firstname'];
    if (isset($this->_params['lastname'])) $params['lastname'] = $this->_params['lastname'];
    if (isset($this->_params['phone'])) $params['phone'] = $this->_params['phone'];
    if (isset($this->_params['email'])) $params['email'] = $this->_params['email'];
    if (!$userId) $params['parent'] = $this->_app->auth->getUserId();

    // atributy zakaznika
    if (isset($this->_params['attribute'])&&is_array($this->_params['attribute'])) {
      // musim ziskat atributy, ktere jdou ulozit
      $bUser = new BUser($this->_params['id']?$this->_params['id']:null);
      $userAttributes = $bUser->getAttribute($this->_params['provider'],$this->_app->language->getLanguage(),'SUBACCOUNT',array('CREATEONLY'));

      $params['attributeLanguage'] = $this->_app->language->getLanguage();
      $params['attributeConverted'] = false;
      $params['attributeValidation'] = (isset($this->_params['checkAttributeMandatory'])&&$this->_params['checkAttributeMandatory'])?'exact':true;
      $params['attribute'] = array();
      foreach ($this->_params['attribute'] as $attr) {
        if (!strcmp($attr['value'],'__no_change__')) continue;
        if (!isset($userAttributes[$attr['id']])||(!strcmp($userAttributes[$attr['id']]['restricted'],'CREATEONLY')&&$userAttributes[$attr['id']]['value'])) continue;

        $params['attribute'][$attr['id']] = $attr['value'];
      }
    }
    #adump($params);die;
    
    $b = new BUser(ifsetor($userId));
    $b->save($params);
    
    $o = new OUser($b->getId());
    $oData = $o->getData();
    
    $this->_result = array('id'=>$b->getId(),'name'=>$oData['firstname'].' '.$oData['lastname']);
  }
}

?>
