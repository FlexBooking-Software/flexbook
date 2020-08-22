<?php

class OCustomer extends SqlObject {
  protected $_table = 'customer';
  
  protected function _postDelete($ret=true) {
    $data = $this->getData();
    if ($data['address']) {
      $oAddress = new OAddress($data['address']);
      $oAddress->delete();
    }
  
    if ($data['provider']) {
      $o = new OProvider($data['provider']);
      $o->delete();
    }
    
    return parent::_postDelete($ret);
  }
}

?>