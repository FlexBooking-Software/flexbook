<?php

class ModuleNotificationTemplateEdit extends ProjectModule {

  protected function _userInsert() {
    if (!$this->_app->auth->haveRight('settings_admin', $this->_app->auth->getActualProvider())) throw new ExceptionUserTextStorage('error.accessDenied');

    $this->setTemplateString('
        <div class="settings">
          <div class="settingsContent">{children}</div>
        </div>');
    
    $this->insert(new GuiEditNotificationTemplate);
  }
}

?>
