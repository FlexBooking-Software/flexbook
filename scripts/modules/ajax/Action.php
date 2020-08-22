<?php

class AjaxAction {
  protected $_app;
  
  protected $_request;
  protected $_params;
  
  protected $_resultType = 'json';
  protected $_result;
  
  public function __construct($request) {
    $this->_app = Application::get();
    
    $this->_request = $request;
    
    $this->_request->setResultType($this->_resultType);
    
    $this->_params = $this->_request->getParams();
    if (!isset($this->_params['prefix'])&&isset($this->_params['parentNode'])) $this->_params['prefix'] = $this->_params['parentNode'] . '_';
    if (!isset($this->_params['parentNode'])&&isset($this->_params['prefix'])) $this->_params['parentNode'] = substr($this->_params['prefix'],0,-1);
    
    if (isset($this->_params['language'])) $this->_app->language->setLanguage($this->_params['language']);

    if (isset($this->_params['provider'])) {
      $s = new SProvider;
      $s->addStatement(new SqlStatementBi($s->columns['provider_id'], $this->_params['provider'], '%s=%s'));
      $s->setColumnsMask(array('provider_id','customer_id','name'));
      $res = $this->_app->db->doQuery($s->toString());
      $row = $this->_app->db->fetchAssoc($res);
      $this->_app->auth->setActualProvider($row['provider_id'], $row['name'], $row['customer_id']);
    }

    $this->_initDefaultParams();

    $this->_modifyTextStorage();
  }

  protected function _modifyTextStorage() {
    if (isset($this->_params['provider'])&&$this->_params['provider']&&!is_array($this->_params['provider'])) {
      $s = new SProviderTextStorage;
      $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_params['provider'], '%s=%s'));
      $s->addStatement(new SqlStatementBi($s->columns['language'], $this->_app->language->getLanguage(), '%s=%s'));
      $s->addStatement(new SqlStatementMono($s->columns['new_value'], '%s IS NOT NULL'));
      $s->setColumnsMask(array('ts_key','new_value'));
      $res = $this->_app->db->doQuery($s->toString());
      if ($this->_app->db->getRowsNumber($res)) {
        $content ='';
        while ($row = $this->_app->db->fetchAssoc($res)) {
          $content .= sprintf("%s\t%s\n", $row['ts_key'], $row['new_value']);
        }
        $this->_app->textStorage->addResource($content);
      }
    }
  }

  protected function _initDefaultParams() { }
  
  public function setParams($key, $value) { $this->_params[$key] = $value; }
  
  public function getResult() { return $this->_result; }
  
  protected function _userRun() {}
  
  public function run() {
    $this->_userRun();
  
    $this->_request->setResult($this->_result);
  }
}

?>
