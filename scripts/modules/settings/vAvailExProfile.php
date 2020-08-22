<?php

class ModuleAvailExProfile extends ProjectModule {

  protected function _userInsert() {
    if (!$this->_app->auth->haveRight('settings_admin', $this->_app->auth->getActualProvider())) throw new ExceptionUserTextStorage('error.accessDenied');

    $this->_app->auth->setSubSection('availExProfile');
  
    $this->setTemplateString('
        <div class="settingsContent">
          <div class="contentTitle">{__label.listAvailExProfile_title}</div>
          <div class="listAvailProfile">
            <form action="{%basefile%}" method="post">
              <div>
                <input type="hidden" name="sessid" value="{%sessid%}" />
                {newAvailExProfile}
              </div>
            </form>
            {listAvailExProfile}
          </div>
        </div>');
    
    $this->insert(new GuiListAvailExProfile, 'listAvailExProfile');
    
    $this->insert(new GuiFormButton(array(
            'label' => $this->_app->textStorage->getText('button.listAvailExProfile_new'),
            'classInput' => 'inputSubmit',
            'action' => 'eAvailExProfileEdit',
            'showDiv' => false)), 'newAvailExProfile');
  }
}

?>
