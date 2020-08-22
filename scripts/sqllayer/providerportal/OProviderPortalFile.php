<?php

class OProviderPortalFile extends SqlObject {
  protected $_table = 'providerportalfile';
  
  protected function _postDelete($ret=true) {
    $data = $this->getData();
    
    $o = new OFile($date['file']);
    $o->delete();
    
    return parent::_postDelete($ret);
  }
}

?>