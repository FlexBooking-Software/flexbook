<?php

class OCreditnote extends SqlObject {
  protected $_table = 'creditnote';
  
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