<?php

class OProviderAccountType extends SqlObject {
  protected $_table = 'provideraccounttype';
  
  protected function _preDelete($ret=true) {
    $data = $this->getData();
    
    $app = Application::get();
    
    return parent::_preDelete($ret);
  }
}

?>