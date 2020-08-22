<?php

class XMLAction {
  protected $_app;
  
  protected $_reqDoc;
  protected $_respDoc; 
  protected $_respCode;
  protected $_respMessage;
  protected $_respDesc;
  
  protected $_module;
  
  public function __construct($request, $response, $module) {
    $this->_app = Application::get();
    
    $this->_reqDoc = $request;
    $this->_respDoc = $response;
    $this->_respCode = 0;
    $this->_respMessage = null;
    
    $this->_module = $module;
    
    $response = $this->_respDoc->getElementsByTagName('response');
    $dataNode = $this->_respDoc->createElement('data');
    $response->item(0)->appendChild($dataNode);
  }
  
  public function getResponse() {
    $this->_prepareResponse();
    
    return array($this->_respDoc, $this->_respCode, $this->_respMessage, $this->_respDesc);
  }
  
  protected function _prepareResponse() { }
  
  protected function _getResponseDataTag() {
    $data = $this->_respDoc->getElementsByTagName('data');
    return $data->item(0);
  }
  
  // vrati elementy dle name, ale pouze prime potomky (non-recursive)
  protected function _getElementsByTagName($element, $name) {
    if (!$element instanceof DOMElement) return array();
    
    $result = array();
    foreach ($element->childNodes as $child) {
      if (($child instanceof DOMElement)&&($child->tagName == $name)) {
        $result[] = $child;
      }
    }
    return $result;
  }

  protected function _createDebugRecord($content) { $this->_module->createDebugRecord($content); }
  
  protected function _getTSText($key) { return $this->_module->getTSText($key); }

  protected function _convertOutput($text) { return $this->_module->convertOutput($text); }
  
  protected function _convertInput($text) {
    $encoding = $this->_module->getXMLEncoding();
    
    $text = trim($text);
    $text = strtolower($text);
    //$this->_createDebugRecord($text);
    if ($encoding == 'ISO-8859-2') $ret = $text;
    else $ret = @iconv($encoding, 'ISO-8859-2//IGNORE', $text);
    
    // kontrola na povolene znaky v kodovani
    if ($error=getEncodingError($ret)) {
      $logString = sprintf('XML (%s): Bad character %s (%s) at %s in "%s"', getmypid(), $ret[$error['position']], $error['ordValue'], $error['position']+1, $ret);
      $this->_createDebugRecord($logString);
      throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.xml_message_invalidEncoding'), $ret));
    }
    
    return $ret;
  }
}

?>