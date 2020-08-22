<?php

class AjaxSaveUser extends AjaxAction {

  protected function _userRun() {
    if ($this->_app->session->getExpired()) throw new ExceptionUserTextStorage('error.ajax_sessionExpired');
    
    $params = array();
    if (isset($this->_params['firstname'])) $params['firstname'] = $this->_params['firstname'];
    if (isset($this->_params['lastname'])) $params['lastname'] = $this->_params['lastname'];
    if (isset($this->_params['street'])) $params['street'] = $this->_params['street'];
    if (isset($this->_params['city'])) $params['city'] = $this->_params['city'];
    if (isset($this->_params['postalCode'])) $params['postalCode'] = $this->_params['postalCode'];
    if (isset($this->_params['state'])) $params['state'] = $this->_params['state'];
    if (isset($this->_params['phone'])) $params['phone'] = $this->_params['phone'];
    if (isset($this->_params['email'])) $params['email'] = $this->_params['email'];
    if (isset($this->_params['facebookId'])) $params['facebookId'] = $this->_params['facebookId'];
    if (isset($this->_params['googleId'])) $params['googleId'] = $this->_params['googleId'];
    if (isset($this->_params['twitterId'])) $params['twitterId'] = $this->_params['twitterId'];
    
    if (isset($this->_params['provider'])&&(isset($this->_params['registration']))) {
      if (!$this->_params['registration']) throw new ExceptionUserTextStorage('error.ajax_profile_registrationError');
      
      $params['registration'] = array(array(
          'registrationId'  => $this->_params['registration'],
          'advertising'     => ifsetor($this->_params['advertising'],'Y'),
          'providerId'      => $this->_params['provider'],
          ));
    }
    
    // atributy zakaznika
    if (isset($this->_params['attribute'])&&is_array($this->_params['attribute'])) {
      // musim ziskat atributy, ktere jdou ulozit
      $bUser = new BUser($this->_app->auth->getUserId()?$this->_app->auth->getUserId():null);
      $userAttributes = $bUser->getAttribute($this->_params['provider'],$this->_app->language->getLanguage(),'USER',array('CREATEONLY'));

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
    
    $b = new BUser($this->_app->auth->getUserId()?$this->_app->auth->getUserId():null);
    $b->save($params);
    
    $o = new OUser($b->getId());
    $oData = $o->getData();
    
    $this->_result = array('id'=>$b->getId(),'name'=>$oData['firstname'].' '.$oData['lastname']);
  }
}

?>
