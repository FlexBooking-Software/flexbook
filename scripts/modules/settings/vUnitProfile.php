<?php

class ModuleUnitProfile extends ProjectModule {

  protected function _userInsert() {
    if (!$this->_app->auth->haveRight('settings_admin', $this->_app->auth->getActualProvider())) throw new ExceptionUserTextStorage('error.accessDenied');

    $this->_app->auth->setSubSection('unitProfile');
  
    $this->setTemplateString('
        <div class="settingsContent">
          <div class="contentTitle">{__label.listUnitProfile_title}</div>
          <div class="listUnitProfile">
            <form action="{%basefile%}" method="post">
              <div>
                <input type="hidden" name="sessid" value="{%sessid%}" />
                {newUnitProfile}
              </div>
            </form>
            {listUnitProfile}
          </div>
        </div>');
    
    $this->insert(new GuiListUnitProfile, 'listUnitProfile');
    $this->insert(new GuiFormButton(array(
            'label' => $this->_app->textStorage->getText('button.listUnitProfile_new'),
            'classInput' => 'inputSubmit',
            'action' => 'eUnitProfileEdit',
            'showDiv' => false)), 'newUnitProfile');
  }
}

?>
