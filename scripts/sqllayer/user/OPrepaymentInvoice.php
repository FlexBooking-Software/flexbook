<?php

class OPrepaymentInvoice extends SqlObject {
  protected $_table = 'prepaymentinvoice';
  
  protected function _preDelete($ret=true) {
    $data = $this->getData();

    return parent::_preDelete($ret);
  }
  
  protected function _postDelete($ret=true) {
    $data = $this->getData();

    $oFile = new OFile($data['content']);
    $oFile->delete();
    
    return parent::_postDelete($ret);
  }
}

?>