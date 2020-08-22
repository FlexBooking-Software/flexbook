<?php

class GuiUserInfo extends GuiElement {

  protected function _userRender() {
    if ($this->_app->auth->getUserId()) {
      if ($this->_app->auth->isAdministrator()) {
        $this->setTemplateString('
                      <ul class="userInfo">
                        <li><a href="index.php?action=eLogout{%sessionUrl%}">{__button.loginForm_logout}</a></li>
                        <li class="separator">|</li>
                        <li><a href="index.php?action=ePasswordEdit{%sessionUrl%}">{__button.changePassword_change}</a></li>
                        <li class="separator">|</li>
                        <li class="highlighted">{username}</li>
                      </ul>');
      } else {
        $this->setTemplateString('
                      <ul class="userInfo">
                        <li><a href="index.php?action=eLogout{%sessionUrl%}">{__button.loginForm_logout}</a></li>
                        <li class="separator">|</li>
                        <li class="highlighted"><a href="index.php?action=eUserProfile{%sessionUrl%}">{username}</a></li>
                        <li class="highlighted provider">{provider}</li>
                        <li class="center">
                          <form action="{%basefile%}" method="post" id="center_form">
                            <input type="hidden" name="{%sessname%}" value="{%sessid%}" />
                            {center}
                            <input id="fi_centerChange" type="submit" name="action_eChangeCenter" value="{__button.ok}" />
                          </form>
                        </li>
                      </ul>');
                      
        $providerGui = new GuiElement(array('template'=>sprintf('
                        <span class="provider">%s</span>',
          $this->_app->auth->haveRight('provider_edit', $this->_app->auth->getActualProvider())?'<a href="{%basefile%}?action=eMyCustomerEdit&id={id}{%sessionUrl%}">{name}</a>':'<a href="#">{name}</a>')));
        $providerGui->insertTemplateVar('id', $this->_app->auth->getActualProviderCustomer());
        $providerGui->insertTemplateVar('name', $this->_app->auth->getActualProviderName());
        
        if ($this->_app->auth->isProviderMultiple()) {
          $gui = new GuiElement(array('template'=>'
                       <span id="providerLabel">{provider}&nbsp;</span>
                       <span id="providerChange" onclick="$(\'#providerLabel\').css({display:\'none\'});$(\'#providerChange\').css({display:\'none\'});$(\'#providerForm\').css({display:\'inline\'});"><img src="img/button_switch.png"></span> 
                       <span id="providerForm" class="provider" style="display:none;">
                         <form action="{%basefile%}" method="post" id="provider_form">
                            <input type="hidden" name="{%sessname%}" value="{%sessid%}" />
                            {select}
                            <input type="submit" name="action_eChangeProvider" value="{__button.ok}" />
                         </form>
                       </span>
                    '));
          $gui->insert($providerGui, 'provider');
          
          $s = new SProvider;
          $s->addStatement(new SqlStatementMono($s->columns['provider_id'], sprintf('%%s IN (%s)', $this->_app->auth->getAllowedProvider(null,'list'))));
          $s->setColumnsMask(array('provider_id','name'));
          $ds = new SqlDataSource(new DataSourceSettings, $s);
          $gui->insert(new GuiFormSelect(array(
              'name'        => 'provider',
              'dataSource'  => $ds,
              'value'       => $this->_app->auth->getActualProvider(),
              )), 'select');
                    
          $this->insert($gui, 'provider');
        } else {
          $this->insert($providerGui, 'provider');
        }
        
        $s = new SCenter;
        $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
        $s->addStatement(new SqlStatementMono($s->columns['center_id'], sprintf('%%s IN (%s)', $this->_app->auth->getAllowedCenter('list'))));
        $s->addOrder(new SqlStatementAsc($s->columns['name']));
        $s->setColumnsMask(array('center_id','name'));
        $hash = new SqlDataSource(new DataSourceSettings, $s);
        $this->insert(new GuiFormSelect(array(
                      'name'          => 'center',
                      'dataSource'    => $hash,
                      'firstOption'   => $this->_app->textStorage->getText('label.home_allCenter'),
                      'value'         => $this->_app->auth->getActualCenter(),
                      'onchange'      => "$('#fi_centerChange').click();",
                      )), 'center');
        
      }
      
      $this->insertTemplateVar('username', $this->_app->auth->getFullName());
    }
  }
}

?>
