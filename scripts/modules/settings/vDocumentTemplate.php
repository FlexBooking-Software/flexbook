<?php

class ModuleDocumentTemplate extends ProjectModule {

  protected function _userInsert() {
    if (!$this->_app->auth->haveRight('settings_admin', $this->_app->auth->getActualProvider())) throw new ExceptionUserTextStorage('error.accessDenied');

    $this->_app->auth->setSubSection('documentTemplate');
  
    $this->setTemplateString('
        <div class="settingsContent">
          <div class="contentTitle">{__label.listDocumentTemplate_title}</div>
          <div class="listDocumentTemplate">
            <form action="{%basefile%}" method="post">
              <div>
                <input type="hidden" name="sessid" value="{%sessid%}" />
                {newDocumentTemplate}
              </div>
            </form>
            {listDocumentTemplate}
          </div>
        </div>');
    
    $this->insert(new GuiListDocumentTemplate, 'listDocumentTemplate');
    
    $this->insert(new GuiFormButton(array(
            'label' => $this->_app->textStorage->getText('button.listDocumentTemplate_new'),
            'classInput' => 'inputSubmit',
            'action' => 'eDocumentTemplateEdit',
            'showDiv' => false)), 'newDocumentTemplate');
  }
}

?>
