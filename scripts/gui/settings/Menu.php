<?php

class GuiSettingsMenu extends GuiElement {

  protected function _userRender() {
    if (!$section = $this->_app->auth->getSubSection()) $section = 'profile';
    $selectedHtml = ' class="selected"';
    
    if ($this->_app->auth->isAdministrator()) $action = 'vSettings';
    else $action = 'eSettings';
    
    $template = '';
    
    //$template .= sprintf('<li class="first %s"><a href="{%%basefile%%}?action=vSettings&section=availProfile{%%sessionUrl%%}">{__label.settingsMenu_availProfile}</a></li>', $section=='availProfile'?'selected':'');
    //$template .= sprintf('<li class="separator">|</li><li%s><a href="{%%basefile%%}?action=vSettings&section=availExProfile{%%sessionUrl%%}">{__label.settingsMenu_availProfileException}</a></li>', $section=='availExProfile'?$selectedHtml:'');
    //$template .= sprintf('<li class="separator">|</li><li%s><a href="{%%basefile%%}?action=vSettings&section=unitProfile{%%sessionUrl%%}">{__label.settingsMenu_unitProfile}</a></li>', $section=='unitProfile'?$selectedHtml:'');
    if (!$this->_app->auth->isAdministrator()) $template .= sprintf('<li class="first %s"><a href="{%%basefile%%}?action=eMyCustomerEdit&id=%s{%%sessionUrl%%}">{__label.settingsMenu_profile}</a></li><li class="separator">|</li>', $section=='profile'?'selected':'', $this->_app->auth->getActualProviderCustomer());
    $template .= sprintf('<li class="%s%s"><a href="{%%basefile%%}?action=%s&section=general{%%sessionUrl%%}">{__label.settingsMenu_general}</a></li>', !$this->_app->auth->isAdministrator()?'':'first ', $section=='general'?'selected':'', $action);
    $template .= sprintf('<li class="separator">|</li><li%s><a href="{%%basefile%%}?action=vSettings&section=availability{%%sessionUrl%%}">{__label.settingsMenu_availability}</a></li>', $section=='availability'?$selectedHtml:'');
    $template .= sprintf('<li class="separator">|</li><li%s><a href="{%%basefile%%}?action=vSettings&section=priceList{%%sessionUrl%%}">{__label.settingsMenu_priceList}</a></li>', $section=='priceList'?$selectedHtml:'');
    $template .= sprintf('<li class="separator">|</li><li%s><a href="{%%basefile%%}?action=vSettings&section=ticketList{%%sessionUrl%%}">{__label.settingsMenu_ticketList}</a></li>', $section=='ticketList'?$selectedHtml:'');
    $template .= sprintf('<li class="separator">|</li><li%s><a href="{%%basefile%%}?action=vSettings&section=voucherList{%%sessionUrl%%}">{__label.settingsMenu_voucherList}</a></li>', $section=='voucherList'?$selectedHtml:'');
    $template .= sprintf('<li class="separator">|</li><li%s><a href="{%%basefile%%}?action=vSettings&section=reservationCondition{%%sessionUrl%%}">{__label.settingsMenu_reservationCondition}</a></li>', $section=='reservationCondition'?$selectedHtml:'');
    $template .= sprintf('<li class="separator">|</li><li%s><a href="{%%basefile%%}?action=vSettings&section=notificationTemplate{%%sessionUrl%%}">{__label.settingsMenu_notificationTemplate}</a></li>', $section=='notificationTemplate'?$selectedHtml:'');
    $template .= sprintf('<li class="separator">|</li><li%s><a href="{%%basefile%%}?action=vSettings&section=documentTemplate{%%sessionUrl%%}">{__label.settingsMenu_documentTemplate}</a></li>', $section=='documentTemplate'?$selectedHtml:'');
    $template .= sprintf('<li class="separator">|</li><li%s><a href="{%%basefile%%}?action=vSettings&section=providerPortal{%%sessionUrl%%}">{__label.settingsMenu_providerPortal}</a></li>', $section=='providerPortal'?$selectedHtml:'');
    $template .= sprintf('<li class="separator">|</li><li%s><a href="{%%basefile%%}?action=vSettings&section=tag{%%sessionUrl%%}">{__label.settingsMenu_tag}</a></li>', $section=='tag'?$selectedHtml:'');
    
    $template = "<ul class=\"submenu\">\n".$template."</ul>\n";
    
    $this->setTemplateString($template);
  }
}

?>
