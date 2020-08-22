<?php

class OCenter extends SqlObject {
  protected $_table = 'center';
  
  protected function _postDelete($ret=true) {
    $data = $this->getData();
    if ($data['address']) {
      $oAddress = new OAddress($data['address']);
      $oAddress->delete();
    }
    
    return parent::_postDelete($ret);
  }
}

?>