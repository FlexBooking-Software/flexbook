<?php

class ModuleAvailProfile extends ProjectModule {

  protected function _userInsert() {
    if (!$this->_app->auth->haveRight('settings_admin', $this->_app->auth->getActualProvider())) throw new ExceptionUserTextStorage('error.accessDenied');

    $this->_app->auth->setSubSection('availProfile');
  
    $this->setTemplateString('
        <div class="settingsContent">
          <div class="contentTitle">{__label.listAvailProfile_title}</div>
          <div class="listAvailProfile">
            <form action="{%basefile%}" method="post">
              <div>
                <input type="hidden" name="sessid" value="{%sessid%}" />
                {newAvailProfile}
              </div>
            </form>
            {listAvailProfile}
          </div>
        </div>');
    
    $this->insert(new GuiListAvailProfile, 'listAvailProfile');
    
    $this->insert(new GuiFormButton(array(
            'label' => $this->_app->textStorage->getText('button.listAvailProfile_new'),
            'classInput' => 'inputSubmit',
            'action' => 'eAvailProfileEdit',
            'showDiv' => false)), 'newAvailProfile');
  }
}

?>
