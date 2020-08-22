<?php

class ProjectModule extends DocumentModule {
  
  protected function _projectInsert() {
    $this->_app->document->addMeta(array('http-equiv'=>'X-UA-Compatible','content'=>'IE=11'));
    
    global $HTTPS;
    if ($HTTPS) { Application::get()->setProtocol('https'); }

    $this->setTitle(Application::get()->textStorage->getText('label.browserTitle'));

    $this->addCssFile('style.css?ver=2020-06-03-001');
    $this->addJavascriptFile('script.js?ver=2020-05-07-001');
    
    // jQuery
    $this->_app->document->addJavascriptFile('jq/jquery.js');
    $this->_app->document->addJavascriptFile('jq/jquery.cookie.js');
    $this->_app->document->addJavascriptFile('jq/jquery-ui.js');
    $this->_app->document->addCssFile('jq/jquery-ui.css');
    $this->_app->document->addJavascriptFile('jq/jquery.datetimepicker.js');
    $this->_app->document->addCssFile('jq/jquery.datetimepicker.css');
    $this->_app->document->addJavascriptFile('jq/jquery.ui.combogrid.js');
    $this->_app->document->addCssFile('jq/jquery.ui.combogrid.css');
    $this->_app->document->addJavascriptFile('jq/moment.min.js');
    $this->_app->document->addJavascriptFile('jq/fullcalendar-3.10.0.js');
    $this->_app->document->addCssFile('jq/fullcalendar-3.10.0.css');
    $this->_app->document->addJavascriptFile('jq/locale-all.js');
    $this->_app->document->addJavascriptFile('jq/scheduler-1.9.4.js');
    $this->_app->document->addCssFile('jq/scheduler-1.9.4.css');
    $this->_app->document->addJavascriptFile('jq/jquery.autocomplete.js');
    $this->_app->document->addJavascriptFile('jq/jquery.form.js');
    $this->_app->document->addJavascriptFile('jq/jquery.uploadfile.js');
    $this->_app->document->addCssFile('jq/jquery.uploadfile.css');
    $this->_app->document->addJavascriptFile('jq/jquery.tokeninput.js');
    $this->_app->document->addCssFile('jq/token-input-facebook.css');
    #$this->_app->document->addJavascriptFile('jq/jquery-sortable.js');
    $this->_app->document->addCssFile('jq/intlTelInput.css');
    $this->_app->document->addJavascriptFile('jq/intlTelInput.min.js');
    $this->_app->document->addJavascriptFile('jq/jquery-ui.multidatespicker.js');
    $this->_app->document->addCssFile('jq/jquery-ui.multidatespicker.css');

    if ($cookieTab = $this->_app->session->get('updateCookieTab')) {
      list($tabName,$tabValue) = explode('=>', $cookieTab);
      $this->_app->document->addJavascript(sprintf("$(document).ready(function() { $.cookie('%s', %s); });", $tabName, $tabValue),true);

      $this->_app->session->remove('updateCookieTab');
    }

    if ($this->_app->getAction() == 'vLogin') {
      $this->setTemplateFile(dirname(__FILE__).'/LoginModule.html');
      
      $this->_app->document->addJavascript("
          function showStatic(type) {
            $('.staticText').hide();
            $('#'+type).css('left',$('#loginForm').position().left+60);
            $('#'+type).show();
          }
          
          function closeStatic(type) {
            $('#'+type).hide();
          }
        ");
    } else {
      $this->setTemplateFile(dirname(__FILE__).'/ProjectModule.html');      
    }
    
    global $NODE_ID;
    $this->insertTemplateVar('fbTitle', $NODE_ID);
    
    $this->insert(new GuiUserInfo, 'userInfo');
    $this->insert(new GuiProjectMenu, 'menu');
    $this->insert(new GuiMessages, 'messageList');
    $this->insert(new GuiDialog, 'dialog');
  }
}

?>
