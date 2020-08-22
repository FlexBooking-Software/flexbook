<?php

class InPageModule extends DocumentModule {
  
  private function _insertLoginForm($data) {
    if (!$this->_app->auth->getUserId()) {
      $template = '
          <form class="login" action="inpage.php">
            {%sessionInput%}
            <label for="fi_login_username">{__label.inpage_login_username}:</label>
            <input class="mediumText" type="text" name="username" value="" />
            <label for="fi_password">{__label.login_password}:</label>
            <input class="mediumText" type="password" name="password" value="" />
            <input class="button" type="submit" name="action_eInPageLogin" value="{__button.login_login}" />
            <input class="button" type="submit" name="action_eInPageRegistration" value="{__button.login_registrate}" />
          </form>
          <hr/>
          ';
    } else {
      $template = sprintf('<form class="login">
          <span class="name">%s</span>&nbsp;&nbsp;&nbsp;[<a href="inpage.php?action=eInPageLogout%s">%s</a>] [<a href="inpage.php?action=vInPageCzechTourismReservation%s">%s</a>]
          </form><hr/>', $this->_app->auth->getFullname(), $this->_app->session->getTagForUrl(),
          $this->_app->textStorage->getText('button.loginForm_logout'),
          $this->_app->session->getTagForUrl(), $this->_app->textStorage->getText('button.inpage_reservation'));
    }
    
    $this->insert(new GuiElement(array('template'=>$template)), 'loginForm');
  }
  
  protected function _insertDescription() { $this->insertTemplateVar('description', ''); }
  
  protected function _projectInsert() {
    global $HTTPS;
    if ($HTTPS) { Application::get()->setProtocol('https'); }

    $this->addJavascriptFile('script.js');
    
    // jQuery
    $this->_app->document->addJavascriptFile('jq/jquery.js');
    $this->_app->document->addJavascriptFile('jq/jquery-ui.js');
    $this->_app->document->addCssFile('jq/jquery-ui.css');   
    $this->_app->document->addJavascriptFile('jq/jquery.datetimepicker.js');
    $this->_app->document->addCssFile('jq/jquery.datetimepicker.css');
    $this->_app->document->addJavascriptFile('jq/jquery.ui.combogrid.js');
    $this->_app->document->addCssFile('jq/jquery.ui.combogrid.css');
    $this->_app->document->addJavascriptFile('jq/fullcalendar.js');
    $this->_app->document->addCssFile('jq/fullcalendar.css');
    
    $this->insert(new GuiMessages, 'message');
    $this->insert(new GuiDialog, 'dialog');
    
    $validator = Validator::get('inpage', 'InPageValidator');
    $data = $validator->getValues();
    
    if ($validator->getVarValue('providerId')=='14') {
      $this->setTemplateString('
            <div class="page">
              {message}{dialog}
              <a href="http://www.flexbook.cz/inpage.php?id=czechtourism{%sessionUrl%}"><img src="img/ct_logo.png"/></a>
              {loginForm}
              <div class="description">{description}</div>
              {children}
              <div class="cleaner">&nbsp;</div>
            </div>');
      
      $this->setTitle('CzechTourism');
      $this->addCssFile('czechtourism_style.css');
    } else {
      $this->setTemplateString('
            {message}
            <b>{title}</b>
            <br/><br/>
            {loginForm}
            <hr/>
            {children}
            <hr/>
            <div class="cleaner">&nbsp;</div>');
      
      $this->setTitle('FlexBook InPage');
      $this->addCssFile('inpage_style.css');
    }
    
    if (!$providerName = $validator->getVarValue('providerName')) {
      $providerName = 'NO PROVIDER!';
    }
    $this->insertTemplateVar('title', $providerName);
        
    $this->_insertLoginForm($data);
    $this->_insertDescription();
  }
}

?>
