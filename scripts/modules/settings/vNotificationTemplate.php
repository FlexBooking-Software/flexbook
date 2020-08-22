<?php

class ModuleNotificationTemplate extends ProjectModule {

  protected function _userInsert() {
    if (!$this->_app->auth->haveRight('settings_admin', $this->_app->auth->getActualProvider())) throw new ExceptionUserTextStorage('error.accessDenied');

    $this->_app->auth->setSubSection('notificationTemplate');
  
    $this->setTemplateString('
        <div class="settingsContent">
          <div class="contentTitle">{__label.listNotificationTemplate_title}</div>
          <div class="listNotificationTemplate">
            <form action="{%basefile%}" method="post">
              <div>
                <input type="hidden" name="sessid" value="{%sessid%}" />
                {newNotificationTemplate}
              </div>
            </form>
            {listNotificationTemplate}
          </div>
        </div>');
    
    $this->insert(new GuiListNotificationTemplate, 'listNotificationTemplate');
    
    $this->insert(new GuiFormButton(array(
            'label' => $this->_app->textStorage->getText('button.listNotificationTemplate_new'),
            'classInput' => 'inputSubmit',
            'action' => 'eNotificationTemplateEdit',
            'showDiv' => false)), 'newNotificationTemplate');
  }
}

?>
