<?php

class ModuleAjaxRequest extends ExecModule {
  private $_encoding = 'UTF-8';
  
  private $_params = array();
  private $_action;
  
  private $_resultType = 'json';
  private $_result;
  
  public function __construct() {
    global $AJAX_SETTINGS;
    
    parent::__construct();
  
    if (isset($AJAX_SETTINGS['encoding'])) $this->_encoding = $AJAX_SETTINGS['encoding'];
  }
  
  public function getEncoding() { return $this->_encoding; }

  public function getTSText($key) {
    return $this->convertOutput($this->_app->textStorage->getText($key));
  }

  public function convertOutput($input) {
    if ($this->_encoding != $this->_app->getCharset()) {
      if (is_array($input)) {
        foreach ($input as $id=>$value) {
          $ret[$id] = @iconv($this->_app->getCharset(), $this->_encoding, $this->convertOutput($value));
        }
      } else $ret = iconv($this->_app->getCharset(), $this->_encoding, $input);
    } else $ret = $input;

    return $ret;
  }

  private function _readRequest() {
    foreach ($this->_app->request->getParams(null,array('get','post')) as $key=>$value) {
      if ($this->_encoding != $this->_app->getCharset()) $value = @iconv($this->_encoding,$this->_app->getCharset(),$value);
      $this->_params[$key] = $value;
    }
    
    if (isset($this->_params['action'])) $this->_action = $this->_params['action'];
  }
  
  public function getParams() { return $this->_params; }
  
  public function setResultType($resultType) { $this->_resultType = $resultType; }
  public function setResult($result) { $this->_result = $result; }

  protected function _userRun() {
    try {
      $this->_readRequest();
      $className = sprintf('Ajax%s', ucfirst($this->_action));
      
      global $DOWN;
      if (isset($DOWN)&&strcmp($className,'AjaxGuiCore')) {
        if (is_subclass_of($className, 'AjaxGuiAction')) $this->_result = array('error'=>false,'output'=>$DOWN);
        else $this->_result = array('error'=>true,'message'=>$DOWN);
      } else {
        if (class_exists($className)) {
          $action = new $className($this);
        } else throw new ExceptionUser('Unknown action!');
        
        $action->run();
      }
    } catch (Exception $e) {
      $this->_app->db->shutdownTransaction();
      
      $this->_result = array('error'=>true,'message'=>$this->convertOutput(str_replace('<br/>',"\n",$e->printMessage())));
    }
    
    $this->_app->messages->reset();
    
    header('Access-Control-Allow-Origin: *'); 
    if ($this->_resultType == 'json') {
      header('Content-type: text/json; charset='.$this->_encoding);
      
      #error_log($ret);
      #$this->_result['debug'] = $this->_app->messages->getMessages();
      #$this->_app->messages->reset();

      //$ret = $_GET['callback']."(".json_encode($this->_result).")";
      $ret = json_encode($this->_result);
      if (isset($this->_params['callback'])) {
        $ret = sprintf('%s(%s)', $this->_params['callback'], $ret);
      }

      echo $ret;
    } elseif ($this->_resultType == 'html') {
      header('Content-type: text/html; charset='.$this->_encoding);
      #adump($this->_result);die;
      
      if (isset($this->_result['error'])) echo $this->_result['message'];
      else echo $this->_result['output'];
    }
  }
}

?>
