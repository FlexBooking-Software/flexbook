<?php

class AjaxGuiAction extends AjaxAction {
  
  public function __construct($request) {
    #$this->_resultType = 'html';
    
    parent::__construct($request);

    if (!isset($this->_params['provider'])||!$this->_params['provider']) {
      throw new ExceptionUser('FLB error: invalid provider!');
    }
  }
  
  public function run() {
    $this->_userRun();
    
    #global $AJAX;
    #$this->_result['output'] = str_replace(array('{url}','{urlDir}'), array($AJAX['url'],dirname($AJAX['url'])), $this->_result['output']);
    $url = "http" . (($_SERVER['SERVER_PORT'] == 443) ? "s://" : "://") . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/ajax.php';
    $this->_result['output'] = str_replace(array('{url}','{urlDir}'), array($url,dirname($url)), $this->_result['output']);
    
    $this->_request->setResult($this->_result);
  }
}

class AjaxGuiAction2 extends AjaxGuiAction {
  protected $_id;
  protected $_class;
  
  protected $_data = array();
  
  protected $_guiParams = array();
  protected $_guiHtml = '';
  
  public function __construct($request) {
    parent::__construct($request);

    $this->_guiParams['parentNode'] = $this->_params['parentNode'];
    $this->_guiParams['prefix'] = $this->_params['prefix'];
    $this->_guiParams['language'] = $this->_app->language->getLanguage();
    
    $this->_guiParams['params'] = encodeAjaxParams($this->_params);
  }
  
  protected function _createTemplate() { }
  
  protected function _modifyTemplate() { } // tady se v potomcich muzou delat nejaky "prasarny" po vygenerovani sablony a ziskani vsech dat

  protected function _getEventHandling() {
    return sprintf("<script>
      $(document).ready(function() {
        var evt = $.Event('flbGuiReady');
        evt.gui = '%s';
        evt.parent = '%s';
        
        $(window).trigger(evt);
      });</script>", str_replace('Ajax','',get_class($this)), $this->_params['parentNode']);
  }

  protected function _getPhotoThumb($id, $photos) {
    if (!$photos) {
      $ret = sprintf('<img class="noPhoto" src="%simg/no_photo.png"/>', $this->_app->getBaseDir());
    } else {
      $ret = sprintf('<div id="%s_fotorama" class="fotorama" data-auto="false" data-nav="thumbs" data-allowfullscreen="true">', $id);
      foreach (explode(',',$photos) as $photo) {
        if ($photo) {
          $ret .= sprintf('<img src="%s"/>', $photo);
        }
      }
      $ret .= '</div>';
      $ret .= sprintf("<script>$(document).ready(function() { $('#%s_fotorama').fotorama(); });</script>", $id);
    }

    return $ret;
  }
  
  protected function _injectTemplate() {
    $p = $this->_params;
    unset($p['sessid']); unset($p['provider']); unset($p['extraDivContent']); unset($p['action']);
    
    $this->_guiHtml = sprintf('<input type="hidden" id="flb_guiType" value="%s"/><input type="hidden" id="flb_guiParams" value="{refreshParams}"/>%s',
                              lcfirst(str_replace('Ajax','',get_class($this))), $this->_guiHtml);

    $this->_guiHtml .= $this->_getEventHandling();

    // musim odstranit key: null, ktere muze vzniknout ifsetor(...)
    $this->_guiParams['refreshParams'] = htmlspecialchars(preg_replace('/,\s*"[^"]+":null|"[^"]+":null,?/', '', json_encode($p)));
  }
  
  protected function _userRun() {
    $this->_getData();
    
    $this->_createTemplate();
    $this->_modifyTemplate(); 
    $this->_injectTemplate();
    
    $gui = new GuiElement;
    $gui->setTemplateString($this->_guiHtml);
    
    foreach ($this->_guiParams as $key=>$value) {
      if (is_object($value)) {
        $gui->insert($value, $key);
      } else {
        $gui->insertTemplateVar($key, $value, false);
      }
    }
    
    $this->_result['output'] = sprintf('<div id="%s" class="flb_output %s">%s</div>', $this->_id, $this->_class, $gui->render());
    $this->_result['error'] = false;
  }
}


?>
