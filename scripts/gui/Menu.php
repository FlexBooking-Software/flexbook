<?php

class GuiProjectMenu extends GuiElement {

  protected function _userRender() {
    $section = $this->_app->auth->getSection();
    $selectedHtml = ' class="selected"';
    
    $template = '';
    if ($this->_app->auth->getUserId()) {
      if ($this->_app->auth->haveRight('user_admin', $this->_app->auth->getActualProvider())) {
        $template .= sprintf('<li%s><a href="{%%basefile%%}?action=vUser{%%sessionUrl%%}">{__label.menu_user}</a></li>', $section=='user'?$selectedHtml:'');
      }
      if ($this->_app->auth->haveRight('customer_admin', $this->_app->auth->getActualProvider())) {
        if ($template) $template .= '<li class="separator">|</li>';
        $template .= sprintf('<li%s><a href="{%%basefile%%}?action=vCustomer{%%sessionUrl%%}">{__label.menu_customer}</a></li>', $section=='customer'?$selectedHtml:'');
      }
      if ($this->_app->auth->haveRight('commodity_read', $this->_app->auth->getActualProvider())) {
        if ($template) $template .= '<li class="separator">|</li>';
        $template .= sprintf('<li%s><a href="{%%basefile%%}?action=vResource{%%sessionUrl%%}">{__label.menu_resource}</a></li>', $section=='resource'?$selectedHtml:'');
      }
      if ($this->_app->auth->haveRight('commodity_read', $this->_app->auth->getActualProvider())) {
        if ($template) $template .= '<li class="separator">|</li>';
        $template .= sprintf('<li%s><a href="{%%basefile%%}?action=vEvent{%%sessionUrl%%}">{__label.menu_event}</a></li>', $section=='event'?$selectedHtml:'');
      }
      if ($this->_app->auth->haveRight('reservation_admin', $this->_app->auth->getActualProvider())) {
        if ($template) $template .= '<li class="separator">|</li>';
        $template .= sprintf('<li%s><a href="{%%basefile%%}?action=vReservation{%%sessionUrl%%}">{__label.menu_reservation}</a></li>', $section=='reservation'?$selectedHtml:'');
      }
      if ($this->_app->auth->haveRight('settings_admin', $this->_app->auth->getActualProvider())) {
        if ($this->_app->auth->isAdministrator()) $action = 'vSettings';
        else $action = 'eSettings';
        if ($template) $template .= '<li class="separator">|</li>';
        $template .= sprintf('<li%s><a href="{%%basefile%%}?action=%s{%%sessionUrl%%}">{__label.menu_settings}</a></li>', $section=='settings'?$selectedHtml:'', $action);
      }
      global $NOTIFICATION;
      if (in_array($this->_app->auth->getUsername(),explode(',',$NOTIFICATION['adminEmail']))) {
        if ($template) $template .= '<li class="separator">|</li>';
        $template .= sprintf('<li%s><a href="{%%basefile%%}?action=vInvoice{%%sessionUrl%%}">{__label.menu_invoice}</a></li>', $section=='invoice'?$selectedHtml:'');
      }
      if ($this->_app->auth->haveRight('report_admin', $this->_app->auth->getActualProvider())||$this->_app->auth->haveRight('report_reception', $this->_app->auth->getActualProvider())) {
        if ($template) $template .= '<li class="separator">|</li>';
        $template .= sprintf('<li%s><a href="{%%basefile%%}?action=eReport{%%sessionUrl%%}">{__label.menu_report}</a></li>', $section=='report'?$selectedHtml:'');
      }
      
      $template = "<ul class=\"menu\">\n".$template."</ul>{submenu}\n";
    }
    
    $this->setTemplateString($template);
    
    if ($section == 'settings') {
      $this->insert(new GuiSettingsMenu, 'submenu');
    } elseif ($section == 'report') {
      $this->insert(new GuiReportMenu, 'submenu');
    } else $this->insertTemplateVar('submenu', '');
  }
}

?>
