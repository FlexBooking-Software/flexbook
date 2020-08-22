<?php

class OProviderInvoice extends SqlObject {
  protected $_table = 'providerinvoice';
  
  protected function _postDelete($ret=true) {
    $data = $this->getData();
    
    $o = new OFile($data['file']);
    $o->delete();
    
    return parent::_postDelete($ret);
  }
}

?>